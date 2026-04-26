<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'name',
        'code',
        'tenant_database',
    ];
}

