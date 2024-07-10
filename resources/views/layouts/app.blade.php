<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'LuminaMind') }} - @yield('title', 'AIカウンセラー')</title>

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    @stack('styles')

    <!-- Custom CSS -->
    <style>
        :root {
            --primary-blue: #3498db;
            --primary-green: #2ecc71;
            --primary-orange: #f39c12;
            --light-bg: #ecf0f1;
            --dark-bg: #343a40;
            --light-text: #ffffff;
            --dark-text: #333333;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Roboto', sans-serif;
            color: var(--dark-text);
        }

        .navbar {
            background-color: transparent;
            transition: background-color 0.5s ease;
        }

        .navbar.scrolled {
            background-color: var(--dark-bg);
        }

        .navbar-brand, .nav-link {
            color: var(--light-text) !important;
            transition: color 0.3s ease;
        }

        .navbar-brand:hover, .nav-link:hover {
            color: var(--primary-orange) !important;
        }

        .footer {
            background-color: var(--dark-bg);
            color: var(--light-text);
            padding: 10px 0;
        }

        .gradient-button {
            background: linear-gradient(45deg, var(--primary-blue), var(--primary-green));
            border: none;
            color: white;
            font-weight: bold;
            transition: all 0.3s ease;
            padding: 10px 20px;
            border-radius: 50px;
        }

        .gradient-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, .1), 0 3px 6px rgba(0, 0, 0, .08);
        }

        .dropdown-menu {
            background-color: var(--dark-bg);
            border: none;
        }

        .dropdown-item {
            color: var(--light-text);
        }

        .dropdown-item:hover {
            background-color: var(--primary-orange);
            color: var(--light-text);
        }
    </style>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var navbar = document.querySelector('.navbar');
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });
        });
    </script>
    @stack('head-scripts')
</head>
<body>
    <nav class="navbar navbar-expand-md fixed-top navbar-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'LuminaMind') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                @auth
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('conversations.index') }}">対話一覧</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('conversations.start') }}">新しい対話を始める</a>
                    </li>
                </ul>
                @endauth

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
                    <!-- Authentication Links -->
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a href="{{ route('login') }}" class="nav-link">ログイン</a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a href="{{ route('register') }}" class="nav-link">新規登録</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a href="{{ url('/profile') }}" class="dropdown-item">プロフィール</a>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                 document.getElementById('logout-form').submit();">
                                    {{ __('ログアウト') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4 mt-5">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="footer mt-auto py-3">
        <div class="container text-center">
            <span class="text-muted">&copy; {{ date('Y') }} {{ config('app.name', 'LuminaMind') }}. All rights reserved.</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
