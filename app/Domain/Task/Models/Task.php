<?php

namespace App\Domain\Task\Models;

use App\Domain\Project\Models\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
    'project_id',
    'title',
    'description',
    'status',
    'priority',
    'due_date',
    'is_completed',
];

    protected $casts = [
        'is_completed' => 'boolean',
        'due_date' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
