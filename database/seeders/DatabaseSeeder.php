<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            CourseSeeder::class,
            BlogCategorySeeder::class,
            BlogPostSeeder::class,
            NewsletterSubscriberSeeder::class,
            AnnouncementSeeder::class,
            PartnerSeeder::class,
            TestimonialSeeder::class,
            NotificationSeeder::class,
            BannerSeeder::class,
        ]);
    }
}