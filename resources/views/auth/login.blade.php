<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Nongkrongin Univ</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f3f4f6;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .login-container h1 {
            text-align: center;
            margin-bottom: 10px;
            color: #333;
        }

        .login-container p {
            text-align: center;
            color: #777;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 7px;
            font-size: 14px;
        }

        .form-group input:focus {
            outline: none;
            border-color: #555;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 7px;
            background: #333;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-login:hover {
            background: #555;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            border-radius: 7px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .demo-account {
            margin-top: 20px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 7px;
            font-size: 13px;
            color: #555;
        }

        .demo-account strong {
            color: #333;
        }
    </style>
</head>

<body>

    <div class="login-container">

        <h1>Nongkrongin Univ</h1>

        <p>Silakan login untuk melanjutkan</p>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Masukkan email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    required
                >
            </div>

            <button type="submit" class="btn-login">
                Login
            </button>
        </form>

        <div class="demo-account">
            <strong>Akun untuk pengujian:</strong><br><br>

            Admin:<br>
            admin@nongkrongin.test / password
            <br><br>

            Kasir:<br>
            kasir@nongkrongin.test / password
            <br><br>

            Customer:<br>
            customer@nongkrongin.test / password
        </div>

    </div>

</body>
</html>