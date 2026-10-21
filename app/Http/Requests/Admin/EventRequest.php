<?php

namespace App\Http\Requests\Admin;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        /** @var Event|null $event */
        $event = $this->route('event');

        return [
            'title' => ['required', 'string', 'max:160'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string', 'min:20', 'max:10000'],
            'organizer' => ['required', 'string', 'max:120'],
            'location' => ['required', 'string', 'max:160'],
            'city' => ['required', 'string', 'max:80'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'quota' => [
                'required', 'integer', 'min:1', 'max:100000',
                // Kuota tidak boleh lebih kecil dari jumlah pendaftar aktif.
                function (string $attribute, mixed $value, \Closure $fail) use ($event) {
                    if ($event && (int) $value < $event->active_registrations) {
                        $fail("Kuota tidak boleh kurang dari jumlah pendaftar aktif ({$event->active_registrations}).");
                    }
                },
            ],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
            'status' => ['required', Rule::in(array_keys(Event::STATUSES))],
            'is_featured' => ['sometimes', 'boolean'],
            'poster' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul',
            'category_id' => 'kategori',
            'description' => 'deskripsi',
            'organizer' => 'penyelenggara',
            'location' => 'lokasi',
            'city' => 'kota',
            'starts_at' => 'waktu mulai',
            'ends_at' => 'waktu selesai',
            'quota' => 'kuota',
            'price' => 'harga',
            'status' => 'status',
            'poster' => 'poster',
        ];
    }
}
