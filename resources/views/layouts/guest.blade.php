<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Gestion de Partidos') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="{{ asset('css/tournament.css') }}">
        <style>
            body {
                background: #1b2e47 !important;
                min-height: 100vh;
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
            }
            .guest-bg {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding-top: 40px;
            }
            .guest-card {
                background: #232946;
                color: #fff;
                max-width: 420px;
                width: 100%;
                margin: auto;
                padding: 38px 32px;
                border-radius: 18px;
                box-shadow: 0 8px 32px rgba(13,110,253,0.10);
            }
            .guest-logo {
                margin-bottom: 18px;
                display: flex;
                justify-content: center;
            }
        </style>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="guest-bg">
            <div class="guest-logo">
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>
            <div class="guest-card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
