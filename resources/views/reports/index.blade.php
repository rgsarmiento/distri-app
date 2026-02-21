<x-app-layout>
    <x-slot name="header">Reportes y Exportaciones</x-slot>

    {{-- ══ EXPORTAR EXCEL ══════════════════════════════════════════ --}}
    <div class="card" style="padding:28px;margin-bottom:24px;">
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;">
            <div style="background:linear-gradient(135deg,#059669,#10B981);border-radius:12px;padding:12px;">
                <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9 17v-2m3 2v-4m3 4v-6M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                </svg>
            </div>
            <div>
                <div style="font-size:17px;font-weight:700;color:#1E1B2E;">Exportar Órdenes a Excel</div>
                <div style="font-size:13px;color:#9CA3AF;margin-top:3px;">
                    Descarga un archivo Excel con las órdenes filtradas
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('reports.orders.excel') }}"
              style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">

            <div style="min-width:160px;flex:1;">
                <label class="form-label">Estado</label>
                <select name="status" class="form-input">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="facturado">Facturado</option>
                </select>
            </div>

            <div style="min-width:150px;">
                <label class="form-label">Fecha desde</label>
                <input type="date" name="date_from" class="form-input">
            </div>

            <div style="min-width:150px;">
                <label class="form-label">Fecha hasta</label>
                <input type="date" name="date_to" class="form-input" value="{{ now()->format('Y-m-d') }}">
            </div>

            @if(Auth::user()->role_id === 1)
            <div style="min-width:180px;flex:1;">
                <label class="form-label">Distribuidor (ID)</label>
                <input type="number" name="user_id" class="form-input" placeholder="Dejar vacío = todos">
            </div>
            @endif

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn-primary" style="background:linear-gradient(135deg,#059669,#10B981);box-shadow:0 3px 12px rgba(5,150,105,0.3);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 15V3m0 12l-4-4m4 4l4-4M2 17l.621 2.485A2 2 0 004.561 21H19.44a2 2 0 001.94-1.515L22 17"/>
                    </svg>
                    Excel
                </button>
                <button type="submit" onclick="this.form.action='{{ route('reports.orders.pdf') }}'; this.form.target='_self';" class="btn-primary" style="background:linear-gradient(135deg,#DC2626,#EF4444);box-shadow:0 3px 12px rgba(220,38,38,0.3);">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    PDF
                </button>
            </div>
            <script>
                // Reset action to excel when submitting with enter/others if needed, 
                // but usually the specific button clicks handle it.
            </script>
        </form>
    </div>

    {{-- ══ ACCESOS RÁPIDOS ════════════════════════════════════════ --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">

        {{-- Excel hoy --}}
        <div class="card" style="padding:22px;">
            <div style="font-size:14px;font-weight:700;color:#1E1B2E;margin-bottom:6px;">
                📅 Órdenes de Hoy
            </div>
            <div style="font-size:12px;color:#9CA3AF;margin-bottom:16px;">
                Exporta solo las órdenes creadas el día de hoy.
            </div>
            <a href="{{ route('reports.orders.excel') }}?date_from={{ now()->format('Y-m-d') }}&date_to={{ now()->format('Y-m-d') }}"
               class="btn-secondary" style="font-size:13px;display:inline-flex;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:6px;">
                    <path d="M12 15V3m0 12l-4-4m4 4l4-4M2 17l.621 2.485A2 2 0 004.561 21H19.44a2 2 0 001.94-1.515L22 17"/>
                </svg>
                Descargar
            </a>
        </div>

        {{-- Excel mes actual --}}
        <div class="card" style="padding:22px;">
            <div style="font-size:14px;font-weight:700;color:#1E1B2E;margin-bottom:6px;">
                📆 Órdenes del Mes
            </div>
            <div style="font-size:12px;color:#9CA3AF;margin-bottom:16px;">
                Exporta las órdenes del mes en curso.
            </div>
            <a href="{{ route('reports.orders.excel') }}?date_from={{ now()->startOfMonth()->format('Y-m-d') }}&date_to={{ now()->endOfMonth()->format('Y-m-d') }}"
               class="btn-secondary" style="font-size:13px;display:inline-flex;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:6px;">
                    <path d="M12 15V3m0 12l-4-4m4 4l4-4M2 17l.621 2.485A2 2 0 004.561 21H19.44a2 2 0 001.94-1.515L22 17"/>
                </svg>
                Descargar
            </a>
        </div>

        {{-- Excel solo facturadas --}}
        <div class="card" style="padding:22px;">
            <div style="font-size:14px;font-weight:700;color:#1E1B2E;margin-bottom:6px;">
                ✅ Solo Facturadas
            </div>
            <div style="font-size:12px;color:#9CA3AF;margin-bottom:16px;">
                Exporta únicamente las órdenes con estado "Facturado".
            </div>
            <a href="{{ route('reports.orders.excel') }}?status=facturado"
               class="btn-secondary" style="font-size:13px;display:inline-flex;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:6px;">
                    <path d="M12 15V3m0 12l-4-4m4 4l4-4M2 17l.621 2.485A2 2 0 004.561 21H19.44a2 2 0 001.94-1.515L22 17"/>
                </svg>
                Descargar
            </a>
        </div>

    </div>

    {{-- Información --}}
    <div style="margin-top:24px;background:#F8F7FF;border-radius:12px;padding:16px 20px;border:1.5px solid #EDE9FF;">
        <div style="font-size:13px;font-weight:600;color:#6C3DE0;margin-bottom:8px;">
            ℹ️ Información sobre los reportes
        </div>
        <ul style="font-size:12px;color:#6B7280;padding-left:18px;line-height:2;">
            <li>Los reportes Excel incluyen solo los datos que tienes permiso para ver.</li>
            <li>Los importes están en Pesos Colombianos (COP).</li>
            <li>Para generar el PDF de una orden específica, ve al detalle de la orden y haz clic en "Descargar PDF".</li>
        </ul>
    </div>
</x-app-layout>
