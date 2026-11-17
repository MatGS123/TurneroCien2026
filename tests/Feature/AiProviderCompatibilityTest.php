<?php

use App\Services\Ai\OpenAiCompatibleClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('envía las solicitudes de Consultant a OpenRouter con restricciones ZDR', function () {
    config([
        'ai.providers.openrouter.key' => 'test-openrouter-key',
        'ai.providers.openrouter.url' => 'https://openrouter.ai/api/v1',
        'ai.request_timeout' => 30,
    ]);

    Http::fake([
        'https://openrouter.ai/api/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => '{"message":"ok"}']]],
        ], 200),
    ]);

    $answer = app(OpenAiCompatibleClient::class)->chat(
        'openrouter',
        'deepseek/deepseek-chat',
        [['role' => 'user', 'content' => 'Prueba sin datos clínicos']],
        ['response_format' => ['type' => 'json_object']]
    );

    expect($answer)->toBe('{"message":"ok"}');

    Http::assertSent(function (Request $request) {
        $data = $request->data();

        return $request->url() === 'https://openrouter.ai/api/v1/chat/completions'
            && $request->hasHeader('Authorization', 'Bearer test-openrouter-key')
            && ($data['provider']['zdr'] ?? false) === true
            && ($data['provider']['data_collection'] ?? null) === 'deny'
            && ($data['provider']['allow_fallbacks'] ?? true) === false;
    });
});

it('obtiene transcripciones mediante el endpoint oficial de Groq', function () {
    config([
        'ai.providers.groq.key' => 'test-groq-key',
        'ai.providers.groq.url' => 'https://api.groq.com/openai/v1',
    ]);

    $audio = tempnam(sys_get_temp_dir(), 'cien-audio-');
    file_put_contents($audio, 'audio de prueba no clínico');

    try {
        Http::fake([
            'https://api.groq.com/openai/v1/audio/transcriptions' => Http::response([
                'text' => 'Texto de prueba',
            ], 200),
        ]);

        $text = app(OpenAiCompatibleClient::class)->transcribeWithGroq(
            $audio,
            'whisper-large-v3',
            'es',
            30
        );

        expect($text)->toBe('Texto de prueba');

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.groq.com/openai/v1/audio/transcriptions'
                && $request->hasHeader('Authorization', 'Bearer test-groq-key');
        });
    } finally {
        @unlink($audio);
    }
});
