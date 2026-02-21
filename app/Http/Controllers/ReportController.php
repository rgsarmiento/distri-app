<?php

namespace App\Http\Controllers;

use App\Models\CustomerDetail;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;

class ReportController extends Controller
{
    /**
     * Exportar una orden individual a PDF
     */
    public function orderPdf(Order $order)
    {
        $user = Auth::user();

        // Verificar permisos
        if ($user->role_id === 2 && $user->id !== $order->user_id) {
            abort(403, 'No tienes permiso para ver esta orden.');
        }
        if ($user->role_id === 3) {
            $companyCustomerIds = CustomerDetail::where('company_id', $user->company_id)->pluck('id');
            if (!$companyCustomerIds->contains($order->customer_id)) {
                abort(403, 'No tienes permiso para ver esta orden.');
            }
        }

        $order->load(['customer', 'user', 'products']);
        $pdf = Pdf::loadView('reports.order-pdf', compact('order'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("orden-{$order->id}.pdf");
    }

    /**
     * Página de reportes
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        return view('reports.index', compact('user'));
    }

    /**
     * Exportar órdenes a Excel
     */
    public function exportExcel(Request $request)
    {
        $user    = Auth::user();
        $filters = $request->only(['status', 'date_from', 'date_to', 'user_id']);

        return Excel::download(
            new OrdersExport($user, $filters),
            'ordenes-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Exportar órdenes a PDF (Lista consolidada)
     */
    public function exportPdf(Request $request)
    {
        $user    = Auth::user();
        $filters = $request->only(['status', 'date_from', 'date_to', 'user_id']);

        // Usamos la misma lógica del Export de Excel para filtrar
        $export = new OrdersExport($user, $filters);
        $orders = $export->query()->get(); // Obtenemos la colección filtrada

        $pdf = Pdf::loadView('reports.orders-list-pdf', compact('orders', 'filters'))
            ->setPaper('a4', 'landscape'); // Horizontal para que quepan las columnas

        return $pdf->stream('reporte-ordenes-' . now()->format('Y-m-d') . '.pdf');
    }
}
