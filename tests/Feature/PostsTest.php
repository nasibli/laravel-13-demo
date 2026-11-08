<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PostsTest extends TestCase
{
    private const string PAGE_URL = 'https://example.com/blog';

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->travelTo(now()->parse('2026-11-07 07:05:09', 'UTC'));
    }

    public function test_home_page_shows_fields_and_button_below_them(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['url страницы постов', 'количество дней', 'Записать']);
    }

    public function test_posts_are_uploaded_into_new_folders_on_yandex_disk(): void
    {
        $existingFolders = ['/07.11.2026 10:05:09', '/07.11.2026 10:05:091'];
        $this->fakeApis(
            "```json\n".json_encode([
                ['title' => 'Первый', 'post' => 'Текст первого'],
                ['title' => 'Второй', 'post' => 'Текст второго'],
            ])."\n```",
            $existingFolders,
        );

        $this->post('/posts', ['url' => self::PAGE_URL, 'day_count' => 3])
            ->assertRedirect()
            ->assertSessionHas('success', 'Загрузка постов на Яндекс Диск произведена успешно. Загружено постов: 2.');

        Http::assertSent(function (Request $request): bool {
            $prompt = $request['contents'][0]['parts'][0]['text'] ?? '';

            return str_contains($request->url(), 'generativelanguage.googleapis.com')
                && str_contains($prompt, 'Возьми посты со страницы '.self::PAGE_URL.' день публикации которых не больше 3 дней')
                && ! str_contains($prompt, '%url')
                && ! str_contains($prompt, '%day_count');
        });

        $this->assertSame(
            ['/07.11.2026 10:05:092', '/07.11.2026 10:05:093'],
            $this->createdFolders(),
        );

        Http::assertSent(fn (Request $request): bool => urldecode($request->url()) === 'https://uploader.test/07.11.2026 10:05:092/Post.txt'
            && $request->body() === "Первый\n\nТекст первого");
        Http::assertSent(fn (Request $request): bool => urldecode($request->url()) === 'https://uploader.test/07.11.2026 10:05:093/Post.txt'
            && $request->body() === "Второй\n\nТекст второго");
    }

    public function test_nothing_is_uploaded_when_no_posts_found(): void
    {
        $this->fakeApis('[]');

        $this->post('/posts', ['url' => self::PAGE_URL, 'day_count' => 1])
            ->assertSessionHas('error', 'На странице не найдено постов за указанное количество дней.');

        Http::assertNotSent(fn (Request $request): bool => str_contains($request->url(), 'yandex'));
    }

    public function test_error_is_shown_when_gemini_answer_is_not_json(): void
    {
        $this->fakeApis('Не удалось открыть страницу');

        $this->post('/posts', ['url' => self::PAGE_URL, 'day_count' => 1])
            ->assertSessionHas('error', fn (string $message): bool => str_starts_with($message, 'Не удалось обработать посты'));
    }

    public function test_url_is_required(): void
    {
        Http::fake();

        $this->post('/posts', ['url' => 'не url', 'day_count' => 1])
            ->assertSessionHasErrors(['url' => 'Введите корректный url страницы постов.']);

        Http::assertNothingSent();
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function invalidDayCounts(): array
    {
        return [
            'отсутствует' => [null, 'Введите количество дней.'],
            'дробное' => ['1.5', 'Количество дней должно быть целым числом.'],
            'не число' => ['abc', 'Количество дней должно быть целым числом.'],
            'ноль' => [0, 'Количество дней должно быть больше нуля.'],
            'отрицательное' => [-2, 'Количество дней должно быть больше нуля.'],
        ];
    }

    #[DataProvider('invalidDayCounts')]
    public function test_day_count_must_be_positive_integer(mixed $dayCount, string $message): void
    {
        Http::fake();

        $this->post('/posts', ['url' => self::PAGE_URL, 'day_count' => $dayCount])
            ->assertSessionHasErrors(['day_count' => $message]);

        Http::assertNothingSent();
    }

    /**
     * @param  list<string>  $existingFolders
     */
    private function fakeApis(string $answer, array $existingFolders = []): void
    {
        Http::fake(function (Request $request) use ($answer, &$existingFolders) {
            if (str_contains($request->url(), 'generativelanguage.googleapis.com')) {
                return Http::response(['candidates' => [
                    ['content' => ['role' => 'model', 'parts' => [['text' => $answer]]]],
                ]]);
            }

            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            if ($request->method() === 'PUT' && str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/resources')) {
                if (in_array($query['path'], $existingFolders, true)) {
                    return Http::response(['error' => 'DiskPathPointsToExistentDirectoryError'], 409);
                }

                $existingFolders[] = $query['path'];

                return Http::response([], 201);
            }

            if ($request->method() === 'GET' && str_ends_with(parse_url($request->url(), PHP_URL_PATH), '/resources/upload')) {
                return Http::response(['href' => 'https://uploader.test'.$query['path'], 'method' => 'PUT']);
            }

            return Http::response('', 201);
        });
    }

    /**
     * @return list<string>
     */
    private function createdFolders(): array
    {
        return Http::recorded(fn (Request $request, $response): bool => $request->method() === 'PUT'
            && $response->status() === 201
            && str_contains($request->url(), 'cloud-api.yandex.net'))
            ->map(function (array $pair): string {
                parse_str((string) parse_url($pair[0]->url(), PHP_URL_QUERY), $query);

                return $query['path'];
            })
            ->values()
            ->all();
    }
}
