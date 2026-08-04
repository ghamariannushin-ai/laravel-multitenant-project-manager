<?php

namespace App\Domain\Tenant\Models;

use Illuminate\Database\Eloquent\Model;

class TenantReport extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'type',
        'status',
        'payload',
        'started_at',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
