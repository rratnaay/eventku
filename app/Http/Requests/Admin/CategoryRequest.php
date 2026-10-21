<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public const ICONS = ['music', 'cpu', 'palette', 'run', 'book', 'cup', 'sparkles', 'heart', 'briefcase', 'camera'];

    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'name' => ['required', 'string', 'max:80', Rule::unique('categories', 'name')->ignore($category)],
            'icon' => ['required', Rule::in(self::ICONS)],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama kategori', 'icon' => 'ikon', 'description' => 'deskripsi'];
    }
}
