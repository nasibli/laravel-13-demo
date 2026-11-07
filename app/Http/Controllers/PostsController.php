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
        // Ответ ChatGPT с веб-поиском может идти несколько минут.
        set_time_limit((int) config('services.openai.timeout') + 60);

        try {
            $count = $postsService->rewriteToYandexDisk($request->validated('url'));
        } catch (ConnectionException|RequestException|RuntimeException $e) {
            report($e);

            return back()->withInput()->with('error', 'Не удалось обработать посты: '.$e->getMessage());
        }

        if ($count === 0) {
            return back()->withInput()->with('error', 'На странице не найдено постов за последний день.');
        }

        return back()->with('success', "Загрузка постов на Яндекс Диск произведена успешно. Загружено постов: {$count}.");
    }
}
