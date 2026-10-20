<?php

namespace App\Models;

use App\Models\Traits\BelongsToCompany;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerDetail extends Model
{
    use HasFactory, BelongsToCompany;

    protected $fillable = [
        'identification',
        'full_name',
        'email',
        'phone',
        'address',
        'company_id',
        'municipality',
        'route_id',
        'visit_monday',
        'visit_tuesday',
        'visit_wednesday',
        'visit_thursday',
        'visit_friday',
        'visit_saturday',
        'visit_sunday',
        'web_access',
        'web_user',
        'web_password',
        'discount_rate',
    ];

    protected $casts = [
        'visit_monday'    => 'boolean',
        'visit_tuesday'   => 'boolean',
        'visit_wednesday' => 'boolean',
        'visit_thursday'  => 'boolean',
        'visit_friday'    => 'boolean',
        'visit_saturday'  => 'boolean',
        'visit_sunday'    => 'boolean',
        'web_access'      => 'boolean',
        'discount_rate'   => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * Vendedores asignados a este cliente
     */
    public function sellers()
    {
        return $this->belongsToMany(User::class, 'customer_user', 'customer_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Scope para filtrar los clientes que corresponden a la visita del día de hoy
     */
    public function scopeVisitToday(Builder $query): Builder
    {
        $dayOfWeek = Carbon::now()->dayOfWeekIso; // 1 = Lunes, 7 = Domingo

        $columnMap = [
            1 => 'visit_monday',
            2 => 'visit_tuesday',
            3 => 'visit_wednesday',
            4 => 'visit_thursday',
            5 => 'visit_friday',
            6 => 'visit_saturday',
            7 => 'visit_sunday',
        ];

        $column = $columnMap[$dayOfWeek] ?? 'visit_monday';

        return $query->where($column, true);
    }

    /**
     * Scope para filtrar clientes asignados a un vendedor (o de su empresa)
     */
    public function scopeForSeller(Builder $query, $userId): Builder
    {
        return $query->whereHas('sellers', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    /**
     * Scope para filtrar por ruta
     */
    public function scopeByRoute(Builder $query, $routeId): Builder
    {
        if (!empty($routeId)) {
            return $query->where('route_id', $routeId);
        }
        return $query;
    }
}
