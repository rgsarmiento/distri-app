<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; justify-content:space-between; align-items:center; width:100%;">
            <span>Alistamiento y Auditoría - Pedido #{{ $order->id }}</span>
            <div style="display:flex; gap:10px;">
                <a href="{{ route('warehouse.index') }}" class="btn-secondary" style="font-size:13px;">
                    📦 Bandeja Bodega
                </a>
                <a href="{{ route('orders.show', $order->id) }}" class="btn-secondary" style="font-size:13px; font-weight:700;">
                    ← Volver a la Orden
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $statusUpper = strtoupper($order->status);
        $badgeStyles = [
            'CRUDO'     => 'background:#FEF3C7;color:#92400E;border:1px solid #FDE68A;',
            'SEPARADO'  => 'background:#E0E7FF;color:#3730A3;border:1px solid #C7D2FE;',
            'REVISADO'  => 'background:#F3E8FF;color:#6B21A8;border:1px solid #E9D5FF;',
            'DEPURADO'  => 'background:#CCFBF1;color:#115E59;border:1px solid #99F6E4;',
            'FACTURADO' => 'background:#D1FAE5;color:#065F46;border:1px solid #A7F3D0;',
            'ANULADO'   => 'background:#FEE2E2;color:#991B1B;border:1px solid #FECACA;',
        ];
        $style = $badgeStyles[$statusUpper] ?? 'background:#F3F4F6;color:#374151;';
    @endphp

    {{-- Cabecera con datos del cliente y estado --}}
    <div class="card" style="padding:24px; margin-bottom:24px;">
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:20px; align-items:center;">
            <div>
                <div style="font-size:12px; color:#9CA3AF; text-transform:uppercase; font-weight:700;">Cliente</div>
                <div style="font-size:16px; font-weight:700; color:#1E1B2E; margin-top:2px;">{{ $order->customer->full_name ?? 'N/A' }}</div>
                <div style="font-size:12px; color:#6B7280;">CC/NIT: {{ $order->customer->identification ?? '' }} | Tel: {{ $order->customer->phone ?? '' }}</div>
                <div style="font-size:12px; color:#6B7280;">Dir: {{ $order->customer->address ?? '' }}</div>
            </div>

            <div>
                <div style="font-size:12px; color:#9CA3AF; text-transform:uppercase; font-weight:700;">Preventista / Vendedor</div>
                <div style="font-size:15px; font-weight:600; color:#1E1B2E; margin-top:2px;">{{ $order->user->name ?? 'N/A' }}</div>
                <div style="font-size:12px; color:#6B7280;">Fecha Pedido: {{ $order->created_at->format('d/m/Y g:i A') }}</div>
                <div style="font-size:12px; color:#6B7280;">Ruta: R-{{ $order->route_id ?: ($order->customer->route_id ?: '0') }} | Pago: <strong>{{ $order->payment_term ?: 'CONTADO' }}</strong></div>
            </div>

            <div>
                <div style="font-size:12px; color:#9CA3AF; text-transform:uppercase; font-weight:700;">Estado de Alistamiento</div>
                <div style="margin-top:6px;">
                    <span style="{{ $style }} padding:6px 14px; border-radius:8px; font-size:13px; font-weight:800; display:inline-block;">
                        {{ $statusUpper }}
                    </span>
                </div>
                @if($order->customer && $order->customer->discount_rate > 0)
                <div style="margin-top:6px; font-size:12px; color:#059669; font-weight:700;">
                    🎯 Descuento Cliente: {{ $order->customer->discount_rate }}%
                </div>
                @endif
            </div>

            {{-- Botones de cambio de estado paso a paso --}}
            <div style="display:flex; flex-direction:column; gap:8px;">
                <form action="{{ route('warehouse.update-status', $order->id) }}" method="POST">
                    @csrf
                    @if($order->status === 'CRUDO')
                        <input type="hidden" name="status" value="SEPARADO">
                        <button type="submit" class="btn-primary" style="width:100%; justify-content:center; background:#4F46E5; padding:10px; font-size:13px;">
                            📦 Marcar como SEPARADO
                        </button>
                        <div style="font-size:11px; color:#6B7280; text-align:center; margin-top:3px;">
                            (Indica que bodega ya está alistando/recogiendo el producto físico)
                        </div>
                    @elseif($order->status === 'SEPARADO')
                        <input type="hidden" name="status" value="REVISADO">
                        <button type="submit" class="btn-primary" style="width:100%; justify-content:center; background:#7C3AED; padding:10px; font-size:13px;">
                            ✔️ Marcar como REVISADO
                        </button>
                        <div style="font-size:11px; color:#6B7280; text-align:center; margin-top:3px;">
                            (Conteo físico verificado y sustituciones completadas)
                        </div>
                    @elseif($order->status === 'REVISADO')
                        <input type="hidden" name="status" value="DEPURADO">
                        <button type="submit" class="btn-primary" style="width:100%; justify-content:center; background:#059669; padding:10px; font-weight:800; font-size:13px;">
                            🚀 Aprobar para Facturar (DEPURADO)
                        </button>
                        <div style="font-size:11px; color:#6B7280; text-align:center; margin-top:3px;">
                            (Queda disponible para ser descargado y facturado por Nodo ERP)
                        </div>
                    @else
                        <div style="font-size:12px; color:#6B7280; text-align:center; padding:8px; background:#F3F4F6; border-radius:6px;">
                            Pedido en estado finalizado o depurado.
                        </div>
                    @endif
                </form>

                @if($order->status === 'CRUDO' || $order->status === 'SEPARADO')
                <form action="{{ route('warehouse.update-status', $order->id) }}" method="POST" onsubmit="return confirm('¿Aprobar directamente como DEPURADO para que Nodo ERP lo descargue y facture de inmediato?');">
                    @csrf
                    <input type="hidden" name="status" value="DEPURADO">
                    <button type="submit" class="btn-secondary" style="width:100%; justify-content:center; font-size:12px; padding:6px; color:#059669; border-color:#A7F3D0;" title="Atajo para pedidos rápidos donde no hay alistamiento en fases">
                        ⚡ Aprobar directo a Nodo ERP (DEPURADO)
                    </button>
                </form>
                @endif
            </div>
        </div>

        @if($order->observations)
        <div style="margin-top:16px; padding:10px 14px; background:#F9FAFB; border-left:4px solid #6C3DE0; border-radius:4px; font-size:13px; color:#374151;">
            <strong>Observaciones de Preventa:</strong> {{ $order->observations }}
        </div>
        @endif
    </div>

    {{-- Tabla Comparativa: Unidades Pedidas vs Unidades Físicas --}}
    <div class="card" style="padding:0; overflow:hidden; margin-bottom:24px;">
        <div style="padding:18px 24px; border-bottom:1px solid #F3F4F8; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="font-size:16px; font-weight:700; color:#1E1B2E; margin:0;">Verificación de Unidades Físicas y Stock</h3>
                <p style="font-size:12px; color:#9CA3AF; margin:2px 0 0 0;">Confirma que la cantidad física en bodega coincida con lo solicitado por el cliente.</p>
            </div>
            <div style="font-size:13px; font-weight:700; color:#4B5563;">
                {{ $order->products->count() }} referencias en la orden
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table" id="items_table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto Solicitado</th>
                        <th style="text-align:center;">Stock Disp.</th>
                        <th style="text-align:center;">Unid. Pedidas</th>
                        <th style="text-align:center;">Unid. Físicas (Bodega)</th>
                        <th style="text-align:right;">Precio Unit.</th>
                        <th style="text-align:right;">Subtotal</th>
                        <th style="text-align:center;">Estado Físico</th>
                        <th style="text-align:center;">Gestión de Falta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->products as $item)
                    @php
                        $basePrice = $item->pivot->price_final > 0 
                            ? (float) $item->pivot->price_final 
                            : ($item->pivot->quantity > 0 ? (float) ($item->pivot->subtotal / $item->pivot->quantity) : 0);
                        $hasStockIssue = $item->stock < $item->pivot->quantity;
                    @endphp
                    <tr id="row_product_{{ $item->id }}" style="{{ $hasStockIssue ? 'background:#FFFBEB;' : '' }}">
                        <td style="font-size:12px; font-weight:bold; color:#6C3DE0;">{{ $item->code }}</td>
                        <td>
                            <div style="font-weight:600; color:#1E1B2E;">{{ $item->name }}</div>
                            @if($hasStockIssue)
                                <span style="font-size:11px; color:#D97706; font-weight:bold;">⚠️ Stock en sistema menor al pedido</span>
                            @endif
                        </td>
                        <td style="text-align:center; font-weight:600; color:{{ $hasStockIssue ? '#DC2626' : '#059669' }};">
                            {{ number_format($item->stock, 2) }}
                        </td>
                        <td style="text-align:center; font-weight:800; font-size:14px; color:#1E1B2E;">
                            {{ number_format($item->pivot->quantity, 2) }}
                        </td>
                        <td style="text-align:center;">
                            <input type="number" 
                                   class="form-input physical-qty" 
                                   data-product-id="{{ $item->id }}" 
                                   data-requested="{{ $item->pivot->quantity }}"
                                   value="{{ $item->pivot->quantity }}" 
                                   min="0" 
                                   step="any"
                                   style="width:90px; text-align:center; padding:6px; font-weight:bold; font-size:13px;"
                                   onchange="checkPhysicalMismatch(this)">
                        </td>
                        <td style="text-align:right; font-size:13px;">${{ number_format($basePrice, 2) }}</td>
                        <td style="text-align:right; font-weight:700; color:#1E1B2E;">${{ number_format($item->pivot->total, 2) }}</td>
                        <td style="text-align:center;" class="physical-status-col">
                            <span class="badge-status-ok" style="background:#D1FAE5; color:#065F46; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:bold;">
                                ✔️ Completo
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <div style="display:flex; gap:6px; justify-content:center;">
                                <button type="button" 
                                        onclick="openSubstituteModal({{ $item->id }}, '{{ addslashes($item->name) }}', '{{ $item->code }}', {{ $item->pivot->quantity }}, {{ $basePrice }})"
                                        class="btn-primary" 
                                        style="padding:6px 10px; font-size:11px; background:#6366F1;" 
                                        title="Sustituir por producto equivalente">
                                    🔄 Sustituir
                                </button>
                                <button type="button" 
                                        onclick="openRemoveModal({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->pivot->quantity }})"
                                        class="btn-secondary" 
                                        style="padding:6px 10px; font-size:11px; color:#EF4444; border-color:#FCA5A5;" 
                                        title="Eliminar por falta total de stock">
                                    ❌ Quitar
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totales de la Orden --}}
        <div style="padding:20px 24px; background:#F9FAFB; border-top:1px solid #F3F4F8; display:flex; justify-content:flex-end;">
            <div style="width:280px; display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6B7280;">
                    <span>Subtotal:</span>
                    <span id="label_subtotal">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px; color:#6B7280;">
                    <span>Impuestos (IVA):</span>
                    <span id="label_tax">${{ number_format($order->total_tax, 2) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:16px; font-weight:800; color:#1E1B2E; border-top:1.5px solid #E5E7EB; padding-top:8px;">
                    <span>Total Orden:</span>
                    <span id="label_total" style="color:#6C3DE0;">${{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bitácora de Auditoría y Trazabilidad de Cambios --}}
    <div class="card" style="padding:24px;">
        <h3 style="font-size:16px; font-weight:700; color:#1E1B2E; margin-bottom:16px; display:flex; align-items:center; gap:8px;">
            <span>🛡️ Trazabilidad de Auditoría (Cambios Realizados en Bodega)</span>
        </h3>

        <div id="audits_container">
            @forelse($order->audits as $audit)
            <div style="padding:14px; border-radius:8px; background:#F8FAFC; border:1px solid #E2E8F0; margin-bottom:12px; display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
                <div style="flex:1;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
                        <span style="background:{{ $audit->action_type === 'SUBSTITUTE' ? '#E0E7FF' : '#FEE2E2' }}; color:{{ $audit->action_type === 'SUBSTITUTE' ? '#3730A3' : '#991B1B' }}; font-size:11px; font-weight:800; padding:2px 8px; border-radius:4px;">
                            {{ $audit->action_type }}
                        </span>
                        <span style="font-size:12px; color:#64748B;">{{ $audit->created_at->format('d/m/Y H:i:s') }}</span>
                        <span style="font-size:12px; color:#64748B;">por: <strong>{{ $audit->user->name ?? 'Sistema' }}</strong></span>
                    </div>

                    <div style="font-size:13px; color:#1E293B;">
                        @if($audit->action_type === 'SUBSTITUTE')
                            Se cambió <strong>{{ $audit->original_quantity }} und</strong> de <em>{{ $audit->originalProduct->name ?? 'Producto ID '.$audit->original_product_id }}</em> 
                            por <strong>{{ $audit->new_quantity }} und</strong> de <strong style="color:#4338CA;">{{ $audit->newProduct->name ?? 'Producto ID '.$audit->new_product_id }}</strong>.
                        @elseif($audit->action_type === 'REMOVE')
                            Se eliminó <strong>{{ $audit->original_quantity }} und</strong> de <em>{{ $audit->originalProduct->name ?? 'Producto' }}</em> por falta de existencia.
                        @else
                            Se ajustó cantidad de {{ $audit->original_quantity }} a <strong>{{ $audit->new_quantity }}</strong>.
                        @endif
                    </div>

                    @if($audit->notes)
                    <div style="font-size:12px; color:#64748B; margin-top:4px; font-style:italic;">
                        Motivo: "{{ $audit->notes }}"
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div id="no_audits_msg" style="padding:20px; text-align:center; color:#9CA3AF; font-size:13px;">
                No se han registrado sustituciones ni modificaciones en este pedido.
            </div>
            @endforelse
        </div>
    </div>

    {{-- MODAL INTERACTIVO: Sustituir Producto Agotado --}}
    <div id="substitute_modal" style="display:none; position:fixed; inset:0; background:rgba(30,27,46,0.65); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; padding:20px;">
        <div class="card" style="max-width:560px; width:100%; padding:28px; box-shadow:0 20px 40px rgba(0,0,0,0.25);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h3 style="font-size:18px; font-weight:800; color:#1E1B2E; margin:0;">🔄 Sustituir Producto Agotado</h3>
                <button type="button" onclick="closeSubstituteModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:#9CA3AF;">✕</button>
            </div>

            {{-- Resumen del Producto Original --}}
            <div style="background:#F8FAFC; border:1px solid #E2E8F0; padding:12px 16px; border-radius:8px; margin-bottom:18px;">
                <div style="font-size:11px; color:#64748B; font-weight:bold; text-transform:uppercase;">Producto Original a Sustituir</div>
                <div id="modal_original_name" style="font-size:14px; font-weight:700; color:#1E293B; margin-top:2px;"></div>
                <div style="display:flex; gap:16px; font-size:12px; color:#64748B; margin-top:4px;">
                    <span>Código: <strong id="modal_original_code"></strong></span>
                    <span>Cantidad pedida: <strong id="modal_original_qty"></strong></span>
                    <span>Precio unitario: <strong id="modal_original_price"></strong></span>
                </div>
            </div>

            <form id="substitute_form" onsubmit="submitSubstitution(event)">
                <input type="hidden" id="modal_orig_prod_id" name="original_product_id">
                <input type="hidden" id="modal_action_type" name="action_type" value="SUBSTITUTE">

                <div style="margin-bottom:16px;">
                    <label class="form-label">Seleccionar Producto Sustituto Disponible:</label>
                    <select id="modal_new_prod_id" name="new_product_id" class="form-input" required onchange="calculatePriceDifference()">
                        <option value="">-- Elige una referencia alternativa --</option>
                        @foreach($availableProducts as $prod)
                        <option value="{{ $prod->id }}" 
                                data-name="{{ $prod->name }}"
                                data-code="{{ $prod->code }}"
                                data-price="{{ $prod->base_price_1 ?: $prod->base_price }}"
                                data-stock="{{ $prod->stock }}"
                                data-tax="{{ $prod->tax_rate }}">
                            {{ $prod->name }} (Cód: {{ $prod->code }}) - Stock: {{ $prod->stock }} - Base: ${{ number_format($prod->base_price_1 ?: $prod->base_price, 2) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                    <div>
                        <label class="form-label">Nueva Cantidad:</label>
                        <input type="number" id="modal_new_qty" name="new_quantity" class="form-input" min="0.01" step="any" required oninput="calculatePriceDifference()">
                    </div>
                    <div>
                        <label class="form-label">Diferencia Estimada:</label>
                        <div id="modal_diff_label" style="padding:10px 12px; border-radius:8px; background:#F1F5F9; font-weight:800; font-size:13px; color:#1E293B;">
                            $0.00
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label class="form-label">Motivo de la Sustitución (Obligatorio para Auditoría):</label>
                    <textarea id="modal_notes" name="notes" class="form-input" rows="2" placeholder="Ej: Se agotó la presentación de 500g, se envía presentación de 250g con autorización del cliente..." required></textarea>
                </div>

                <div style="display:flex; gap:12px;">
                    <button type="button" onclick="closeSubstituteModal()" class="btn-secondary" style="flex:1; justify-content:center; padding:12px;">
                        Cancelar
                    </button>
                    <button type="submit" id="btn_submit_substitution" class="btn-primary" style="flex:2; justify-content:center; padding:12px; font-weight:800;">
                        Aplicar y Registrar Auditoría
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL INTERACTIVO: Eliminar Producto Agotado --}}
    <div id="remove_modal" style="display:none; position:fixed; inset:0; background:rgba(30,27,46,0.65); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center; padding:20px;">
        <div class="card" style="max-width:460px; width:100%; padding:28px; text-align:center;">
            <div style="font-size:40px; margin-bottom:12px;">⚠️</div>
            <h3 style="font-size:18px; font-weight:800; color:#1E1B2E; margin-bottom:8px;">¿Eliminar Producto por Agotado?</h3>
            <p id="remove_modal_text" style="font-size:13px; color:#6B7280; margin-bottom:16px;"></p>
            
            <form onsubmit="submitRemoval(event)">
                <input type="hidden" id="remove_prod_id">
                <textarea id="remove_notes" class="form-input" rows="2" placeholder="Motivo de la eliminación (falta de stock en bodega)..." required style="margin-bottom:16px;"></textarea>

                <div style="display:flex; gap:12px;">
                    <button type="button" onclick="closeRemoveModal()" class="btn-secondary" style="flex:1; justify-content:center;">Cancelar</button>
                    <button type="submit" class="btn-primary" style="flex:1; justify-content:center; background:#EF4444; font-weight:bold;">Sí, Quitar de Orden</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const orderId = {{ $order->id }};
        const customerDiscount = {{ (float) ($order->customer->discount_rate ?? 0) }};
        let currentOrigPrice = 0;
        let currentOrigQty = 0;

        function checkPhysicalMismatch(input) {
            const requested = parseFloat(input.dataset.requested) || 0;
            const physical = parseFloat(input.value) || 0;
            const row = input.closest('tr');
            const statusCol = row.querySelector('.physical-status-col');

            if (physical < requested) {
                statusCol.innerHTML = `<span style="background:#FEE2E2; color:#991B1B; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:bold;">⚠️ Incompleto (${physical}/${requested})</span>`;
                row.style.background = '#FEF2F2';
            } else if (physical > requested) {
                statusCol.innerHTML = `<span style="background:#FEF3C7; color:#92400E; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:bold;">Excedente</span>`;
                row.style.background = '#FFFBEB';
            } else {
                statusCol.innerHTML = `<span style="background:#D1FAE5; color:#065F46; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:bold;">✔️ Completo</span>`;
                row.style.background = '#fff';
            }
        }

        function openSubstituteModal(prodId, name, code, qty, price) {
            document.getElementById('modal_orig_prod_id').value = prodId;
            document.getElementById('modal_original_name').textContent = name;
            document.getElementById('modal_original_code').textContent = code;
            document.getElementById('modal_original_qty').textContent = qty;
            document.getElementById('modal_original_price').textContent = `$${price.toFixed(2)}`;
            document.getElementById('modal_new_qty').value = qty;
            document.getElementById('modal_notes').value = '';
            document.getElementById('modal_new_prod_id').value = '';
            document.getElementById('modal_diff_label').textContent = '$0.00';

            currentOrigPrice = price;
            currentOrigQty = qty;

            document.getElementById('substitute_modal').style.display = 'flex';
        }

        function closeSubstituteModal() {
            document.getElementById('substitute_modal').style.display = 'none';
        }

        function calculatePriceDifference() {
            const select = document.getElementById('modal_new_prod_id');
            const opt = select.options[select.selectedIndex];
            if (!opt || !opt.value) {
                document.getElementById('modal_diff_label').textContent = '$0.00';
                return;
            }

            let rawPrice = parseFloat(opt.dataset.price) || 0;
            let taxRate = parseFloat(opt.dataset.tax) || 0;
            let newQty = parseFloat(document.getElementById('modal_new_qty').value) || 0;

            if (customerDiscount > 0) {
                rawPrice = rawPrice * (1 - (customerDiscount / 100));
            }

            const newTotal = (rawPrice * newQty) * (1 + (taxRate / 100));
            const origTotal = (currentOrigPrice * currentOrigQty);
            const diff = newTotal - origTotal;

            const sign = diff >= 0 ? '+' : '';
            const color = diff > 0 ? '#059669' : (diff < 0 ? '#DC2626' : '#1E293B');
            document.getElementById('modal_diff_label').innerHTML = `<span style="color:${color};">${sign}$${diff.toLocaleString('es-CO', {minimumFractionDigits:2, maximumFractionDigits:2})}</span>`;
        }

        function submitSubstitution(e) {
            e.preventDefault();
            const btn = document.getElementById('btn_submit_substitution');
            btn.disabled = true;
            btn.textContent = 'Procesando...';

            const payload = {
                original_product_id: document.getElementById('modal_orig_prod_id').value,
                new_product_id: document.getElementById('modal_new_prod_id').value,
                new_quantity: document.getElementById('modal_new_qty').value,
                action_type: 'SUBSTITUTE',
                notes: document.getElementById('modal_notes').value,
                _token: '{{ csrf_token() }}'
            };

            fetch(`/api/orders/${orderId}/substitute-item`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    alert('Sustitución registrada y auditada correctamente.');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo realizar la sustitución.'));
                    btn.disabled = false;
                    btn.textContent = 'Aplicar y Registrar Auditoría';
                }
            })
            .catch(err => {
                alert('Error en la comunicación con el servidor.');
                btn.disabled = false;
                btn.textContent = 'Aplicar y Registrar Auditoría';
            });
        }

        function openRemoveModal(prodId, name, qty) {
            document.getElementById('remove_prod_id').value = prodId;
            document.getElementById('remove_modal_text').textContent = `Se quitará "${name}" (${qty} und) del pedido por agotamiento de inventario.`;
            document.getElementById('remove_notes').value = '';
            document.getElementById('remove_modal').style.display = 'flex';
        }

        function closeRemoveModal() {
            document.getElementById('remove_modal').style.display = 'none';
        }

        function submitRemoval(e) {
            e.preventDefault();
            const prodId = document.getElementById('remove_prod_id').value;
            const notes = document.getElementById('remove_notes').value;

            fetch(`/api/orders/${orderId}/substitute-item`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    original_product_id: prodId,
                    action_type: 'REMOVE',
                    notes: notes,
                    _token: '{{ csrf_token() }}'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status) {
                    alert('Producto removido y auditado.');
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    </script>
</x-app-layout>
