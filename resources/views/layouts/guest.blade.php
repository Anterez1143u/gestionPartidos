<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#0f2436">

        <title>{{ config('app.name', 'Tournamet') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link rel="stylesheet" href="{{ asset('css/tournament.css') }}">
        <style>
            :root{
                --bg:#0f2436; --bg2:#142c44; --card:#232946; --line:#2f3f67;
                --accent:#00c896; --accent2:#0d6efd; --text:#eaf6ff; --muted:#a9b4c2;
            }
            body{
                background: linear-gradient(160deg,var(--bg) 0%, var(--bg2) 60%) !important;
                min-height: 100vh;
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                color: var(--text);
            }
            .guest-bg{
                min-height: 100vh; display:flex; flex-direction:column; justify-content:center; align-items:center; padding:36px 16px;
            }
            .brand{
                display:flex; align-items:center; gap:12px; margin-bottom:18px;
            }
            .brand .logo{
                width:48px; height:48px; border-radius:14px;
                background: conic-gradient(from 160deg at 50% 50%, var(--accent), #16a085, var(--accent2));
                box-shadow: 0 8px 28px rgba(13,110,253,.25);
            }
            .brand .name{ margin:0; font-size:1.5rem; font-weight:800; letter-spacing:.3px; }
            .guest-card{
                background: var(--card); color:#fff; width:100%; max-width:460px;
                margin:auto; padding:34px 28px; border-radius:18px;
                box-shadow: 0 12px 40px rgba(13,110,253,.14); border:1px solid var(--line);
            }
            .footer{
                margin-top:16px; color:var(--muted); font-size:.9rem; text-align:center;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="guest-bg">
            <div class="brand">
                <div class="logo" aria-hidden="true"></div>
                <h1 class="name">{{ config('app.name', 'Tournamet') }}</h1>
            </div>
            <div class="guest-card">
                {{ $slot }}
            </div>
            <div class="footer">
                <a href="/" style="color:var(--accent); text-decoration:none;">Volver al inicio</a>
            </div>
        </div>
    </body>
</html>
