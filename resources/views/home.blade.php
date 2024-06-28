<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', __('LuminaMind - AIカウンセリングで心の健康をサポート'))">
    <title>LuminaMind</title>
    <style></style>
</head>
<body>
    <header>
        @include('layouts.nav')
    </header>

    <main>
        <div class="hero">

        </div>
        <div class="about">
            
        </div>
        <div class="feature">
            
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. @lang('home.footer.rights_reserved')</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
