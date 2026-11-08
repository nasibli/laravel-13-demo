<?php

namespace App\Services;

use App\API\GeminiAPI;
use App\API\YandexDiskAPI;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PostsService
{
    private const string PROMPT = <<<'PROMPT'
        Ты опытный рерайтер
        Возьми посты со страницы %url день публикации которых не больше %day_count дней по сравнению с текущей датой, текущую дату бери по часовому поясу Москва. Если не находишь даты публикации поста, то обрати внимание на то, сколько минут, часов, или дней назад опубликована статья и сравни с текущей датой.
        Сделай классный рерайт, чтобы пост выглядел еще лучше и интереснее и сделай цепляющий заголовок.
        Должно получиться заголовок и сам рерайт поста
        Сделай ответ в формате json, который содержит массив объектов (данные по каждому посту), каждый объект c двумя полями title - новый заголовок поста, post - текст пересказанного поста
        PROMPT;

    private const string FILE_NAME = 'Post.txt';

    public function __construct(
        private GeminiAPI $geminiAPI,
        private YandexDiskAPI $yandexDiskAPI,
    ) {}

    /**
     * Переписывает посты со страницы за последние $dayCount дней через Gemini и загружает каждый в свою папку на Яндекс Диске.
     *
     * @return int Количество загруженных постов.
     *
     * @throws ConnectionException
     * @throws RequestException
     * @throws RuntimeException
     */
    public function rewriteToYandexDisk(string $url, int $dayCount): int
    {
        $prompt = strtr(self::PROMPT, ['%url' => $url, '%day_count' => (string) $dayCount]);
        $posts = $this->parsePosts($this->geminiAPI->ask($prompt));

        foreach ($posts as $post) {
            $folder = $this->createUniqueFolder();

            Storage::disk('local')->put(self::FILE_NAME, $post['title']."\n\n".$post['post']);

            $this->yandexDiskAPI->uploadFile(
                $folder.'/'.self::FILE_NAME,
                Storage::disk('local')->get(self::FILE_NAME),
            );
        }

        return count($posts);
    }

    /**
     * Создаёт папку вида "день.месяц.год часы:минуты:секунды" по Москве, а если она занята, дописывает 1, 2, 3 и т.д.
     */
    private function createUniqueFolder(): string
    {
        $name = now('Europe/Moscow')->format('d.m.Y H:i:s');
        $path = '/'.$name;
        $suffix = 0;

        while (! $this->yandexDiskAPI->createFolder($path)) {
            $suffix++;
            $path = '/'.$name.$suffix;
        }

        return $path;
    }

    /**
     * Достаёт из ответа модели массив постов, пропуская обёртку ```json и элементы без title или post.
     *
     * @return list<array{title: string, post: string}>
     *
     * @throws RuntimeException
     */
    private function parsePosts(string $answer): array
    {
        $json = preg_replace('/^\s*```(?:json)?\s*|\s*```\s*$/', '', $answer);
        $data = json_decode($json, true);

        if (! is_array($data)) {
            throw new RuntimeException('Gemini вернул ответ не в формате JSON: '.$answer);
        }

        if (! array_is_list($data)) {
            $data = collect($data)->first(fn (mixed $value): bool => is_array($value) && array_is_list($value)) ?? [];
        }

        return collect($data)
            ->filter(fn (mixed $item): bool => is_array($item)
                && is_string($item['title'] ?? null)
                && is_string($item['post'] ?? null))
            ->map(fn (array $item): array => ['title' => $item['title'], 'post' => $item['post']])
            ->values()
            ->all();
    }
}
