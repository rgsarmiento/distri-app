<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal B2B Mayorista — {{ $customer->full_name }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --purple-dark: #4C1D95;
            --purple-main: #6C3DE0;
            --purple-light: #8B5CF6;
            --purple-glow: rgba(108,61,224,0.18);
            --bg-main: #F4F5F9;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-main);
            color: #1E1B2E;
            min-height: 100vh;
        }

        /* ── NAVBAR ─────────────────────────────────────── */
        .b2b-header {
            background: #ffffff;
            border-bottom: 1px solid #E5E7EB;
            padding: 14px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }

        .brand-pill {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: linear-gradient(135deg, #6C3DE0 0%, #4338CA 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(108,61,224,0.3);
        }

        /* ── TABS NAVEGACIÓN ────────────────────────────── */
        .nav-tabs {
            display: flex;
            gap: 8px;
            background: #E5E7EB;
            padding: 4px;
            border-radius: 12px;
        }

        .tab-btn {
            padding: 8px 18px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #4B5563;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn.active {
            background: #ffffff;
            color: #6C3DE0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        /* ── TARJETAS Y CONTENEDORES ────────────────────── */
        .b2b-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            overflow: hidden;
        }

        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 18px;
        }

        .product-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1.5px solid #F3F4F8;
            padding: 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-3px);
            border-color: #DDD6FE;
            box-shadow: 0 10px 24px -5px rgba(108,61,224,0.12);
        }

        .product-icon-wrap {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748B;
            font-size: 22px;
            flex-shrink: 0;
        }

        /* ── STEPPER DE CANTIDAD ───────────────────────── */
        .stepper {
            display: flex;
            align-items: center;
            background: #F3F4F6;
            border-radius: 10px;
            padding: 3px;
            border: 1.5px solid #E5E7EB;
            flex-shrink: 0;
        }

        .stepper button {
            background: #ffffff;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 16px;
            color: #4B5563;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            transition: all 0.15s;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
        }

        .stepper button:hover {
            background: #EDE9FE;
            color: #6C3DE0;
        }

        .stepper button:active {
            transform: scale(0.92);
        }

        .stepper input {
            width: 58px;
            min-width: 58px;
            height: 32px;
            text-align: center;
            background: #ffffff;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
            font-weight: 800;
            font-size: 15px;
            color: #1E1B2E;
            outline: none;
            margin: 0 3px;
            padding: 0 2px;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.04);
            -moz-appearance: textfield;
        }

        .stepper input::-webkit-outer-spin-button,
        .stepper input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .stepper input:focus {
            border-color: #6C3DE0;
            box-shadow: 0 0 0 2px rgba(108,61,224,0.15);
        }

        /* ── BOTONES ───────────────────────────────────── */
        .btn-add-cart {
            background: #6C3DE0;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: all 0.2s;
            width: 100%;
        }

        .btn-add-cart:hover {
            background: #5B21B6;
            transform: scale(1.01);
        }

        .btn-checkout {
            background: linear-gradient(135deg, #6C3DE0 0%, #4F46E5 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 8px 20px -4px rgba(108,61,224,0.4);
            transition: all 0.2s;
        }

        .btn-checkout:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 26px -4px rgba(108,61,224,0.5);
        }

        .btn-checkout:disabled {
            background: #D1D5DB;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* ── BADGES DE ESTADO ─────────────────────────── */
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-crudo     { background: #FEF3C7; color: #92400E; border: 1px solid #FDE68A; }
        .status-separado  { background: #E0E7FF; color: #3730A3; border: 1px solid #C7D2FE; }
        .status-revisado  { background: #F3E8FF; color: #6B21A8; border: 1px solid #E9D5FF; }
        .status-depurado  { background: #CCFBF1; color: #115E59; border: 1px solid #99F6E4; }
        .status-facturado { background: #D1FAE5; color: #065F46; border: 1px solid #A7F3D0; }
        .status-anulado   { background: #FEE2E2; color: #991B1B; border: 1px solid #FECACA; }

        /* ── MODAL ─────────────────────────────────────── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 200;
            padding: 20px;
        }

        .modal-card {
            background: #ffffff;
            border-radius: 20px;
            max-width: 650px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalFadeIn 0.2s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.96); }
            to { opacity: 1; transform: scale(1); }
        }

        @media (max-width: 992px) {
            .portal-layout {
                grid-template-columns: 1fr !important;
            }
            .cart-sidebar-wrapper {
                position: relative !important;
                top: 0 !important;
            }
        }
    </style>
</head>
<body>

    {{-- ── BARRA SUPERIOR ────────────────────────────────────── --}}
    <header class="b2b-header">
        <div class="brand-pill">
            <div class="brand-icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                    <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <div>
                <div style="font-size:16px; font-weight:800; color:#1E1B2E; display:flex; align-items:center; gap:8px;">
                    <span>{{ $customer->full_name }}</span>
                    <span style="font-size:11px; font-weight:700; background:#EDE9FE; color:#6C3DE0; padding:2px 8px; border-radius:6px; font-family:monospace;">
                        NIT: {{ $customer->identification }}
                    </span>
                </div>
                <div style="font-size:12px; color:#6B7280; margin-top:2px;">
                    Distribuidora: <strong>{{ $customer->company->name ?? 'DistriApp' }}</strong> • Ruta: R-{{ $customer->route_id ?: '0' }}
                </div>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:16px;">
            @if($customer->discount_rate > 0)
            <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:6px 14px; border-radius:30px; font-size:12px; font-weight:800; display:flex; align-items:center; gap:6px;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>🎯 Descuento: {{ $customer->discount_rate }}%</span>
            </div>
            @endif

            <form action="{{ route('b2b.logout') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" style="background:#F3F4F6; border:1px solid #E5E7EB; color:#4B5563; font-weight:700; font-size:12px; padding:8px 14px; border-radius:10px; cursor:pointer; display:flex; align-items:center; gap:6px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </header>

    <div style="max-width:1380px; margin:24px auto; padding:0 24px;">

        @if(session('success_message'))
        <div style="background:#ECFDF5; border:1.5px solid #10B981; color:#065F46; padding:16px 20px; border-radius:14px; margin-bottom:24px; font-weight:700; display:flex; align-items:center; gap:12px; box-shadow:0 4px 12px rgba(16,185,129,0.1);">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div style="flex:1;">
                <div style="font-size:15px;">{{ session('success_message') }}</div>
                <div style="font-size:12px; opacity:0.85; margin-top:2px;">Puedes monitorear el progreso de tu pedido en la pestaña "Mis Pedidos".</div>
            </div>
        </div>
        @endif

        {{-- ── NAVEGACIÓN POR PESTAÑAS ──────────────────────────── --}}
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:16px;">
            <div class="nav-tabs">
                <button type="button" class="tab-btn active" onclick="switchTab('catalog')" id="tab_btn_catalog">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>Catálogo y Pedido</span>
                </button>
                <button type="button" class="tab-btn" onclick="switchTab('orders')" id="tab_btn_orders">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span>Mis Pedidos ({{ $myOrders->count() }})</span>
                </button>
                <button type="button" class="tab-btn" onclick="switchTab('account')" id="tab_btn_account">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <span>Mi Cuenta y Asesor</span>
                </button>
            </div>

            {{-- Resumen Rápido / Notificación de Días de Visita --}}
            <div style="font-size:12px; color:#6B7280; display:flex; align-items:center; gap:8px;">
                <span>📅 Días de Atención:</span>
                @php
                    $days = [
                        'visit_monday' => 'Lun',
                        'visit_tuesday' => 'Mar',
                        'visit_wednesday' => 'Mié',
                        'visit_thursday' => 'Jue',
                        'visit_friday' => 'Vie',
                        'visit_saturday' => 'Sáb',
                    ];
                @endphp
                <div style="display:flex; gap:4px;">
                    @foreach($days as $col => $label)
                        @if($customer->$col)
                            <span style="background:#EDE9FE; color:#6C3DE0; font-weight:800; font-size:10px; padding:2px 6px; border-radius:4px;">{{ $label }}</span>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: CATÁLOGO Y CARRITO                                --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div id="tab_content_catalog">
            <div class="portal-layout" style="display:grid; grid-template-columns:1fr 380px; gap:26px; align-items:start;">

                {{-- Catálogo de Productos --}}
                <div>
                    {{-- Barra de Búsqueda y Filtros --}}
                    <div class="b2b-card" style="padding:18px 22px; margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;">
                            <div>
                                <h2 style="font-size:18px; font-weight:800; color:#1E1B2E; margin:0;">Catálogo de Productos Mayorista</h2>
                                <p style="font-size:12px; color:#6B7280; margin:2px 0 0 0;">
                                    Precios calculados con tu tarifa preferencial del {{ $customer->discount_rate }}% e impuestos correspondientes.
                                </p>
                            </div>
                            
                            <div style="display:flex; gap:10px; align-items:center; width:100%; max-width:320px;">
                                <div style="position:relative; width:100%;">
                                    <input type="text" 
                                           id="catalog_search" 
                                           oninput="filterCatalog()" 
                                           placeholder="Buscar por nombre o código..." 
                                           class="form-input" 
                                           style="width:100%; padding:9px 12px 9px 36px; font-size:13px; border-radius:10px; border:1.5px solid #E5E7EB; background:#F9FAFB;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position:absolute; left:12px; top:11px; color:#9CA3AF;">
                                        <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- Chips de filtrado rápido --}}
                        <div style="display:flex; gap:8px; margin-top:14px; border-top:1px solid #F3F4F8; padding-top:12px;">
                            <button type="button" onclick="setQuickFilter('all')" id="qf_all" class="qf-btn" style="background:#6C3DE0; color:#fff; border:none; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; cursor:pointer;">
                                Todos ({{ $products->count() }})
                            </button>
                            <button type="button" onclick="setQuickFilter('stock')" id="qf_stock" class="qf-btn" style="background:#F3F4F6; color:#4B5563; border:none; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; cursor:pointer;">
                                Con Stock Disponible
                            </button>
                        </div>
                    </div>

                    {{-- Cuadrícula de Productos --}}
                    <div id="catalog_grid" class="catalog-grid">
                        @forelse($products as $product)
                        @php
                            $rawPrice = (float) ($product->base_price_1 ?: $product->base_price);
                            $discountFactor = $customer->discount_rate > 0 ? (1 - ($customer->discount_rate / 100)) : 1;
                            $discountedBase = round($rawPrice * $discountFactor, 2);
                            $taxRate = (float) $product->tax_rate;
                            $finalPriceWithTax = round($discountedBase * (1 + ($taxRate / 100)), 2);
                            $originalPriceWithTax = round($rawPrice * (1 + ($taxRate / 100)), 2);
                            $savings = $originalPriceWithTax - $finalPriceWithTax;
                            $hasStock = $product->stock > 0;
                        @endphp
                        <div class="product-card catalog-item" 
                             data-name="{{ strtolower($product->name) }}" 
                             data-code="{{ strtolower($product->code) }}"
                             data-stock="{{ $product->stock }}">
                            
                            <div>
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                                    <div class="product-icon-wrap">
                                        📦
                                    </div>
                                    <div style="text-align:right;">
                                        <span style="font-size:11px; font-weight:800; color:#6C3DE0; background:#F5F3FF; padding:3px 8px; border-radius:6px; font-family:monospace; display:inline-block; margin-bottom:4px;">
                                            {{ $product->code }}
                                        </span>
                                        <div>
                                            @if($product->stock > 10)
                                                <span style="font-size:11px; font-weight:700; color:#059669; background:#ECFDF5; padding:2px 8px; border-radius:6px;">
                                                    ● {{ (int)$product->stock }} disp.
                                                </span>
                                            @elseif($product->stock > 0)
                                                <span style="font-size:11px; font-weight:700; color:#D97706; background:#FEF3C7; padding:2px 8px; border-radius:6px;">
                                                    ⚠️ {{ (int)$product->stock }} disp.
                                                </span>
                                            @else
                                                <span style="font-size:11px; font-weight:700; color:#059669; background:#ECFDF5; padding:2px 8px; border-radius:6px;">
                                                    ● Disponible
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div style="font-weight:700; font-size:15px; color:#1E1B2E; line-height:1.35; margin-bottom:8px; min-height:40px;">
                                    {{ $product->name }}
                                </div>

                                <div style="font-size:11px; color:#6B7280; margin-bottom:12px;">
                                    IVA: {{ number_format($taxRate, 0) }}% incluido
                                </div>
                            </div>

                            <div style="border-top:1px solid #F3F4F8; padding-top:14px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:12px;">
                                    <div>
                                        <div style="font-size:11px; color:#6B7280; text-transform:uppercase; font-weight:700;">Tu Precio B2B</div>
                                        <div style="font-size:20px; font-weight:800; color:#6C3DE0;">
                                            ${{ number_format($finalPriceWithTax, 2, ',', '.') }}
                                        </div>
                                    </div>
                                    @if($savings > 0)
                                    <div style="text-align:right;">
                                        <div style="font-size:11px; color:#9CA3AF; text-decoration:line-through;">
                                            ${{ number_format($originalPriceWithTax, 2, ',', '.') }}
                                        </div>
                                        <div style="font-size:11px; font-weight:800; color:#059669; background:#ECFDF5; padding:2px 6px; border-radius:4px;">
                                            Ahorro -${{ number_format($savings, 2, ',', '.') }}
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                {{-- Controles de Adición --}}
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <div class="stepper">
                                        <button type="button" onclick="decrementLocalQty({{ $product->id }})" title="Disminuir cantidad">-</button>
                                        <input type="number" 
                                               id="qty_{{ $product->id }}" 
                                               value="1" 
                                               min="1" 
                                               onchange="onLocalQtyInputChange({{ $product->id }})"
                                               oninput="if(this.value < 1 && this.value !== '') this.value = 1;"
                                               title="Ingresa la cantidad deseada">
                                        <button type="button" onclick="incrementLocalQty({{ $product->id }})" title="Aumentar cantidad">+</button>
                                    </div>

                                    <button type="button" 
                                            id="btn_add_{{ $product->id }}"
                                            onclick="addLocalToCart({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->code }}', {{ $finalPriceWithTax }}, {{ $originalPriceWithTax }})" 
                                            class="btn-add-cart">
                                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>Agregar</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div style="grid-column: 1 / -1; padding:40px; text-align:center; color:#9CA3AF;">
                            No hay productos disponibles en este momento.
                        </div>
                        @endforelse
                    </div>
                </div>

                {{-- Barra Lateral del Carrito --}}
                <div class="cart-sidebar-wrapper" style="position:sticky; top:86px;">
                    <div class="b2b-card" style="padding:22px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="font-size:18px;">🛒</span>
                                <h3 style="font-size:16px; font-weight:800; color:#1E1B2E; margin:0;">Resumen del Pedido</h3>
                            </div>
                            <span id="cart_badge" style="background:#6C3DE0; color:#fff; font-size:11px; font-weight:800; padding:3px 10px; border-radius:20px;">
                                0 ítems
                            </span>
                        </div>

                        <form action="{{ route('b2b.order.store') }}" method="POST" id="b2b_order_form">
                            @csrf

                            {{-- Contenedor de Ítems --}}
                            <div id="cart_items_container" style="max-height:300px; overflow-y:auto; display:flex; flex-direction:column; gap:10px; margin-bottom:18px; padding-right:4px;">
                                <div id="empty_cart_msg" style="text-align:center; padding:36px 12px; color:#9CA3AF; font-size:13px; background:#F9FAFB; border-radius:12px; border:1px dashed #E5E7EB;">
                                    <div style="font-size:28px; margin-bottom:6px;">🛍️</div>
                                    Tu pedido está vacío.<br>Elige productos del catálogo para comenzar.
                                </div>
                            </div>

                            {{-- Observaciones del Pedido --}}
                            <div style="margin-bottom:18px;">
                                <label style="display:block; font-size:11px; font-weight:700; color:#4B5563; text-transform:uppercase; margin-bottom:6px;">
                                    Instrucciones de Entrega / Notas:
                                </label>
                                <textarea name="observations" 
                                          rows="2" 
                                          placeholder="Ej: Entregar en horario de mañana, puerta lateral..." 
                                          style="width:100%; padding:10px; font-size:13px; font-family:inherit; border:1.5px solid #E5E7EB; border-radius:10px; background:#F9FAFB; outline:none; resize:none;"></textarea>
                            </div>

                            {{-- Desglose de Totales --}}
                            <div style="border-top:1.5px solid #F3F4F8; padding-top:14px; margin-bottom:18px;">
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6B7280; margin-bottom:6px;">
                                    <span>Subtotal Productos:</span>
                                    <span id="cart_subtotal_raw">$0,00</span>
                                </div>
                                @if($customer->discount_rate > 0)
                                <div style="display:flex; justify-content:space-between; font-size:13px; color:#059669; font-weight:700; margin-bottom:6px;">
                                    <span>Tu Descuento ({{ $customer->discount_rate }}%):</span>
                                    <span id="cart_savings">-$0,00</span>
                                </div>
                                @endif
                                <div style="display:flex; justify-content:space-between; align-items:baseline; font-size:18px; font-weight:800; color:#1E1B2E; border-top:1px solid #E5E7EB; padding-top:10px; margin-top:6px;">
                                    <span>Total Estimado:</span>
                                    <span id="cart_total" style="color:#6C3DE0; font-size:22px;">$0,00</span>
                                </div>
                                <div style="font-size:11px; color:#6B7280; text-align:right; margin-top:4px;">
                                    Estado: <strong>CRUDO (En cola de alistamiento)</strong>
                                </div>
                            </div>

                            <button type="submit" id="btn_submit_order" disabled class="btn-checkout">
                                <span>🚀 Enviar Pedido a Bodega</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: HISTORIAL DE PEDIDOS Y TRAZABILIDAD               --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div id="tab_content_orders" style="display:none;">
            <div class="b2b-card" style="padding:24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
                    <div>
                        <h2 style="font-size:18px; font-weight:800; color:#1E1B2E; margin:0;">Historial y Seguimiento de Pedidos</h2>
                        <p style="font-size:13px; color:#6B7280; margin:3px 0 0 0;">
                            Supervisa el avance de tus pedidos en bodega y repite pedidos anteriores con 1 clic.
                        </p>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:13px;">
                        <thead>
                            <tr style="border-bottom:2px solid #F3F4F8; color:#6B7280; text-align:left;">
                                <th style="padding:12px 14px;"># Pedido</th>
                                <th style="padding:12px 14px;">Fecha</th>
                                <th style="padding:12px 14px; text-align:center;">Ítems</th>
                                <th style="padding:12px 14px; text-align:right;">Total</th>
                                <th style="padding:12px 14px; text-align:center;">Estado en Bodega</th>
                                <th style="padding:12px 14px; text-align:center;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myOrders as $order)
                            @php
                                $statusClass = match(strtoupper($order->status)) {
                                    'CRUDO'     => 'status-crudo',
                                    'SEPARADO'  => 'status-separado',
                                    'REVISADO'  => 'status-revisado',
                                    'DEPURADO'  => 'status-depurado',
                                    'FACTURADO' => 'status-facturado',
                                    'ANULADO'   => 'status-anulado',
                                    default     => 'status-crudo'
                                };
                                $statusLabel = match(strtoupper($order->status)) {
                                    'CRUDO'     => '🟡 Recibido (En espera)',
                                    'SEPARADO'  => '🔵 En Alistamiento',
                                    'REVISADO'  => '🟣 Auditado y Listo',
                                    'DEPURADO'  => '🟢 Aprobado para Factura',
                                    'FACTURADO' => '🏁 Facturado Nodo ERP',
                                    'ANULADO'   => '🔴 Anulado',
                                    default     => strtoupper($order->status)
                                };
                            @endphp
                            <tr style="border-bottom:1px solid #F3F4F8;">
                                <td style="padding:14px; font-weight:800; color:#6C3DE0;">
                                    #{{ $order->id }}
                                </td>
                                <td style="padding:14px; color:#4B5563;">
                                    {{ $order->created_at->format('d/m/Y g:i A') }}
                                </td>
                                <td style="padding:14px; text-align:center; color:#4B5563;">
                                    {{ $order->products->count() }} producto(s)
                                </td>
                                <td style="padding:14px; text-align:right; font-weight:800; color:#1E1B2E; font-size:15px;">
                                    ${{ number_format($order->total, 2, ',', '.') }}
                                </td>
                                <td style="padding:14px; text-align:center;">
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td style="padding:14px; text-align:center;">
                                    <div style="display:flex; gap:6px; justify-content:center;">
                                        <button type="button" 
                                                onclick='openOrderDetailModal(@json($order))' 
                                                style="background:#F3F4F6; border:1px solid #E5E7EB; color:#374151; font-weight:700; font-size:12px; padding:6px 12px; border-radius:8px; cursor:pointer;">
                                            👁️ Ver Detalle
                                        </button>
                                        <button type="button" 
                                                onclick='reorderPastOrder(@json($order->products))' 
                                                style="background:#EDE9FE; border:1px solid #DDD6FE; color:#6C3DE0; font-weight:700; font-size:12px; padding:6px 12px; border-radius:8px; cursor:pointer;"
                                                title="Carga todos estos productos en el pedido actual">
                                            🔁 Repetir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="padding:40px; text-align:center; color:#9CA3AF;">
                                    Aún no has registrado pedidos en el portal B2B.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ════════════════════════════════════════════════════════ --}}
        {{-- TAB 3: INFORMACIÓN DE MI CUENTA Y ASESOR                 --}}
        {{-- ════════════════════════════════════════════════════════ --}}
        <div id="tab_content_account" style="display:none;">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">
                {{-- Ficha del Cliente --}}
                <div class="b2b-card" style="padding:26px;">
                    <h3 style="font-size:16px; font-weight:800; color:#1E1B2E; margin:0 0 16px 0; display:flex; align-items:center; gap:8px;">
                        <span>🏢</span> Datos Comerciales del Cliente
                    </h3>
                    
                    <div style="display:flex; flex-direction:column; gap:12px; font-size:13px;">
                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid #F3F4F8; padding-bottom:8px;">
                            <span style="color:#6B7280;">Razón Social:</span>
                            <strong style="color:#1E1B2E;">{{ $customer->full_name }}</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid #F3F4F8; padding-bottom:8px;">
                            <span style="color:#6B7280;">NIT / Cédula:</span>
                            <strong>{{ $customer->identification }}</strong>
                        </div>
                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid #F3F4F8; padding-bottom:8px;">
                            <span style="color:#6B7280;">Dirección de Entrega:</span>
                            <span>{{ $customer->address }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid #F3F4F8; padding-bottom:8px;">
                            <span style="color:#6B7280;">Municipio:</span>
                            <span>{{ $customer->municipality ?: 'No especificado' }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; border-bottom:1px solid #F3F4F8; padding-bottom:8px;">
                            <span style="color:#6B7280;">Teléfono de Contacto:</span>
                            <span>{{ $customer->phone }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:#6B7280;">Ruta de Despacho:</span>
                            <span style="font-weight:700; color:#6C3DE0;">Ruta R-{{ $customer->route_id ?: '0' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Condiciones Comerciales y Vendedor Asignado --}}
                <div class="b2b-card" style="padding:26px;">
                    <h3 style="font-size:16px; font-weight:800; color:#1E1B2E; margin:0 0 16px 0; display:flex; align-items:center; gap:8px;">
                        <span>🤝</span> Asesor y Condiciones
                    </h3>

                    <div style="display:flex; flex-direction:column; gap:14px; font-size:13px;">
                        <div style="background:#F5F3FF; border:1px solid #DDD6FE; border-radius:12px; padding:14px;">
                            <div style="font-size:11px; text-transform:uppercase; font-weight:700; color:#6C3DE0; margin-bottom:4px;">Tu Descuento Mayorista</div>
                            <div style="font-size:24px; font-weight:800; color:#4C1D95;">{{ $customer->discount_rate }}% OFF</div>
                            <div style="font-size:12px; color:#6B7280; margin-top:2px;">Aplicado automáticamente a todos los precios de este portal.</div>
                        </div>

                        <div>
                            <div style="color:#6B7280; font-size:12px; font-weight:700; text-transform:uppercase; margin-bottom:6px;">Asesor(es) Comercial(es) Asignado(s):</div>
                            @if($customer->sellers && $customer->sellers->isNotEmpty())
                                @foreach($customer->sellers as $seller)
                                <div style="display:flex; align-items:center; gap:10px; padding:8px 12px; background:#F8FAFC; border:1px solid #E2E8F0; border-radius:8px; margin-bottom:6px;">
                                    <div style="width:32px; height:32px; border-radius:50%; background:#6C3DE0; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px;">
                                        {{ substr($seller->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:700; color:#1E1B2E;">{{ $seller->name }}</div>
                                        <div style="font-size:11px; color:#6B7280;">{{ $seller->email }}</div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div style="color:#9CA3AF; font-style:italic;">No hay asesor exclusivo asignado. Atendido por la mesa central de pedidos.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── MODAL DETALLE DE PEDIDO ────────────────────────────── --}}
    <div id="order_detail_modal" class="modal-overlay" onclick="closeOrderDetailModal(event)">
        <div class="modal-card" onclick="event.stopPropagation()">
            <div style="padding:22px 26px; border-bottom:1px solid #F3F4F8; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 id="modal_order_title" style="font-size:17px; font-weight:800; color:#1E1B2E; margin:0;">Detalle del Pedido</h3>
                    <div id="modal_order_date" style="font-size:12px; color:#6B7280; margin-top:2px;"></div>
                </div>
                <button type="button" onclick="closeOrderDetailModal()" style="background:#F3F4F6; border:none; width:32px; height:32px; border-radius:8px; cursor:pointer; font-size:16px; font-weight:700; color:#4B5563;">✕</button>
            </div>

            <div style="padding:22px 26px;">
                <div style="margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:13px; font-weight:700; color:#4B5563;">Estado:</span>
                    <span id="modal_order_status" class="status-badge"></span>
                </div>

                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse:collapse; font-size:12px;">
                        <thead>
                            <tr style="border-bottom:1px solid #E5E7EB; color:#6B7280; text-align:left;">
                                <th style="padding:8px 0;">Producto</th>
                                <th style="padding:8px 0; text-align:center;">Cant.</th>
                                <th style="padding:8px 0; text-align:right;">Precio</th>
                                <th style="padding:8px 0; text-align:right;">Total</th>
                            </tr>
                        </thead>
                        <tbody id="modal_products_tbody"></tbody>
                    </table>
                </div>

                <div style="border-top:1.5px solid #F3F4F8; margin-top:16px; padding-top:14px;">
                    <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:800; color:#1E1B2E;">
                        <span>Total Pedido:</span>
                        <span id="modal_order_total" style="color:#6C3DE0; font-size:18px;"></span>
                    </div>
                </div>
            </div>

            <div style="padding:16px 26px; background:#F8FAFC; border-top:1px solid #F3F4F8; border-radius:0 0 20px 20px; display:flex; justify-content:flex-end;">
                <button type="button" onclick="closeOrderDetailModal()" class="btn-primary" style="padding:8px 18px; font-size:13px;">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    {{-- ── LÓGICA JAVASCRIPT DEL CARRITO Y PESTAÑAS ─────────── --}}
    <script>
        let cart = {};

        function switchTab(tabId) {
            document.querySelectorAll('[id^="tab_content_"]').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));

            const targetContent = document.getElementById('tab_content_' + tabId);
            const targetBtn = document.getElementById('tab_btn_' + tabId);

            if (targetContent) targetContent.style.display = 'block';
            if (targetBtn) targetBtn.classList.add('active');
        }

        function filterCatalog() {
            const query = document.getElementById('catalog_search').value.toLowerCase();
            document.querySelectorAll('.catalog-item').forEach(item => {
                const name = item.dataset.name;
                const code = item.dataset.code;
                if (name.includes(query) || code.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        function setQuickFilter(type) {
            document.querySelectorAll('.qf-btn').forEach(btn => {
                btn.style.background = '#F3F4F6';
                btn.style.color = '#4B5563';
            });
            const activeBtn = document.getElementById('qf_' + type);
            if (activeBtn) {
                activeBtn.style.background = '#6C3DE0';
                activeBtn.style.color = '#ffffff';
            }

            document.querySelectorAll('.catalog-item').forEach(item => {
                const stock = parseInt(item.dataset.stock || 0);
                if (type === 'stock') {
                    item.style.display = stock > 0 ? 'flex' : 'none';
                } else {
                    item.style.display = 'flex';
                }
            });
        }

        function incrementLocalQty(productId) {
            const input = document.getElementById('qty_' + productId);
            if (!input) return;

            if (cart[productId]) {
                // Ya está en el pedido: incrementa 1 unidad directamente al pedido
                cart[productId].qty += 1;
                renderCart();
            } else {
                // Aún no se ha agregado: incrementa el valor en la casilla
                const current = parseInt(input.value) || 0;
                input.value = current + 1;
            }
        }

        function decrementLocalQty(productId) {
            const input = document.getElementById('qty_' + productId);
            if (!input) return;

            if (cart[productId]) {
                // Ya está en el pedido: disminuye 1 unidad del pedido; si llega a 0 se elimina
                cart[productId].qty -= 1;
                if (cart[productId].qty <= 0) {
                    delete cart[productId];
                }
                renderCart();
            } else {
                // Aún no se ha agregado: decrementa mínimo a 1
                const current = parseInt(input.value) || 1;
                if (current > 1) {
                    input.value = current - 1;
                }
            }
        }

        function onLocalQtyInputChange(productId) {
            const input = document.getElementById('qty_' + productId);
            if (!input) return;

            let val = parseInt(input.value);
            if (isNaN(val) || val < 1) {
                val = 1;
                input.value = 1;
            }

            if (cart[productId]) {
                // Si el producto ya está en el carrito, ajusta la cantidad exacta escrita
                cart[productId].qty = val;
                renderCart();
            }
        }

        function addLocalToCart(id, name, code, finalPrice, originalPrice) {
            const input = document.getElementById('qty_' + id);
            const chosenQty = input ? Math.max(1, parseInt(input.value) || 1) : 1;

            // Fija la cantidad exacta elegida en la casilla (no suma sobre sí misma)
            cart[id] = { id, name, code, price: finalPrice, originalPrice: originalPrice, qty: chosenQty };

            renderCart();

            // Animación y confirmación en el botón
            const btn = document.getElementById('btn_add_' + id);
            if (btn) {
                btn.style.transform = 'scale(0.96)';
                setTimeout(() => btn.style.transform = 'scale(1)', 150);
            }

            // Animación en el badge del carrito
            const badge = document.getElementById('cart_badge');
            if (badge) {
                badge.style.transform = 'scale(1.25)';
                setTimeout(() => badge.style.transform = 'scale(1)', 200);
            }
        }

        function updateCartQty(id, delta) {
            if (!cart[id]) return;
            cart[id].qty += delta;
            if (cart[id].qty <= 0) {
                delete cart[id];
            }
            renderCart();
        }

        function removeCartItem(id) {
            delete cart[id];
            renderCart();
        }

        function syncCatalogCardsWithCart() {
            document.querySelectorAll('.catalog-item').forEach(card => {
                const input = card.querySelector('input[id^="qty_"]');
                if (!input) return;
                const id = input.id.replace('qty_', '');
                const btn = document.getElementById('btn_add_' + id);

                if (cart[id]) {
                    // Está en el carrito: la casilla muestra EXACTAMENTE lo que hay en el pedido
                    input.value = cart[id].qty;
                    if (btn) {
                        btn.style.background = '#059669';
                        btn.innerHTML = `<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg><span>En Pedido (${cart[id].qty})</span>`;
                        btn.setAttribute('data-in-cart', 'true');
                    }
                } else {
                    // No está en el carrito: si estaba marcado, resetea a 1 y muestra "Agregar"
                    if (btn && btn.getAttribute('data-in-cart') === 'true') {
                        input.value = 1;
                        btn.style.background = '#6C3DE0';
                        btn.innerHTML = `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg><span>Agregar</span>`;
                        btn.removeAttribute('data-in-cart');
                    }
                }
            });
        }

        function renderCart() {
            const container = document.getElementById('cart_items_container');
            const keys = Object.keys(cart);
            let total = 0;
            let rawSubtotal = 0;
            let totalItems = 0;

            if (keys.length === 0) {
                container.innerHTML = `
                    <div id="empty_cart_msg" style="text-align:center; padding:36px 12px; color:#9CA3AF; font-size:13px; background:#F9FAFB; border-radius:12px; border:1px dashed #E5E7EB;">
                        <div style="font-size:28px; margin-bottom:6px;">🛍️</div>
                        Tu pedido está vacío.<br>Elige productos del catálogo para comenzar.
                    </div>`;
                document.getElementById('btn_submit_order').disabled = true;
                document.getElementById('cart_total').textContent = '$0,00';
                document.getElementById('cart_subtotal_raw').textContent = '$0,00';
                const savEl = document.getElementById('cart_savings');
                if (savEl) savEl.textContent = '-$0,00';
                document.getElementById('cart_badge').textContent = '0 ítems';
                syncCatalogCardsWithCart();
                return;
            }

            container.innerHTML = '';
            keys.forEach(id => {
                const item = cart[id];
                const lineTotal = item.price * item.qty;
                const lineRaw = (item.originalPrice || item.price) * item.qty;
                total += lineTotal;
                rawSubtotal += lineRaw;
                totalItems += item.qty;

                const row = document.createElement('div');
                row.style.display = 'flex';
                row.style.justifyContent = 'space-between';
                row.style.alignItems = 'center';
                row.style.padding = '10px 12px';
                row.style.background = '#F9FAFB';
                row.style.border = '1px solid #E5E7EB';
                row.style.borderRadius = '10px';

                row.innerHTML = `
                    <div style="flex:1; margin-right:8px;">
                        <div style="font-size:13px; font-weight:700; color:#1E1B2E; line-height:1.2;">${item.name}</div>
                        <div style="font-size:11px; color:#6B7280; margin-top:2px;">
                            $${item.price.toLocaleString('es-CO', {minimumFractionDigits:2, maximumFractionDigits:2})} c/u
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <button type="button" onclick="updateCartQty(${item.id}, -1)" class="stepper-btn" style="width:24px;height:24px;border:none;background:#fff;border-radius:4px;cursor:pointer;font-weight:bold;box-shadow:0 1px 2px rgba(0,0,0,0.1);">-</button>
                        <span style="font-weight:800; font-size:13px; width:22px; text-align:center;">${item.qty}</span>
                        <button type="button" onclick="updateCartQty(${item.id}, 1)" class="stepper-btn" style="width:24px;height:24px;border:none;background:#fff;border-radius:4px;cursor:pointer;font-weight:bold;box-shadow:0 1px 2px rgba(0,0,0,0.1);">+</button>
                        <button type="button" onclick="removeCartItem(${item.id})" style="background:none;border:none;color:#9CA3AF;cursor:pointer;padding:4px;margin-left:4px;" title="Quitar">✕</button>
                    </div>
                    <input type="hidden" name="products[]" value="${item.id}">
                    <input type="hidden" name="quantities[]" value="${item.qty}">
                `;
                container.appendChild(row);
            });

            const savings = Math.max(0, rawSubtotal - total);

            document.getElementById('cart_subtotal_raw').textContent = `$${rawSubtotal.toLocaleString('es-CO', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
            const savEl = document.getElementById('cart_savings');
            if (savEl) savEl.textContent = `-$${savings.toLocaleString('es-CO', {minimumFractionDigits:2, maximumFractionDigits:2})}`;

            document.getElementById('cart_total').textContent = `$${total.toLocaleString('es-CO', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
            document.getElementById('cart_badge').textContent = `${totalItems} ítem(s)`;
            document.getElementById('btn_submit_order').disabled = false;

            // Sincronizar las tarjetas del catálogo
            syncCatalogCardsWithCart();
        }

        // ── MODAL DETALLE DE PEDIDO ─────────────────────────
        function openOrderDetailModal(order) {
            document.getElementById('modal_order_title').textContent = 'Detalle del Pedido #' + order.id;
            const date = new Date(order.created_at);
            document.getElementById('modal_order_date').textContent = 'Fecha: ' + date.toLocaleDateString('es-CO', {day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit'});
            
            const statusEl = document.getElementById('modal_order_status');
            statusEl.textContent = (order.status || 'CRUDO').toUpperCase();
            statusEl.className = 'status-badge status-' + (order.status || 'crudo').toLowerCase();

            const tbody = document.getElementById('modal_products_tbody');
            tbody.innerHTML = '';

            if (order.products && order.products.length > 0) {
                order.products.forEach(p => {
                    const tr = document.createElement('tr');
                    tr.style.borderBottom = '1px solid #F3F4F8';
                    const qty = p.pivot ? p.pivot.quantity : 1;
                    const price = p.pivot ? p.pivot.price_final : (p.base_price || 0);
                    const totalLine = p.pivot ? p.pivot.total : (price * qty);

                    tr.innerHTML = `
                        <td style="padding:8px 0;">
                            <div style="font-weight:700; color:#1E1B2E;">${p.name}</div>
                            <div style="font-size:10px; color:#6B7280; font-family:monospace;">${p.code || ''}</div>
                        </td>
                        <td style="padding:8px 0; text-align:center; font-weight:700;">${qty}</td>
                        <td style="padding:8px 0; text-align:right; color:#6B7280;">$${parseFloat(price).toLocaleString('es-CO', {minimumFractionDigits:2})}</td>
                        <td style="padding:8px 0; text-align:right; font-weight:800; color:#1E1B2E;">$${parseFloat(totalLine).toLocaleString('es-CO', {minimumFractionDigits:2})}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            document.getElementById('modal_order_total').textContent = '$' + parseFloat(order.total).toLocaleString('es-CO', {minimumFractionDigits:2, maximumFractionDigits:2});
            document.getElementById('order_detail_modal').style.display = 'flex';
        }

        function closeOrderDetailModal(e) {
            if (e && e.target !== e.currentTarget && e.target.tagName !== 'BUTTON') return;
            document.getElementById('order_detail_modal').style.display = 'none';
        }

        // ── REPETIR PEDIDO ANTERIOR ─────────────────────────
        function reorderPastOrder(products) {
            if (!products || products.length === 0) return;
            if (Object.keys(cart).length > 0) {
                if (!confirm('¿Deseas reemplazar o agregar estos productos a tu pedido actual?')) return;
            }

            products.forEach(p => {
                const qty = p.pivot ? parseFloat(p.pivot.quantity) : 1;
                const price = p.pivot ? parseFloat(p.pivot.price_final) : parseFloat(p.base_price_1 || p.base_price);
                const rawPrice = parseFloat(p.base_price_1 || p.base_price);

                if (cart[p.id]) {
                    cart[p.id].qty += qty;
                } else {
                    cart[p.id] = { id: p.id, name: p.name, code: p.code, price: price, originalPrice: rawPrice, qty: qty };
                }
            });

            renderCart();
            switchTab('catalog');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    </script>
</body>
</html>
