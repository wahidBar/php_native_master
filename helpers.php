<?php
session_start();
require_once BASE_PATH . '/config/db.php';
require_once BASE_PATH . '/controllers/DashboardController.php';

function dd(...$vars)
{
    echo '<style>
                body { background:#1e1e1e; color:#fff; font-family: monospace; }
                pre { background:#2d2d2d; padding:15px; border-radius:6px; overflow:auto; }
              </style>';

    foreach ($vars as $var) {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }

    die();
}

function is_ajax()
{
    return (
        (isset($_GET['ajax']) && $_GET['ajax'] == 1) ||
        (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    );
}

/* ============================================================
|  CSRF PROTECTION
============================================================ */
function csrf_token()
{
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function validate_csrf()
{
    if (
        !isset($_POST['csrf']) ||
        !isset($_SESSION['csrf']) ||
        !hash_equals($_SESSION['csrf'], $_POST['csrf'])
    ) {
        flash('CSRF token tidak valid.', 'error');
        redirect_back();
    }
}


/* ============================================================
|  FLASH MESSAGE
============================================================ */
function flash($message, $type = 'info')
{
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}


function flash_show()
{
    if (empty($_SESSION['flash']))
        return;

    $flash = $_SESSION['flash'];

    unset($_SESSION['flash']);

    $map = [
        'success' => 'success',
        'error'   => 'danger',
        'warning' => 'warning',
        'info'    => 'info'
    ];

    $type = $map[$flash['type']] ?? 'info';

    echo '
    <div id="flash-alert" class="alert alert-' . $type . ' alert-dismissible fade show">

        ' . htmlspecialchars($flash['message']) . '

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

    <script>
        setTimeout(function() {
            const alert = document.getElementById("flash-alert");
            if(alert){
                alert.classList.remove("show");
                alert.classList.add("fade");

                setTimeout(()=>alert.remove(),300);
            }
        },500); // 3 detik
    </script>
    ';
}


/* ============================================================
|  REDIRECT
============================================================ */
function redirect($url)
{
    header("Location: $url");
    exit;
}

function redirect_back()
{
    $prev = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    redirect($prev);
}


/* ============================================================
|  ERROR HANDLING
============================================================ */
function abort($code = 500, $title = 'Error', $message = '')
{
    http_response_code($code);

    $controller = new DashboardController();

    switch ($code) {
        case 403:
            $controller->forbidden($code, $title, $message);
            break;

        case 404:
            $controller->notFound($code, $title, $message);
            break;

        default:
            $controller->error($code, $title, $message);
            break;
    }

    exit;
}


function log_error($message, $exception = null)
{
    $file = __DIR__ . '/logs/error.log';
    $time = date('Y-m-d H:i:s');

    $text  = "[$time] ERROR: $message\n";

    if ($exception) {
        $text .= "Message: " . $exception->getMessage() . "\n";
        $text .= "Trace: " . $exception->getTraceAsString() . "\n";
    }

    $text .= "---------------------------------\n";

    file_put_contents($file, $text, FILE_APPEND);
}


/* ============================================================
|  AUTH SYSTEM
============================================================ */
function auth()
{
    return $_SESSION['auth'] ?? null;
}

function auth_id()
{
    return auth()['id'] ?? null;
}

function auth_role_id()
{
    return auth()['role_id'] ?? null;
}

function is_logged_in()
{
    return isset($_SESSION['auth']);
}

function require_login()
{
    if (!is_logged_in()) {
        redirect("?action=auth.login");
    }
}

function logout()
{
    session_destroy();
    redirect("?action=auth.login");
}


/* ============================================================
|  ROLE CHECK (LEGACY SUPPORT)
============================================================ */
function has_role($roles)
{
    if (!is_logged_in()) return false;
    return in_array(auth_role_id(), (array)$roles);
}

function require_role($roles = [])
{
    require_login();

    if (!has_role($roles)) {
        abort(403, "Forbidden", "Anda tidak memiliki akses.");
    }
}


/* ============================================================
|  RBAC PERMISSION SYSTEM (NEW)
============================================================ */
function user_has_permission($slug)
{
    global $pdo;

    if (!is_logged_in()) return false;

    // Superadmin bypass
    if (auth_role_id() == 1) return true;

    static $cache = [];

    $roleId = auth_role_id();

    if (!isset($cache[$roleId])) {

        $sql = "
            SELECT p.slug
            FROM role_permissions rp
            JOIN permissions p ON p.id = rp.permission_id
            WHERE rp.role_id = ?
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$roleId]);

        $cache[$roleId] = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    return in_array($slug, $cache[$roleId]);
}


function require_permission($slug)
{
    require_login();

    if (!user_has_permission($slug)) {
        abort(403, "Forbidden", "Anda tidak memiliki permission: $slug");
    }
}

if (!function_exists('can')) {
    function can($permission)
    {
        if (!is_logged_in()) {
            return false;
        }

        return user_has_permission($permission);
    }
}

/* ============================================================
|  DYNAMIC MENU
============================================================ */
function active_menu($prefix)
{
    $action = $_GET['action'] ?? '';
    return str_starts_with($action, $prefix) ? 'active-menu' : '';
}

function build_menu_tree($menus, $parentId = null)
{
    $branch = [];

    foreach ($menus as $menu) {
        if ($menu['parent_id'] == $parentId) {
            $children = build_menu_tree($menus, $menu['id']);
            if ($children) {
                $menu['children'] = $children;
            }
            $branch[] = $menu;
        }
    }

    return $branch;
}

function get_user_menus()
{
    global $pdo;

    if (!is_logged_in()) return [];

    $sql = "
        SELECT DISTINCT m.*
        FROM menus m
        JOIN menu_permissions mp ON mp.menu_id = m.id
        JOIN role_permissions rp ON rp.permission_id = mp.permission_id
        WHERE rp.role_id = ?
        ORDER BY m.order_number ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([auth_role_id()]);
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return build_menu_tree($menus);
}

function render_menu($menus, $isSub = false)
{
    echo $isSub
        ? "<ul class='nxl-submenu'>"
        : "<ul class='nxl-navbar'>";

    foreach ($menus as $menu) {

        $hasChildren = !empty($menu['children']);
        $isActive = active_menu($menu['route']);

        if ($hasChildren) {

            // cek apakah ada child yang aktif
            $childActive = false;
            foreach ($menu['children'] as $child) {
                if (active_menu($child['route'])) {
                    $childActive = true;
                    break;
                }
            }

            echo "<li class='nxl-item nxl-hasmenu " . ($childActive ? "active open" : "") . "'>";

            echo "<a href='javascript:void(0);' class='nxl-link'>";
            echo "<span class='nxl-micon'><i class='" . htmlspecialchars($menu['icon']) . "'></i></span>";
            echo "<span class='nxl-mtext'>" . htmlspecialchars($menu['name']) . "</span>";
            echo "<span class='nxl-arrow'><i class='feather-chevron-right'></i></span>";
            echo "</a>";

            render_menu($menu['children'], true);

            echo "</li>";
        } else {

            echo "<li class='nxl-item'>";
            echo "<a href='?action=" . htmlspecialchars($menu['route']) . "' 
                     class='nxl-link $isActive'>";
            echo "<span class='nxl-micon'><i class='" . htmlspecialchars($menu['icon']) . "'></i></span>";
            echo "<span class='nxl-mtext'>" . htmlspecialchars($menu['name']) . "</span>";
            echo "</a>";
            echo "</li>";
        }
    }

    echo "</ul>";
}



/* ============================================================
|  API HELPERS
============================================================ */
function json_response($data, $status = 200)
{
    http_response_code($status);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit;
}

function generate_api_token()
{
    return bin2hex(random_bytes(32));
}

function require_api_token()
{
    global $pdo;

    $headers = getallheaders();
    $token   = $headers['Authorization'] ?? '';

    if (!$token) {
        json_response(["error" => "Unauthorized"], 401);
    }

    $sql = "SELECT * FROM users WHERE api_token = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$token]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        json_response(["error" => "Invalid Token"], 401);
    }

    return $user;
}
