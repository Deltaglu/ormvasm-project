<?php
namespace App\Traits;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany()
    {
        static::creating(function ($model) {
            if (!$model->company_id && session()->has('active_company_id')) {
                $model->company_id = session('active_company_id');
            }
        });

        static::addGlobalScope('company', function (Builder $builder) {
            if (session()->has('active_company_id')) {
                $builder->where($builder->getQuery()->from . '.company_id', session('active_company_id'));
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
