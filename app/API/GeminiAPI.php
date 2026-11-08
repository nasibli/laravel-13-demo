<?php

namespace App\API;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class GeminiAPI
{
    private const string BASE_URL = 'https://generativelanguage.googleapis.com/v1beta';

    /**
     * Отправляет промпт в Gemini с включёнными url context и поиском Google и возвращает json строку ответа.
     *
     * Инструменты нужны, чтобы модель могла открыть страницу по url из промпта.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function ask(string $prompt): string
    {
        $response = Http::baseUrl(self::BASE_URL)
            ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
            ->acceptJson()
            ->timeout((int) config('services.gemini.timeout'))
            ->post('models/'.config('services.gemini.model').':generateContent', [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'tools' => [
                    ['url_context' => (object) []],
                    ['google_search' => (object) []],
                ],
            ])
            ->throw();

        return collect($response->json('candidates.0.content.parts', []))
            ->reject(fn (array $part): bool => $part['thought'] ?? false)
            ->pluck('text')
            ->filter()
            ->implode('');
    }
}
