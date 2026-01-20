<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = [
        'name', 'category', 'aliases', 'nutritional_values', 'image', 'popularity_score'
    ];

    protected $casts = [
        'aliases' => 'array',
        'nutritional_values' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_pantries')
                    ->withPivot('quantity', 'unit', 'expiry_date', 'last_used_at')
                    ->withTimestamps();
    }
}
