<x-app-layout>
    <x-slot name="header">Orden #{{ $order->id }}</x-slot>

    <div class="grid-cols-mobile-1" style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

        {{-- ── DETALLE PRODUCTOS ────────────────────────────────── --}}
        <div class="card" style="padding:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <div>
                    <div style="font-size:16px;font-weight:700;color:#1E1B2E;">Productos de la Orden</div>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:2px;">
                        {{ $order->products->count() }} producto(s)
                    </div>
                </div>
                <div style="display:flex;gap:8px;">
                    <a href="{{ route('reports.order.pdf', $order->id) }}" class="btn-primary" style="font-size:13px;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/>
                        </svg>
                        Descargar PDF
                    </a>
                    @if($order->status !== 'facturado')
                        <a href="{{ route('orders.edit', $order->id) }}" class="btn-edit">Editar</a>
                    @endif
                    <a href="{{ route('orders.index') }}" class="btn-secondary">← Volver</a>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Código</th>
                            <th style="text-align:center;">Cantidad</th>
                            <th style="text-align:right;">Precio Base</th>
                            <th style="text-align:right;">Subtotal</th>
                            <th style="text-align:right;">Impuestos</th>
                            <th style="text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->products as $product)
                        <tr>
                            <td style="font-weight:600;">{{ $product->name }}</td>
                            <td style="color:#9CA3AF;font-size:12px;">{{ $product->code }}</td>
                            <td style="text-align:center;">
                                <span style="background:#EDE9FF;color:#6C3DE0;border-radius:6px;padding:3px 10px;font-weight:700;">
                                    {{ $product->pivot->quantity }}
                                </span>
                            </td>
                            <td style="text-align:right;color:#6B7280;">
                                ${{ number_format($product->base_price, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right;">
                                ${{ number_format($product->pivot->subtotal, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right;color:#9CA3AF;">
                                ${{ number_format($product->pivot->total_tax, 0, ',', '.') }}
                            </td>
                            <td style="text-align:right;font-weight:700;">
                                ${{ number_format($product->pivot->total, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Observaciones --}}
            @if($order->observations)
            <div style="margin-top:20px; background:#F9FAFB; padding:16px; border-radius:12px; border:1px solid #F3F4F8;">
                <div style="font-size:12px; font-weight:700; color:#9CA3AF; text-transform:uppercase; margin-bottom:8px;">Observaciones</div>
                <div style="font-size:14px; color:#4B5563; line-height:1.5;">{{ $order->observations }}</div>
            </div>
            @endif

            {{-- Totales --}}
            <div style="margin-top:20px;border-top:2px solid #F3F4F8;padding-top:16px;">
                <div style="display:flex;flex-direction:column;gap:8px;max-width:280px;margin-left:auto;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:#6B7280;">
                        <span>Subtotal</span>
                        <span>${{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:#6B7280;">
                        <span>Impuestos</span>
                        <span>${{ number_format($order->total_tax, 0, ',', '.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;color:#1E1B2E;border-top:1.5px solid #E5E7EB;padding-top:8px;">
                        <span>Total</span>
                        <span style="color:#6C3DE0;">${{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── INFO LATERAL ─────────────────────────────────────── --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Estado --}}
            <div class="card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
                    Estado del Pedido
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="width:10px;height:10px;border-radius:50%;background:{{ $order->status === 'facturado' ? '#10B981' : '#F59E0B' }};"></span>
                    <span style="font-size:15px;font-weight:700;color:#1E1B2E;">{{ ucfirst($order->status) }}</span>
                </div>
                <div style="font-size:11px;color:#9CA3AF;margin-top:8px;">
                    Creado: {{ $order->created_at->format('d/m/Y — H:i') }}
                </div>
            </div>

            {{-- Distribuidor --}}
            <div class="card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
                    Distribuidor
                </div>
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#6C3DE0,#8B5CF6);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:15px;">
                        {{ strtoupper(substr($order->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:14px;font-weight:600;color:#1E1B2E;">{{ $order->user->name }}</div>
                        <div style="font-size:12px;color:#9CA3AF;">{{ $order->user->email }}</div>
                    </div>
                </div>
            </div>

            {{-- Cliente --}}
            <div class="card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
                    Cliente
                </div>
                @if($order->customer)
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <div style="font-size:14px;font-weight:700;color:#1E1B2E;">{{ $order->customer->full_name }}</div>
                    <div style="font-size:12px;color:#6B7280;">
                        <strong>ID:</strong> {{ $order->customer->identification }}
                    </div>
                    <div style="font-size:12px;color:#6B7280;">
                        <strong>📞</strong> {{ $order->customer->phone }}
                    </div>
                    <div style="font-size:12px;color:#6B7280;">
                        <strong>📍</strong> {{ $order->customer->address }}
                    </div>
                    <a href="{{ route('customer-details.show', $order->customer->id) }}"
                       class="btn-secondary" style="font-size:12px;margin-top:4px;">
                        Ver historial del cliente →
                    </a>
                </div>
                @else
                <p style="color:#9CA3AF;font-size:13px;">No asignado</p>
                @endif
            </div>

            {{-- Acciones --}}
            @if($order->status !== 'facturado')
            <div class="card" style="padding:20px;">
                <div style="font-size:13px;font-weight:600;color:#9CA3AF;text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
                    Acciones
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <form action="{{ route('orders.destroy', $order->id) }}" method="POST"
                          onsubmit="return confirm('¿Eliminar esta orden permanentemente?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger" style="width:100%;justify-content:center;">
                            Eliminar Orden
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
