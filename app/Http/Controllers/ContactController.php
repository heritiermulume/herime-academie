<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\ContactMessageReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use App\Mail\ContactMessageMail;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Veuillez vérifier les informations saisies.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Sauvegarder le message dans la base de données
            $contactMessage = ContactMessage::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'unread',
            ]);

            // Envoyer un email à academie@herime.com
            try {
                Mail::to('academie@herime.com')->send(new ContactMessageMail($contactMessage));
            } catch (\Exception $e) {
                // Log l'erreur mais ne pas faire échouer la sauvegarde
                Log::error('Erreur envoi email contact: ' . $e->getMessage());
            }

            // Notifier les administrateurs et super_user (notification en base pour la navbar)
            try {
                $admins = User::admins()->get();
                if ($admins->isNotEmpty()) {
                    Notification::sendNow($admins, new ContactMessageReceived($contactMessage));
                }
            } catch (\Exception $e) {
                Log::error('Erreur envoi notification contact aux admins: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement du message de contact: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer plus tard.',
            ], 500);
        }
    }
}
