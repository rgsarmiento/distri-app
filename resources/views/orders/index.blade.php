<x-app-layout>
    <x-slot name="header">Órdenes</x-slot>

    {{-- ── FILTROS ────────────────────────────────────────────── --}}
    <div class="card" style="padding:20px;margin-bottom:20px;">
        <form method="GET" action="{{ route('orders.index') }}"
              style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">

            <div style="flex:1;min-width:160px;">
                <label class="form-label">Buscar cliente</label>
                <input type="text" name="customer_search" class="form-input"
                       value="{{ $filters['customer_search'] ?? '' }}"
                       placeholder="Nombre o identificación…">
            </div>

            <div style="min-width:140px;">
                <label class="form-label">Estado</label>
                <select name="status" class="form-input">
                    <option value="">Todos</option>
                    <option value="pendiente"  {{ ($filters['status'] ?? '') === 'pendiente'  ? 'selected' : '' }}>Pendiente</option>
                    <option value="facturado"  {{ ($filters['status'] ?? '') === 'facturado'  ? 'selected' : '' }}>Facturado</option>
                </select>
            </div>

            <div style="min-width:140px;">
                <label class="form-label">Fecha desde</label>
                <input type="date" name="date_from" class="form-input"
                       value="{{ $filters['date_from'] ?? '' }}">
            </div>

            <div style="min-width:140px;">
                <label class="form-label">Fecha hasta</label>
                <input type="date" name="date_to" class="form-input"
                       value="{{ $filters['date_to'] ?? '' }}">
            </div>

            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button type="submit" class="btn-primary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                    </svg>
                    Filtrar
                </button>
                <a href="{{ route('orders.index') }}" class="btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    {{-- ── TABLA ──────────────────────────────────────────────── --}}
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid #F3F4F8;">
            <div>
                <div style="font-size:15px;font-weight:700;color:#1E1B2E;">Lista de Órdenes</div>
                <div style="font-size:12px;color:#9CA3AF;margin-top:2px;">
                    {{ $orders->total() }} orden(es) encontradas
                </div>
            </div>
            <div style="display:flex;gap:10px;">
                @if(Auth::user()->role_id !== 2)
                <a href="{{ route('reports.orders.excel') }}?{{ http_build_query($filters ?? []) }}"
                   class="btn-secondary" style="font-size:13px;">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 15V3m0 12l-4-4m4 4l4-4M2 17l.621 2.485A2 2 0 004.561 21H19.44a2 2 0 001.94-1.515L22 17"/>
                    </svg>
                    Exportar Excel
                </a>
                @endif
                <a href="{{ route('orders.create') }}" class="btn-primary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                    Nueva Orden
                </a>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="text-align:center;">Acciones</th>
                        <th style="text-align:center;">#</th>
                        @if(Auth::user()->role_id === 1 || Auth::user()->role_id === 3)
                        <th>Distribuidor</th>
                        @endif
                        <th>Cliente</th>
                        <th style="text-align:right;">Subtotal</th>
                        <th style="text-align:right;">Total</th>
                        <th style="text-align:center;">Estado</th>
                        <th style="text-align:center;">Fecha</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr onclick="window.location='{{ route('orders.show', $order) }}'" style="cursor:pointer;">
                        <td style="text-align:center;" onclick="event.stopPropagation()">
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <a href="{{ route('orders.invoice', $order->id) }}" class="btn-primary" style="padding:4px 8px;font-size:10px;background:#6C3DE0;" title="Imprimir Ticket">
                                    🖨️
                                </a>
                                @if($order->status === 'pendiente')
                                    <a href="{{ route('orders.edit', $order->id) }}" class="btn-edit" style="padding:4px 8px;font-size:10px;" title="Editar">
                                        ✏️
                                    </a>
                                @else
                                    <span style="opacity:0.3; cursor:not-allowed; padding:4px 8px; font-size:10px;" title="Facturado no editable">✏️</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align:center;font-weight:700;color:#6C3DE0;">{{ $order->id }}</td>

                        @if(Auth::user()->role_id === 1 || Auth::user()->role_id === 3)
                        <td>{{ $order->user->name ?? 'N/A' }}</td>
                        @endif

                        <td style="font-weight:500;">{{ $order->customer?->full_name ?? 'No asignado' }}</td>

                        <td style="text-align:right;color:#6B7280;">
                            ${{ number_format($order->subtotal, 0, ',', '.') }}
                        </td>
                        <td style="text-align:right;font-weight:700;">
                            ${{ number_format($order->total, 0, ',', '.') }}
                        </td>

                        <td style="text-align:center;">
                            <span class="{{ $order->status === 'facturado' ? 'badge-billed' : 'badge-pending' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>

                        <td style="text-align:center;color:#9CA3AF;font-size:12px;">
                            {{ $order->created_at->format('d/m/Y') }}
                        </td>

                        <td style="text-align:center;" onclick="event.stopPropagation()">
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <a href="{{ route('reports.order.pdf', $order->id) }}"
                                   class="btn-secondary" style="padding:6px 10px;font-size:11px;"
                                   title="Ver PDF">
                                    📄 PDF
                                </a>
                                @if($order->status === 'pendiente')
                                    <form action="{{ route('orders.destroy', $order->id) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar esta orden?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-danger" style="padding:6px 10px;font-size:11px;">
                                            🗑️
                                        </button>
                                    </form>
                                @else
                                    <span style="opacity:0.3; cursor:not-allowed; padding:6px 10px; font-size:11px;" title="Facturado - No se puede eliminar">🗑️</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:48px;color:#9CA3AF;">
                            <svg width="40" height="40" fill="none" stroke="#DDD6FE" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px;">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <div style="font-size:14px;font-weight:600;color:#6B7280;">No hay órdenes</div>
                            <div style="font-size:12px;margin-top:4px;">Intenta cambiar los filtros o crea una nueva orden.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div style="padding:16px 24px;border-top:1px solid #F3F4F8;">
            {{ $orders->links() }}
        </div>
    </div>
</x-app-layout>
