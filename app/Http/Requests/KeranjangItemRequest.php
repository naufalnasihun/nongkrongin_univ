<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KeranjangItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->customer;
    }

    public function rules(): array
    {
        return [
            'jumlah' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }
}
