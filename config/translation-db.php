<?php

return [
    'disable_debugbar' => true,

    'use_cache' => env('CACHE_TRANSLATION', true),

    'cache_prefix' => '_translations',

    'cache_tag' => 'trans',

    'database' => 'mysql', //Database connection name to use

    'fallback_locale' => 'de',

    'add_new_translations' => false, // Add not found translations to DB

    'add_to_all_locales' => true, // Add not found translations to DB for all locales
];
