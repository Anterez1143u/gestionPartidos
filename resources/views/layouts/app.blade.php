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
                --bg:#0f2436; --bg2:#142c44; --nav:#232946; --card:#192f4b; --line:#244465;
                --accent:#00c896; --accent2:#0d6efd; --text:#eaf6ff; --muted:#a9b4c2;
            }
            body{ background: linear-gradient(160deg,var(--bg) 0%, var(--bg2) 60%) !important; color: var(--text); }
            nav, .navbar{ background: var(--nav) !important; color:#fff !important; border-bottom:1px solid #1e2d49; }
            .navbar a, nav.navbar a{ color:#fff !important; }
            .navbar .dropdown-menu{ background: var(--nav); color:#fff; }

            .header-bar{
                background: var(--card);
                border:1px solid var(--line);
                border-radius:14px;
                margin:18px auto 20px;
                max-width:1200px;
                box-shadow:0 8px 28px rgba(13,110,253,.12);
            }
            .header-inner{ padding:16px 18px; display:flex; align-items:center; justify-content:space-between; gap:12px; }
            .brand{ display:flex; align-items:center; gap:10px; }
            .brand .logo{ width:32px; height:32px; border-radius:10px; background: conic-gradient(from 160deg, var(--accent), #16a085, var(--accent2)); }
            .brand .name{ margin:0; font-weight:800; letter-spacing:.3px; }

            .wrap{ max-width:1200px; margin:0 auto; padding:0 16px 40px; }
            .card{ background: var(--card); border:1px solid var(--line); border-radius:14px; box-shadow:0 8px 28px rgba(13,110,253,.12); }

            /* Botones base por si se usan en el contenido */
            .btn{ display:inline-flex; align-items:center; gap:8px; padding:9px 14px; border-radius:10px; font-weight:800; border:1.5px solid transparent; color:#fff; background:#1b3a5d; }
            .btn-primary{ background:linear-gradient(135deg,var(--accent), #14b8a6); color:#06202d; }
            .btn-outline{ background:transparent; border-color:#2a4e73; }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        @include('layouts.navigation')

        @isset($header)
            <header class="header-bar">
                <div class="header-inner">
                    <div class="brand">
                        <div class="logo" aria-hidden="true"></div>
                        <h1 class="name">{{ config('app.name', 'Tournamet') }}</h1>
                    </div>
                    <div style="color:var(--muted); font-weight:600;">
                        Panel de administración
                    </div>
                </div>
            </header>
        @endisset

        <main class="wrap">
            {{ $slot }}
        </main>
    </body>
</html>
