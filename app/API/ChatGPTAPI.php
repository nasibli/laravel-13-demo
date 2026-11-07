<?php

namespace App\API;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class ChatGPTAPI
{
    private const string BASE_URL = 'https://api.openai.com/v1';

    /**
     * Отправляет промпт в ChatGPT с включённым веб-поиском и возвращает текст ответа.
     *
     * Веб-поиск нужен, чтобы модель могла открыть страницу по url из промпта.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function ask(string $prompt): string
    {
        $response = Http::baseUrl(self::BASE_URL)
            ->withToken(config('services.openai.key'))
            ->acceptJson()
            ->timeout((int) config('services.openai.timeout'))
            ->post('responses', [
                'model' => config('services.openai.model'),
                'input' => $prompt,
                'tools' => [['type' => 'web_search']],
            ])
            ->throw();

        return collect($response->json('output', []))
            ->where('type', 'message')
            ->flatMap(fn (array $message): array => $message['content'] ?? [])
            ->where('type', 'output_text')
            ->pluck('text')
            ->implode('');
    }
}
