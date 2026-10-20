<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Portal B2B - {{ $customer->full_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background:#F8F9FD; font-family:'Plus Jakarta Sans', sans-serif; color:#1E1B2E; margin:0;">
    {{-- Barra Superior --}}
    <header style="background:#fff; border-bottom:1px solid #E5E7EB; padding:16px 28px; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:100;">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:40px; height:40px; background:#6C3DE0; border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:20px;">
                🛒
            </div>
            <div>
                <div style="font-size:16px; font-weight:800; color:#1E1B2E;">{{ $customer->full_name }}</div>
                <div style="font-size:12px; color:#6B7280;">Distribuidora: {{ $customer->company->name ?? 'DistriApp' }}</div>
            </div>
        </div>

        <div style="display:flex; align-items:center; gap:16px;">
            @if($customer->discount_rate > 0)
            <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:6px 14px; border-radius:20px; font-size:12px; font-weight:800;">
                🎯 Descuento Exclusivo: {{ $customer->discount_rate }}%
            </div>
            @endif

            <form action="{{ route('b2b.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-secondary" style="font-size:12px; padding:6px 14px;">
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </header>

    <div style="max-width:1280px; margin:28px auto; padding:0 20px;">
        @if(session('success_message'))
        <div style="background:#D1FAE5; border:1px solid #A7F3D0; color:#065F46; padding:16px; border-radius:12px; margin-bottom:24px; font-weight:700;">
            {{ session('success_message') }}
        </div>
        @endif

        <div style="display:grid; grid-template-columns:1fr 380px; gap:28px; align-items:start;">
            {{-- Catálogo de Productos con Descuento --}}
            <div>
                <div class="card" style="padding:20px 24px; margin-bottom:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <h2 style="font-size:18px; font-weight:800; margin:0;">Catálogo de Productos Disponibles</h2>
                            <p style="font-size:12px; color:#6B7280; margin:2px 0 0 0;">Los precios mostrados ya incluyen tu descuento comercial del {{ $customer->discount_rate }}% e IVA.</p>
                        </div>
                        <input type="text" id="catalog_search" oninput="filterCatalog()" placeholder="Buscar producto..." class="form-input" style="max-width:240px; padding:8px 12px; font-size:13px;">
                    </div>
                </div>

                <div id="catalog_grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:16px;">
                    @foreach($products as $product)
                    @php
                        $rawPrice = (float) ($product->base_price_1 ?: $product->base_price);
                        $discountFactor = $customer->discount_rate > 0 ? (1 - ($customer->discount_rate / 100)) : 1;
                        $discountedBase = round($rawPrice * $discountFactor, 2);
                        $taxRate = (float) $product->tax_rate;
                        $finalPriceWithTax = round($discountedBase * (1 + ($taxRate / 100)), 2);
                        $originalPriceWithTax = round($rawPrice * (1 + ($taxRate / 100)), 2);
                    @endphp
                    <div class="card catalog-item" 
                         data-name="{{ strtolower($product->name) }}" 
                         data-code="{{ strtolower($product->code) }}" 
                         style="padding:18px; display:flex; flex-direction:column; justify-content:space-between; border-radius:12px;">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:6px;">
                                <span style="font-size:11px; font-weight:800; color:#6C3DE0; background:#F5F3FF; padding:2px 8px; border-radius:4px;">{{ $product->code }}</span>
                                <span style="font-size:11px; color:#6B7280;">Stock: {{ $product->stock }}</span>
                            </div>
                            <div style="font-weight:700; font-size:15px; color:#1E1B2E; margin-bottom:8px;">{{ $product->name }}</div>
                        </div>

                        <div style="margin-top:14px; padding-top:12px; border-top:1px solid #F3F4F8;">
                            <div style="display:flex; align-items:baseline; gap:8px; margin-bottom:12px;">
                                <div style="font-size:18px; font-weight:800; color:#1E1B2E;">
                                    ${{ number_format($finalPriceWithTax, 2) }}
                                </div>
                                @if($customer->discount_rate > 0)
                                <div style="font-size:12px; color:#9CA3AF; text-decoration:line-through;">
                                    ${{ number_format($originalPriceWithTax, 2) }}
                                </div>
                                @endif
                            </div>

                            <button type="button" 
                                    onclick="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ $product->code }}', {{ $finalPriceWithTax }})" 
                                    class="btn-primary" 
                                    style="width:100%; justify-content:center; padding:10px; font-size:13px; font-weight:700;">
                                ➕ Añadir al Pedido
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Carrito y Envío de Orden en estado CRUDO --}}
            <div>
                <div class="card" style="padding:24px; position:sticky; top:90px; border-radius:14px;">
                    <h3 style="font-size:16px; font-weight:800; margin:0 0 16px 0; display:flex; justify-content:space-between; align-items:center;">
                        <span>📦 Tu Pedido Actual</span>
                        <span id="cart_badge" style="background:#6C3DE0; color:#fff; font-size:11px; padding:2px 8px; border-radius:10px;">0 ítems</span>
                    </h3>

                    <form action="{{ route('b2b.order.store') }}" method="POST" id="b2b_order_form">
                        @csrf
                        <div id="cart_items_container" style="max-height:280px; overflow-y:auto; display:flex; flex-direction:column; gap:10px; margin-bottom:16px;">
                            <div id="empty_cart_msg" style="text-align:center; padding:32px 0; color:#9CA3AF; font-size:13px;">
                                Tu pedido está vacío.<br>Agrega productos del catálogo para comenzar.
                            </div>
                        </div>

                        <div style="margin-bottom:16px;">
                            <label class="form-label" style="font-size:11px;">Notas / Instrucciones de Entrega:</label>
                            <textarea name="observations" class="form-input" rows="2" placeholder="Ej: Entregar en horario de la mañana..."></textarea>
                        </div>

                        <div style="border-top:1.5px solid #F3F4F8; padding-top:14px; margin-bottom:16px;">
                            <div style="display:flex; justify-content:space-between; font-size:18px; font-weight:800; color:#1E1B2E;">
                                <span>Total Pedido:</span>
                                <span id="cart_total" style="color:#6C3DE0;">$0.00</span>
                            </div>
                            <div style="font-size:11px; color:#6B7280; text-align:right; margin-top:2px;">Estado inicial: <strong>CRUDO (Preventa)</strong></div>
                        </div>

                        <button type="submit" id="btn_submit_order" disabled class="btn-primary" style="width:100%; justify-content:center; padding:14px; font-size:15px; font-weight:800;">
                            🚀 Confirmar y Enviar Pedido
                        </button>
                    </form>
                </div>

                {{-- Historial de pedidos anteriores --}}
                <div class="card" style="padding:20px; margin-top:20px; border-radius:14px;">
                    <h4 style="font-size:14px; font-weight:800; margin:0 0 12px 0;">Tus Pedidos Recientes</h4>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                        @forelse($myOrders as $order)
                        <div style="display:flex; justify-content:space-between; align-items:center; font-size:12px; padding:8px 0; border-bottom:1px solid #F3F4F8;">
                            <div>
                                <span style="font-weight:700; color:#6C3DE0;">#{{ $order->id }}</span>
                                <span style="color:#6B7280; margin-left:6px;">{{ $order->created_at->format('d/m/Y') }}</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <strong style="color:#1E1B2E;">${{ number_format($order->total, 0, ',', '.') }}</strong>
                                <span style="background:#F3F4F6; color:#374151; padding:2px 6px; border-radius:4px; font-size:10px; font-weight:bold;">
                                    {{ strtoupper($order->status) }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div style="font-size:12px; color:#9CA3AF; text-align:center; padding:10px;">Aún no tienes pedidos registrados.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let cart = {};

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

        function addToCart(id, name, code, price) {
            if (cart[id]) {
                cart[id].qty += 1;
            } else {
                cart[id] = { id, name, code, price, qty: 1 };
            }
            renderCart();
        }

        function updateCartQty(id, delta) {
            if (!cart[id]) return;
            cart[id].qty += delta;
            if (cart[id].qty <= 0) {
                delete cart[id];
            }
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cart_items_container');
            const keys = Object.keys(cart);
            let total = 0;
            let totalItems = 0;

            if (keys.length === 0) {
                container.innerHTML = '<div id="empty_cart_msg" style="text-align:center; padding:32px 0; color:#9CA3AF; font-size:13px;">Tu pedido está vacío.<br>Agrega productos del catálogo para comenzar.</div>';
                document.getElementById('btn_submit_order').disabled = true;
                document.getElementById('cart_total').textContent = '$0.00';
                document.getElementById('cart_badge').textContent = '0 ítems';
                return;
            }

            container.innerHTML = '';
            keys.forEach(id => {
                const item = cart[id];
                const lineTotal = item.price * item.qty;
                total += lineTotal;
                totalItems += item.qty;

                const row = document.createElement('div');
                row.style.display = 'flex';
                row.style.justifyContent = 'space-between';
                row.style.alignItems = 'center';
                row.style.padding = '8px 10px';
                row.style.background = '#F9FAFB';
                row.style.borderRadius = '8px';

                row.innerHTML = `
                    <div style="flex:1;">
                        <div style="font-size:13px; font-weight:700; color:#1E1B2E;">${item.name}</div>
                        <div style="font-size:11px; color:#6B7280;">$${item.price.toFixed(2)} c/u</div>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <button type="button" onclick="updateCartQty(${item.id}, -1)" class="btn-secondary" style="padding:2px 8px; font-size:12px;">-</button>
                        <span style="font-weight:800; font-size:13px; width:24px; text-align:center;">${item.qty}</span>
                        <button type="button" onclick="updateCartQty(${item.id}, 1)" class="btn-secondary" style="padding:2px 8px; font-size:12px;">+</button>
                    </div>
                    <input type="hidden" name="products[]" value="${item.id}">
                    <input type="hidden" name="quantities[]" value="${item.qty}">
                `;
                container.appendChild(row);
            });

            document.getElementById('cart_total').textContent = `$${total.toLocaleString('es-CO', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
            document.getElementById('cart_badge').textContent = `${totalItems} ítem(s)`;
            document.getElementById('btn_submit_order').disabled = false;
        }
    </script>
</body>
</html>
