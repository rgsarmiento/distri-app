<x-app-layout>
    <x-slot name="header">Inventario de Productos</x-slot>

    {{-- ── FILTROS ────────────────────────────────────────────── --}}
    <div class="card" style="padding:20px;margin-bottom:20px;">
        <form method="GET" action="{{ route('products.index') }}"
              style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">

            <div style="flex:1;min-width:240px;">
                <label class="form-label">Buscar producto</label>
                <input type="text" name="search" class="form-input"
                       value="{{ request('search') }}"
                       placeholder="Nombre o código del producto…">
            </div>

            <div style="min-width:180px;">
                <label class="form-label" style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}
                           onchange="this.form.submit()">
                    Solo Stock Bajo
                </label>
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-primary">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                    </svg>
                    Buscar
                </button>
                <a href="{{ route('products.index') }}" class="btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    {{-- ── TABLA ──────────────────────────────────────────────── --}}
    <div class="card">
        <div style="padding:20px 24px;border-bottom:1px solid #F3F4F8;">
            <div style="font-size:15px;font-weight:700;color:#1E1B2E;">Estado del Inventario</div>
            <div style="font-size:12px;color:#9CA3AF;margin-top:2px;">
                Sincronizado desde Nodo POS
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Código</th>
                        <th style="text-align:right;">Precio Base</th>
                        <th style="text-align:right;">IVA</th>
                        <th style="text-align:center;">Stock</th>
                        <th style="text-align:center;">Mínimo</th>
                        <th style="text-align:center;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td>
                            <div style="font-weight:600;color:#1E1B2E;">{{ $product->name }}</div>
                            @if(Auth::user()->role_id === 1)
                            <div style="font-size:10px;color:#9CA3AF;text-transform:uppercase;">{{ $product->company->name ?? 'N/A' }}</div>
                            @endif
                        </td>
                        <td style="color:#6B7280;font-size:13px;font-family:monospace;">{{ $product->code }}</td>
                        <td style="text-align:right;color:#1E1B2E;font-weight:500;">
                            ${{ number_format($product->base_price_1 ?: $product->base_price, 0, ',', '.') }}
                        </td>
                        <td style="text-align:right;color:#6B7280;">
                            {{ number_format($product->tax_rate, 0) }}%
                        </td>
                        <td style="text-align:center;">
                            <span style="font-size:15px;font-weight:800; color: {{ $product->stock <= $product->min_stock ? '#DC2626' : '#10B981' }};">
                                {{ number_format($product->stock, 1) }}
                            </span>
                        </td>
                        <td style="text-align:center;color:#9CA3AF;">{{ number_format($product->min_stock, 1) }}</td>
                        <td style="text-align:center;">
                            @if($product->stock <= $product->min_stock)
                                <span style="background:#FEE2E2;color:#991B1B;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;text-transform:uppercase;border:1px solid #FCA5A5;">
                                    Bajo
                                </span>
                            @else
                                <span style="background:#D1FAE5;color:#065F46;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;text-transform:uppercase;">
                                    OK
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:64px;color:#9CA3AF;">
                            <div style="font-size:40px;margin-bottom:12px;">🔍</div>
                            <div style="font-size:14px;font-weight:600;">No se encontraron productos</div>
                            <div style="font-size:12px;">Intenta cambiar los filtros de búsqueda.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 24px;border-top:1px solid #F3F4F8;">
            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>
