<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemAudit extends Model
{
    use HasFactory;

    protected $table = 'order_item_audits';

    protected $fillable = [
        'order_id',
        'user_id',
        'original_product_id',
        'original_quantity',
        'new_product_id',
        'new_quantity',
        'action_type', // 'SUBSTITUTE', 'REMOVE', 'ADJUST_QTY'
        'notes',
    ];

    protected $casts = [
        'original_quantity' => 'decimal:2',
        'new_quantity'      => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function originalProduct()
    {
        return $this->belongsTo(Product::class, 'original_product_id');
    }

    public function newProduct()
    {
        return $this->belongsTo(Product::class, 'new_product_id');
    }
}
