<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] dark:text-[#EDEDEC] min-h-screen flex items-center justify-center p-6">
        <main class="w-full max-w-2xl">
            @if (session('success'))
                <div class="mb-4 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('posts.store') }}" class="flex flex-col gap-3">
                @csrf

                <div>
                    <input
                        type="url"
                        name="url"
                        value="{{ old('url') }}"
                        placeholder="url страницы постов"
                        aria-label="url страницы постов"
                        required
                        class="w-full rounded-md border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] px-4 py-2 outline-none focus:border-[#1b1b18] dark:focus:border-[#EDEDEC]"
                    >

                    @error('url')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <input
                        type="number"
                        name="day_count"
                        value="{{ old('day_count') }}"
                        placeholder="количество дней"
                        aria-label="количество дней"
                        min="1"
                        step="1"
                        required
                        class="w-full rounded-md border border-[#19140035] dark:border-[#3E3E3A] bg-white dark:bg-[#161615] px-4 py-2 outline-none focus:border-[#1b1b18] dark:focus:border-[#EDEDEC]"
                    >

                    @error('day_count')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button
                    type="submit"
                    class="self-start rounded-md bg-[#1b1b18] dark:bg-[#EDEDEC] px-5 py-2 text-white dark:text-[#1b1b18] hover:bg-black dark:hover:bg-white"
                >
                    Записать
                </button>
            </form>
        </main>
    </body>
</html>
