<x-app-layout>
    <x-slot name="header">Detalle del Cliente</x-slot>

    <div class="grid-cols-mobile-1" style="display:grid;grid-template-columns:340px 1fr;gap:24px;align-items:start;">

        {{-- ── PERFIL DEL CLIENTE ────────────────────────────────── --}}
        <div>
            <div class="card" style="padding:28px;text-align:center;">
                <div style="width:80px;height:80px;border-radius:24px;background:linear-gradient(135deg,#6C3DE0,#8B5CF6);color:#fff;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:800;margin:0 auto 20px;box-shadow:0 8px 16px rgba(108,61,224,0.25);">
                    {{ strtoupper(substr($customerDetail->full_name, 0, 1)) }}
                </div>
                <div style="font-size:20px;font-weight:800;color:#1E1B2E;margin-bottom:4px;">{{ $customerDetail->full_name }}</div>
                <div style="font-size:13px;color:#9CA3AF;margin-bottom:24px;">ID: {{ $customerDetail->identification }}</div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px;">
                    <div style="background:#F8F7FF;padding:12px;border-radius:12px;border:1px solid #EDE9FF;">
                        <div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:700;margin-bottom:4px;">Pedidos</div>
                        <div style="font-size:18px;font-weight:800;color:#6C3DE0;">{{ $customerDetail->orders->count() }}</div>
                    </div>
                    <div style="background:#F8F7FF;padding:12px;border-radius:12px;border:1px solid #EDE9FF;">
                        <div style="font-size:11px;color:#9CA3AF;text-transform:uppercase;font-weight:700;margin-bottom:4px;">Total</div>
                        <div style="font-size:16px;font-weight:800;color:#10B981;">${{ number_format($customerDetail->orders->where('status','facturado')->sum('total'), 0, ',', '.') }}</div>
                    </div>
                </div>

                <div style="text-align:left;display:flex;flex-direction:column;gap:14px;margin-bottom:28px;">
                    <div style="display:flex;gap:12px;">
                        <div style="color:#6C3DE0;background:#EDE9FF;width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">📞</div>
                        <div>
                            <div style="font-size:11px;color:#9CA3AF;font-weight:600;">Teléfono</div>
                            <div style="font-size:14px;color:#1E1B2E;font-weight:500;">{{ $customerDetail->phone }}</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;">
                        <div style="color:#6C3DE0;background:#EDE9FF;width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">✉️</div>
                        <div>
                            <div style="font-size:11px;color:#9CA3AF;font-weight:600;">Email</div>
                            <div style="font-size:14px;color:#1E1B2E;font-weight:500;">{{ $customerDetail->email }}</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:12px;">
                        <div style="color:#6C3DE0;background:#EDE9FF;width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">📍</div>
                        <div>
                            <div style="font-size:11px;color:#9CA3AF;font-weight:600;">Dirección</div>
                            <div style="font-size:14px;color:#1E1B2E;font-weight:500;">{{ $customerDetail->address }}, {{ $customerDetail->municipality }}</div>
                        </div>
                    </div>
                </div>

                <div style="display:flex;flex-direction:column;gap:10px;">
                    <a href="{{ route('customer-details.edit', $customerDetail) }}" class="btn-primary" style="justify-content:center;">Editar Perfil</a>
                    <a href="{{ route('customer-details.index') }}" class="btn-secondary" style="justify-content:center;">Volver al Listado</a>
                </div>
            </div>
        </div>

        {{-- ── HISTORIAL DE PEDIDOS ──────────────────────────────── --}}
        <div class="card">
            <div style="padding:24px;border-bottom:1px solid #F3F4F8;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-size:16px;font-weight:700;color:#1E1B2E;">Historial de Pedidos</div>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:2px;">Últimos pedidos realizados por el cliente</div>
                </div>
                <a href="{{ route('orders.create') }}?customer_id={{ $customerDetail->id }}" class="btn-secondary" style="font-size:13px;">+ Nuevo Pedido</a>
            </div>

            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Orden #</th>
                            <th>Fecha</th>
                            <th>Distribuidor</th>
                            <th style="text-align:right;">Total</th>
                            <th style="text-align:center;">Estado</th>
                            <th style="text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customerDetail->orders->sortByDesc('created_at') as $order)
                        <tr>
                            <td style="font-weight:700;color:#6C3DE0;">{{ $order->id }}</td>
                            <td style="color:#6B7280;font-size:13px;">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>{{ $order->user->name ?? 'N/A' }}</td>
                            <td style="text-align:right;font-weight:700;">${{ number_format($order->total, 0, ',', '.') }}</td>
                            <td style="text-align:center;">
                                <span class="{{ $order->status === 'facturado' ? 'badge-billed' : 'badge-pending' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <div style="display:flex;gap:6px;justify-content:center;">
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn-secondary" style="padding:5px 10px;font-size:11px;">Detalles</a>
                                    <a href="{{ route('reports.order.pdf', $order->id) }}" target="_blank" class="btn-edit" style="padding:5px 10px;font-size:11px;">PDF</a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="padding:48px;text-align:center;color:#9CA3AF;">
                                <div style="font-size:40px;margin-bottom:12px;">📦</div>
                                <div style="font-size:14px;font-weight:600;">Sin pedidos registrados</div>
                                <div style="font-size:12px;">Este cliente aún no ha realizado compras.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>