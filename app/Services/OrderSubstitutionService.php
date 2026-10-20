<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItemAudit;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderSubstitutionService
{
    /**
     * Ejecuta una sustitución, ajuste de cantidad o eliminación auditable en un pedido.
     *
     * @param Order $order
     * @param int $originalProductId
     * @param int|null $newProductId
     * @param float $newQuantity
     * @param string $actionType ('SUBSTITUTE', 'REMOVE', 'ADJUST_QTY')
     * @param string|null $notes
     * @param int|null $userId
     * @return array
     * @throws Exception
     */
    public function execute(
        Order $order,
        int $originalProductId,
        ?int $newProductId,
        float $newQuantity,
        string $actionType = 'SUBSTITUTE',
        ?string $notes = null,
        ?int $userId = null
    ): array {
        return DB::transaction(function () use (
            $order,
            $originalProductId,
            $newProductId,
            $newQuantity,
            $actionType,
            $notes,
            $userId
        ) {
            // 1. Validar producto original en la orden
            $originalPivot = DB::table('order_products')
                ->where('order_id', $order->id)
                ->where('product_id', $originalProductId)
                ->first();

            if (!$originalPivot) {
                throw new Exception("El producto original (ID: {$originalProductId}) no pertenece a la orden #{$order->id}.");
            }

            $originalProduct = Product::findOrFail($originalProductId);
            $originalQuantity = (float) $originalPivot->quantity;
            $originalTotal = (float) $originalPivot->total;

            $newProduct = null;
            $newTotal = 0;

            // 2. Ejecutar la acción correspondiente
            switch ($actionType) {
                case 'SUBSTITUTE':
                    if (!$newProductId) {
                        throw new Exception("Para sustituir un producto se requiere especificar el producto sustituto.");
                    }
                    if ($newQuantity <= 0) {
                        throw new Exception("La cantidad del nuevo producto debe ser mayor a 0.");
                    }

                    $newProduct = Product::findOrFail($newProductId);

                    // Validar misma empresa si aplica multi-tenant
                    if ($order->company_id && $newProduct->company_id && $order->company_id != $newProduct->company_id) {
                        throw new Exception("El producto sustituto no pertenece a la misma empresa.");
                    }

                    // Determinar precio base del nuevo producto
                    $basePrice = (float) ($newProduct->base_price_1 ?: $newProduct->base_price);
                    
                    // Aplicar descuento comercial del cliente si aplica
                    $customerDiscount = $order->customer ? (float) $order->customer->discount_rate : 0;
                    if ($customerDiscount > 0) {
                        $basePrice = round($basePrice * (1 - ($customerDiscount / 100)), 2);
                    }

                    $taxRate = (float) ($newProduct->tax_rate ?? 0);
                    $subtotal = round($basePrice * $newQuantity, 2);
                    $totalTax = round($subtotal * ($taxRate / 100), 2);
                    $total = $subtotal + $totalTax;
                    $newTotal = $total;

                    // Remover el producto original
                    DB::table('order_products')
                        ->where('order_id', $order->id)
                        ->where('product_id', $originalProductId)
                        ->delete();

                    // Insertar o actualizar el nuevo producto
                    DB::table('order_products')->updateOrInsert(
                        ['order_id' => $order->id, 'product_id' => $newProductId],
                        [
                            'quantity'    => $newQuantity,
                            'price_final' => $basePrice,
                            'subtotal'    => $subtotal,
                            'total_tax'   => $totalTax,
                            'total'       => $total,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ]
                    );

                    // Gestión de inventario si la orden ya afectó stock
                    if ($order->status === Order::STATUS_FACTURADO) {
                        $originalProduct->increment('stock', $originalQuantity);
                        $newProduct->decrement('stock', $newQuantity);
                    }
                    break;

                case 'REMOVE':
                    DB::table('order_products')
                        ->where('order_id', $order->id)
                        ->where('product_id', $originalProductId)
                        ->delete();

                    if ($order->status === Order::STATUS_FACTURADO) {
                        $originalProduct->increment('stock', $originalQuantity);
                    }
                    $newQuantity = 0;
                    $newProductId = null;
                    break;

                case 'ADJUST_QTY':
                    if ($newQuantity <= 0) {
                        throw new Exception("Para ajustar cantidad a cero o menor use la acción REMOVE.");
                    }

                    $basePrice = (float) ($originalPivot->price_final ?: ($originalPivot->subtotal / $originalPivot->quantity));
                    $taxRate = (float) $originalProduct->tax_rate;
                    $subtotal = round($basePrice * $newQuantity, 2);
                    $totalTax = round($subtotal * ($taxRate / 100), 2);
                    $total = $subtotal + $totalTax;
                    $newTotal = $total;

                    DB::table('order_products')
                        ->where('order_id', $order->id)
                        ->where('product_id', $originalProductId)
                        ->update([
                            'quantity'   => $newQuantity,
                            'subtotal'   => $subtotal,
                            'total_tax'  => $totalTax,
                            'total'      => $total,
                            'updated_at' => now(),
                        ]);

                    if ($order->status === Order::STATUS_FACTURADO) {
                        $diffQty = $newQuantity - $originalQuantity;
                        if ($diffQty > 0) {
                            $originalProduct->decrement('stock', $diffQty);
                        } else {
                            $originalProduct->increment('stock', abs($diffQty));
                        }
                    }

                    $newProductId = $originalProductId;
                    break;

                default:
                    throw new Exception("Tipo de acción '{$actionType}' no soportada.");
            }

            // 3. Recalcular totales generales de la orden
            $aggregates = DB::table('order_products')
                ->where('order_id', $order->id)
                ->selectRaw('COALESCE(SUM(subtotal), 0) as subtotal, COALESCE(SUM(total_tax), 0) as total_tax, COALESCE(SUM(total), 0) as total')
                ->first();

            $order->update([
                'subtotal'  => $aggregates->subtotal,
                'total_tax' => $aggregates->total_tax,
                'total'     => $aggregates->total,
                'status'    => ($order->status === Order::STATUS_CRUDO || $order->status === Order::STATUS_SEPARADO) 
                    ? Order::STATUS_REVISADO 
                    : $order->status,
            ]);

            // 4. Registrar auditoría con trazabilidad completa
            $audit = OrderItemAudit::create([
                'order_id'            => $order->id,
                'user_id'             => $userId ?: (auth()->id() ?? $order->user_id),
                'original_product_id' => $originalProductId,
                'original_quantity'   => $originalQuantity,
                'new_product_id'      => $newProductId,
                'new_quantity'        => $newQuantity,
                'action_type'         => $actionType,
                'notes'               => $notes ?: "Modificación en alistamiento/bodega ($actionType)",
            ]);

            return [
                'order'           => $order->fresh(['products', 'audits', 'customer', 'user']),
                'audit'           => $audit->load(['originalProduct', 'newProduct', 'user']),
                'difference'      => round($newTotal - $originalTotal, 2),
                'original_total'  => $originalTotal,
                'new_total'       => $newTotal,
            ];
        });
    }
}
