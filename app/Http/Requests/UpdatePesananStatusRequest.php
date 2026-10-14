<?php

namespace App\Http\Requests;

use App\Models\Pesanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePesananStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->kasir;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(Pesanan::STATUSES)],
            'catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}
