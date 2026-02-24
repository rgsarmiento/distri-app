<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

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
                    ->withPivot('quantity', 'subtotal', 'total_tax', 'total')
                    ->withTimestamps();
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
