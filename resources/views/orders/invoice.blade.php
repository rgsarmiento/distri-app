<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden #{{ $order->id }} - Impresión</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            line-height: 1.2;
            color: #000;
        }

        body {
            padding: 10px;
        }

        .ticket {
            max-width: 300px;
            margin: 0 auto;
            text-align: center;
        }

        .btn-print {
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
            font-size: 16px;
            width: 100%;
            display: block;
        }

        @media print {
            @page {
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .ticket {
                width: 100%;
                max-width: none;
                margin: 0;
                padding: 5px; /* Pequeño respiro interno para que no pegue al borde físico si la impresora lo permite */
            }
            .btn-print {
                display: none;
            }
        }

        .header { margin-bottom: 10px; }
        .company-name { font-size: 24px; font-weight: bold; margin: 5px 0; }
        .company-nit { font-size: 13px; }

        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { text-align: left; vertical-align: top; padding: 1px 0; }
        .info-table td:first-child { font-weight: bold; width: 40%; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th { border-bottom: 1px dashed #000; padding: 5px 0; font-size: 12px; }
        .items-table td { padding: 5px 0; text-align: left; }
        .items-table .num { text-align: right; }

        .totals { border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px; }
        .total-row { display: flex; justify-content: space-between; font-weight: bold; font-size: 15px; margin-top: 5px; }

        .footer { margin-top: 20px; font-size: 11px; color: #555; }
        .divider { border-top: 1px dashed #000; margin: 10px 0; }
    </style>
</head>
<body>

    <div class="ticket">
        <button class="btn-print" onclick="window.print()">Imprimir</button>

        <div class="header">
            <div class="company-name">{{ $order->user->company->name ?? 'Tunja' }}</div>
            <div class="company-nit">
                NIT: {{ $order->user->company->nit ?? '22222222222' }}<br>
                {{ $order->user->company->municipality ?? 'Tunja' }} - Boy<br>
                {{ $order->user->company->municipality ?? 'Tunja' }}
            </div>
        </div>

        <table class="info-table">
            <tr><td>Orden #.</td><td>{{ $order->id }}</td></tr>
            <tr><td>Cliente:</td><td>{{ $order->customer->full_name ?? 'CONSUMIDOR FINAL' }}</td></tr>
            <tr><td>Identificación:</td><td>{{ $order->customer->identification ?? '' }}</td></tr>
            <tr><td>Fecha:</td><td>{{ $order->created_at->format('d/m/Y') }}</td></tr>
            <tr><td>Hora:</td><td>{{ $order->created_at->format('H:i:s') }}</td></tr>
            <tr><td>Distribuidor:</td><td>{{ $order->user->name }}</td></tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 60%;">Producto</th>
                    <th style="width: 15%; text-align: center;">Cantidad</th>
                    <th style="width: 25%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->products as $product)
                <tr>
                    <td style="font-size: 11.5px;">{{ $product->name }}</td>
                    <td style="text-align: center;">{{ number_format($product->pivot->quantity, 2) }}</td>
                    <td class="num">${{ number_format($product->pivot->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($order->observations)
        <div style="text-align: left; font-size: 12px; margin-bottom: 10px;">
            <strong>Observaciones</strong>
            <div class="divider"></div>
            {{ $order->observations }}
        </div>
        @endif

        <div class="totals">
            <div style="text-align: left; font-weight: bold; font-size: 12px;">Detalle de Valores</div>
            <div class="total-row">
                <span>Total A Pagar:</span>
                <span>${{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        <div class="footer">
            Fecha y hora de impresión: {{ now()->format('d/m/Y, g:i:s a') }}<br>
            Impreso por software de facturación - nodo<br>
            Desarrolladores Colombia SAS<br>
            Tel. 313 4537566 - 321 2560280
        </div>
    </div>

</body>
</html>
