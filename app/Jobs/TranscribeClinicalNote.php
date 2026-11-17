<?php

namespace App\Jobs;

use App\Exceptions\TranscriptionException;
use App\Models\ClinicalNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\AiManager;
use Laravel\Ai\Files\Audio;
use Throwable;

class TranscribeClinicalNote implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 900;

    public function __construct(public int $clinicalNoteId)
    {
    }

    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(AiManager $ai): void
    {
        $note = ClinicalNote::find($this->clinicalNoteId);
        if (! $note || in_array($note->status, ['transcribed', 'organized', 'confirmed'], true)) {
            return;
        }

        $disk = Storage::disk(config('ai.scribe_audio_disk', 'local'));
        $audioPath = $note->audio_path;
        if (blank($audioPath) || ! $disk->exists($audioPath)) {
            $this->markFailed($note);
            return;
        }

        $note->update(['status' => 'transcribing', 'error_message' => null]);

        try {
            $response = $ai->transcriptionProvider('groq')->transcribe(
                Audio::fromPath($disk->path($audioPath)),
                language: config('ai.scribe_language', 'es'),
                model: config('ai.scribe_stt_model', 'whisper-large-v3'),
                timeout: (int) config('ai.scribe_timeout', 180),
            );

            $text = trim($response->text);
            if ($text === '') {
                throw new TranscriptionException('Groq no devolvió texto. Verificá que el audio se escuche bien.');
            }
            if (mb_strlen($text) > (int) config('ai.scribe_max_transcription_chars', 12000)) {
                throw new TranscriptionException('La transcripción supera el límite de caracteres permitido. Grabá un audio más corto.');
            }

            // 1) PRIMERO se guarda la transcripción en la base de datos.
            //    Si este update falla, el audio sigue en disco y el reintento puede volver a procesarlo.
            $note->update([
                'status' => 'transcribed',
                'transcription' => $text,
                'transcribed_at' => now(),
                'error_message' => null,
            ]);
        } catch (TranscriptionException $exception) {
            // Error permanente: reintentar gastaría cuota de Groq sin cambiar el resultado.
            $this->failPermanently($note, $exception->getMessage(), $disk);
            return;
        } catch (Throwable $exception) {
            try {
                $note->update([
                    'status' => 'queued',
                    'error_message' => 'No se pudo transcribir todavía. Se reintentará automáticamente.',
                ]);
            } catch (Throwable $updateException) {
                Log::warning('Scribe could not reset note status.', [
                    'clinical_note_id' => $note->id,
                    'exception_class' => $updateException::class,
                ]);
            }
            Log::warning('Scribe transcription attempt failed.', [
                'clinical_note_id' => $note->id,
                'exception_class' => $exception::class,
                'code' => (string) $exception->getCode(),
            ]);
            throw $exception;
        }

        // 2) RECIÉN DESPUÉS se borra el audio. Un fallo acá no reintenta la transcripción:
        //    queda registrado y el comando scribe:clean-audio lo barre por antigüedad.
        $this->deleteAudio($note, $disk);
    }

    public function failed(Throwable $exception): void
    {
        $note = ClinicalNote::find($this->clinicalNoteId);
        if (! $note || in_array($note->status, ['transcribed', 'organized', 'confirmed'], true)) {
            return;
        }

        $this->failPermanently(
            $note,
            'No se pudo transcribir el audio. Podés volver a intentarlo con la copia local.',
            Storage::disk(config('ai.scribe_audio_disk', 'local'))
        );
    }

    private function deleteAudio(ClinicalNote $note, FilesystemAdapter $disk): void
    {
        $path = $note->audio_path;
        if (blank($path)) {
            return;
        }

        try {
            $disk->delete($path);
            if (! $disk->exists($path)) {
                $note->update(['audio_path' => null]);
                return;
            }
            Log::warning('Scribe temporary audio could not be deleted.', ['clinical_note_id' => $note->id]);
        } catch (Throwable $exception) {
            Log::warning('Scribe audio cleanup failed.', [
                'clinical_note_id' => $note->id,
                'exception_class' => $exception::class,
            ]);
        }
    }

    private function failPermanently(ClinicalNote $note, string $message, FilesystemAdapter $disk): void
    {
        if (filled($note->audio_path)) {
            try {
                $disk->delete($note->audio_path);
            } catch (Throwable $cleanupException) {
                Log::warning('Scribe audio cleanup failed.', [
                    'clinical_note_id' => $note->id,
                    'exception_class' => $cleanupException::class,
                ]);
            }
        }

        $note->update([
            'status' => 'failed',
            'audio_path' => null,
            'error_message' => $message,
        ]);
    }

    private function markFailed(ClinicalNote $note): void
    {
        $note->update([
            'status' => 'failed',
            'audio_path' => null,
            'error_message' => 'El audio temporal ya no está disponible. Podés volver a cargar la copia local.',
        ]);
    }
}
