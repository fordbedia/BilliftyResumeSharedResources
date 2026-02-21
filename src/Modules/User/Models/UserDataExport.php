<?php

namespace BilliftyResumeSDK\SharedResources\Modules\User\Models;


use Illuminate\Database\Eloquent\Model;

class UserDataExport extends Model
{
	protected $table = 'user_data_exports';

    protected $fillable = [
        'user_id',
        'status',
        'file_path',
        'expires_at',
        'error',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
	{
        return $this->belongsTo(User::class);
    }
}
