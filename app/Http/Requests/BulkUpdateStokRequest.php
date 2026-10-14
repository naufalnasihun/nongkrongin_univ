<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkUpdateStokRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->admin !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'aksi' => ['required', Rule::in(['set', 'tambah', 'kurang'])],
            'nilai' => ['required', 'integer', 'min:0', 'max:100000'],
            'menu_ids' => ['required', 'array', 'min:1'],
            'menu_ids.*' => ['integer', 'exists:menus,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'menu_ids.required' => 'Pilih minimal satu menu terlebih dahulu.',
            'menu_ids.min' => 'Pilih minimal satu menu terlebih dahulu.',
            'nilai.min' => 'Nilai stok tidak boleh negatif.',
        ];
    }
}
