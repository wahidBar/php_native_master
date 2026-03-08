<?php

require_once 'controllers/API/AuthController.php';
require_once 'controllers/API/UserController.php';

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
*/
$publicApi = [
    'api.auth.login'
];

if (!in_array($action, $publicApi)) {
    $apiUser = require_api_token();
}

/*
|--------------------------------------------------------------------------
| Parse
|--------------------------------------------------------------------------
*/
$parts    = explode('.', $action);
$resource = $parts[1] ?? '';
$method   = $parts[2] ?? 'index';

/*
|--------------------------------------------------------------------------
| Routing
|--------------------------------------------------------------------------
*/
switch ($resource) {

    case 'auth':
        $controller = new ApiAuthController();
        break;

    case 'users':

        match ($method) {
            'index', 'show'   => require_permission('users.view'),
            'store'           => require_permission('users.create'),
            'update'          => require_permission('users.edit'),
            'delete'          => require_permission('users.delete'),
            default           => null
        };

        $controller = new ApiUserController();
        break;

    default:
        json_response(["error" => "API route not found"], 404);
}

/*
|--------------------------------------------------------------------------
| Execute
|--------------------------------------------------------------------------
*/
if (method_exists($controller, $method)) {
    $controller->$method();
} else {
    json_response(["error" => "API method not found"], 404);
}
