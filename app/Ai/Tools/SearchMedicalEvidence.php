<?php

namespace App\Ai\Tools;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SearchMedicalEvidence
{
    public function search(string $query): array
    {
        $apiKey = config('services.tavily.key');
        if (! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('TAVILY_API_KEY no está configurada.');
        }

        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout((int) config('ai.search_timeout', 45))
            ->post('https://api.tavily.com/search', [
                'query' => $query,
                'search_depth' => 'basic',
                'chunks_per_source' => 1,
                'max_results' => 5,
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

        return collect($response->json('results', []))
            ->map(fn ($result) => [
                'title' => $result['title'] ?? null,
                'url' => $result['url'] ?? null,
                'content' => $result['content'] ?? null,
                'score' => $result['score'] ?? null,
            ])
            ->filter(fn ($result) => ! empty($result['title']) && ! empty($result['url']) && ! empty($result['content']))
            ->values()
            ->all();
    }
}
