<?php

namespace App\Notifications;

use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CategoryCreatedNotification extends Notification
{
    use Queueable;

    protected Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Nouvelle catégorie : {$this->category->name}",
            'excerpt' => "Découvrez les nouveaux cours disponibles dans « {$this->category->name} ».",
            'type' => 'info',
            'button_text' => 'Explorer la catégorie',
            'button_url' => route('courses.category', $this->category->slug),
            'category_id' => $this->category->id,
            'category_name' => $this->category->name,
            'color' => $this->category->color,
        ];
    }
}












