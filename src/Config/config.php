<?php
/**
 * @author captain-redbeard
 * @since 05/02/17
 */

return [
    'app' => [
        'config_directory' =>       '/Config/',
        'models_directory' =>       '/Models/',
        'views_directory' =>        '/Views/',
        'controllers_directory' =>  '/Controllers/',
        'models_path' =>            'Models\\',
        'views_path' =>             'Views\\',
        'controllers_path' =>       'Controllers\\',
        'system_path' =>            '\\Redbeard\\Crew\\',
        'default_controller' =>     'Home',
        'default_method' =>         'index',
        'timezone' =>               'UTC',
        'user_session' =>           'redbeard_user',
        'user_role' =>              10,
        'password_cost' =>          12,
        'max_login_attempts' =>     5,
        'secure_cookies' =>         true,
        'token_expire_time' =>      900
    ]
];
