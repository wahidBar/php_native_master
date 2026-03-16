<?php
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/helpers.php';
require_once BASE_PATH . '/controllers/BaseController.php';

class DashboardController extends BaseController
{
    public function index()
    {
        global $pdo;

        $users = $pdo->query("
            SELECT users.*, roles.name AS role_name 
            FROM users 
            JOIN roles ON roles.id = users.role_id
            ORDER BY users.id ASC
        ")->fetchAll();

        $layout = is_ajax() ? null : 'main';

        $this->render('dashboard', compact('users'), $layout);
    }

    public function forbidden($code = '', $title = '', $message = '')
    {
        $layout = is_ajax() ? null : 'main';

        $this->render('errors/error', compact('code', 'title', 'message'), $layout);
    }

    public function notFound($code = '', $title = '', $message = '')
    {
        $layout = is_ajax() ? null : 'main';

        $this->render('errors/error', compact('code', 'title', 'message'), $layout);
    }

    public function error($code = '', $title = '', $message = '')
    {
        $layout = is_ajax() ? null : 'main';

        $this->render('errors/error', compact('code', 'title', 'message'), $layout);
    }
}