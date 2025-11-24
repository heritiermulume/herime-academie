<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\NewsletterWelcome;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:newsletter_subscribers,email',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email invalide ou déjà inscrit.',
                'errors' => $validator->errors()
            ], 422);
        }

        $subscriber = NewsletterSubscriber::create([
            'email' => $request->email,
            'name' => $request->name,
            'status' => 'active',
        ]);

        // Envoyer un email de bienvenue de manière synchrone (immédiate)
        // Mail::to()->send() envoie immédiatement, contrairement à Mail::to()->queue()
        try {
            Mail::to($subscriber->email)->send(new NewsletterWelcome($subscriber));
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas faire échouer l'inscription
            \Log::error('Erreur envoi email newsletter: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Inscription à la newsletter réussie ! Merci de votre intérêt.',
        ]);
    }

    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:newsletter_subscribers,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email non trouvé.',
            ], 422);
        }

        $subscriber = NewsletterSubscriber::where('email', $request->email)->first();
        
        if ($subscriber) {
            $subscriber->update(['status' => 'unsubscribed']);
            
            return response()->json([
                'success' => true,
                'message' => 'Désinscription réussie. Vous ne recevrez plus nos emails.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la désinscription.',
        ], 500);
    }

    public function confirm($token)
    {
        $subscriber = NewsletterSubscriber::where('confirmation_token', $token)->first();
        
        if (!$subscriber) {
            return redirect()->route('home')
                ->with('error', 'Lien de confirmation invalide ou expiré.');
        }

        $subscriber->update([
            'status' => 'active',
            'confirmed_at' => now(),
            'confirmation_token' => null,
        ]);

        return redirect()->route('home')
            ->with('success', 'Votre inscription à la newsletter a été confirmée !');
    }
}
