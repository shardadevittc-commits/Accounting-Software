<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the User Profile & Preferences page.
     */
    public function index()
    {
        $user = Auth::user();
        return view('admin.auth.profile', compact('user'));
    }

    /**
     * Update User Profile details (Full Name, Email, and Avatar Photo).
     * Uses Laravel Native Storage Facade (Storage::disk('public')).
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $allowedMimes = implode(',', config('profile.allowed_mimes', ['jpg', 'jpeg', 'png', 'webp']));
        $maxSize = config('profile.max_file_size', 8192);
        $disk = config('profile.disk', 'public');
        $folder = config('profile.folder', 'avatars');

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'avatar' => ['nullable', 'image', 'mimes:' . $allowedMimes, 'max:' . $maxSize],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // Handle Avatar File Upload using Laravel Native Storage Facade
        if ($request->hasFile('avatar')) {
            $avatarFile = $request->file('avatar');

            // Delete old avatar file from storage disk if exists
            if ($user->avatar && Storage::disk($disk)->exists($user->avatar)) {
                Storage::disk($disk)->delete($user->avatar);
            }

            // Generate unique filename to prevent conflicts
            $filename = 'avatar_' . $user->id . '_' . time() . '.' . $avatarFile->getClientOriginalExtension();

            // Store file using Laravel Storage disk
            $storedPath = $avatarFile->storeAs($folder, $filename, $disk);

            // Save only relative path in DB (e.g. 'avatars/avatar_1_1786356978.png')
            $user->avatar = $storedPath;
        }

        $user->save();

        return redirect()->back()->with('profile_success', 'Profile details and photo updated successfully!');
    }

    /**
     * Remove User Avatar Photo and delete file from Storage disk.
     */
    public function removeAvatar()
    {
        $user = Auth::user();
        $disk = config('profile.disk', 'public');

        // Delete avatar file from Storage disk
        if ($user->avatar && Storage::disk($disk)->exists($user->avatar)) {
            Storage::disk($disk)->delete($user->avatar);
        }

        $user->avatar = null;
        $user->save();

        return redirect()->back()->with('profile_success', 'Profile photo removed successfully!');
    }

    /**
     * Update User Password.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors(['current_password' => 'The current password you entered is incorrect.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return redirect()->back()->with('password_success', 'Password changed successfully!');
    }
}
