<?php

namespace App\Http\Requests;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->admin;
    }

    public function rules(): array
    {
        return [
            'warung_id' => ['required', 'exists:warungs,id'],
            'nama_menu' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::in(array_keys(Menu::KATEGORI))],
            'harga' => ['required', 'integer', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
            'ketersediaan' => ['required', 'boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ];
    }
}
