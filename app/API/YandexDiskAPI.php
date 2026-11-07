<?php

namespace App\API;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class YandexDiskAPI
{
    private const string BASE_URL = 'https://cloud-api.yandex.net/v1/disk';

    /**
     * Создаёт папку и возвращает false, если папка с таким путём уже существует.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function createFolder(string $path): bool
    {
        $response = $this->client()
            ->withQueryParameters(['path' => $path])
            ->put('resources');

        if ($response->status() === 409) {
            return false;
        }

        $response->throw();

        return true;
    }

    /**
     * Загружает содержимое файла по указанному пути, перезаписывая существующий файл.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function uploadFile(string $path, string $contents): void
    {
        $uploadUrl = $this->client()
            ->withQueryParameters(['path' => $path, 'overwrite' => 'true'])
            ->get('resources/upload')
            ->throw()
            ->json('href');

        Http::withBody($contents, 'text/plain; charset=utf-8')
            ->put($uploadUrl)
            ->throw();
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->withHeaders(['Authorization' => 'OAuth '.config('services.yandex_disk.token')])
            ->acceptJson();
    }
}
