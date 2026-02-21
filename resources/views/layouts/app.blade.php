<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Distri-APP') }} — {{ $title ?? 'Panel' }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CDN (desarrollo) -->
    @if (app()->environment('local'))
        <script src="https://cdn.tailwindcss.com"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('build/assets/app-?.css') }}">
        <script src="{{ asset('build/assets/app-?.js') }}" defer></script>
    @endif

    <style>
        :root {
            --sidebar-w: 260px;
            --purple-dark:  #4C1D95;
            --purple-main:  #6C3DE0;
            --purple-light: #8B5CF6;
            --purple-glow:  rgba(108,61,224,0.18);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: #F3F4F8;
            color: #1E1B2E;
            margin: 0;
        }

        /* ── SIDEBAR ─────────────────────────────────── */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            width: var(--sidebar-w);
            background: linear-gradient(170deg, #4C1D95 0%, #6C3DE0 60%, #8B5CF6 100%);
            display: flex;
            flex-direction: column;
            z-index: 50;
            transition: transform .3s ease;
            box-shadow: 4px 0 24px rgba(76,29,149,0.25);
        }

        #sidebar .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }

        #sidebar .logo-icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 18px; color: #fff;
            flex-shrink: 0;
        }

        #sidebar .logo-text {
            font-size: 18px; font-weight: 700;
            color: #fff; letter-spacing: -0.3px;
        }

        #sidebar .logo-sub {
            font-size: 11px; color: rgba(255,255,255,0.6);
            font-weight: 400; margin-top: 1px;
        }

        .sidebar-section-label {
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,0.45);
            text-transform: uppercase;
            padding: 18px 24px 6px;
        }

        .sidebar-nav { flex: 1; overflow-y: auto; padding: 8px 12px; }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: rgba(255,255,255,0.78);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all .2s ease;
            margin-bottom: 2px;
        }

        .nav-item svg { flex-shrink: 0; opacity: .8; }

        .nav-item:hover {
            background: rgba(255,255,255,0.12);
            color: #fff;
        }

        .nav-item.active {
            background: rgba(255,255,255,0.2);
            color: #fff;
            font-weight: 600;
            box-shadow: 0 2px 12px rgba(0,0,0,0.12);
        }

        /* User card at bottom */
        .sidebar-user {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,0.12);
            display: flex; align-items: center; gap: 12px;
        }

        .sidebar-avatar {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 15px; color: #fff;
            flex-shrink: 0;
        }

        .sidebar-user-name  { font-size: 13px; font-weight: 600; color: #fff; }
        .sidebar-user-role  { font-size: 11px; color: rgba(255,255,255,0.55); }

        /* ── MAIN CONTENT ────────────────────────────── */
        #main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── TOP BAR ─────────────────────────────────── */
        #topbar {
            background: #fff;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            border-bottom: 1px solid #E8E8F0;
            position: sticky; top: 0; z-index: 40;
            box-shadow: 0 1px 8px rgba(0,0,0,0.05);
        }

        .page-title {
            font-size: 16px; font-weight: 600;
            color: #1E1B2E; letter-spacing: -0.2px;
        }

        #topbar .user-chip {
            display: flex; align-items: center; gap: 10px;
            cursor: pointer; position: relative;
        }

        .user-avatar-sm {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, var(--purple-main), var(--purple-light));
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: #fff;
        }

        /* ── CARDS ───────────────────────────────────── */
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(108,61,224,0.07);
            transition: box-shadow .2s;
        }
        .card:hover { box-shadow: 0 6px 24px rgba(108,61,224,0.13); }

        .stat-card {
            border-radius: 16px;
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            color: #fff;
        }

        .stat-card .stat-icon {
            width: 44px; height: 44px; border-radius: 12px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
        }

        .stat-card .stat-value {
            font-size: 26px; font-weight: 800;
            letter-spacing: -0.8px;
        }

        .stat-card .stat-label {
            font-size: 12px; font-weight: 500;
            opacity: 0.8; margin-top: 4px;
        }

        /* gradient backgrounds for stat cards */
        .bg-grad-purple  { background: linear-gradient(135deg, #6C3DE0, #8B5CF6); }
        .bg-grad-indigo  { background: linear-gradient(135deg, #4F46E5, #6C3DE0); }
        .bg-grad-pink    { background: linear-gradient(135deg, #EC4899, #F43F5E); }
        .bg-grad-emerald { background: linear-gradient(135deg, #059669, #10B981); }
        .bg-grad-amber   { background: linear-gradient(135deg, #D97706, #F59E0B); }
        .bg-grad-cyan    { background: linear-gradient(135deg, #0891B2, #06B6D4); }

        /* ── BADGE ───────────────────────────────────── */
        .badge-pending  { background: #FEF3C7; color: #92400E; border-radius: 6px; padding: 3px 10px; font-size: 12px; font-weight: 600; }
        .badge-billed   { background: #D1FAE5; color: #065F46; border-radius: 6px; padding: 3px 10px; font-size: 12px; font-weight: 600; }

        /* ── TABLE ───────────────────────────────────── */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead th {
            background: #F8F7FF;
            color: #6C3DE0;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            padding: 13px 16px;
            text-align: left;
            border-bottom: 2px solid #EDE9FF;
        }
        .data-table tbody td {
            padding: 13px 16px;
            font-size: 13.5px;
            color: #374151;
            border-bottom: 1px solid #F3F4F8;
        }
        .data-table tbody tr:hover { background: #FAFAFF; }

        /* ── BUTTONS ─────────────────────────────────── */
        .btn-primary {
            background: linear-gradient(135deg, #6C3DE0, #8B5CF6);
            color: #fff; border: none; border-radius: 10px;
            padding: 10px 20px; font-size: 13.5px; font-weight: 600;
            cursor: pointer; text-decoration: none; display: inline-flex;
            align-items: center; gap: 6px;
            transition: all .2s ease;
            box-shadow: 0 3px 12px rgba(108,61,224,0.3);
        }
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(108,61,224,0.4);
        }

        .btn-secondary {
            background: #F3F4F8; color: #4B5563; border: none;
            border-radius: 10px; padding: 10px 18px;
            font-size: 13.5px; font-weight: 600; cursor: pointer;
            text-decoration: none; display: inline-flex;
            align-items: center; gap: 6px;
            transition: all .2s;
        }
        .btn-secondary:hover { background: #E5E7EB; }

        .btn-danger {
            background: #FEE2E2; color: #B91C1C; border: none;
            border-radius: 8px; padding: 8px 14px;
            font-size: 13px; font-weight: 600; cursor: pointer;
            text-decoration: none; transition: all .2s;
        }
        .btn-danger:hover { background: #FCA5A5; }

        .btn-edit {
            background: #EDE9FF; color: #6C3DE0; border: none;
            border-radius: 8px; padding: 8px 14px;
            font-size: 13px; font-weight: 600; cursor: pointer;
            text-decoration: none; transition: all .2s;
        }
        .btn-edit:hover { background: #DDD6FE; }

        /* ── INPUTS ──────────────────────────────────── */
        .form-input {
            width: 100%; border: 1.5px solid #E5E7EB;
            border-radius: 10px; padding: 10px 14px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            background: #fff; color: #1E1B2E;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .form-input:focus {
            border-color: #8B5CF6;
            box-shadow: 0 0 0 3px rgba(139,92,246,0.15);
        }

        .form-label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 6px;
        }

        /* ── ALERT BOX ───────────────────────────────── */
        .alert-warning {
            background: #FFFBEB; border: 1.5px solid #FCD34D;
            border-radius: 12px; padding: 14px 18px;
            display: flex; align-items: flex-start; gap: 12px;
        }

        /* ── PAGE CONTENT ────────────────────────────── */
        .page-content { padding: 28px; flex: 1; }

        /* ── RESPONSIVE ──────────────────────────────── */
        @media (max-width: 992px) {
            #sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            #sidebar.open {
                transform: translateX(0);
            }
            #main-content {
                margin-left: 0 !important;
            }
            #topbar {
                padding: 0 16px;
            }
            .page-content {
                padding: 16px;
            }
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(30,27,46,0.5);
            backdrop-filter: blur(2px);
            z-index: 45;
            display: none;
        }
        .sidebar-overlay.show {
            display: block;
        }

        /* Grid stacking on mobile */
        @media (max-width: 768px) {
            .grid-cols-mobile-1 {
                grid-template-columns: 1fr !important;
            }
            .stat-card .stat-value {
                font-size: 22px;
            }
            .page-title {
                font-size: 14px;
            }
        }

        /* ── NOTIFICATION MODAL ──────────────────────── */
        .notif-modal-overlay {
            position: fixed; inset: 0;
            background: rgba(30,27,46,0.45);
            display: flex; align-items: center; justify-content: center;
            z-index: 100;
            backdrop-filter: blur(3px);
        }
        .notif-modal-box {
            background: #fff; border-radius: 18px;
            padding: 28px 32px; max-width: 400px; width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            animation: slideUp .25s ease;
        }
        @keyframes slideUp {
            from { transform: translateY(20px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        /* ── DROPDOWN ────────────────────────────────── */
        .dropdown-menu {
            position: absolute; right: 0; top: calc(100% + 8px);
            background: #fff; border-radius: 12px; min-width: 180px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            border: 1px solid #E8E8F0;
            overflow: hidden; z-index: 50;
        }
        .dropdown-menu a, .dropdown-menu button {
            display: flex; align-items: center; gap: 10px;
            padding: 11px 16px; font-size: 13.5px;
            color: #374151; text-decoration: none;
            background: none; border: none; width: 100%; text-align: left;
            cursor: pointer; transition: background .15s;
        }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background: #F8F7FF; }
        .dropdown-menu .divider { height: 1px; background: #F3F4F8; margin: 4px 0; }
    </style>
</head>

<body x-data="appLayout()" @keydown.escape="closeAll()">

    <div class="sidebar-overlay" :class="{ 'show': sidebarOpen }" @click="sidebarOpen = false"></div>

    <!-- ══ SIDEBAR ══════════════════════════════════════ -->
    <aside id="sidebar" :class="{ 'open': sidebarOpen }">
        <div class="sidebar-logo">
            <div class="logo-icon">D</div>
            <div>
                <div class="logo-text">Distri-App</div>
                <div class="logo-sub">Sistema de Pedidos</div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Principal</div>

            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Dashboard
            </a>

            @if(Auth::user()->role_id === 1 || Auth::user()->role_id === 3)
            <a href="{{ route('orders.index') }}" class="nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Órdenes
            </a>
            <a href="{{ route('products.index') }}" class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Inventario
            </a>
            @else
            <a href="{{ route('orders.index') }}" class="nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Mis Órdenes
            </a>
            @endif

            <a href="{{ route('customer-details.index') }}" class="nav-item {{ request()->routeIs('customer-details.*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
                Clientes
            </a>

            <div class="sidebar-section-label">Reportes</div>

            <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 17v-2m3 2v-4m3 4v-6M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                </svg>
                Reportes y Exportar
            </a>

            @if(Auth::user()->role_id === 1 || Auth::user()->role_id === 3)
            <div class="sidebar-section-label">Administración</div>

            <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
                Usuarios
            </a>
            @endif

            @if(Auth::user()->role_id === 1)
            <a href="{{ route('companies.index') }}" class="nav-item {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 21h18M3 10h18M3 7l9-4 9 4M4 10v11M20 10v11M8 14v3M12 14v3M16 14v3"/>
                </svg>
                Empresas
            </a>
            @endif
        </nav>

        <!-- User card -->
        <div class="sidebar-user" x-data="{ open: false }" @click.away="open = false">
            <div class="sidebar-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            <div style="flex:1; min-width:0;">
                <div class="sidebar-user-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ Auth::user()->name }}
                </div>
                <div class="sidebar-user-role">
                    @if(Auth::user()->role_id === 1) Admin
                    @elseif(Auth::user()->role_id === 3) Supervisor
                    @else Distribuidor
                    @endif
                </div>
            </div>
            <button @click="open = !open" style="background:none;border:none;color:rgba(255,255,255,0.6);cursor:pointer;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>

            <div x-show="open" x-transition class="dropdown-menu" style="bottom:70px;top:auto;left:12px;right:12px;">
                <a href="{{ route('profile.edit') }}">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                    </svg>
                    Mi Perfil
                </a>
                <div class="divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ══ MAIN ══════════════════════════════════════════ -->
    <div id="main-content">

        <!-- TOP BAR -->
        <header id="topbar">
            <div style="display:flex;align-items:center;gap:14px;">
                <!-- Hamburger (mobile) -->
                <button @click="sidebarOpen = !sidebarOpen" class="sm:hidden"
                    style="background:none;border:none;cursor:pointer;color:#6C3DE0;">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <span class="page-title">{{ $header ?? 'Dashboard' }}</span>
            </div>

            <!-- Right side -->
            <div style="display:flex;align-items:center;gap:16px;">
                <!-- Notificación de alerta si hay clientes inactivos -->
                @if(isset($inactiveCustomers) && $inactiveCustomers->count() > 0)
                <a href="{{ route('dashboard') }}#inactive-customers"
                   style="position:relative;color:#D97706;text-decoration:none;"
                   title="{{ $inactiveCustomers->count() }} clientes sin pedidos">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span style="position:absolute;top:-5px;right:-5px;background:#EF4444;color:#fff;border-radius:50%;width:18px;height:18px;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;">
                        {{ $inactiveCustomers->count() }}
                    </span>
                </a>
                @endif

                <!-- User chip -->
                <div x-data="{ open: false }" @click.away="open = false" class="user-chip">
                    <div @click="open = !open" style="display:flex;align-items:center;gap:10px;">
                        <div class="user-avatar-sm">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                        <div style="display:none;" class="sm:block">
                            <div style="font-size:13px;font-weight:600;color:#1E1B2E;">{{ Auth::user()->name }}</div>
                            <div style="font-size:11px;color:#9CA3AF;">
                                @if(Auth::user()->role_id===1) Administrador
                                @elseif(Auth::user()->role_id===3) Supervisor
                                @else Distribuidor
                                @endif
                            </div>
                        </div>
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>

                    <div x-show="open" x-transition class="dropdown-menu">
                        <a href="{{ route('profile.edit') }}">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                            </svg>
                            Mi Perfil
                        </a>
                        <div class="divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="page-content">
            {{ $slot }}
        </main>
    </div>

    <!-- ══ NOTIFICATION MODAL ═══════════════════════════ -->
    <div x-show="notifOpen" x-transition class="notif-modal-overlay" @click.self="notifOpen = false">
        <div class="notif-modal-box" @click.stop>
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
                <div :style="notifType === 'success'
                    ? 'background:#D1FAE5;border-radius:50%;padding:10px;'
                    : 'background:#FEE2E2;border-radius:50%;padding:10px;'">
                    <svg x-show="notifType === 'success'" width="22" height="22" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M5 13l4 4L19 7"/>
                    </svg>
                    <svg x-show="notifType !== 'success'" width="22" height="22" fill="none" stroke="#B91C1C" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:16px;font-weight:700;color:#1E1B2E;" x-text="notifTitle"></div>
                    <div style="font-size:13.5px;color:#6B7280;margin-top:3px;" x-text="notifMessage"></div>
                </div>
            </div>
            <button @click="notifOpen = false" class="btn-primary" style="width:100%;justify-content:center;">
                Entendido
            </button>
        </div>
    </div>

    @stack('scripts')

    @if (session('notification'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window._initNotif = {
                type:    "{{ session('notification.type') }}",
                title:   "{{ session('notification.title') }}",
                message: "{{ session('notification.message') }}",
            };
        });
    </script>
    @endif

    <script>
        function appLayout() {
            return {
                sidebarOpen: false,
                notifOpen:   false,
                notifType:   'success',
                notifTitle:  '',
                notifMessage: '',

                init() {
                    if (window._initNotif) {
                        this.notifType    = window._initNotif.type;
                        this.notifTitle   = window._initNotif.title;
                        this.notifMessage = window._initNotif.message;
                        this.notifOpen    = true;
                    }
                },

                closeAll() {
                    this.sidebarOpen = false;
                    this.notifOpen   = false;
                }
            };
        }

        function goBack() { window.history.back(); }
    </script>
</body>
</html>
