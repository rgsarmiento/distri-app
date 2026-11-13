<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'nit',
        'name',
        'phone',
        'address',
        'department',
        'municipality',
        'alert_days',
        'db_database',
        'db_host',
        'db_port',
        'db_username',
        'db_password',
    ];

    /**
     * Indica si la empresa cuenta con una base de datos física dedicada
     */
    public function hasDedicatedDatabase(): bool
    {
        return !empty($this->db_database);
    }

    /**
     * Scope para empresas con base de datos propia
     */
    public function scopeDedicated($query)
    {
        return $query->whereNotNull('db_database')->where('db_database', '!=', '');
    }

    /**
     * Scope para empresas en base de datos compartida
     */
    public function scopeShared($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('db_database')->orWhere('db_database', '');
        });
    }

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
