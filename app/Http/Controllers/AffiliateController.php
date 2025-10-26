<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Order;
use App\Models\Course;
use App\Traits\DatabaseCompatibility;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    use DatabaseCompatibility;
    public function dashboard()
    {
        $affiliate = auth()->user()->affiliate;
        
        if (!$affiliate) {
            // Créer un compte d'affiliation si il n'existe pas
            $affiliate = Affiliate::create([
                'user_id' => auth()->id(),
                'code' => 'AFF-' . strtoupper(Str::random(8)),
                'name' => auth()->user()->name . ' - Affiliate',
                'commission_rate' => 10.00, // 10% par défaut
            ]);
        }

        // Statistiques de l'affilié
        $stats = [
            'total_earnings' => $affiliate->total_earnings,
            'pending_earnings' => $affiliate->pending_earnings,
            'paid_earnings' => $affiliate->paid_earnings,
            'total_orders' => Order::where('affiliate_id', $affiliate->id)->count(),
            'total_commission' => Order::where('affiliate_id', $affiliate->id)
                ->where('status', 'paid')
                ->sum('total'),
        ];

        // Commandes récentes générées
        $recentOrders = Order::where('affiliate_id', $affiliate->id)
            ->with(['user', 'orderItems.course'])
            ->latest()
            ->limit(10)
            ->get();

        // Cours populaires pour promotion
        $popularCourses = Course::published()
            ->with(['instructor', 'category'])
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(6)
            ->get();

        return view('affiliate.dashboard', compact('affiliate', 'stats', 'recentOrders', 'popularCourses'));
    }

    public function links()
    {
        $affiliate = auth()->user()->affiliate;
        $courses = Course::published()
            ->with(['instructor', 'category'])
            ->latest()
            ->paginate(12);

        return view('affiliate.links', compact('affiliate', 'courses'));
    }

    public function earnings()
    {
        $affiliate = auth()->user()->affiliate;
        
        // Historique des gains
        $earnings = Order::where('affiliate_id', $affiliate->id)
            ->where('status', 'paid')
            ->with(['user', 'orderItems.course'])
            ->latest()
            ->paginate(20);

        // Statistiques détaillées
        $monthlyEarnings = Order::where('affiliate_id', $affiliate->id)
            ->where('status', 'paid')
            ->selectRaw($this->buildDateFormatSelect('created_at', '%Y-%m', 'month') . ', SUM(total) as total, COUNT(*) as orders')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('affiliate.earnings', compact('affiliate', 'earnings', 'monthlyEarnings'));
    }

    public function generateLink(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'custom_text' => 'nullable|string|max:255',
        ]);

        $course = Course::findOrFail($request->course_id);
        $affiliate = auth()->user()->affiliate;

        $baseUrl = route('courses.show', $course->slug);
        $affiliateUrl = $baseUrl . '?ref=' . $affiliate->code;

        return response()->json([
            'success' => true,
            'url' => $affiliateUrl,
            'course_title' => $course->title,
            'course_price' => $course->current_price,
            'commission_rate' => $affiliate->commission_rate,
            'estimated_commission' => ($course->current_price * $affiliate->commission_rate) / 100,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $affiliate = auth()->user()->affiliate;
        
        $affiliate->update([
            'name' => $request->name,
            'commission_rate' => $request->commission_rate ?? $affiliate->commission_rate,
        ]);

        return redirect()->back()
            ->with('success', 'Profil d\'affiliation mis à jour avec succès.');
    }

    public function requestPayout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'payment_method' => 'required|in:bank_transfer,mobile_money,paypal',
            'account_details' => 'required|string',
        ]);

        $affiliate = auth()->user()->affiliate;

        // Vérifier que l'affilié a suffisamment de gains en attente
        if ($affiliate->pending_earnings < $request->amount) {
            return redirect()->back()
                ->with('error', 'Montant insuffisant. Vous avez seulement $' . number_format($affiliate->pending_earnings, 2) . ' en attente.');
        }

        // Créer une demande de retrait
        $payout = $affiliate->payouts()->create([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'account_details' => $request->account_details,
            'status' => 'pending',
        ]);

        // Mettre à jour les gains
        $affiliate->decrement('pending_earnings', $request->amount);

        return redirect()->back()
            ->with('success', 'Demande de retrait soumise avec succès. Elle sera traitée dans les 2-3 jours ouvrés.');
    }
}
