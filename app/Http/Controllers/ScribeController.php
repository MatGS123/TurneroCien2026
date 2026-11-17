<?php

namespace App\Http\Controllers;

use App\Ai\Agents\Scribe;
use App\Jobs\TranscribeClinicalNote;
use App\Models\Appointment;
use App\Models\ClinicalNote;
use App\Services\AudioDurationValidator;
use App\Services\ClinicalNoteDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Ai\Enums\Lab;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ScribeController extends Controller
{
    public function __construct(
        private ClinicalNoteDocumentService $documents,
        private AudioDurationValidator $audioDuration,
    ) {
    }

    public function index(): View
    {
        $user = auth()->user();
        if ($user->hasRole('superadmin')) {
            $appointments = Appointment::latest('booking_date')->latest('booking_time')->limit(100)->get();
        } else {
            $employee = $this->employeeOrAbort();
            $appointments = $employee->appointments()
                ->latest('booking_date')->latest('booking_time')->limit(100)->get();
        }

        return view('backend.ai-suite.scribe', compact('appointments'));
    }

    public function transcribe(Request $request): JsonResponse
    {
        $user = auth()->user();
        $employee = $user->hasRole('superadmin') ? null : $this->employeeOrAbort();
        $validated = $request->validate([
            'appointment_id' => ['required', 'integer'],
            'audio' => ['required', 'file', 'max:25600', 'mimetypes:audio/webm,video/webm,audio/ogg,application/ogg,audio/wav,audio/x-wav,audio/mpeg,audio/mp4,audio/x-m4a,audio/aac,video/mp4'],
        ]);
        $appointment = $user->hasRole('superadmin')
            ? Appointment::findOrFail($validated['appointment_id'])
            : $employee->appointments()->findOrFail($validated['appointment_id']);
        $file = $request->file('audio');
        $diskName = config('ai.scribe_audio_disk', 'local');
        $disk = Storage::disk($diskName);
        $path = null;

        try {
            $path = $file->store('scribe/tmp', $diskName);
            $this->audioDuration->validate($disk->path($path), (int) config('ai.scribe_max_duration_seconds', 600));

            $note = DB::transaction(function () use ($appointment, $employee, $path) {
                return ClinicalNote::create([
                    'user_id' => auth()->id(),
                    'employee_id' => $appointment->employee_id,
                    'status' => 'queued',
                    'audio_path' => $path,
                    'context' => [
                        'appointment_id' => $appointment->id,
                        'employee_id' => $appointment->employee_id,
                    ],
                ]);
            });

            try {
                TranscribeClinicalNote::dispatch($note->id)
                    ->onQueue(config('ai.scribe_queue', 'default'))
                    ->afterCommit();
            } catch (Throwable $dispatchException) {
                $disk->delete($path);
                $note->delete();
                throw $dispatchException;
            }

            return response()->json(['note_id' => $note->id, 'status' => $note->status]);
        } catch (Throwable $exception) {
            if ($path && $disk->exists($path)) {
                $disk->delete($path);
            }
            Log::warning('Scribe audio intake failed.', [
                'user_id' => auth()->id(),
                'exception_class' => $exception::class,
                'code' => (string) $exception->getCode(),
            ]);
            return response()->json(['message' => $this->safeUploadMessage($exception)], 422);
        }
    }

    public function status(ClinicalNote $clinicalNote): JsonResponse
    {
        $this->authorize('view', $clinicalNote);

        return response()->json($this->notePayload($clinicalNote, ['transcription', 'organized_content', 'error_message']));
    }

    public function organize(Request $request, ClinicalNote $clinicalNote): JsonResponse
    {
        $this->authorize('update', $clinicalNote);
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:180'],
            'transcription' => ['required', 'string', 'min:2', 'max:12000'],
        ]);

        try {
            $provider = config('ai.scribe_provider', 'groq');
            if ($provider !== Lab::Groq->value) {
                throw new \RuntimeException('Scribe solo admite el proveedor Groq.');
            }

            $response = retry(2, fn () => Scribe::make()->prompt(
                Scribe::promptFor($validated['transcription']),
                provider: Lab::Groq,
                model: config('ai.scribe_text_model', 'openai/gpt-oss-120b'),
            ), 2000, fn (Throwable $exception) => (int) $exception->getCode() === 429);
            $organized = trim((string) ($response->text ?? $response));
            if ($organized === '') {
                throw new \RuntimeException('Groq no devolvió una nota organizada.');
            }

            $clinicalNote->update([
                'title' => $validated['title'] ?: 'Nota clínica',
                'transcription' => $validated['transcription'],
                'organized_content' => $organized,
                'status' => 'organized',
                'organized_at' => now(),
                'error_message' => null,
            ]);

            return response()->json([
                'note' => $this->notePayload($clinicalNote->fresh(), ['transcription', 'organized_content']),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Scribe organization failed.', [
                'clinical_note_id' => $clinicalNote->id,
                'user_id' => auth()->id(),
                'exception_class' => $exception::class,
                'code' => (string) $exception->getCode(),
            ]);
            return response()->json([
                'message' => 'Groq no pudo organizar la nota. La transcripción permanece disponible para reintentar.',
            ], 502);
        }
    }

    public function confirm(Request $request, ClinicalNote $clinicalNote): JsonResponse
    {
        $this->authorize('confirm', $clinicalNote);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'organized_content' => ['required', 'string', 'max:50000'],
        ]);
        $user = auth()->user();
        $employee = $user->hasRole('superadmin') ? null : $this->employeeOrAbort();
        $appointmentId = (int) data_get($clinicalNote->context, 'appointment_id');
        $appointment = $user->hasRole('superadmin')
            ? Appointment::findOrFail($appointmentId)
            : $employee->appointments()->findOrFail($appointmentId);

        $clinicalNote->update([
            'title' => $validated['title'],
            'organized_content' => $validated['organized_content'],
            'appointment_id' => $appointment->id,
            'employee_id' => $appointment->employee_id,
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'error_message' => null,
        ]);

        // La nota ya está confirmada y guardada. La exportación se puede reintentar sin perderla.
        return $this->runExport($clinicalNote->fresh());
    }

    /** Reintenta TXT/DOCX/PDF de una nota ya confirmada. */
    public function export(ClinicalNote $clinicalNote): JsonResponse
    {
        $this->authorize('export', $clinicalNote);

        return $this->runExport($clinicalNote);
    }

    public function download(ClinicalNote $clinicalNote, string $format): StreamedResponse
    {
        $this->authorize('download', $clinicalNote);
        abort_unless(in_array($format, ['txt', 'docx', 'pdf'], true), 404);
        $path = $clinicalNote->{$format.'_path'};
        abort_unless(filled($path), 404);

        return Storage::disk(config('ai.scribe_document_disk', 'local'))
            ->download($path, Str::slug($clinicalNote->title ?: 'nota-clinica').'.'.$format);
    }

    private function runExport(ClinicalNote $note): JsonResponse
    {
        try {
            $paths = $this->documents->generate($note);
            $note->update([
                'txt_path' => $paths['txt'],
                'docx_path' => $paths['docx'],
                'pdf_path' => $paths['pdf'],
            ]);

            return response()->json([
                'export_ok' => true,
                'note' => $this->notePayload($note->fresh(), ['title', 'organized_content']),
            ]);
        } catch (Throwable $exception) {
            // Sin archivos a medias: el servicio ya borró lo que alcanzó a escribir.
            $note->update(['txt_path' => null, 'docx_path' => null, 'pdf_path' => null]);
            Log::warning('Scribe export failed.', [
                'clinical_note_id' => $note->id,
                'user_id' => auth()->id(),
                'exception_class' => $exception::class,
                'code' => (string) $exception->getCode(),
            ]);

            return response()->json([
                'export_ok' => false,
                'message' => 'La nota quedó confirmada, pero no se pudieron generar los archivos. Podés reintentar la exportación.',
                'note' => $this->notePayload($note->fresh(), ['title']),
            ]);
        }
    }

    /** Campos seguros para el navegador (nunca rutas internas de disco). */
    private function notePayload(ClinicalNote $note, array $extra = []): array
    {
        return $note->only(array_merge(['id', 'status', 'title'], $extra)) + [
            'has_txt' => filled($note->txt_path),
            'has_docx' => filled($note->docx_path),
            'has_pdf' => filled($note->pdf_path),
        ];
    }

    private function employeeOrAbort()
    {
        $user = auth()->user();
        abort_unless($user?->hasRole('empleado') && $user->employee, 403);
        return $user->employee;
    }

    private function safeUploadMessage(Throwable $exception): string
    {
        $message = $exception->getMessage();
        if (str_contains($message, '10 minutos') || str_contains($message, 'ffprobe') || str_contains($message, 'duración')) {
            return $message;
        }
        return 'No se pudo recibir el audio. Verificá que sea un archivo de audio válido y que dure como máximo 10 minutos.';
    }
}
