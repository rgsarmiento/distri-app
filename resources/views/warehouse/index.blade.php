<x-app-layout>
    <x-slot name="header">Módulo de Bodega y Alistamiento</x-slot>

    {{-- Filtros y Pipeline de Bodega --}}
    <div class="card" style="padding:20px; margin-bottom:20px;">
        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:16px;">
            <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <a href="{{ route('warehouse.index') }}" 
                   class="btn-secondary" 
                   style="{{ empty($statusFilter) ? 'background:#6C3DE0; color:#fff; border-color:#6C3DE0;' : '' }} font-size:13px; padding:8px 16px;">
                    Todos en Bodega
                </a>
                <a href="{{ route('warehouse.index', ['status' => 'CRUDO']) }}" 
                   class="btn-secondary" 
                   style="{{ $statusFilter === 'CRUDO' ? 'background:#FEF3C7; color:#92400E; border-color:#FDE68A;' : '' }} font-size:13px; padding:8px 16px;">
                    🟡 CRUDO (Por Alistar)
                </a>
                <a href="{{ route('warehouse.index', ['status' => 'SEPARADO']) }}" 
                   class="btn-secondary" 
                   style="{{ $statusFilter === 'SEPARADO' ? 'background:#E0E7FF; color:#3730A3; border-color:#C7D2FE;' : '' }} font-size:13px; padding:8px 16px;">
                    🔵 SEPARADO (En Proceso)
                </a>
                <a href="{{ route('warehouse.index', ['status' => 'REVISADO']) }}" 
                   class="btn-secondary" 
                   style="{{ $statusFilter === 'REVISADO' ? 'background:#F3E8FF; color:#6B21A8; border-color:#E9D5FF;' : '' }} font-size:13px; padding:8px 16px;">
                    🟣 REVISADO (Con Sustituciones)
                </a>
                <a href="{{ route('warehouse.index', ['status' => 'DEPURADO']) }}" 
                   class="btn-secondary" 
                   style="{{ $statusFilter === 'DEPURADO' ? 'background:#CCFBF1; color:#115E59; border-color:#99F6E4;' : '' }} font-size:13px; padding:8px 16px;">
                    🟢 DEPURADO (Listo ERP)
                </a>
            </div>

            <form method="GET" action="{{ route('warehouse.index') }}" style="display:flex; gap:8px;">
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Buscar pedido # o cliente..." style="width:240px; padding:8px 12px; font-size:13px;">
                <button type="submit" class="btn-primary" style="padding:8px 14px;">Buscar</button>
            </form>
        </div>
    </div>

    {{-- Tabla de Pedidos --}}
    <div class="card">
        <div style="padding:20px 24px; border-bottom:1px solid #F3F4F8; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-size:16px; font-weight:700; color:#1E1B2E;">Bandeja de Pedidos en Alistamiento</div>
                <div style="font-size:12px; color:#9CA3AF; margin-top:2px;">{{ $orders->total() }} pedidos pendientes de validación o despacho</div>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="text-align:center;"># Pedido</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Preventista</th>
                        <th style="text-align:center;">Ruta</th>
                        <th style="text-align:center;">Pago</th>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:center;">Estado Actual</th>
                        <th style="text-align:center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
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
                    <tr>
                        <td style="text-align:center; font-weight:700; color:#6C3DE0;">#{{ $order->id }}</td>
                        <td style="font-size:12px; color:#6B7280;">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div style="font-weight:600; color:#1E1B2E;">{{ $order->customer->full_name ?? 'N/A' }}</div>
                            <div style="font-size:11px; color:#9CA3AF;">{{ $order->customer->identification ?? '' }} - {{ $order->customer->address ?? '' }}</div>
                        </td>
                        <td style="font-size:13px; color:#4B5563;">{{ $order->user->name ?? 'N/A' }}</td>
                        <td style="text-align:center;">
                            <span style="background:#F3F4F6; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:bold;">
                                R-{{ $order->route_id ?: ($order->customer->route_id ?: '0') }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <span style="font-size:11px; font-weight:bold; color:{{ $order->payment_term === 'CREDITO' ? '#DC2626' : '#059669' }};">
                                {{ $order->payment_term ?: 'CONTADO' }}
                            </span>
                        </td>
                        <td style="text-align:right; font-weight:700; color:#1E1B2E;">${{ number_format($order->total, 0, ',', '.') }}</td>
                        <td style="text-align:center;">
                            <span style="{{ $style }} padding:4px 10px; border-radius:6px; font-size:11px; font-weight:700;">
                                {{ $statusUpper }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <a href="{{ route('warehouse.review', $order->id) }}" class="btn-primary" style="padding:6px 14px; font-size:12px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                                🔍 Auditar y Alistar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" style="text-align:center; padding:48px; color:#9CA3AF;">
                            <div style="font-size:32px; margin-bottom:8px;">📦</div>
                            <div style="font-size:15px; font-weight:600; color:#4B5563;">No hay pedidos en este estado en bodega</div>
                            <div style="font-size:13px; margin-top:4px;">Selecciona otro filtro arriba para revisar otros estados.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 24px; border-top:1px solid #F3F4F8;">
            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>
