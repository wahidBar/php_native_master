<?php
class BaseController
{
    protected function render($view, $data = [], $layout = 'main')
    {
        extract($data);

        $viewFile = BASE_PATH . "/views/$view.php";

        if (!file_exists($viewFile)) {
            abort(404, "View Not Found", "View $view tidak ditemukan.");
        }

        // ==============================
        // 🔥 Jika layout null → render partial saja
        // ==============================
        if ($layout === null) {
            require $viewFile;
            return;
        }

        switch ($layout) {
            case 'home':
                $layoutFile = BASE_PATH . '/views/home/layouts/home.php';
                break;

            case 'main':
            default:
                $layoutFile = BASE_PATH . '/views/layouts/main.php';
                break;
        }

        if (!file_exists($layoutFile)) {
            abort(500, "Layout Error", "Layout $layout tidak ditemukan.");
        }

        $content = $viewFile;
        require $layoutFile;
    }
}
