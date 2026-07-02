<?php

// =====================================================
// KEMASKINI config/services.php
// Tambah 'anon_key' dalam bahagian supabase
// =====================================================
// Cari bahagian supabase dalam fail asal dan GANTIKAN dengan ini:

return [

    // ... bahagian lain kekal sama ...

    'supabase' => [
        'url'      => env('SUPABASE_URL', ''),
        'key'      => env('SUPABASE_KEY', ''),       // service_role key
        'secret'   => env('SUPABASE_SECRET', ''),
        'anon_key' => env('SUPABASE_ANON_KEY', ''),  // ← TAMBAH INI
    ],

    // ... bahagian lain kekal sama ...

];
