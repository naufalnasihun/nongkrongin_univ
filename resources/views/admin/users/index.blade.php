<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Management Pengguna - Nongkrongin Univ</title>

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
            max-width: 1100px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        h1 {
            margin: 0;
            color: #333;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 7px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-add {
            background: #333;
            color: white;
        }

        .btn-edit {
            background: #2563eb;
            color: white;
        }

        .btn-delete {
            background: #dc2626;
            color: white;
        }

        .btn-dashboard {
            background: #6b7280;
            color: white;
            margin-bottom: 20px;
        }

        .success {
            padding: 12px;
            margin-bottom: 20px;
            background: #dcfce7;
            color: #166534;
            border-radius: 7px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #f3f4f6;
            color: #333;
        }

        .role {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            background: #e5e7eb;
            font-size: 12px;
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 6px;
        }

        .actions form {
            display: inline;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="header">
        <h1>Management Pengguna</h1>

        <a href="{{ route('admin.users.create') }}" class="btn btn-add">
            + Tambah Pengguna
        </a>
    </div>

    <a href="{{ route('admin.dashboard') }}" class="btn btn-dashboard">
        ← Kembali ke Dashboard
    </a>

    @if (session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-wrapper">

        <table>

            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Identitas</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($users as $user)

                    @php
                        if ($user->admin) {
                            $role = 'Admin';
                            $identitas = $user->admin->id_admin;
                        } elseif ($user->kasir) {
                            $role = 'Kasir';
                            $identitas = $user->kasir->id_kasir;
                        } elseif ($user->customer) {
                            $role = 'Customer';
                            $identitas = $user->customer->nim;
                        } else {
                            $role = 'Tidak ada role';
                            $identitas = '-';
                        }
                    @endphp

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            {{ $user->name }}
                        </td>

                        <td>
                            {{ $user->email }}
                        </td>

                        <td>
                            <span class="role">
                                {{ $role }}
                            </span>
                        </td>

                        <td>
                            {{ $identitas }}
                        </td>

                        <td>

                            <div class="actions">

                                <a
                                    href="{{ route('admin.users.edit', $user->id) }}"
                                    class="btn btn-edit"
                                >
                                    Edit
                                </a>

                                <form
                                    action="{{ route('admin.users.destroy', $user->id) }}"
                                    method="POST"
                                    onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')"
                                >

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn btn-delete"
                                    >
                                        Hapus
                                    </button>

                                </form>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" style="text-align: center;">
                            Belum ada data pengguna.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

</body>
</html>