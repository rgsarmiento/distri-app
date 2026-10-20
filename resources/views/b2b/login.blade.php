<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal B2B Clientes — Acceso Mayorista</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0F0E17;
            color: #1E1B2E;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .b2b-bg-gradient {
            position: fixed;
            inset: 0;
            background: radial-gradient(circle at 15% 20%, rgba(108,61,224,0.35) 0%, transparent 45%),
                        radial-gradient(circle at 85% 80%, rgba(79,70,229,0.25) 0%, transparent 45%),
                        #0F0E17;
            z-index: 0;
        }

        .b2b-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 980px;
            margin: 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #ffffff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255,255,255,0.08);
        }

        @media (max-width: 768px) {
            .b2b-wrapper {
                grid-template-columns: 1fr;
                max-width: 440px;
            }
            .b2b-side-info {
                display: none !important;
            }
        }

        .b2b-side-info {
            background: linear-gradient(150deg, #4C1D95 0%, #6C3DE0 60%, #4338CA 100%);
            padding: 48px 40px;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .b2b-side-info::after {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 240px;
            height: 240px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
            pointer-events: none;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 22px;
        }

        .feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(255,255,255,0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .form-container {
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .input-group {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #4B5563;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #9CA3AF;
            pointer-events: none;
            display: flex;
            align-items: center;
        }

        .b2b-input {
            width: 100%;
            padding: 13px 14px 13px 44px;
            font-size: 14px;
            font-family: inherit;
            color: #1E1B2E;
            background: #F9FAFB;
            border: 1.5px solid #E5E7EB;
            border-radius: 12px;
            outline: none;
            transition: all 0.2s ease;
        }

        .b2b-input:focus {
            background: #ffffff;
            border-color: #6C3DE0;
            box-shadow: 0 0 0 4px rgba(108,61,224,0.12);
        }

        .btn-b2b-submit {
            background: linear-gradient(135deg, #6C3DE0 0%, #4F46E5 100%);
            color: #ffffff;
            font-weight: 800;
            font-size: 15px;
            border: none;
            border-radius: 12px;
            padding: 14px;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 10px 20px -5px rgba(108,61,224,0.4);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .btn-b2b-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 26px -5px rgba(108,61,224,0.5);
        }

        .btn-b2b-submit:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="b2b-bg-gradient"></div>

    <div class="b2b-wrapper">
        {{-- Lado Izquierdo: Beneficios B2B --}}
        <div class="b2b-side-info">
            <div>
                <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,0.15); padding:6px 14px; border-radius:30px; font-size:12px; font-weight:700; margin-bottom:28px; backdrop-filter:blur(6px);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Portal Comercial Mayorista
                </div>

                <h1 style="font-size:30px; font-weight:800; margin:0 0 12px 0; line-height:1.2;">
                    Haz tus pedidos directamente con tu Distribuidora
                </h1>
                <p style="font-size:14px; color:rgba(255,255,255,0.8); margin:0 0 36px 0; line-height:1.6;">
                    Acceso preferencial para clientes comerciales. Consulta inventario, tarifas con descuento y envía pedidos en cualquier momento.
                </p>

                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:14px;">Precios y Descuentos Exclusivos</div>
                        <div style="font-size:12px; color:rgba(255,255,255,0.7); margin-top:2px;">Tu tarifa preferencial se calcula de forma automática en cada ítem.</div>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:14px;">Disponibilidad de Bodega en Vivo</div>
                        <div style="font-size:12px; color:rgba(255,255,255,0.7); margin-top:2px;">Consulta el stock actualizado sincronizado con el sistema central.</div>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight:700; font-size:14px;">Seguimiento y Trazabilidad</div>
                        <div style="font-size:12px; color:rgba(255,255,255,0.7); margin-top:2px;">Sigue el estado de alistamiento y despacho de cada orden en tiempo real.</div>
                    </div>
                </div>
            </div>

            <div style="font-size:11px; color:rgba(255,255,255,0.5); padding-top:20px; border-top:1px solid rgba(255,255,255,0.12);">
                DistriApp B2B • Conexión en tiempo real con Nodo ERP
            </div>
        </div>

        {{-- Lado Derecho: Formulario de Autenticación --}}
        <div class="form-container">
            <div style="margin-bottom:28px;">
                <div style="width:48px; height:48px; background:#F5F3FF; border:1px solid #DDD6FE; border-radius:14px; display:flex; align-items:center; justify-content:center; color:#6C3DE0; margin-bottom:14px;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <h2 style="font-size:24px; font-weight:800; color:#1E1B2E; margin:0 0 6px 0;">Iniciar Sesión B2B</h2>
                <p style="font-size:13px; color:#6B7280; margin:0;">
                    Ingresa con las credenciales web facilitadas por tu distribuidora.
                </p>
            </div>

            @if($errors->any())
            <div style="background:#FEF2F2; border:1px solid #FCA5A5; color:#991B1B; padding:12px 16px; border-radius:12px; font-size:13px; margin-bottom:22px; display:flex; align-items:center; gap:10px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
                </svg>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form action="{{ route('b2b.login.post') }}" method="POST">
                @csrf

                <div class="input-group">
                    <label>Usuario Comercial</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input type="text" 
                               name="web_user" 
                               value="{{ old('web_user') }}" 
                               required 
                               autofocus 
                               class="b2b-input" 
                               placeholder="Ej: micomercio o NIT...">
                    </div>
                </div>

                <div class="input-group">
                    <label>Contraseña</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </span>
                        <input type="password" 
                               id="web_password"
                               name="web_password" 
                               required 
                               class="b2b-input" 
                               placeholder="••••••••"
                               style="padding-right:44px;">
                        <button type="button" 
                                onclick="togglePasswordVisibility()" 
                                style="position:absolute; right:14px; background:none; border:none; color:#9CA3AF; cursor:pointer; padding:0; display:flex;">
                            <svg id="eye_icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center;">
                    <div style="font-size:12px; color:#6B7280;">
                        ¿No tienes credenciales?
                    </div>
                    <span style="font-size:12px; color:#6C3DE0; font-weight:600; cursor:pointer;" onclick="alert('Por favor contacta a tu asesor comercial o preventista habitual para que active tu usuario B2B.');">
                        Solicitar acceso
                    </span>
                </div>

                <button type="submit" class="btn-b2b-submit">
                    <span>Ingresar al Catálogo</span>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </form>

            <div style="margin-top:32px; padding-top:20px; border-top:1px solid #F3F4F8; text-align:center;">
                <a href="{{ route('login') }}" style="font-size:13px; color:#4B5563; text-decoration:none; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    <span>Acceso para Vendedores y Administradores</span>
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('web_password');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
            } else {
                pwdInput.type = 'password';
            }
        }
    </script>
</body>
</html>
