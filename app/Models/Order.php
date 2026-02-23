<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',          // Referencia al usuario que creó la orden
        'customer_id',      // Referencia al cliente
        'subtotal',
        'total_tax',
        'total',
        'status',
        'observations',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_products')
            ->withPivot('quantity', 'subtotal', 'total_tax', 'total', 'price_final')
            ->withTimestamps();
    }

    public function customer()
    {
        return $this->belongsTo(CustomerDetail::class, 'customer_id'); // Una orden pertenece a un cliente
    }
}
