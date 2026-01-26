<?php

namespace App\Policies;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RecipePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can view public recipes
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Recipe $recipe): bool
    {
        // Public recipes can be viewed by anyone
        if ($recipe->is_public) {
            return true;
        }

        // Private recipes can only be viewed by owner
        return $user && $user->id === $recipe->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Guests can generate recipes (but not save them)
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Recipe $recipe): bool
    {
        // Only owner can update, and only if premium
        return $user->id === $recipe->user_id && $user->isPremium();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Recipe $recipe): bool
    {
        // Only owner can delete
        return $user->id === $recipe->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Recipe $recipe): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Recipe $recipe): bool
    {
        return false;
    }
}
