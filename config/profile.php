<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Profile Avatar Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Uses Laravel's native Storage system (Storage disk 'public').
    | Files are stored in storage/app/public/avatars/ and linked via public/storage.
    |
    */

    'disk' => 'public',

    'folder' => 'avatars',
    
    'default_avatar' => 'assets/images/avatar-3d.png',

    'max_file_size' => 8192, // KB (8 MB)
    
    'allowed_mimes' => ['jpg', 'jpeg', 'png', 'webp'],
];
