<?php
namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait AnalyseOpenAITrait
{
    public function uploadToOpenAI($filePath, $purpose)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->attach(
            'file', file_get_contents($filePath), basename($filePath)
        )->post('https://api.openai.com/v1/files', [
            'purpose' => $purpose,
        ]);

        return $response->json()['id'] ?? null;
    }

    public function createAssistant($model, $instructions)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/assistants', [
            'model' => $model,
            'instructions' => $instructions,
        ]);

        return $response->json()['id'] ?? null;
    }

    public function createThread()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.openai.com/v1/threads');

        return $response->json()['id'] ?? null;
    }

    public function postMessageToThread($threadId, $fileId, $type)
    {
        $url = "https://api.openai.com/v1/threads/{$threadId}/messages";

        $content = $type === 'pdf'
            ? [
                'role' => 'user',
                'content' => 'Analyse ce document et vérifie s’il correspond à la matière et classe.',
                'file_ids' => [$fileId],
            ]
            : [
                'role' => 'user',
                'content' => [
                    ['type' => 'text', 'text' => 'Analyse cette image et indique si elle correspond au contenu pédagogique.'],
                    ['type' => 'image_file', 'image_file' => ['file_id' => $fileId]],
                ],
            ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post($url, $content);

        return $response->successful();
    }

    public function runAssistant($threadId, $assistantId)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post("https://api.openai.com/v1/threads/{$threadId}/runs", [
            'assistant_id' => $assistantId,
        ]);

        return $response->json()['id'] ?? null;
    }

    public function getFinalMessage($threadId)
    {
        sleep(5); // ou boucle avec retries

        $messages = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
        ])->get("https://api.openai.com/v1/threads/{$threadId}/messages");

        return $messages->json()['data'][0]['content'][0]['text']['value'] ?? null;
    }
}
