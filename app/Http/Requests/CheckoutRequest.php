<?php

namespace App\Http\Requests;

use App\Models\Pesanan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi checkout customer, termasuk pilihan metode pembayaran.
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->customer !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'metode_bayar' => ['required', Rule::in(array_keys(Pesanan::METODE_BAYAR))],
            'catatan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'metode_bayar.required' => 'Silakan pilih metode pembayaran terlebih dahulu.',
            'metode_bayar.in' => 'Metode pembayaran tidak dikenal.',
            'catatan.max' => 'Catatan maksimal 255 karakter.',
        ];
    }
}
