<?php

namespace App\Http\Controllers;

use App\Ai\Agents\Consultant;
use App\Models\Appointment;
use App\Models\Employee;
use App\Models\Estudio;
use App\Services\DocumentTextExtractor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

class ConsultantController extends Controller
{
    public function __construct(private DocumentTextExtractor $extractor)
    {
    }

    public function index(): View
    {
        $patients = $this->patientOptions();

        return view('backend.ai-suite.consultant', compact('patients'));
    }

    public function studies(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_key' => ['required', 'string', 'max:255'],
        ]);

        $appointments = $this->patientAppointments($validated['patient_key'])->with('estudios')->get();
        $studies = $appointments->flatMap(function (Appointment $appointment) {
            return $appointment->estudios->map(fn (Estudio $study) => [
                'id' => $study->id,
                'name' => $study->nombre_estudio,
                'file_type' => strtolower(pathinfo($study->archivo_pdf, PATHINFO_EXTENSION)),
                                               'date' => optional($study->created_at)->format('d/m/Y'),
                                               'appointment_date' => $appointment->booking_date,
                                               'observations' => $study->observaciones,
            ]);
        })->values();

        return response()->json(['studies' => $studies]);
    }

    public function analyze(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_key' => ['required', 'string', 'max:255'],
            'study_ids' => ['required', 'array', 'min:1', 'max:5'],
            'study_ids.*' => ['integer', 'distinct'],
            'question' => ['nullable', 'string', 'max:2000'],
            'analysis_type' => ['nullable', 'in:document_analysis,comparison,extraction,document_question'],
        ]);

        $appointments = $this->patientAppointments($validated['patient_key'])->with('estudios')->get();
        $allowedAppointmentIds = $appointments->pluck('id');
        $studies = Estudio::with('appointment')
        ->whereIn('id', $validated['study_ids'])
        ->whereIn('appointment_id', $allowedAppointmentIds)
        ->get();

        if ($studies->count() !== count($validated['study_ids'])) {
            return response()->json(['message' => 'Uno o más estudios no están disponibles para este usuario.'], 403);
        }

        $documents = [];
        $warnings = [];

        foreach ($studies as $study) {
            try {
                $extracted = $this->extractor->extract($study);
                $documents[] = [
                    'study' => $study,
                    'text' => $extracted['text'],
                    'pages' => $extracted['pages'],
                    'extension' => $extracted['extension'],
                ];
            } catch (Throwable $exception) {
                $warnings[] = $study->nombre_estudio . ': ' . $exception->getMessage();
                Log::warning('Consultant could not extract a study.', [
                    'study_id' => $study->id,
                    'user_id' => $request->user()?->id,
                             'exception' => $exception,
                ]);
            }
        }

        if ($documents === []) {
            return response()->json([
                'message' => 'No se pudo extraer texto legible de los documentos seleccionados.',
                'warnings' => $warnings,
            ], 422);
        }

        $prompt = $this->buildPrompt($documents, $validated['question'] ?? null, $validated['analysis_type'] ?? null, $warnings);

        try {
            $response = $this->askConsultant($prompt);

            if (! $response instanceof StructuredAgentResponse) {
                throw new \RuntimeException('El proveedor no devolvió una respuesta estructurada.');
            }

            $analysis = $response->structured;
            $analysis['warnings'] = array_values(array_unique([
                ...$warnings,
                ...($analysis['warnings'] ?? []),
            ]));
            $analysis['chart'] = $this->sanitizeChart($analysis['chart'] ?? []);

            return response()->json([
                'analysis' => $analysis,
                'documents' => collect($documents)->map(fn (array $document) => [
                    'id' => $document['study']->id,
                    'name' => $document['study']->nombre_estudio,
                    'type' => $document['extension'],
                ])->values(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Consultant failed to analyze documents.', [
                'user_id' => $request->user()?->id,
                       'study_ids' => $validated['study_ids'],
                       'exception' => $exception,
            ]);

            return response()->json([
                'message' => config('app.debug')
                ? 'Consultant no pudo completar el análisis: ' . $exception->getMessage()
                : 'No se pudo analizar la documentación. Verificá la configuración del proveedor de IA e intentá nuevamente.',
            ], 502);
        }
    }

    private function patientOptions()
    {
        return $this->authorizedAppointments()
        ->get(['id', 'user_id', 'name', 'email'])
        ->groupBy(fn (Appointment $appointment) => $appointment->user_id
        ? 'user:' . $appointment->user_id
        : 'guest:' . base64_encode((string) $appointment->email))
        ->map(function ($appointments, string $key) {
            $first = $appointments->first();

            return [
                'key' => $key,
                'name' => $first->name,
                'email' => $first->email,
                'study_count' => $appointments->sum(fn (Appointment $appointment) => $appointment->estudios()->count()),
            ];
        })->values();
    }

    private function authorizedAppointments()
    {
        $query = Appointment::query();
        $user = auth()->user();

        if ($user?->hasRole('employee')) {
            $employeeId = Employee::where('user_id', $user->id)->value('id');
            $query->where('employee_id', $employeeId ?: 0);
        }

        return $query;
    }

    private function patientAppointments(string $key)
    {
        $query = $this->authorizedAppointments();

        if (preg_match('/^user:(\d+)$/', $key, $matches)) {
            return $query->where('user_id', (int) $matches[1]);
        }

        if (str_starts_with($key, 'guest:')) {
            $email = base64_decode(substr($key, 6), true);

            return $query->whereNull('user_id')->where('email', $email ?: '');
        }

        return $query->whereRaw('1 = 0');
    }

    private function buildPrompt(array $documents, ?string $question, ?string $analysisType, array $warnings): string
    {
        $sections = [];

        foreach ($documents as $document) {
            $study = $document['study'];
            $sections[] = sprintf(
                "<DOCUMENTO nombre=\"%s\" fecha=\"%s\" tipo=\"%s\">\n%s\n</DOCUMENTO>",
                $this->promptValue($study->nombre_estudio),
                                  $study->appointment?->booking_date ?? $study->created_at?->format('Y-m-d') ?? '',
                                  $document['extension'],
                                  $document['text']
            );
        }

        $instruction = $question ?: match ($analysisType) {
            'comparison' => 'Compara los documentos seleccionados e identifica cambios observables entre valores equivalentes.',
            'extraction' => 'Extrae los resultados, parámetros, unidades, fechas y rangos de referencia documentados.',
            default => 'Resume y analiza los documentos seleccionados.',
        };

        return "Tipo de análisis: " . ($analysisType ?: 'document_analysis') . "\n"
        . "Solicitud del profesional: {$instruction}\n"
        . "Documentos con problemas de extracción: " . (empty($warnings) ? 'ninguno' : implode('; ', $warnings)) . "\n\n"
        . implode("\n\n", $sections);
    }

    private function askConsultant(string $prompt): mixed
    {
        $provider = Lab::from(config('ai.consultant_provider', 'openai'));

        try {
            $providerKey = config('ai.providers.' . $provider->value . '.key');

            if ($provider === Lab::OpenRouter && blank($providerKey)) {
                throw new \RuntimeException('OPENROUTER_API_KEY no está configurada.');
            }

            return Consultant::make()->prompt(
                $prompt,
                provider: $provider,
                model: config('ai.consultant_model')
            );
        } catch (Throwable $primaryException) {
            $openAiKey = config('ai.providers.openai.key');

            if ($provider !== Lab::OpenAI && filled($openAiKey)) {
                Log::warning('Consultant provider failed; retrying with OpenAI fallback.', [
                    'provider' => $provider->value,
                    'exception' => $primaryException,
                ]);

                return Consultant::make()->prompt(
                    $prompt,
                    provider: Lab::OpenAI,
                    model: env('CONSULTANT_FALLBACK_MODEL', 'gpt-5-mini')
                );
            }

            throw $primaryException;
        }
    }

    private function promptValue(?string $value): string
    {
        return str_replace(['"', '<', '>'], ['&quot;', '&lt;', '&gt;'], (string) $value);
    }

    private function sanitizeChart(array $chart): array
    {
        if (! ($chart['available'] ?? false)) {
            return [
                'available' => false,
                'type' => $chart['type'] ?? 'line',
                'title' => $chart['title'] ?? '',
                'parameter' => $chart['parameter'] ?? '',
                'unit' => $chart['unit'] ?? '',
                'x_axis_label' => $chart['x_axis_label'] ?? 'Fecha',
                'y_axis_label' => $chart['y_axis_label'] ?? 'Valor',
                'data_points' => [],
                'reason_unavailable' => $chart['reason_unavailable'] ?? 'No hay suficientes mediciones verificables.',
            ];
        }

        $points = collect($chart['data_points'] ?? [])
        ->filter(fn ($point) => isset($point['date'], $point['value']) && is_numeric($point['value']))
        ->take(50)
        ->map(fn ($point) => [
            'date' => (string) $point['date'],
              'value' => (float) $point['value'],
              'source_document' => (string) ($point['source_document'] ?? ''),
        ])->values()->all();

        if (count($points) < 2) {
            $chart['available'] = false;
            $chart['data_points'] = [];
            $chart['reason_unavailable'] = 'No hay al menos dos mediciones numéricas verificables.';
        } else {
            $chart['data_points'] = $points;
        }

        return $chart;
    }
}
