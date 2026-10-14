<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->admin;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['Admin', 'Kasir', 'Customer'])],
            'nama' => ['required', 'string', 'max:255'],
            'nim' => ['required_if:role,Customer', 'nullable', 'string', 'max:255', 'unique:customers,nim'],
            'kelas' => ['required_if:role,Customer', 'nullable', 'string', 'max:255'],
            'id_kasir' => ['required_if:role,Kasir', 'nullable', 'string', 'max:255', 'unique:kasirs,id_kasir'],
            'id_admin' => ['required_if:role,Admin', 'nullable', 'string', 'max:255', 'unique:admins,id_admin'],
        ];
    }
}
