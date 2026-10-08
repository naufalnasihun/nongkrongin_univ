<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Pengguna - Nongkrongin Univ</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f3f4f6;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-top: 0;
            color: #333;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #333;
        }

        input,
        select {
            width: 100%;
            padding: 11px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 14px;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #2563eb;
        }

        .error {
            margin-bottom: 20px;
            padding: 12px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 7px;
        }

        .error ul {
            margin: 0;
            padding-left: 20px;
        }

        .info {
            padding: 12px;
            margin-bottom: 20px;
            background: #f3f4f6;
            border-radius: 7px;
            color: #555;
            font-size: 14px;
        }

        .buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            padding: 11px 18px;
            border: none;
            border-radius: 7px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-save {
            background: #2563eb;
            color: white;
        }

        .btn-back {
            background: #6b7280;
            color: white;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Edit Pengguna</h1>

    <div class="info">
        Silakan ubah data pengguna. Password boleh dikosongkan jika tidak ingin mengubahnya.
    </div>

    @if ($errors->any())

        <div class="error">

            <strong>Terjadi kesalahan:</strong>

            <ul>

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif

    @php

        if ($user->admin) {
            $role = 'Admin';
        } elseif ($user->kasir) {
            $role = 'Kasir';
        } elseif ($user->customer) {
            $role = 'Customer';
        } else {
            $role = '';
        }

    @endphp

    <form
        action="{{ route('admin.users.update', $user->id) }}"
        method="POST"
    >

        @csrf

        @method('PUT')


        <div class="form-group">

            <label for="name">
                Nama Akun
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $user->name) }}"
                required
            >

        </div>


        <div class="form-group">

            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $user->email) }}"
                required
            >

        </div>


        <div class="form-group">

            <label for="password">
                Password Baru
            </label>

            <input
                type="password"
                id="password"
                name="password"
                placeholder="Kosongkan jika tidak diubah"
            >

        </div>


        <div class="form-group">

            <label for="role">
                Role
            </label>

            <select
                id="role"
                name="role"
                required
            >

                <option value="">
                    -- Pilih Role --
                </option>

                <option
                    value="Admin"
                    {{ old('role', $role) == 'Admin' ? 'selected' : '' }}
                >
                    Admin
                </option>

                <option
                    value="Kasir"
                    {{ old('role', $role) == 'Kasir' ? 'selected' : '' }}
                >
                    Kasir
                </option>

                <option
                    value="Customer"
                    {{ old('role', $role) == 'Customer' ? 'selected' : '' }}
                >
                    Customer
                </option>

            </select>

        </div>


        <div class="form-group">

            <label for="nama">
                Nama Lengkap
            </label>

            <input
                type="text"
                id="nama"
                name="nama"
                value="{{ old('nama',
                    $user->admin->nama ??
                    $user->kasir->nama ??
                    $user->customer->nama ??
                    $user->name
                ) }}"
                required
            >

        </div>


        <div class="form-group">

            <label for="nim">
                NIM
            </label>

            <input
                type="text"
                id="nim"
                name="nim"
                value="{{ old('nim', $user->customer->nim ?? '') }}"
                placeholder="Diisi untuk Customer"
            >

        </div>


        <div class="form-group">

            <label for="kelas">
                Kelas
            </label>

            <input
                type="text"
                id="kelas"
                name="kelas"
                value="{{ old('kelas', $user->customer->kelas ?? '') }}"
                placeholder="Diisi untuk Customer"
            >

        </div>


        <div class="form-group">

            <label for="id_kasir">
                ID Kasir
            </label>

            <input
                type="text"
                id="id_kasir"
                name="id_kasir"
                value="{{ old('id_kasir', $user->kasir->id_kasir ?? '') }}"
                placeholder="Diisi untuk Kasir"
            >

        </div>


        <div class="form-group">

            <label for="id_admin">
                ID Admin
            </label>

            <input
                type="text"
                id="id_admin"
                name="id_admin"
                value="{{ old('id_admin', $user->admin->id_admin ?? '') }}"
                placeholder="Diisi untuk Admin"
            >

        </div>


        <div class="buttons">

            <button
                type="submit"
                class="btn btn-save"
            >
                Simpan Perubahan
            </button>

            <a
                href="{{ route('admin.users.index') }}"
                class="btn btn-back"
            >
                Kembali
            </a>

        </div>

    </form>

</div>

</body>

</html>