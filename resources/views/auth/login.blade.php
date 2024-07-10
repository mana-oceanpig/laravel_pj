<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LuminaMind') }} - @yield('title', 'AIカウンセラー')</title>

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')

    <!-- Custom CSS -->
    <style>
            :root {
            --primary-color: #4A90E2;
            --secondary-color: #50E3C2;
            --accent-color: #F5A623;
            --text-color: #333333;
            --background-color: #FFFFFF;
            --light-gray: #F8F8F8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Noto Sans JP', 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background-color);
        }

        .container-fluid {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 20px;
            overflow: hidden;
        }

        .card-body {
            padding: 2rem;
        }

        .form-floating .form-control {
            border-radius: 10px;
            border: 1px solid var(--light-gray);
        }

        .form-floating .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(74, 144, 226, 0.5);
        }

        .form-floating label {
            color: var(--text-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #357ABD;
            border-color: #357ABD;
        }

        .btn-link {
            color: var(--primary-color);
            font-weight: 600;
        }

        .btn-link:hover {
            color: #357ABD;
        }

        .gradient-button {
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border: none;
            color: white;
            font-weight: bold;
            transition: background 0.3s ease-in-out;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
        }

        .gradient-button:hover {
            background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
        }

        img {
            width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
    </style>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row w-100">
            <div class="col-md-6 d-flex align-items-center justify-content-center bg-white">
                <div class="card w-75">
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="form-floating mb-3">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="メールアドレス">
                                <label for="email">{{ __('メールアドレス') }}</label>
                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="パスワード">
                                <label for="password">{{ __('パスワード') }}</label>
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('ログイン状態を保持する') }}
                                </label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg gradient-button">
                                    {{ __('ログイン') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link mt-2" href="{{ route('password.request') }}">
                                        {{ __('パスワードをお忘れですか？') }}
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <img src="https://example.com/your-login-image.jpg" alt="ログインイメージ画像">
            </div>
        </div>
    </div>
</body>
</html>