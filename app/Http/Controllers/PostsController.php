<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Services\PostsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class PostsController extends Controller
{
    public function index(): View
    {
        return view('posts.index');
    }

    public function store(PostRequest $request, PostsService $postsService): RedirectResponse
    {
        // Ответ Gemini с чтением страницы и поиском может идти несколько минут.
        set_time_limit((int) config('services.gemini.timeout') + 60);

        try {
            $count = $postsService->rewriteToYandexDisk(
                $request->validated('url'),
                (int) $request->validated('day_count'),
            );
        } catch (ConnectionException|RequestException|RuntimeException $e) {
            report($e);

            return back()->withInput()->with('error', 'Не удалось обработать посты: '.$e->getMessage());
        }

        if ($count === 0) {
            return back()->withInput()->with('error', 'На странице не найдено постов за указанное количество дней.');
        }

        return back()->with('success', "Загрузка постов на Яндекс Диск произведена успешно. Загружено постов: {$count}.");
    }
}
