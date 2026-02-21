<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1E1B2E;
            font-size: 12px;
            padding: 32px;
            background: #fff;
        }

        /* ENCABEZADO */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 32px;
            border-bottom: 3px solid #6C3DE0;
            padding-bottom: 20px;
        }
        .header-left  { display: table-cell; vertical-align: top; width: 55%; }
        .header-right { display: table-cell; vertical-align: top; width: 45%; text-align: right; }

        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #6C3DE0;
            letter-spacing: -0.5px;
        }
        .company-sub {
            font-size: 11px;
            color: #9CA3AF;
            margin-top: 4px;
        }

        .order-badge {
            background: #6C3DE0;
            color: #fff;
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 8px;
        }
        .order-meta { font-size: 11px; color: #6B7280; line-height: 1.8; }

        /* DATOS */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 24px;
            gap: 16px;
        }
        .info-box {
            display: table-cell;
            width: 50%;
            padding: 14px 16px;
            border: 1.5px solid #EDE9FF;
            border-radius: 10px;
            vertical-align: top;
        }
        .info-box:first-child { margin-right: 8px; }

        .info-label {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9CA3AF;
            margin-bottom: 8px;
        }
        .info-value { font-size: 12px; color: #1E1B2E; line-height: 1.7; }
        .info-name  { font-size: 14px; font-weight: bold; color: #1E1B2E; margin-bottom: 4px; }

        /* TABLA DE PRODUCTOS */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .products-table thead th {
            background: #6C3DE0;
            color: #fff;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .8px;
            padding: 10px 12px;
            text-align: left;
        }
        .products-table thead th:last-child { text-align: right; }
        .products-table thead th.num { text-align: right; }

        .products-table tbody tr:nth-child(even) { background: #F8F7FF; }
        .products-table tbody td {
            padding: 10px 12px;
            font-size: 11.5px;
            color: #374151;
            border-bottom: 1px solid #EDE9FF;
            vertical-align: top;
        }
        .products-table tbody td.num { text-align: right; }
        .products-table tbody td.center { text-align: center; }

        /* TOTALES */
        .totals-table {
            width: 240px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 12px;
            font-size: 12px;
            color: #6B7280;
        }
        .totals-table .total-row td {
            font-size: 14px;
            font-weight: bold;
            color: #6C3DE0;
            border-top: 2px solid #EDE9FF;
            padding-top: 10px;
        }

        /* FOOTER */
        .footer {
            margin-top: 40px;
            border-top: 1px solid #EDE9FF;
            padding-top: 16px;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; font-size: 10px; color: #9CA3AF; vertical-align: bottom; }
        .footer-right { display: table-cell; text-align: right; font-size: 10px; color: #9CA3AF; vertical-align: bottom; }

        /* Estado badge */
        .status-pending { background: #FEF3C7; color: #92400E; border-radius: 4px; padding: 2px 8px; font-size: 10px; font-weight: bold; }
        .status-billed  { background: #D1FAE5; color: #065F46; border-radius: 4px; padding: 2px 8px; font-size: 10px; font-weight: bold; }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="header-left">
            <div class="company-name">Distri-App</div>
            <div class="company-sub">Sistema de Gestión de Pedidos</div>
            @if($order->user?->company)
            <div style="margin-top:10px;font-size:11px;color:#374151;line-height:1.8;">
                <strong>{{ $order->user->company->name }}</strong><br>
                NIT: {{ $order->user->company->nit }}<br>
                {{ $order->user->company->address }}, {{ $order->user->company->municipality }}<br>
                Tel: {{ $order->user->company->phone }}
            </div>
            @endif
        </div>
        <div class="header-right">
            <div class="order-badge">ORDEN #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div class="order-meta">
                <strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y') }}<br>
                <strong>Hora:</strong>  {{ $order->created_at->format('H:i') }}<br>
                <strong>Estado:</strong>
                @if($order->status === 'facturado')
                    <span class="status-billed">Facturado</span>
                @else
                    <span class="status-pending">Pendiente</span>
                @endif<br>
                <strong>Distribuidor:</strong> {{ $order->user->name ?? 'N/A' }}
            </div>
        </div>
    </div>

    <!-- CLIENTE -->
    <div class="info-grid">
        <div class="info-box">
            <div class="info-label">Datos del Cliente</div>
            <div class="info-name">{{ $order->customer?->full_name ?? 'No asignado' }}</div>
            @if($order->customer)
            <div class="info-value">
                ID: {{ $order->customer->identification }}<br>
                Tel: {{ $order->customer->phone }}<br>
                Email: {{ $order->customer->email }}<br>
                Dir: {{ $order->customer->address }}
            </div>
            @endif
        </div>
        <div class="info-box" style="padding-left:24px;">
            <div class="info-label">Resumen</div>
            <div class="info-value">
                <strong>Productos:</strong> {{ $order->products->count() }}<br>
                <strong>Subtotal:</strong> ${{ number_format($order->subtotal, 0, ',', '.') }}<br>
                <strong>Impuestos:</strong> ${{ number_format($order->total_tax, 0, ',', '.') }}<br>
                <strong style="font-size:14px;color:#6C3DE0;">Total: ${{ number_format($order->total, 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <!-- PRODUCTOS -->
    <table class="products-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Código</th>
                <th class="num">Precio Base</th>
                <th class="num">Cant.</th>
                <th class="num">Subtotal</th>
                <th class="num">IVA</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->products as $i => $product)
            <tr>
                <td class="center" style="color:#9CA3AF;">{{ $i + 1 }}</td>
                <td><strong>{{ $product->name }}</strong></td>
                <td style="color:#9CA3AF;">{{ $product->code }}</td>
                <td class="num">${{ number_format($product->base_price, 0, ',', '.') }}</td>
                <td class="center"><strong>{{ $product->pivot->quantity }}</strong></td>
                <td class="num">${{ number_format($product->pivot->subtotal, 0, ',', '.') }}</td>
                <td class="num" style="color:#9CA3AF;">${{ number_format($product->pivot->total_tax, 0, ',', '.') }}</td>
                <td class="num"><strong>${{ number_format($product->pivot->total, 0, ',', '.') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TOTALES -->
    <table class="totals-table">
        <tr>
            <td>Subtotal:</td>
            <td style="text-align:right;">${{ number_format($order->subtotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>IVA:</td>
            <td style="text-align:right;">${{ number_format($order->total_tax, 0, ',', '.') }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL:</td>
            <td style="text-align:right;">${{ number_format($order->total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        <div class="footer-left">
            Distri-App · Sistema de Gestión de Pedidos<br>
            Generado el {{ now()->format('d/m/Y') }} a las {{ now()->format('H:i') }}
        </div>
        <div class="footer-right">
            Este documento es una orden de pedido. No es una factura.<br>
            La facturación se realiza en el sistema Nodo POS.
        </div>
    </div>

</body>
</html>
