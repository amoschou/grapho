<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Source path and Markdown files
    |--------------------------------------------------------------------------
    |
    | This is the folder of Markdown files. If you are using GitHub to manage
    | the Markdown files, you would normally `cd` into this path and then
    | run `git pull` to update them. The Github repository is here too.
    |
    */

    'source_path' => resource_path('src/grapho'),

    'github_repo' => 'githubusername/repositoryname',

    'github_api_token' => env('GH_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Routes and middleware
    |--------------------------------------------------------------------------
    |
    | This prefix is applied to all routes provided by this package. Use the
    | empty string to apply no prefix. Middleware can also be chosen here,
    | uncomment 'auth' and 'verify' to protect the routes as desired.
    |
    */

    'route_prefix' => 'docs',

    'middleware' => [
        // 'auth',
        // 'verified',
    ],

];
