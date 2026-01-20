<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPantry extends Model
{
    public $incrementing = true;
    protected $table = 'user_pantries';

    protected $fillable = [
        'user_id', 'ingredient_id', 'quantity', 'unit', 'expiry_date', 'last_used_at'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
