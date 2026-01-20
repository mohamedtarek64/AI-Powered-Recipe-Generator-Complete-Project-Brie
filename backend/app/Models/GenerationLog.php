<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerationLog extends Model
{
    protected $fillable = [
        'user_id', 'inputs', 'model_used', 'tokens_consumed', 'response_time', 'status', 'error_message', 'cost_estimate'
    ];

    protected $casts = [
        'inputs' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
