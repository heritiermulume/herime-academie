<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RatingPendingController extends Controller
{
    public function dismiss(Request $request)
    {
        $request->session()->forget('pending_content_rating_course_id');

        return back();
    }
}
