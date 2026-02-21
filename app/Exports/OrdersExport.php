<?php

namespace App\Exports;

use App\Models\CustomerDetail;
use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    private $user;
    private $filters;

    public function __construct($user, array $filters = [])
    {
        $this->user    = $user;
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Order::with(['customer', 'user'])->orderBy('created_at', 'desc');

        // Filtro por rol
        if ($this->user->role_id === 2) {
            // Distribuidor: solo sus órdenes
            $query->where('user_id', $this->user->id);
        } elseif ($this->user->role_id === 3) {
            // Supervisor: solo órdenes de su empresa
            $customerIds = CustomerDetail::where('company_id', $this->user->company_id)->pluck('id');
            $query->whereIn('customer_id', $customerIds);
        }

        // Filtros adicionales
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }
        if (!empty($this->filters['user_id']) && $this->user->role_id !== 2) {
            $query->where('user_id', $this->filters['user_id']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            '#',
            'Distribuidor',
            'Cliente',
            'Identificación Cliente',
            'Fecha',
            'Subtotal (COP)',
            'Impuestos (COP)',
            'Total (COP)',
            'Estado',
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $order->user->name ?? 'N/A',
            $order->customer->full_name ?? 'N/A',
            $order->customer->identification ?? 'N/A',
            $order->created_at->format('d/m/Y'),
            number_format($order->subtotal, 2, ',', '.'),
            number_format($order->total_tax, 2, ',', '.'),
            number_format($order->total, 2, ',', '.'),
            ucfirst($order->status),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color'    => ['argb' => 'FF6C3DE0'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Órdenes';
    }
}
