<?php

namespace App\Domain\Tenant\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'name',
        'domain',
        'database',
    ];
}
