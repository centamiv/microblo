<?php
if (!defined('MICROBLO_APP') && !defined('MICROBLO_ADMIN')) { http_response_code(403); exit; }

return [
    // Site
    'site_name' => 'Microblo',
    'site_description' => '',
    'external_links' => [ 
        // 'name' => 'url'
    ],

    // Language
    'lang_default' => 'en',
    'supported_languages' => ['en', 'it'],

    // Admin
    'admin_user' => 'admin', // empty to disable admin
    'admin_pass' => 'password',

    // Theme
    'theme_name' => 'terminal', // 'bootstrap', 'terminal'

    // Analytics
    'analytics_id' => '', // e.g. UA-XXXXX-Y

    // Cache
    'cache_ttl' => 3600, // seconds
];
