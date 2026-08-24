<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    /**
     * Update the authenticated user's primary theme color preference.
     */
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/i'],
        ]);

        $user = Auth::user();
        if ($user) {
            $user->theme_color = strtoupper($request->theme_color);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Theme color updated successfully!',
                'theme_color' => $user->theme_color,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not authenticated',
        ], 401);
    }
}
