<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'nit',
        'name',
        'phone',
        'address',
        'department',
        'municipality',
        'alert_days',
    ];

    // Relación: una empresa puede tener muchos clientes
    public function customerDetails()
    {
        return $this->hasMany(CustomerDetail::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
