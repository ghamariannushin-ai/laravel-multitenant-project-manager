<?php

namespace App\Domain\Tenant\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    // این خط را اضافه کنید تا مدل همیشه از اتصال central استفاده کند
    protected $connection = 'central';

    protected $fillable = [
        'name',
        'domain',
        'database',
    ];
}
