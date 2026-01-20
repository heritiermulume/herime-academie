<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        // Pour MySQL, modifier d'abord l'enum pour inclure les nouveaux rôles
        // Cela permet d'éviter les erreurs lors de la mise à jour des données
        if ($driver === 'mysql') {
            try {
                // D'abord, ajouter les nouveaux rôles à l'enum (si pas déjà présents)
                // On vérifie d'abord si les anciens rôles existent encore
                $hasOldRoles = DB::table('users')
                    ->whereIn('role', ['student', 'instructor'])
                    ->exists();
                
                if ($hasOldRoles) {
                    // Ajouter temporairement les nouveaux rôles à l'enum pour permettre la migration
                    DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'customer', 'provider', 'admin', 'affiliate', 'super_user') DEFAULT 'student'");
                }
            } catch (\Exception $e) {
                // Si l'enum contient déjà les nouveaux rôles, continuer
                Log::info('Migration roles: Enum might already contain new roles', ['error' => $e->getMessage()]);
            }
        }
        
        // Mettre à jour les rôles dans la table users (idempotent)
        $studentsUpdated = DB::table('users')
            ->where('role', 'student')
            ->update(['role' => 'customer']);
        
        $instructorsUpdated = DB::table('users')
            ->where('role', 'instructor')
            ->update(['role' => 'provider']);
        
        Log::info('Migration roles: Updated roles', [
            'students_to_customers' => $studentsUpdated,
            'instructors_to_providers' => $instructorsUpdated
        ]);
        
        // Mettre à jour les références dans les données JSON (scheduled_emails, etc.)
        $this->updateJsonRoleReferences();
        
        // Mettre à jour l'enum dans MySQL pour ne garder que les nouveaux rôles
        if ($driver === 'mysql') {
            try {
                // Modifier l'enum pour remplacer 'student' par 'customer' et 'instructor' par 'provider'
                DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('customer', 'provider', 'admin', 'affiliate', 'super_user') DEFAULT 'customer'");
            } catch (\Exception $e) {
                Log::warning('Migration roles: Could not update enum', ['error' => $e->getMessage()]);
                // Ne pas faire échouer la migration si l'enum est déjà correct
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        // Pour MySQL, modifier d'abord l'enum pour inclure les anciens rôles
        if ($driver === 'mysql') {
            try {
                // Ajouter temporairement les anciens rôles à l'enum pour permettre le rollback
                DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'customer', 'provider', 'admin', 'affiliate', 'super_user') DEFAULT 'customer'");
            } catch (\Exception $e) {
                Log::info('Migration roles rollback: Enum might already contain old roles', ['error' => $e->getMessage()]);
            }
        }
        
        // Remettre les anciens rôles
        $customersReverted = DB::table('users')
            ->where('role', 'customer')
            ->update(['role' => 'student']);
        
        $providersReverted = DB::table('users')
            ->where('role', 'provider')
            ->update(['role' => 'instructor']);
        
        Log::info('Migration roles rollback: Reverted roles', [
            'customers_to_students' => $customersReverted,
            'providers_to_instructors' => $providersReverted
        ]);
        
        // Remettre l'enum dans MySQL pour ne garder que les anciens rôles
        if ($driver === 'mysql') {
            try {
                DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'instructor', 'admin', 'affiliate', 'super_user') DEFAULT 'student'");
            } catch (\Exception $e) {
                Log::warning('Migration roles rollback: Could not update enum', ['error' => $e->getMessage()]);
            }
        }
    }
    
    /**
     * Mettre à jour les références aux anciens rôles dans les colonnes JSON
     */
    private function updateJsonRoleReferences(): void
    {
        // Mettre à jour scheduled_emails.recipient_config
        if (Schema::hasTable('scheduled_emails') && Schema::hasColumn('scheduled_emails', 'recipient_config')) {
            $scheduledEmails = DB::table('scheduled_emails')
                ->whereNotNull('recipient_config')
                ->get();
            
            foreach ($scheduledEmails as $email) {
                $config = json_decode($email->recipient_config, true);
                $updated = false;
                
                if (is_array($config)) {
                    // Mettre à jour les rôles dans recipient_config
                    if (isset($config['roles']) && is_array($config['roles'])) {
                        $newRoles = [];
                        foreach ($config['roles'] as $role) {
                            if ($role === 'student') {
                                $newRoles[] = 'customer';
                                $updated = true;
                            } elseif ($role === 'instructor') {
                                $newRoles[] = 'provider';
                                $updated = true;
                            } else {
                                $newRoles[] = $role;
                            }
                        }
                        $config['roles'] = $newRoles;
                    }
                    
                    // Mettre à jour role si c'est une chaîne simple
                    if (isset($config['role'])) {
                        if ($config['role'] === 'student') {
                            $config['role'] = 'customer';
                            $updated = true;
                        } elseif ($config['role'] === 'instructor') {
                            $config['role'] = 'provider';
                            $updated = true;
                        }
                    }
                    
                    if ($updated) {
                        DB::table('scheduled_emails')
                            ->where('id', $email->id)
                            ->update(['recipient_config' => json_encode($config)]);
                    }
                }
            }
        }
        
        // Mettre à jour les autres tables avec des colonnes JSON qui pourraient contenir des rôles
        // Par exemple, si d'autres tables stockent des préférences ou configurations avec des rôles
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'preferences')) {
            $users = DB::table('users')
                ->whereNotNull('preferences')
                ->get();
            
            foreach ($users as $user) {
                $preferences = json_decode($user->preferences, true);
                $updated = false;
                
                if (is_array($preferences)) {
                    // Chercher récursivement les valeurs 'student' et 'instructor'
                    $preferences = $this->replaceRolesInArray($preferences, $updated);
                    
                    if ($updated) {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['preferences' => json_encode($preferences)]);
                    }
                }
            }
        }
    }
    
    /**
     * Remplacer récursivement les rôles dans un tableau
     */
    private function replaceRolesInArray(array $data, bool &$updated): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->replaceRolesInArray($value, $updated);
            } elseif (is_string($value)) {
                if ($value === 'student') {
                    $data[$key] = 'customer';
                    $updated = true;
                } elseif ($value === 'instructor') {
                    $data[$key] = 'provider';
                    $updated = true;
                }
            }
        }
        return $data;
    }
};
