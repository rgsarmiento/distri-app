<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, BelongsToCompany;

    // Estados del ciclo de vida del pedido
    public const STATUS_CRUDO     = 'CRUDO';     // Preventa inicial
    public const STATUS_SEPARADO  = 'SEPARADO';  // En alistamiento de bodega
    public const STATUS_REVISADO  = 'REVISADO';  // Stock validado y sustituciones hechas
    public const STATUS_DEPURADO  = 'DEPURADO';  // Aprobado para facturación
    public const STATUS_FACTURADO = 'FACTURADO'; // Procesado en Nodo ERP
    public const STATUS_ANULADO   = 'ANULADO';

    // Condiciones de pago
    public const PAYMENT_CONTADO = 'CONTADO';
    public const PAYMENT_CREDITO = 'CREDITO';

    protected $fillable = [
        'company_id',
        'user_id',
        'separator_id',
        'reviewer_id',
        'debugger_id',
        'customer_id',
        'route_id',
        'payment_term',
        'subtotal',
        'total_tax',
        'total',
        'status',
        'invoiced_at',
        'observations',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'total_tax'   => 'decimal:2',
        'total'       => 'decimal:2',
        'invoiced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function separator()
    {
        return $this->belongsTo(User::class, 'separator_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function debugger()
    {
        return $this->belongsTo(User::class, 'debugger_id');
    }

    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot('quantity', 'subtotal', 'total_tax', 'total', 'price_final')
            ->withTimestamps();
    }

    public function audits()
    {
        return $this->hasMany(OrderItemAudit::class, 'order_id');
    }

    /**
     * Scope con Eager Loading estricto para evitar el problema N+1
     */
    public function scopeEagerLoaded(Builder $query): Builder
    {
        return $query->with([
            'customer:id,full_name,identification,address,phone,route_id,discount_rate,company_id',
            'user:id,name,email,company_id',
            'separator:id,name',
            'reviewer:id,name',
            'debugger:id,name',
            'products:id,name,code,base_price,tax_rate,company_id,stock',
            'audits.originalProduct:id,name,code',
            'audits.newProduct:id,name,code',
            'audits.user:id,name',
        ]);
    }

    /**
     * Scope para filtrar por estado admitiendo formatos legado y nuevos
     */
    public function scopeByStatus(Builder $query, $status): Builder
    {
        if (empty($status) || strtoupper($status) === 'ALL') {
            return $query;
        }

        // Mapeo retrocompatible
        $statusMap = [
            '0'         => self::STATUS_CRUDO,
            '1'         => self::STATUS_FACTURADO,
            'pendiente' => self::STATUS_CRUDO,
            'facturado' => self::STATUS_FACTURADO,
        ];

        $targetStatus = $statusMap[strtolower($status)] ?? strtoupper($status);

        return $query->where('status', $targetStatus);
    }

    /**
     * Scope para rango de fechas optimizado con índice compuesto
     */
    public function scopeDateRange(Builder $query, $from = null, $to = null): Builder
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        return $query;
    }

    /**
     * Scope por ruta asignada
     */
    public function scopeByRoute(Builder $query, $routeId): Builder
    {
        if (!empty($routeId)) {
            return $query->where('route_id', $routeId);
        }
        return $query;
    }
}
