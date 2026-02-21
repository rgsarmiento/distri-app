<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    {{-- ══ KPI CARDS ══════════════════════════════════════════════════════════ --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:20px;margin-bottom:28px;">

        <div class="stat-card bg-grad-purple">
            <div class="stat-icon">
                <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div class="stat-value">{{ $totalOrders }}</div>
            <div class="stat-label">Total Pedidos</div>
        </div>

        <div class="stat-card bg-grad-amber">
            <div class="stat-icon">
                <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>
                </svg>
            </div>
            <div class="stat-value">{{ $pendingOrders }}</div>
            <div class="stat-label">Pedidos Pendientes</div>
        </div>

        <div class="stat-card bg-grad-emerald">
            <div class="stat-icon">
                <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 12l2 2 4-4M7 4a2 2 0 012-2h6a2 2 0 012 2"/>
                    <path d="M5 6h14l1 12H4L5 6z"/>
                </svg>
            </div>
            <div class="stat-value">{{ $billedOrders }}</div>
            <div class="stat-label">Pedidos Facturados</div>
        </div>

        <div class="stat-card bg-grad-indigo">
            <div class="stat-icon">
                <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                </svg>
            </div>
            <div class="stat-value" style="font-size:20px;">
                ${{ number_format($totalRevenue, 0, ',', '.') }}
            </div>
            <div class="stat-label">Ingresos Facturados</div>
        </div>

        <div class="stat-card bg-grad-pink">
            <div class="stat-icon">
                <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
            </div>
            <div class="stat-value">{{ $totalCustomers }}</div>
            <div class="stat-label">Clientes</div>
        </div>

        @if($role === 'admin')
        <div class="stat-card bg-grad-cyan">
            <div class="stat-icon">
                <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
            </div>
            <div class="stat-value">{{ $totalUsers }}</div>
            <div class="stat-label">Usuarios</div>
        </div>
        @elseif($role === 'supervisor')
        <div class="stat-card bg-grad-cyan">
            <div class="stat-icon">
                <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
            </div>
            <div class="stat-value">{{ $totalDistributors ?? 0 }}</div>
            <div class="stat-label">Distribuidores</div>
        </div>
        @endif

    </div>

    {{-- ══ GRÁFICAS ════════════════════════════════════════════════════════════ --}}
    <div class="grid-cols-mobile-1" style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:28px;">

        {{-- Ventas por mes --}}
        <div class="card" style="padding:24px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                <div>
                    <div style="font-size:15px;font-weight:700;color:#1E1B2E;">Ventas por Mes</div>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:2px;">Órdenes facturadas (últimos 7 meses)</div>
                </div>
            </div>
            <canvas id="salesChart" height="100"></canvas>
        </div>

        {{-- Top Productos --}}
        <div class="card" style="padding:24px;">
            <div style="font-size:15px;font-weight:700;color:#1E1B2E;margin-bottom:4px;">Top Productos</div>
            <div style="font-size:12px;color:#9CA3AF;margin-bottom:20px;">Por unidades vendidas</div>
            <canvas id="productsChart" height="200"></canvas>
        </div>
    </div>

    {{-- ══ ALERTAS CRÍTICAS (Al cargar la página) ════════════════════════ --}}
    @if(($role === 'admin' || $role === 'supervisor') && isset($inactiveCustomers) && ($inactiveCustomers->inactiveCount ?? 0) > 0)
        <div id="sticky-alert" class="card" style="position:fixed; top:80px; right:20px; z-index:1000; padding:16px; border-left:4px solid #F59E0B; box-shadow:0 10px 25px rgba(0,0,0,0.1); width:320px; animation: slideIn 0.5s ease-out;">
            <div style="display:flex; align-items:flex-start; gap:12px;">
                <div style="background:#FEF3C7; border-radius:50%; width:28px; height:28px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    <span style="color:#D97706; font-weight:bold;">!</span>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700; color:#92400E; font-size:14px;">Atención Requerida</div>
                    <p style="font-size:12px; color:#B45309; margin:4px 0 0 0;">
                        Hay <strong>{{ $inactiveCustomers->inactiveCount }}</strong> cliente(s) inactivos por más de {{ $inactiveDays }} días.
                    </p>
                    <div style="margin-top:10px; display:flex; gap:8px;">
                        <button onclick="document.getElementById('inactive-section').scrollIntoView({behavior:'smooth'}); document.getElementById('sticky-alert').remove();" class="btn-primary" style="padding:4px 10px; font-size:11px;">Ver lista</button>
                        <button onclick="document.getElementById('sticky-alert').remove()" class="btn-secondary" style="padding:4px 10px; font-size:11px;">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <style>@keyframes slideIn { from { transform: translateX(100%); opacity:0; } to { transform: translateX(0); opacity:1; } }</style>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;">

        {{-- Top Clientes --}}
        <div class="card" style="padding:24px;">
            <div style="font-size:15px;font-weight:700;color:#1E1B2E;margin-bottom:4px;">Top Clientes</div>
            <div style="font-size:12px;color:#9CA3AF;margin-bottom:20px;">Por monto total comprado</div>
            @if(count($topCustomers['labels']) > 0)
            <div style="display:flex;flex-direction:column;gap:12px;">
                @foreach($topCustomers['labels'] as $i => $name)
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#6C3DE0,#8B5CF6);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0;">
                        {{ $i + 1 }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;color:#1E1B2E;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $name }}</div>
                        <div style="font-size:11px;color:#9CA3AF;">${{ number_format($topCustomers['data'][$i] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    @php
                        $maxVal = max($topCustomers['data']) ?: 1;
                        $pct = round(($topCustomers['data'][$i] ?? 0) / $maxVal * 100);
                    @endphp
                    <div style="width:80px;height:6px;background:#F3F4F8;border-radius:3px;overflow:hidden;">
                        <div style="width:{{ $pct }}%;height:100%;background:linear-gradient(90deg,#6C3DE0,#8B5CF6);border-radius:3px;"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p style="color:#9CA3AF;font-size:13px;">Sin datos aún.</p>
            @endif
        </div>

        {{-- Órdenes recientes --}}
        <div class="card" style="padding:24px;">
            <div style="font-size:15px;font-weight:700;color:#1E1B2E;margin-bottom:16px;">Pedidos Recientes</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr onclick="window.location='{{ route('orders.show', $order) }}'" style="cursor:pointer;">
                        <td style="font-weight:600;">{{ $order->id }}</td>
                        <td>{{ $order->customer?->full_name ?? 'N/A' }}</td>
                        <td style="font-weight:600;">${{ number_format($order->total, 0, ',', '.') }}</td>
                        <td>
                            <span class="{{ $order->status === 'facturado' ? 'badge-billed' : 'badge-pending' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="color:#9CA3AF;text-align:center;padding:24px;">Sin pedidos recientes</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div style="margin-top:16px;">
                <a href="{{ route('orders.index') }}" class="btn-secondary" style="font-size:12px;">
                    Ver todos los pedidos →
                </a>
            </div>
        </div>
    </div>

    {{-- ══ CLIENTES INACTIVOS (Admin/Supervisor) ═════════════════════════════ --}}
    @if(($role === 'admin' || $role === 'supervisor') && isset($inactiveCustomers))
    <div id="inactive-section" style="margin-bottom:28px;">
        <div class="card" style="padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:16px;">
                <div>
                    <div style="font-size:15px;font-weight:700;color:#1E1B2E;">Clientes Inactivos</div>
                    <div style="font-size:12px;color:#9CA3AF;margin-top:2px;">
                        Clientes que superan el límite de días sin realizar pedidos facturados.
                    </div>
                </div>

                {{-- Filtro de días --}}
                <form action="{{ route('dashboard') }}#inactive-section" method="GET" style="display:flex;align-items:center;gap:8px;">
                    <label style="font-size:12px;font-weight:600;color:#6B7280;">Días de inactividad:</label>
                    <input type="number" name="inactive_days" value="{{ $inactiveDays }}" min="1" max="999"
                        style="width:80px;border:1.5px solid #EDE9FF;border-radius:8px;padding:6px 12px;font-size:13px;text-align:center;">
                    <button type="submit" class="btn-primary" style="padding:7px 14px;font-size:12px;">Filtrar</button>
                    @if($role === 'supervisor')
                        <button type="button" onclick="document.getElementById('hidden_alert_days').value = document.querySelector('input[name=\'inactive_days\']').value; document.getElementById('save-config-form').submit();" class="btn-secondary" style="padding:7px 14px;font-size:12px;" title="Guardar como predeterminado">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><path d="M17 21v-8H7v8m10-18V7H7"/></svg>
                        </button>
                    @endif
                </form>

                @if($role === 'supervisor')
                <form id="save-config-form" action="{{ route('companies.update-alert-days', auth()->user()->company_id) }}#inactive-section" method="POST" style="display:none;">
                    @csrf @method('PATCH')
                    <input type="hidden" name="alert_days" id="hidden_alert_days">
                </form>
                @endif
            </div>

            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>Último Pedido</th>
                            <th style="text-align:center;">Inactividad</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inactiveCustomers as $customer)
                        <tr>
                            <td>
                                <div style="font-weight:600;color:#1E1B2E;">{{ $customer->full_name }}</div>
                                <div style="font-size:11px;color:#9CA3AF;">ID: {{ $customer->identification }}</div>
                            </td>
                            <td style="font-size:13px;color:#6B7280;">{{ $customer->phone }}</td>
                            <td style="font-size:13px;color:#6B7280;">
                                @if($customer->last_order_at)
                                    {{ \Carbon\Carbon::parse($customer->last_order_at)->format('d/m/Y') }}
                                @else
                                    <span style="color:#9CA3AF; font-size:11px; font-style:italic;">Sin pedidos</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($customer->days_inactive !== null)
                                    @php
                                        $isCritical = $customer->days_inactive > ($inactiveDays + 15);
                                        $isAlert = $customer->days_inactive >= $inactiveDays;
                                    @endphp
                                    
                                    @if($isAlert)
                                        <span style="background:{{ $isCritical ? '#FEE2E2' : '#FFF7ED' }}; 
                                                     color:{{ $isCritical ? '#B91C1C' : '#C2410C' }}; 
                                                     border-radius:20px; padding:4px 12px; font-size:12px; font-weight:700; border:1px solid currentColor;">
                                            {{ $customer->days_inactive }} días
                                        </span>
                                    @else
                                        <span style="font-size:12px; color:#10B981; font-weight:700;">
                                            {{ $customer->days_inactive }} días
                                        </span>
                                    @endif
                                @else
                                    <span style="background:#F3F4F8; color:#9CA3AF; border-radius:20px; padding:4px 12px; font-size:11px; font-weight:700;">
                                        Nuevo
                                    </span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('customer-details.show', $customer->id) }}" class="btn-secondary" style="padding:6px 12px; font-size:12px;">Ver Cliente →</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:48px; color:#9CA3AF;">
                                <div style="font-size:24px; margin-bottom:12px;">🎉</div>
                                <div style="font-weight:600; font-size:14px; color:#1E1B2E;">¡No hay clientes inactivos!</div>
                                <div style="font-size:12px;">Todos los clientes han realizado pedidos en los últimos {{ $inactiveDays }} días.</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($inactiveCustomers->hasPages())
            <div style="margin-top:20px; display:flex; justify-content:center;">
                {{ $inactiveCustomers->appends(['inactive_days' => $inactiveDays])->links() }}
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ══ ALERTAS DE STOCK BAJO (Admin/Supervisor) ══════════════════════════ --}}
    @if(($role === 'admin' || $role === 'supervisor') && isset($lowStockProducts) && $lowStockProducts->count() > 0)
    <div style="margin-bottom:28px;">
        <div class="card" style="padding:24px;border:1.5px solid #FCD34D;background:#FFFDF5;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;">
                <div style="background:#FEF3C7;border-radius:12px;padding:10px;">
                    <svg width="22" height="22" fill="none" stroke="#D97706" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:15px;font-weight:700;color:#92400E;">Alertas de Stock Bajo</div>
                    <div style="font-size:12px;color:#B45309;margin-top:2px;">
                        Los siguientes productos están por debajo del nivel mínimo configurado.
                    </div>
                </div>
            </div>

            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr style="background:#FEF3C7;">
                            <th>Producto</th>
                            <th>Código</th>
                            <th style="text-align:center;">Stock Actual</th>
                            <th style="text-align:center;">Stock Mínimo</th>
                            <th style="text-align:center;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lowStockProducts as $lowProd)
                        <tr>
                            <td style="font-weight:600;color:#1E1B2E;">{{ $lowProd->name }}</td>
                            <td style="color:#6B7280;font-size:12px;">{{ $lowProd->code }}</td>
                            <td style="text-align:center;">
                                <span style="font-size:15px;font-weight:800;color:#DC2626;">
                                    {{ number_format($lowProd->stock, 1) }}
                                </span>
                            </td>
                            <td style="text-align:center;color:#6B7280;">{{ number_format($lowProd->min_stock, 1) }}</td>
                            <td style="text-align:center;">
                                <span style="background:#FEE2E2;color:#991B1B;padding:3px 10px;border-radius:6px;font-size:11px;font-weight:700;text-transform:uppercase;">
                                    Reabastecer
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // ─── Ventas por Mes ─────────────────────────────────────────
    const salesData   = @json($salesByMonth['data']);
    const salesLabels = @json($salesByMonth['labels']);

    new Chart(document.getElementById('salesChart'), {
        type: 'bar',
        data: {
            labels: salesLabels,
            datasets: [{
                label: 'Ventas (COP)',
                data: salesData,
                backgroundColor: 'rgba(108,61,224,0.15)',
                borderColor: '#6C3DE0',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' $' + ctx.raw.toLocaleString('es-CO')
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#F3F4F8' },
                    ticks: {
                        callback: v => '$' + (v/1000).toFixed(0) + 'k',
                        color: '#9CA3AF', font: { size: 11 }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#9CA3AF', font: { size: 11 } }
                }
            }
        }
    });

    // ─── Top Productos ───────────────────────────────────────────
    const prodLabels = @json($topProducts['labels']);
    const prodData   = @json($topProducts['data']);

    if (prodLabels.length > 0) {
        new Chart(document.getElementById('productsChart'), {
            type: 'doughnut',
            data: {
                labels: prodLabels,
                datasets: [{
                    data: prodData,
                    backgroundColor: [
                        '#6C3DE0','#8B5CF6','#EC4899','#F59E0B','#10B981'
                    ],
                    borderWidth: 3,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, padding: 14, font: { size: 11 } }
                    }
                }
            }
        });
    } else {
        document.getElementById('productsChart').parentElement.innerHTML +=
            '<p style="color:#9CA3AF;font-size:13px;text-align:center;padding:20px 0;">Sin datos de ventas aún.</p>';
    }
</script>
@endpush
</x-app-layout>
