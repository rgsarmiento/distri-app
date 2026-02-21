<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión — Distri-App</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #F3F4F8;
        }

        /* ── LEFT PANEL ────────────────────────── */
        .panel-left {
            flex: 1;
            background: linear-gradient(150deg, #4C1D95 0%, #6C3DE0 55%, #8B5CF6 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 52px;
            position: relative;
            overflow: hidden;
        }

        .panel-left::before {
            content: '';
            position: absolute;
            top: -80px; right: -80px;
            width: 320px; height: 320px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .panel-left::after {
            content: '';
            position: absolute;
            bottom: -60px; left: -60px;
            width: 280px; height: 280px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .logo-area { position: relative; z-index: 1; }
        .logo-box {
            width: 52px; height: 52px; border-radius: 14px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: 800; color: #fff;
            margin-bottom: 14px;
        }
        .logo-title { font-size: 26px; font-weight: 800; color: #fff; }
        .logo-sub   { font-size: 13px; color: rgba(255,255,255,0.65); margin-top: 6px; }

        .hero-text {
            position: relative; z-index: 1;
        }
        .hero-text h2 {
            font-size: 28px; font-weight: 800; color: #fff;
            line-height: 1.3; letter-spacing: -0.5px;
        }
        .hero-text p {
            font-size: 14px; color: rgba(255,255,255,0.7);
            margin-top: 12px; line-height: 1.7;
        }

        .features { position: relative; z-index: 1; }
        .feature-item {
            display: flex; align-items: center; gap: 14px;
            margin-bottom: 14px;
        }
        .feature-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: rgba(255,255,255,0.15);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .feature-text { font-size: 13px; color: rgba(255,255,255,0.8); }
        .feature-text strong { color: #fff; display: block; font-size: 13.5px; }

        /* ── RIGHT PANEL ───────────────────────── */
        .panel-right {
            width: 480px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 52px;
            background: #fff;
        }

        .login-form { width: 100%; max-width: 360px; }

        .form-title {
            font-size: 26px; font-weight: 800;
            color: #1E1B2E; margin-bottom: 6px;
            letter-spacing: -0.5px;
        }
        .form-subtitle { font-size: 14px; color: #9CA3AF; margin-bottom: 32px; }

        .field-group { margin-bottom: 18px; }

        .field-label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 7px;
        }

        .field-input {
            width: 100%;
            border: 1.5px solid #E5E7EB;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #1E1B2E;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .field-input:focus {
            border-color: #8B5CF6;
            box-shadow: 0 0 0 3px rgba(139,92,246,0.15);
        }

        .error-text { color: #EF4444; font-size: 11.5px; margin-top: 5px; }

        .remember-row {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px;
        }
        .remember-label {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: #6B7280; cursor: pointer;
        }
        .remember-label input[type='checkbox'] { accent-color: #6C3DE0; }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #6C3DE0, #8B5CF6);
            color: #fff; border: none; border-radius: 10px;
            padding: 13px; font-size: 15px; font-weight: 700;
            cursor: pointer; font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 16px rgba(108,61,224,0.35);
            transition: all .2s;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(108,61,224,0.4);
        }
        .btn-login:active { transform: translateY(0); }

        .error-alert {
            background: #FEF2F2; border: 1.5px solid #FCA5A5;
            border-radius: 10px; padding: 13px 16px;
            font-size: 13px; color: #B91C1C;
            margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
        }

        @media (max-width: 900px) {
            .panel-left { display: none; }
            .panel-right { width: 100%; padding: 36px 24px; }
        }
    </style>
</head>
<body>

    <!-- LEFT -->
    <div class="panel-left">
        <div class="logo-area">
            <div class="logo-box">D</div>
            <div class="logo-title">Distri-App</div>
            <div class="logo-sub">Sistema de Gestión de Pedidos</div>
        </div>

        <div class="hero-text">
            <h2>Gestiona tus pedidos<br>con inteligencia</h2>
            <p>Plataforma centralizada para distribuidores y supervisores. Controla tus órdenes, clientes y reportes en tiempo real.</p>
        </div>

        <div class="features">
            <div class="feature-item">
                <div class="feature-icon">
                    <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="feature-text">
                    <strong>Gestión de Pedidos</strong>
                    Crea, edita y rastrea órdenes en tiempo real
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M9 17v-2m3 2v-4m3 4v-6M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                    </svg>
                </div>
                <div class="feature-text">
                    <strong>Reportes Excel y PDF</strong>
                    Exporta datos para análisis y facturación
                </div>
            </div>
            <div class="feature-item">
                <div class="feature-icon">
                    <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-4-5.659V5a2 2 0 10-4 0v.341A6 6 0 006 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div class="feature-text">
                    <strong>Alertas de Clientes</strong>
                    Detecta clientes inactivos automáticamente
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="panel-right">
        <div class="login-form">
            <div class="form-title">Bienvenido de vuelta</div>
            <div class="form-subtitle">Ingresa tus credenciales para continuar</div>

            <!-- Session error -->
            @if ($errors->any())
            <div class="error-alert">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                {{ $errors->first() }}
            </div>
            @endif

            @if (session('status'))
            <div class="error-alert" style="background:#D1FAE5;border-color:#6EE7B7;color:#065F46;">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field-group">
                    <label for="email" class="field-label">Correo Electrónico</label>
                    <input id="email" type="email" name="email" class="field-input"
                           value="{{ old('email') }}" required autofocus
                           placeholder="correo@ejemplo.com">
                    @error('email')
                    <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field-group">
                    <label for="password" class="field-label">Contraseña</label>
                    <input id="password" type="password" name="password" class="field-input"
                           required placeholder="••••••••">
                    @error('password')
                    <span class="error-text">{{ $message }}</span>
                    @enderror
                </div>

                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember">
                        Recordarme
                    </label>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       style="font-size:13px;color:#6C3DE0;text-decoration:none;font-weight:500;">
                        ¿Olvidaste tu contraseña?
                    </a>
                    @endif
                </div>

                <button type="submit" class="btn-login">
                    Iniciar Sesión
                </button>
            </form>
        </div>
    </div>

</body>
</html>
