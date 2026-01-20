<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tier',
        'dietary_preferences',
        'cuisine_preferences',
        'daily_generation_counter',
        'premium_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'dietary_preferences' => 'array',
            'cuisine_preferences' => 'array',
            'premium_until' => 'datetime',
        ];
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function pantry()
    {
        return $this->hasMany(UserPantry::class);
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public function shoppingLists()
    {
        return $this->hasMany(ShoppingList::class);
    }

    public function mealPlans()
    {
        return $this->hasMany(MealPlan::class);
    }

    public function ratings()
    {
        return $this->hasMany(RecipeRating::class);
    }

    public function generationLogs()
    {
        return $this->hasMany(GenerationLog::class);
    }
}
