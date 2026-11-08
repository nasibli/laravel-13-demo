<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'url:http,https', 'max:2048'],
            'day_count' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.required' => 'Введите url страницы постов.',
            'url.url' => 'Введите корректный url страницы постов.',
            'url.max' => 'Url страницы постов слишком длинный.',
            'day_count.required' => 'Введите количество дней.',
            'day_count.integer' => 'Количество дней должно быть целым числом.',
            'day_count.min' => 'Количество дней должно быть больше нуля.',
        ];
    }
}
