<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Users
    |--------------------------------------------------------------------------
    |
    | This are the default users that will have the admin role
    |
    */

    'admin_users' => [
        'admin' => [
            'name' => env('ST_ADMIN_NAME', 'John Doe'),
            'email' => env('ST_ADMIN_EMAIL', 'admin@example.com'),
            'password' => env('ST_ADMIN_PASSWORD', 'password'),
        ],
        'ceo' => [
            'name' => env('ST_CEO_NAME', 'Jane Ceo'),
            'email' => env('ST_CEO_EMAIL', 'ceo@example.com'),
            'password' => env('ST_CEO_PASSWORD', 'password'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact details
    |--------------------------------------------------------------------------
    |
    | This are the default contact details that will be used as initial values
    | for the settings migration. They can be changed on the settings page.
    |
    */

    'contact_details' => [
        'name' => env('ST_CONTACT_NAME', ''),
        'company_name' => env('ST_CONTACT_COMPANY_NAME', ''),
        'company_number' => env('ST_CONTACT_COMPANY_NUMBER', ''),
        'address' => env('ST_CONTACT_ADDRESS', ''),
        'city' => env('ST_CONTACT_CITY', ''),
        'country' => env('ST_CONTACT_COUNTRY', ''),
        'email' => env('ST_CONTACT_EMAIL', ''),
        'phone' => env('ST_CONTACT_PHONE', ''),
    ],

    'social_links' => [
        'facebook' => env('ST_SOCIAL_FACEBOOK', ''),
        'instagram' => env('ST_SOCIAL_INSTAGRAM', ''),
        'linkedin' => env('ST_SOCIAL_LINKEDIN', ''),
    ],
];
