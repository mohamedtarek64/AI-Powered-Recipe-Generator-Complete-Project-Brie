<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'description', 'user_id', 'cuisine', 'difficulty',
        'prep_time', 'cook_time', 'servings', 'ingredients', 'instructions',
        'nutritional_info', 'ai_metadata', 'is_public', 'is_featured', 'views', 'saves'
    ];

    protected $casts = [
        'ingredients' => 'array',
        'instructions' => 'array',
        'nutritional_info' => 'array',
        'ai_metadata' => 'array',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ratings()
    {
        return $this->hasMany(RecipeRating::class);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class, 'collection_recipe')
                    ->withPivot('position', 'notes')
                    ->withTimestamps();
    }

    /**
     * Get average rating.
     */
    public function getAverageRatingAttribute(): float
    {
        return round($this->ratings()->avg('star_rating') ?? 0, 1);
    }

    /**
     * Get rating count.
     */
    public function getRatingCountAttribute(): int
    {
        return $this->ratings()->count();
    }
}
