<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir - Nongkrongin Univ</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,.1);
        }

        h1 {
            color: #333;
        }

        .info {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .logout {
            padding: 10px 18px;
            background: #333;
            color: white;
            border: none;
            border-radius: 7px;
            cursor: pointer;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Dashboard Kasir</h1>

    <div class="info">
        <p><strong>Selamat datang, Kasir!</strong></p>
        <p>Anda login sebagai <strong>Kasir Nongkrongin Univ</strong>.</p>
        <p>Kasir dapat melihat dan memproses pesanan serta mengubah status pesanan.</p>
    </div>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="logout">
            Logout
        </button>
    </form>

</div>

</body>
</html>