<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal B2B Clientes - DistriApp</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#F4F5F9; font-family:'Plus Jakarta Sans', sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px;">
    <div class="card" style="max-width:420px; width:100%; padding:36px; border-radius:16px; box-shadow:0 15px 35px rgba(0,0,0,0.06);">
        <div style="text-align:center; margin-bottom:28px;">
            <div style="width:56px; height:56px; background:#6C3DE0; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; font-size:26px; color:#fff; margin-bottom:12px;">
                🛒
            </div>
            <h2 style="font-size:22px; font-weight:800; color:#1E1B2E; margin:0;">Portal Clientes B2B</h2>
            <p style="font-size:13px; color:#6B7280; margin:6px 0 0 0;">Ingresa con tus credenciales asignadas por tu distribuidora</p>
        </div>

        @if($errors->any())
        <div style="background:#FEE2E2; border:1px solid #FECACA; color:#991B1B; padding:12px 14px; border-radius:8px; font-size:13px; margin-bottom:20px;">
            {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('b2b.login.post') }}" method="POST">
            @csrf

            <div style="margin-bottom:16px;">
                <label class="form-label" style="font-size:12px; font-weight:700;">Usuario Web</label>
                <input type="text" name="web_user" value="{{ old('web_user') }}" required autofocus class="form-input" placeholder="Tu usuario comercial..." style="padding:12px; font-size:14px;">
            </div>

            <div style="margin-bottom:24px;">
                <label class="form-label" style="font-size:12px; font-weight:700;">Contraseña</label>
                <input type="password" name="web_password" required class="form-input" placeholder="••••••••" style="padding:12px; font-size:14px;">
            </div>

            <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:14px; font-size:15px; font-weight:800; border-radius:10px;">
                Iniciar Sesión Comercial
            </button>
        </form>

        <div style="margin-top:24px; text-align:center;">
            <a href="{{ route('login') }}" style="font-size:12px; color:#6C3DE0; text-decoration:none; font-weight:600;">
                ¿Eres preventista o administrador? Ingresa aquí →
            </a>
        </div>
    </div>
</body>
</html>
