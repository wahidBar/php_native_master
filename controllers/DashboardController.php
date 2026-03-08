<?php
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/helpers.php';
require_once BASE_PATH . '/controllers/BaseController.php';

class DashboardController extends BaseController
{
    public function index()
    {
        // dd('halooo');
        global $pdo;

        $users = $pdo->query("
            SELECT users.*, roles.name AS role_name 
            FROM users 
            JOIN roles ON roles.id = users.role_id
            ORDER BY users.id ASC
        ")->fetchAll();

        $this->render('dashboard', compact('users'), 'main');
    }

    public function forbidden($code = '', $title = '', $message = '')
    {
        $this->render('errors/error', compact('code', 'title', 'message'), 'main');
    }

    public function notFound($code = '', $title = '', $message = '')
    {
        $this->render('errors/error', compact('code', 'title', 'message'), 'main');
    }

    public function error($code = '', $title = '', $message = '')
    {
        $this->render('errors/error', compact('code', 'title', 'message'), 'main');
    }
}
