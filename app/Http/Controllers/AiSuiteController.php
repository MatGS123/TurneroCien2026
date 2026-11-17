<?php

namespace App\Http\Controllers;

use App\Ai\Agents\Researcher;
use App\Ai\Tools\SearchMedicalEvidence;
use App\Services\Ai\OpenAiCompatibleClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AiSuiteController extends Controller
{
    public function __construct(
        private OpenAiCompatibleClient $ai,
        private SearchMedicalEvidence $evidence,
    ) {
    }

    public function index(): View
    {
        $agents = [
            [
                'id' => 'researcher',
                'name' => 'Researcher',
                'description' => 'Agente de investigación médica que busca, evalúa y sintetiza evidencia científica reciente y confiable para apoyar al profesional de la salud.',
                'icon' => 'fas fa-search',
                'available' => true,
                'action_url' => route('ai.researcher'),
            ],
            [
                'id' => 'consultant',
                'name' => 'Consultant',
                'description' => 'Agente de análisis documental que resume, extrae resultados y compara estudios seleccionados por el profesional.',
                'icon' => 'fas fa-comments',
                'available' => true,
                'action_url' => route('ai.consultant.documents'),
            ],
            [
                'id' => 'scribe',
                'name' => 'Scribe',
                'description' => 'Agente orientado a ayudar con la redacción y organización de contenidos profesionales.',
                'icon' => 'fas fa-pen',
                'available' => true,
                'action_url' => route('ai.scribe'),
            ],
        ];

        return view('backend.ai-suite.index', compact('agents'));
    }

    public function researcher(): View
    {
        return view('backend.ai-suite.researcher');
    }

    public function askResearcher(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:4000'],
        ]);

        try {
            $results = $this->evidence->search($validated['message']);
            $researchPayload = [
                'consulta' => $validated['message'],
                'resultados_recuperados' => $results,
            ];

            if ($results === []) {
                $researchPayload['nota'] = 'La búsqueda no recuperó fuentes verificables. No respondas con conocimiento interno ni inventes fuentes; explica que no hay evidencia recuperada suficiente.';
            }

            $answer = $this->ai->chat(
                'openrouter',
                config('ai.researcher_model', 'openai/gpt-4o-mini'),
                [
                    ['role' => 'system', 'content' => Researcher::instructions()],
                    ['role' => 'user', 'content' => "Analiza esta consulta usando exclusivamente los resultados reales que siguen.\n\n".json_encode($researchPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
                ],
                ['temperature' => 0.2]
            );

            return response()->json([
                'answer' => $answer,
                'answer_html' => Str::markdown($answer, [
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ]),
            ]);
        } catch (Throwable $exception) {
            Log::error('Researcher failed to answer a question.', [
                'user_id' => $request->user()?->id,
                'exception_class' => $exception::class,
                'code' => (string) $exception->getCode(),
            ]);

            return response()->json([
                'message' => 'No se pudo obtener una respuesta de Researcher. Revisá la configuración de OpenRouter y Tavily e intentá nuevamente.',
            ], 502);
        }
    }
}
