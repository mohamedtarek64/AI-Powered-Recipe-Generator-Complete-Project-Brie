<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecipeRating extends Model
{
    protected $fillable = [
        'user_id', 'recipe_id', 'star_rating', 'text_review', 'difficulty_verification', 'would_make_again'
    ];

    protected $casts = [
        'would_make_again' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
