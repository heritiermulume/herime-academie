<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Support\Facades\Notification;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Récupérer les utilisateurs (premier admin, premier prestataire, premier client)
        $admin = User::where('role', 'admin')->first();
        $provider = User::where('role', 'provider')->first();
        $customer = User::where('role', 'customer')->first();
        
        if (!$admin || !$provider || !$customer) {
            $this->command->warn('Utilisateurs de test non trouvés. Veuillez d\'abord exécuter UserSeeder.');
            return;
        }

        // Récupérer quelques cours
        $courses = Course::take(3)->get();
        
        if ($courses->isEmpty()) {
            $this->command->warn('Aucun cours trouvé. Veuillez d\'abord exécuter CourseSeeder.');
            return;
        }

        // Créer des notifications pour l'admin (sans envoyer d'email)
        try {
            $admin->notify(new \App\Notifications\CourseEnrolled($courses->first()));
            // Adapter PaymentReceived pour nécessiter un Order existant
            $sampleOrder = Order::first();
            if ($sampleOrder) {
                $admin->notify(new \App\Notifications\PaymentReceived($sampleOrder));
            }
            $admin->notify(new \App\Notifications\CoursePublished($provider, $courses->first()));

            // Créer des notifications pour le prestataire
            $provider->notify(new \App\Notifications\CourseEnrolled($courses->first()));
            if ($sampleOrder) {
                $provider->notify(new \App\Notifications\PaymentReceived($sampleOrder));
            }
            $provider->notify(new \App\Notifications\NewMessage($admin, 'Bienvenue sur la plateforme !'));

            // Créer des notifications pour le client
            $customer->notify(new \App\Notifications\CourseEnrolled($courses->first()));
            $customer->notify(new \App\Notifications\CourseCompleted($courses->first()));
            $customer->notify(new \App\Notifications\NewMessage($provider, 'Merci de vous être inscrit à mon cours !'));

            // Créer quelques notifications non lues
            $customer->notify(new \App\Notifications\CourseEnrolled($courses->skip(1)->first()));
            if ($sampleOrder) {
                $customer->notify(new \App\Notifications\PaymentReceived($sampleOrder));
            }

            $this->command->info('Notifications de test créées avec succès !');
        } catch (\Exception $e) {
            $this->command->warn('Erreur lors de la création des notifications : ' . $e->getMessage());
            $this->command->info('Les notifications peuvent être créées plus tard.');
        }
    }
}