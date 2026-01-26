<?php

namespace App\Console\Commands;

use App\Models\UserPantry;
use App\Notifications\IngredientExpiring;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckExpiringIngredients extends Command
{
    protected $signature = 'ingredients:check-expiring';
    protected $description = 'Check for expiring ingredients and send notifications';

    public function handle(): void
    {
        $threeDaysFromNow = Carbon::now()->addDays(3);

        $expiringItems = UserPantry::with(['ingredient', 'user'])
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [Carbon::now(), $threeDaysFromNow])
            ->whereHas('user', function($query) {
                $query->whereNotNull('email_verified_at');
            })
            ->get();

        foreach ($expiringItems as $item) {
            try {
                // Check if we already sent notification today
                $lastNotification = $item->user->notifications()
                    ->where('type', IngredientExpiring::class)
                    ->where('data->pantry_item_id', $item->id)
                    ->whereDate('created_at', Carbon::today())
                    ->first();

                if (!$lastNotification) {
                    $item->user->notify(new IngredientExpiring($item));
                    $this->info("Sent notification for expiring ingredient: {$item->ingredient->name} to {$item->user->email}");
                }
            } catch (\Exception $e) {
                $this->error("Failed to send notification for item {$item->id}: " . $e->getMessage());
            }
        }

        $this->info("Checked {$expiringItems->count()} expiring ingredients.");
    }
}
