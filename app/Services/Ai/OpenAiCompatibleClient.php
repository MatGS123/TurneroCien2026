<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * HTTP client for OpenAI-compatible providers used by CIEN.
 * Does not log prompts, audio, transcripts, documents or provider responses.
 */
class OpenAiCompatibleClient
{
    public function chat(string $provider, string $model, array $messages, array $options = []): string
    {
        $providerConfig = config('ai.providers.'.$provider, []);
        $apiKey = $providerConfig['key'] ?? null;
        $baseUrl = rtrim((string) ($providerConfig['url'] ?? ''), '/');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException(strtoupper($provider).'_API_KEY no está configurada.');
        }

        if ($baseUrl === '') {
            throw new RuntimeException('No está configurada la URL del proveedor '.strtoupper($provider).'.');
        }

        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
        ], $options);

        if ($provider === 'openrouter') {
            // Privacy policy from the previous Laravel AI SDK providerOptions.
            $payload['provider'] = array_merge(
                $payload['provider'] ?? [],
                [
                    'zdr' => true,
                    'data_collection' => 'deny',
                    'allow_fallbacks' => false,
                ]
            );
        }

        $request = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('ai.request_timeout', 180));

        if ($provider === 'openrouter') {
            $request = $request->withHeaders([
                'HTTP-Referer' => (string) config('app.url'),
                'X-Title' => 'CIEN Formosa',
            ]);
        }

        $response = retry(
            2,
            function () use ($request, $baseUrl, $payload) {
                return $request->post($baseUrl.'/chat/completions', $payload)->throw();
            },
            1500,
            function (Throwable $exception) {
                if (! $exception instanceof RequestException || ! $exception->response) {
                    return false;
                }

                $status = $exception->response->status();

                return $status === 429 || $status >= 500;
            }
        );

        $content = data_get($response->json(), 'choices.0.message.content');

        if (is_array($content)) {
            $content = collect($content)
                ->filter(fn ($part) => is_array($part) && ($part['type'] ?? null) === 'text')
                ->pluck('text')
                ->implode("\n");
        }

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('El proveedor no devolvió contenido de texto.');
        }

        return trim($content);
    }

    public function transcribeWithGroq(string $absolutePath, string $model, string $language = 'es', int $timeout = 180): string
    {
        $apiKey = config('ai.providers.groq.key');
        $baseUrl = rtrim((string) config('ai.providers.groq.url'), '/');

        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('GROQ_API_KEY no está configurada.');
        }

        if ($baseUrl === '') {
            throw new RuntimeException('No está configurada la URL de Groq.');
        }

        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            throw new RuntimeException('El archivo de audio temporal no está disponible.');
        }

        $stream = fopen($absolutePath, 'rb');
        if ($stream === false) {
            throw new RuntimeException('No se pudo leer el archivo de audio temporal.');
        }

        try {
            $response = retry(
                2,
                function () use ($apiKey, $baseUrl, $stream, $absolutePath, $model, $language, $timeout) {
                    if (ftell($stream) !== 0) {
                        rewind($stream);
                    }

                    return Http::withToken($apiKey)
                        ->acceptJson()
                        ->timeout($timeout)
                        ->attach('file', $stream, basename($absolutePath))
                        ->post($baseUrl.'/audio/transcriptions', [
                            'model' => $model,
                            'language' => $language,
                            'response_format' => 'json',
                            'temperature' => 0,
                        ])
                        ->throw();
                },
                1500,
                function (Throwable $exception) {
                    if (! $exception instanceof RequestException || ! $exception->response) {
                        return false;
                    }

                    $status = $exception->response->status();

                    return $status === 429 || $status >= 500;
                }
            );
        } finally {
            fclose($stream);
        }

        $text = $response->json('text');

        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('Groq no devolvió texto de transcripción.');
        }

        return trim($text);
    }
}
