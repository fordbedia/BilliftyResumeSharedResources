<?php

namespace BilliftyResumeSDK\SharedResources\Modules\Builder\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeStrength extends Model
{
    protected $table = 'resume_strength';

    protected $guarded = [];

    protected $casts = [
        'passed' => 'boolean',
        'feedback' => 'array',
        'notes' => 'array',
        'scored_at' => 'datetime',
    ];

    public function resume()
    {
        return $this->belongsTo(Resume::class);
    }
}
