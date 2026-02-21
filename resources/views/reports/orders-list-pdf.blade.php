<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; color: #1E1B2E; font-size: 10px; padding: 20px; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #6C3DE0; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #6C3DE0; }
        .meta { font-size: 9px; color: #6B7280; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #6C3DE0; color: #ffffff; padding: 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 8px; border-bottom: 1px solid #EDE9FF; }
        .num { text-align: right; }
        .badge { border-radius: 4px; padding: 2px 6px; font-size: 8px; font-weight: bold; }
        .badge-pending { background: #FEF3C7; color: #92400E; }
        .badge-billed { background: #D1FAE5; color: #065F46; }
        .footer { margin-top: 20px; font-size: 8px; color: #9CA3AF; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Órdenes</div>
        <div class="meta">
            Fecha de generación: {{ now()->format('d/m/Y H:i') }} | 
            Filtros: {{ $filters['status'] ?? 'Todos' }} | 
            Desde: {{ $filters['date_from'] ?? 'Inicio' }} | 
            Hasta: {{ $filters['date_to'] ?? now()->format('d/m/Y') }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Distribuidor</th>
                <th>Cliente</th>
                <th>Identificación</th>
                <th class="num">Subtotal</th>
                <th class="num">Impuestos</th>
                <th class="num">Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                <td>{{ $order->user->name ?? 'N/A' }}</td>
                <td>{{ $order->customer->full_name ?? 'N/A' }}</td>
                <td>{{ $order->customer->identification ?? 'N/A' }}</td>
                <td class="num">${{ number_format($order->subtotal, 0, ',', '.') }}</td>
                <td class="num">${{ number_format($order->total_tax, 0, ',', '.') }}</td>
                <td class="num"><strong>${{ number_format($order->total, 0, ',', '.') }}</strong></td>
                <td>
                    <span class="badge {{ $order->status === 'facturado' ? 'badge-billed' : 'badge-pending' }}">
                        {{ strtoupper($order->status) }}
                    </span>
                </td>
            </tr>
            @php $grandTotal += $order->total; @endphp
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; text-align: right; font-size: 14px;">
        <strong>TOTAL CONSOLIDADO: ${{ number_format($grandTotal, 0, ',', '.') }}</strong>
    </div>

    <div class="footer">
        Distri-App · Sistema de Gestión de Pedidos · Página 1 de 1
    </div>
</body>
</html>
