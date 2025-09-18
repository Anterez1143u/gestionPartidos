<nav x-data="{ open: false }" class="navbar" style="background: var(--nav,#232946); color:#fff; border-bottom:1px solid #1e2d49;">
    <style>
        .nav-wrap{ max-width:1200px; margin:0 auto; padding:0 16px; }
        .nav-inner{ display:flex; align-items:center; justify-content:space-between; height:64px; gap:12px; }
        .brand{ display:flex; align-items:center; gap:10px; }
        .brand .logo{ width:28px; height:28px; border-radius:9px; background:conic-gradient(from 160deg, #00c896, #16a085, #0d6efd); }
        .brand .name{ margin:0; font-weight:800; letter-spacing:.3px; }
        .links{ display:flex; gap:14px; align-items:center; }
        .nav-link{
            display:inline-flex; align-items:center; height:38px; padding:0 12px;
            border-radius:10px; font-weight:700; color:#eaf6ff; text-decoration:none;
            border:1px solid transparent; transition:all .15s ease;
        }
        .nav-link:hover{ background:#1b2f4d; border-color:#3f79b1; }
        .nav-link.active{ background:linear-gradient(135deg,#00c896,#14b8a6); color:#06202d; }

        /* Dropdown trigger */
        .user-btn{
            display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:10px;
            color:#cfe7ff; border:1px solid #2a4e73; background:transparent;
        }
        .user-btn:hover{ background:#1b2f4d; border-color:#3f79b1; color:#fff; }

        /* Mobile */
        .mobile-toggle{ display:none; }
        @media (max-width:640px){
            .links{ display:none; }
            .mobile-toggle{ display:inline-flex; }
            .mobile-panel{ padding:10px 16px; border-top:1px solid #314566; background:#1f2f4b; }
            .mobile-link{
                display:block; padding:10px 12px; border-radius:10px; color:#eaf6ff; text-decoration:none;
            }
            .mobile-link:hover{ background:#223a60; }
        }
    </style>

    <!-- Primary Navigation -->
    <div class="nav-wrap">
        <div class="nav-inner">
            <!-- Brand -->
            <div class="brand">
                <a href="{{ url('/') }}" class="brand" aria-label="Inicio Tournamet">
                    <div class="logo" aria-hidden="true"></div>
                    <span class="name">{{ config('app.name', 'Tournamet') }}</span>
                </a>
            </div>

            <!-- Desktop links -->
            <div class="links">
                <a href="/partidos" class="nav-link {{ request()->is('partidos*') ? 'active' : '' }}">Partidos</a>
                <a href="/equipos" class="nav-link {{ request()->is('equipos*') ? 'active' : '' }}">Equipos</a>
                <a href="/torneos" class="nav-link {{ request()->is('torneos*') ? 'active' : '' }}">Torneos</a>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Panel</a>

                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="user-btn">
                                <div>{{ Auth::user()->name }}</div>
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Perfil</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    Cerrar sesión
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="nav-link">Iniciar sesión</a>
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}" class="nav-link">Crear cuenta</a>
                    @endif
                @endauth
            </div>

            <!-- Hamburger (mobile) -->
            <button @click="open = ! open" class="mobile-toggle nav-link" aria-label="Abrir menú">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile panel -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden mobile-panel">
        <div class="py-2">
            <a href="/partidos" class="mobile-link {{ request()->is('partidos*') ? 'active' : '' }}">Partidos</a>
            <a href="/equipos" class="mobile-link {{ request()->is('equipos*') ? 'active' : '' }}">Equipos</a>
            <a href="/torneos" class="mobile-link {{ request()->is('torneos*') ? 'active' : '' }}">Torneos</a>
            <a href="{{ route('dashboard') }}" class="mobile-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">Panel</a>
        </div>

        <div class="py-2 border-t border-gray-700/40">
            @auth
                <div class="px-2 mb-2 text-sm text-gray-200">
                    <div class="font-semibold">{{ Auth::user()->name }}</div>
                    <div class="opacity-70">{{ Auth::user()->email }}</div>
                </div>
                <a href="{{ route('profile.edit') }}" class="mobile-link">Perfil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" class="mobile-link"
                       onclick="event.preventDefault(); this.closest('form').submit();">
                        Cerrar sesión
                    </a>
                </form>
            @else
                <a href="{{ route('login') }}" class="mobile-link">Iniciar sesión</a>
                @if(Route::has('register'))
                    <a href="{{ route('register') }}" class="mobile-link">Crear cuenta</a>
                @endif
            @endauth
        </div>
    </div>
</nav>
