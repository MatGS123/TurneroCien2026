<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchMedicalEvidence implements Tool
{
    public function description(): Stringable|string
    {
        return 'Busca evidencia médica y científica reciente en fuentes confiables usando Tavily.';
    }

    public function handle(Request $request): Stringable|string
    {
        $response = Http::withToken(config('services.tavily.key'))
        ->acceptJson()
        ->post('https://api.tavily.com/search', [
            'query' => $request['query'],
            'search_depth' => 'advanced',
            'chunks_per_source' => 2,
            'max_results' => 8,
            'include_answer' => false,
            'include_raw_content' => false,

            'include_domains' => [
                'pubmed.ncbi.nlm.nih.gov',
               'ncbi.nlm.nih.gov',
               'pmc.ncbi.nlm.nih.gov',
               'cochranelibrary.com',
               'who.int',
               'paho.org',
               'nih.gov',
            ],
        ]);

        $response->throw();

        $results = collect($response->json('results', []))
        ->map(fn ($result) => [
            'title' => $result['title'] ?? null,
            'url' => $result['url'] ?? null,
            'content' => $result['content'] ?? null,
            'score' => $result['score'] ?? null,
        ])
        ->filter(fn ($result) =>
        ! empty($result['title']) &&
        ! empty($result['url']) &&
        ! empty($result['content'])
        )
        ->values()
        ->all();

        if (empty($results)) {
            return json_encode([
                'status' => 'no_results',
                'message' => 'No se encontraron resultados confiables en las fuentes médicas priorizadas.',
            ], JSON_UNESCAPED_UNICODE);
        }

        return json_encode([
            'status' => 'ok',
            'results' => $results,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema
            ->string()
            ->description(
                'Consulta médica o científica específica para buscar evidencia confiable y reciente.'
            )
            ->required(),
        ];
    }
}
