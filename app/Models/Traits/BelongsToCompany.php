<?php

namespace App\Models\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::creating(function ($model) {
            if (empty($model->company_id) && auth()->check() && auth()->user()->company_id) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope local para filtrar por empresa específica
     */
    public function scopeForCompany(Builder $query, $companyId): Builder
    {
        return $query->where($this->qualifyColumn('company_id'), $companyId);
    }

    /**
     * Scope para filtrar por la empresa del usuario autenticado
     */
    public function scopeCurrentCompany(Builder $query): Builder
    {
        $user = auth()->user();
        if ($user && $user->company_id && $user->role_id !== 1) {
            return $query->where($this->qualifyColumn('company_id'), $user->company_id);
        }
        return $query;
    }
}
