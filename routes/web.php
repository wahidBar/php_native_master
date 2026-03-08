<?php

require_once 'controllers/AuthController.php';
require_once 'controllers/RoleController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/PermissionController.php';
require_once 'controllers/DashboardController.php';
require_once 'controllers/BarangController.php';
require_once 'controllers/MenuController.php';
require_once 'controllers/UnitsController.php';
require_once 'controllers/CategoriesController.php';
require_once 'controllers/SatuanController.php';
require_once 'controllers/AsalUsulController.php';

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
$publicRoutes = [
    'auth.login',
    'auth.loginProcess',
    'auth.register',
    'auth.registerProcess'
];

if (!in_array($action, $publicRoutes)) {
    require_login();
}

/*
|--------------------------------------------------------------------------
| Parse Action
|--------------------------------------------------------------------------
*/
$parts  = explode('.', $action);
$prefix = $parts[0] ?? '';
$method = $parts[1] ?? 'index';

/*
|--------------------------------------------------------------------------
| Routing
|--------------------------------------------------------------------------
*/
switch ($prefix) {

    case 'auth':
        $controller = new AuthController();
        break;

    case 'dashboard':
        match ($method) {
            'index'  => require_permission('dashboard.view'),
            default           => null
        };

        $controller = new DashboardController();
        break;

    case 'roles':
        require_permission('roles.manage');
        $controller = new RoleController();
        break;

    case 'users':

        match ($method) {
            'index', 'show'   => require_permission('users.view'),
            'create', 'store' => require_permission('users.create'),
            'edit', 'update'  => require_permission('users.edit'),
            'delete'          => require_permission('users.delete'),
            default           => null
        };

        $controller = new UserController();
        break;
    case 'menus':

        match ($method) {
            'index', 'show'   => require_permission('menus.view'),
            'create', 'store' => require_permission('menus.create'),
            'edit', 'update'  => require_permission('menus.edit'),
            'delete'          => require_permission('menus.delete'),
            default           => null
        };

        $controller = new MenuController();
        break;
    case 'permissions':

        match ($method) {
            'index', 'show'   => require_permission('permissions.view'),
            'create', 'store' => require_permission('permissions.create'),
            // 'edit', 'update'  => require_permission('permissions.edit'),
            // 'delete'          => require_permission('permissions.delete'),
            default           => null
        };

        $controller = new PermissionController();
        break;

    case 'barang':

        match ($method) {
            'index', 'show'   => require_permission('barang.view'),
            'create', 'store' => require_permission('barang.create'),
            'edit', 'update'  => require_permission('barang.edit'),
            'delete'          => require_permission('barang.delete'),
            default           => null
        };

        $controller = new BarangController();
        break;

    case 'categories':

        match ($method) {
            'index', 'show'   => require_permission('categories.view'),
            'create', 'store' => require_permission('categories.create'),
            'edit', 'update'  => require_permission('categories.edit'),
            'delete'          => require_permission('categories.delete'),
            default           => null
        };

        $controller = new CategoriesController();
        break;

    case 'units':

        match ($method) {
            'index', 'show'   => require_permission('units.view'),
            'create', 'store' => require_permission('units.create'),
            'edit', 'update'  => require_permission('units.edit'),
            'delete'          => require_permission('units.delete'),
            default           => null
        };

        $controller = new UnitsController();
        break;
    case 'satuan':

        match ($method) {
            'index', 'show'   => require_permission('satuan.view'),
            'create', 'store' => require_permission('satuan.create'),
            'edit', 'update'  => require_permission('satuan.edit'),
            'delete'          => require_permission('satuan.delete'),
            default           => null
        };

        $controller = new SatuanController();
        break;
    case 'asal_usul':

        match ($method) {
            'index', 'show'   => require_permission('asal_usul.view'),
            'create', 'store' => require_permission('asal_usul.create'),
            'edit', 'update'  => require_permission('asal_usul.edit'),
            'delete'          => require_permission('asal_usul.delete'),
            default           => null
        };

        $controller = new AsalUsulController();
        break;
    default:
        abort(404, "Route Not Found", "Halaman tidak ditemukan.");
}

/*
|--------------------------------------------------------------------------
| Execute Controller
|--------------------------------------------------------------------------
*/
if (isset($controller)) {

    if (method_exists($controller, $method)) {
        $controller->$method();
    } else {
        abort(404, "Method Not Found", "Method <b>$method</b> tidak ditemukan.");
    }
} else {
    abort(500, "Controller Error", "Controller tidak ditemukan.");
}
