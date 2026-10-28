<?php

namespace App\Http\Controllers;

use App\Ai\Agents\Researcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class AiSuiteController extends Controller
{
    /**
     * Show the AI Suite agent selector.
     */
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
                'available' => false,
            ],
        ];

        return view('backend.ai-suite.index', compact('agents'));
    }

    /**
     * Show the Researcher chat shell.
     */
    public function researcher(): View
    {
        return view('backend.ai-suite.researcher');
    }

    /**
     * Send a question to the Researcher agent and return its answer.
     */
    public function askResearcher(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:4000'],
        ]);

        try {
            $response = Researcher::make()->prompt($validated['message']);
            $answer = $response->text;

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
                       'message' => $validated['message'],
                       'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'No se pudo obtener una respuesta de Researcher. Revisá la configuración del proveedor de IA e intentá nuevamente.',
            ], 502);
        }
    }
}
