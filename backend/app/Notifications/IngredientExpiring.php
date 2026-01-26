<?php

namespace App\Notifications;

use App\Models\UserPantry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IngredientExpiring extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public UserPantry $pantryItem
    ) {
        //
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $daysLeft = now()->diffInDays($this->pantryItem->expiry_date, false);
        $daysText = $daysLeft == 0 ? 'today' : ($daysLeft == 1 ? 'tomorrow' : "in {$daysLeft} days");

        return (new MailMessage)
            ->subject('⚠️ Ingredient Expiring Soon: ' . $this->pantryItem->ingredient->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your **' . $this->pantryItem->ingredient->name . '** is expiring ' . $daysText . '.')
            ->line('Don\'t let it go to waste! Generate a recipe using this ingredient.')
            ->action('Generate Recipe', route('recipes.create'))
            ->line('**Quantity:** ' . $this->pantryItem->quantity . ' ' . $this->pantryItem->unit)
            ->line('**Expiry Date:** ' . $this->pantryItem->expiry_date->format('F j, Y'))
            ->salutation('Happy Cooking! 👨‍🍳');
    }
}
