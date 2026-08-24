<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StorageController extends Controller
{
    /**
     * Serve public storage files (avatars, attachments, media).
     */
    public function show(string $path)
    {
        $filePath = storage_path('app/public/' . $path);

        if (!file_exists($filePath)) {
            abort(404);
        }

        return response()->file($filePath);
    }
}
