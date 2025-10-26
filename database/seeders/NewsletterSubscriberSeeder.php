<?php

namespace Database\Seeders;

use App\Models\NewsletterSubscriber;
use Illuminate\Database\Seeder;

class NewsletterSubscriberSeeder extends Seeder
{
    public function run(): void
    {
        $subscribers = [
            [
                'email' => 'jean.dupont@example.com',
                'name' => 'Jean Dupont',
                'status' => 'active',
                'confirmed_at' => now()->subDays(30),
            ],
            [
                'email' => 'marie.martin@example.com',
                'name' => 'Marie Martin',
                'status' => 'active',
                'confirmed_at' => now()->subDays(25),
            ],
            [
                'email' => 'pierre.durand@example.com',
                'name' => 'Pierre Durand',
                'status' => 'active',
                'confirmed_at' => now()->subDays(20),
            ],
            [
                'email' => 'sophie.bernard@example.com',
                'name' => 'Sophie Bernard',
                'status' => 'active',
                'confirmed_at' => now()->subDays(15),
            ],
            [
                'email' => 'lucas.petit@example.com',
                'name' => 'Lucas Petit',
                'status' => 'active',
                'confirmed_at' => now()->subDays(10),
            ],
            [
                'email' => 'emma.robert@example.com',
                'name' => 'Emma Robert',
                'status' => 'active',
                'confirmed_at' => now()->subDays(5),
            ],
            [
                'email' => 'thomas.richard@example.com',
                'name' => 'Thomas Richard',
                'status' => 'pending',
                'confirmation_token' => 'token123456',
            ],
            [
                'email' => 'laura.moreau@example.com',
                'name' => 'Laura Moreau',
                'status' => 'unsubscribed',
                'unsubscribed_at' => now()->subDays(3),
            ],
        ];

        foreach ($subscribers as $subscriber) {
            NewsletterSubscriber::create($subscriber);
        }
    }
}