<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'name',
        'code',
        'base_price',
        'base_price_1',
        'base_price_2',
        'base_price_3',
        'tax_rate',
        'company_id',
        'stock',
        'min_stock',
    ];

    protected $casts = [
        'base_price'   => 'decimal:2',
        'base_price_1' => 'decimal:2',
        'base_price_2' => 'decimal:2',
        'base_price_3' => 'decimal:2',
        'tax_rate'     => 'decimal:2',
        'stock'        => 'decimal:2',
        'min_stock'    => 'decimal:2',
    ];

    /**
     * Calcula el precio con IVA incluido.
     */
    public function getPriceWithTax()
    {
        $price = $this->base_price_1 ?: $this->base_price;
        return $price * (1 + ($this->tax_rate / 100));
    }

    /**
     * Scope para obtener productos con stock bajo.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_products')
            ->withPivot('quantity', 'subtotal', 'total_tax', 'total', 'price_final')
            ->withTimestamps();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
