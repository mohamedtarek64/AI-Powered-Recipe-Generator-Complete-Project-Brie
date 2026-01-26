<?php

namespace App\Notifications;

use App\Models\Recipe;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RecipeGenerated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Recipe $recipe
    ) {
        //
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your AI Recipe is Ready! 🍳')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your AI-generated recipe **' . $this->recipe->title . '** is ready!')
            ->line('We hope you enjoy cooking this delicious meal.')
            ->action('View Recipe', route('recipes.show', $this->recipe->slug))
            ->line('Thank you for using our AI Recipe Generator!')
            ->salutation('Happy Cooking! 👨‍🍳');
    }
}
