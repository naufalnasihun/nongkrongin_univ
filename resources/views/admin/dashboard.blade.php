<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard Admin - Nongkrongin Univ</title>

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

        h1 {
            margin-top: 0;
            color: #333;
        }

        .welcome {
            padding: 20px;
            background: #f3f4f6;
            border-radius: 10px;
            margin-bottom: 25px;
        }

        .menu {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 18px;
            border-radius: 7px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-secondary {
            background: #333;
            color: white;
        }

        .btn-logout {
            background: #dc2626;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }

        form {
            display: inline;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Dashboard Admin</h1>

    <div class="welcome">

        <h3>Selamat datang, Admin!</h3>

        <p>
            Anda login sebagai
            <strong>{{ Auth::user()->name }}</strong>.
        </p>

        <p>
            Admin dapat mengelola pengguna dan menu.
        </p>

    </div>

    <div class="menu">

        <a
            href="{{ route('admin.users.index') }}"
            class="btn btn-primary"
        >
            Management Pengguna
        </a>

        <a
            href="#"
            class="btn btn-secondary"
        >
            Management Menu
        </a>

        <form
            action="{{ route('logout') }}"
            method="POST"
        >
            @csrf

            <button
                type="submit"
                class="btn btn-logout"
            >
                Logout
            </button>
        </form>

    </div>

</div>

</body>

</html>