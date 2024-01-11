<?php

return [
    'token' => env('FORGE_CLI_TOKEN'),
    'server_id' => env('FORGE_SERVER_ID'),
    'root_domain' => env('FORGE_ROOT_DOMAIN'),
    'git_provider' => env('FORGE_GIT_PROVIDER'),
    'repository' => env('FORGE_APP_REPOSITORY'),
    'db_username' => env('FORGE_DB_USERNAME', 'forge'),
    'db_password' => env('FORGE_DB_PASSWORD'),
];
