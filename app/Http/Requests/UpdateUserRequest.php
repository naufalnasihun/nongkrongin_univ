<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->admin;
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['Admin', 'Kasir', 'Customer'])],
            'nama' => ['required', 'string', 'max:255'],
            'nim' => [
                'required_if:role,Customer',
                'nullable',
                'string',
                'max:255',
                Rule::unique('customers', 'nim')->ignore(optional($user->customer)->id),
            ],
            'kelas' => ['required_if:role,Customer', 'nullable', 'string', 'max:255'],
            'id_kasir' => [
                'required_if:role,Kasir',
                'nullable',
                'string',
                'max:255',
                Rule::unique('kasirs', 'id_kasir')->ignore(optional($user->kasir)->id),
            ],
            'id_admin' => [
                'required_if:role,Admin',
                'nullable',
                'string',
                'max:255',
                Rule::unique('admins', 'id_admin')->ignore(optional($user->admin)->id),
            ],
        ];
    }

    /**
     * Blokir perubahan role bila user sudah punya transaksi, agar riwayat
     * pesanan (yang berelasi ke customer/kasir) tidak rusak.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var User $user */
            $user = $this->route('user');
            $roleLama = $user->admin ? 'Admin' : ($user->kasir ? 'Kasir' : ($user->customer ? 'Customer' : null));

            if ($roleLama !== null && $roleLama !== $this->input('role')) {
                $punyaTransaksi = $user->customer?->pesanans()->exists() ?? false;

                if ($punyaTransaksi) {
                    $validator->errors()->add(
                        'role',
                        'Role tidak dapat diubah karena user ini sudah memiliki riwayat pesanan.'
                    );
                }
            }
        });
    }
}
