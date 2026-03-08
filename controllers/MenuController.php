<?php

require_once BASE_PATH . '/controllers/BaseController.php';

class MenuController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        require_permission('menus.view');
        global $pdo;

        $limit  = 10;
        $page   = max(1, intval($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        $parent = intval($_GET['parent'] ?? 0);

        $offset = ($page - 1) * $limit;

        $where  = [];
        $params = [];

        if ($search !== '') {
            $where[] = "(m.name LIKE ? OR m.route LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if ($parent > 0) {
            $where[] = "m.parent_id = ?";
            $params[] = $parent;
        }

        $whereSQL = $where ? "WHERE " . implode(" AND ", $where) : "";

        // COUNT
        $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM menus m
        LEFT JOIN menus p ON p.id = m.parent_id
        $whereSQL
    ");
        $stmt->execute($params);
        $totalRows = $stmt->fetchColumn();
        $totalPages = ceil($totalRows / $limit);

        // DATA
        $stmt = $pdo->prepare("
        SELECT m.*, p.name as parent_name
        FROM menus m
        LEFT JOIN menus p ON p.id = m.parent_id
        $whereSQL
        ORDER BY 
        COALESCE(m.parent_id, m.id),
        m.parent_id IS NULL DESC,
        m.order_number ASC
        LIMIT $limit OFFSET $offset
        ");
        $stmt->execute($params);
        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Parent dropdown (hanya parent utama)
        $parents = $pdo->query("
        SELECT id, name 
        FROM menus 
        WHERE parent_id IS NULL
        ORDER BY order_number ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // dd('halo', $limit);
        // AJAX
        if (isset($_GET['ajax'])) {
            echo json_encode([
                'menus'       => $menus,
                'totalPages'  => $totalPages,
                'currentPage' => $page,
                'limit'       => $limit
            ]);
            exit;
        }

        $this->render('menus/index', compact(
            'menus',
            'parents',
            'totalPages',
            'page'
        ), 'main');
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE PAGE
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        require_permission('menus.create');
        global $pdo;

        $tables = $pdo->query("
            SELECT table_name
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
            AND table_type = 'BASE TABLE'
            AND table_name NOT IN ('menus','permissions','role_permissions','roles')
        ")->fetchAll(PDO::FETCH_COLUMN);

        $parents = $pdo->query("
            SELECT id, name FROM menus ORDER BY name ASC
        ")->fetchAll();

        $this->render('menus/form', compact('tables', 'parents'), 'main');
    }

    /*
    |--------------------------------------------------------------------------
    | STORE (Insert Menu + Generate CRUD)
    |--------------------------------------------------------------------------
    */
    public function store()
    {
        require_permission('menus.create');
        global $pdo;

        $pdo->beginTransaction();

        try {

            $name  = trim($_POST['name'] ?? '');
            $route = strtolower(trim($_POST['route'] ?? ''));
            $icon  = trim($_POST['icon'] ?? '');

            $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $target_menu = !empty($_POST['target_menu']) ? (int)$_POST['target_menu'] : null;


            $useGenerator = !empty($_POST['use_generator']);
            $controller = ucfirst(trim($_POST['controller'] ?? ''));
            $model      = ucfirst(trim($_POST['model'] ?? ''));
            $view       = strtolower(trim($_POST['view'] ?? ''));
            $tables     = $_POST['tables'] ?? [];

            if (!$name || !$route) {
                flash("Nama & Route wajib diisi", "danger");
                redirect("?action=menus.create");
            }

            if (!preg_match('/^[a-z0-9_]+$/', $route)) {
                flash("Route hanya huruf kecil & underscore", "danger");
                redirect("?action=menus.create");
            }

            /* CEK DUPLICATE */
            $stmt = $pdo->prepare("SELECT id FROM menus WHERE route=?");
            $stmt->execute([$route]);

            if ($stmt->fetch()) {
                flash("Route sudah digunakan", "danger");
                redirect("?action=menus.create");
            }

            if ($target_menu) {

                $stmt = $pdo->prepare("
                SELECT order_number,parent_id
                FROM menus
                WHERE id=?
            ");
                $stmt->execute([$target_menu]);

                $target = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$target) {
                    throw new Exception("Target menu tidak ditemukan");
                }

                $order = $target['order_number'] + 1;

                if ($parent_id) {

                    $shift = $pdo->prepare("
                    UPDATE menus
                    SET order_number = order_number + 1
                    WHERE parent_id = ?
                    AND order_number >= ?
                ");
                    $shift->execute([$parent_id, $order]);
                } else {

                    $shift = $pdo->prepare("
                    UPDATE menus
                    SET order_number = order_number + 1
                    WHERE parent_id IS NULL
                    AND order_number >= ?
                ");
                    $shift->execute([$order]);
                }
            } else {

                if ($parent_id) {

                    $stmt = $pdo->prepare("
                    SELECT COALESCE(MAX(order_number),0)+1
                    FROM menus
                    WHERE parent_id=?
                ");
                    $stmt->execute([$parent_id]);
                } else {

                    $stmt = $pdo->query("
                    SELECT COALESCE(MAX(order_number),0)+1
                    FROM menus
                    WHERE parent_id IS NULL
                ");
                }

                $order = $stmt->fetchColumn();
            }

            /* ===== INSERT MENU ===== */
            $stmt = $pdo->prepare("
            INSERT INTO menus (name, route, icon, parent_id, order_number)
            VALUES (?, ?, ?, ?, ?)
        ");
            $stmt->execute([$name, $route, $icon, $parent_id, $order]);

            $menuId = $pdo->lastInsertId();

            /* ===== CREATE CRUD PERMISSIONS ===== */
            if ($useGenerator) {
                $crudActions = ['view', 'create', 'edit', 'delete'];
            } else {
                $crudActions = ['view'];
            }

            foreach ($crudActions as $action) {

                $slug = $route . '.' . $action;
                $permName = strtoupper($route) . ' ' . strtoupper($action);

                $stmtCheck = $pdo->prepare("SELECT id FROM permissions WHERE slug = ?");
                $stmtCheck->execute([$slug]);
                $permissionId = $stmtCheck->fetchColumn();

                if (!$permissionId) {
                    $stmtPerm = $pdo->prepare("
                    INSERT INTO permissions (name, slug)
                    VALUES (?, ?)
                ");
                    $stmtPerm->execute([$permName, $slug]);
                    $permissionId = $pdo->lastInsertId();
                }

                // link menu ↔ permission
                $stmtMP = $pdo->prepare("
                INSERT IGNORE INTO menu_permissions (menu_id, permission_id)
                VALUES (?, ?)
            ");
                $stmtMP->execute([$menuId, $permissionId]);

                // auto give superadmin (role_id = 1)
                $stmtRP = $pdo->prepare("
                INSERT IGNORE INTO role_permissions (role_id, permission_id)
                VALUES (1, ?)
            ");
                $stmtRP->execute([$permissionId]);
            }

            /* ===== GENERATOR ===== */
            if ($useGenerator && !empty($tables) && $controller && $model && $view) {

                foreach ($tables as $table) {

                    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) continue;

                    $stmtCol = $pdo->prepare("
                    SELECT COLUMN_NAME, DATA_TYPE, COLUMN_KEY, IS_NULLABLE
                    FROM information_schema.columns
                    WHERE table_schema = DATABASE()
                    AND table_name = ?
                ");
                    $stmtCol->execute([$table]);
                    $columns = $stmtCol->fetchAll();

                    if (!$columns) continue;

                    $pk = 'id';
                    foreach ($columns as $c) {
                        if ($c['COLUMN_KEY'] === 'PRI') {
                            $pk = $c['COLUMN_NAME'];
                        }
                    }

                    $stmtRel = $pdo->prepare("
                    SELECT COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                    FROM information_schema.key_column_usage
                    WHERE table_schema = DATABASE()
                    AND table_name = ?
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                    $stmtRel->execute([$table]);
                    $relations = $stmtRel->fetchAll();

                    $this->generateModel($model, $table, $pk, $columns, $relations);
                    $this->generateController($controller, $model, $view, $table, $pk);
                    $this->generateViews($view, $table, $pk, $columns, $relations);
                }
            }

            $pdo->commit();

            flash("Menu, Permission & CRUD berhasil dibuat", "success");
            redirect("?action=menus.index");
        } catch (Exception $e) {

            $pdo->rollBack();
            log_error("Gagal membuat menu", $e);

            flash("Terjadi kesalahan saat membuat menu", "danger");
            redirect("?action=menus.create");
        }
    }
    /*
    |--------------------------------------------------------------------------
    | EDIT PAGE
    |--------------------------------------------------------------------------
    */
    public function edit()
    {
        require_permission('menus.edit');
        global $pdo;

        $id = intval($_GET['id'] ?? 0);

        if (!$id) {
            flash("ID tidak valid", "danger");
            redirect("?action=menus.index");
        }

        $stmt = $pdo->prepare("SELECT * FROM menus WHERE id = ?");
        $stmt->execute([$id]);
        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$menu) {
            flash("Menu tidak ditemukan", "danger");
            redirect("?action=menus.index");
        }

        // Hilangkan .index agar konsisten dengan store
        if (str_ends_with($menu['route'], '.index')) {
            $menu['route'] = str_replace('.index', '', $menu['route']);
        }

        $stmtParent = $pdo->prepare("
        SELECT id, name 
        FROM menus 
        WHERE id != ?
        ORDER BY name ASC
    ");
        $stmtParent->execute([$id]);
        $parents = $stmtParent->fetchAll(PDO::FETCH_ASSOC);

        $this->render('menus/form', compact('menu', 'parents'), 'main');
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE MENU
    |--------------------------------------------------------------------------
    */
    public function update()
    {
        require_permission('menus.edit');
        global $pdo;

        $pdo->beginTransaction();

        try {

            $id        = intval($_POST['id'] ?? 0);
            $name      = trim($_POST['name'] ?? '');
            $route     = trim($_POST['route'] ?? '');
            $icon      = trim($_POST['icon'] ?? '');
            $parent_id = $_POST['parent_id'] ?: null;
            $target_menu = !empty($_POST['target_menu']) ? (int)$_POST['target_menu'] : null;

            if (!$id || !$name || !$route) {
                throw new Exception("Data tidak valid");
            }

            if ($parent_id == $id) {
                throw new Exception("Menu tidak boleh menjadi parent dirinya sendiri");
            }

            /* HITUNG ORDER */

            if ($target_menu) {

                $stmt = $pdo->prepare("
                SELECT order_number
                FROM menus
                WHERE id=?
            ");
                $stmt->execute([$target_menu]);

                $target = $stmt->fetch(PDO::FETCH_ASSOC);

                $order = $target['order_number'] + 1;

                if ($parent_id) {

                    $shift = $pdo->prepare("
                    UPDATE menus
                    SET order_number = order_number + 1
                    WHERE parent_id = ?
                    AND order_number >= ?
                    AND id != ?
                ");
                    $shift->execute([$parent_id, $order, $id]);
                } else {

                    $shift = $pdo->prepare("
                    UPDATE menus
                    SET order_number = order_number + 1
                    WHERE parent_id IS NULL
                    AND order_number >= ?
                    AND id != ?
                ");
                    $shift->execute([$order, $id]);
                }
            } else {

                $order = 1;
            }

            /* ======================================================
        | PREVENT RECURSIVE LOOP (PARENT CIRCULAR)
        ====================================================== */

            $check = $parent_id;
            while ($check) {
                $stmt = $pdo->prepare("SELECT parent_id FROM menus WHERE id = ?");
                $stmt->execute([$check]);
                $check = $stmt->fetchColumn();

                if ($check == $id) {
                    throw new Exception("Circular parent detected");
                }
            }

            /* ======================================================
        | GET OLD DATA
        ====================================================== */

            $stmtOld = $pdo->prepare("SELECT route FROM menus WHERE id = ?");
            $stmtOld->execute([$id]);
            $oldMenu = $stmtOld->fetch(PDO::FETCH_ASSOC);

            if (!$oldMenu) {
                throw new Exception("Menu tidak ditemukan");
            }

            $oldBase = str_replace('.index', '', $oldMenu['route']);
            // $newRouteIndex = $route . '.index';

            /* ======================================================
        | UPDATE MENU
        ====================================================== */

            $stmt = $pdo->prepare("
            UPDATE menus
            SET name=?, route=?, icon=?, parent_id=?, order_number=?
            WHERE id=?
        ");

            $stmt->execute([
                $name,
                $route,
                $icon,
                $parent_id,
                $order,
                $id
            ]);

            /* ======================================================
        | SYNC PERMISSIONS IF ROUTE CHANGED
        ====================================================== */

            if ($oldBase !== $route) {

                $crud = ['view', 'create', 'edit', 'delete'];

                foreach ($crud as $action) {

                    $oldSlug = $oldBase . '.' . $action;
                    $newSlug = $route . '.' . $action;

                    $stmtPerm = $pdo->prepare("
                    UPDATE permissions
                    SET slug=?, name=?
                    WHERE slug=?
                ");

                    $stmtPerm->execute([
                        $newSlug,
                        strtoupper($route) . ' ' . strtoupper($action),
                        $oldSlug
                    ]);
                }
            }

            $pdo->commit();

            flash("Menu berhasil diupdate (Full Sync Mode)", "success");
            redirect("?action=menus.index");
        } catch (Exception $e) {

            $pdo->rollBack();
            log_error("Menu Update Enterprise Error", $e);

            flash($e->getMessage(), "danger");
            redirect("?action=menus.edit&id=" . $id);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE MENU
    |--------------------------------------------------------------------------
    */
    public function delete()
    {
        require_permission('menus.delete');
        global $pdo;

        $pdo->beginTransaction();

        try {

            $id = intval($_GET['id'] ?? 0);
            if (!$id) {
                throw new Exception("ID tidak valid");
            }

            $this->deleteMenuRecursive($id);

            $pdo->commit();

            flash("Menu berhasil dihapus (RBAC Sync Clean)", "success");
        } catch (Exception $e) {

            $pdo->rollBack();
            log_error("Delete Menu RBAC Error", $e);

            flash("Gagal menghapus menu", "danger");
        }

        redirect("?action=menus.index");
    }

    private function deleteMenuRecursive($menuId)
    {
        global $pdo;


        $stmtChild = $pdo->prepare("SELECT id FROM menus WHERE parent_id=?");
        $stmtChild->execute([$menuId]);
        $children = $stmtChild->fetchAll(PDO::FETCH_COLUMN);

        foreach ($children as $childId) {
            $this->deleteMenuRecursive($childId);
        }


        $stmtMP = $pdo->prepare("
        SELECT permission_id 
        FROM menu_permissions 
        WHERE menu_id=?
    ");
        $stmtMP->execute([$menuId]);
        $permissionIds = $stmtMP->fetchAll(PDO::FETCH_COLUMN);


        $pdo->prepare("
        DELETE FROM menu_permissions 
        WHERE menu_id=?
    ")->execute([$menuId]);


        foreach ($permissionIds as $pid) {

            // Cek apakah permission masih dipakai menu lain
            $check = $pdo->prepare("
            SELECT COUNT(*) 
            FROM menu_permissions 
            WHERE permission_id=?
        ");
            $check->execute([$pid]);
            $usedCount = $check->fetchColumn();

            // Kalau tidak dipakai lagi
            if ($usedCount == 0) {

                // Hapus dari role_permissions
                $pdo->prepare("
                DELETE FROM role_permissions 
                WHERE permission_id=?
            ")->execute([$pid]);

                // Hapus dari permissions
                $pdo->prepare("
                DELETE FROM permissions 
                WHERE id=?
            ")->execute([$pid]);
            }
        }

        $pdo->prepare("
        DELETE FROM menus 
        WHERE id=?
    ")->execute([$menuId]);
    }

    public function getMenusByParent()
    {
        global $pdo;

        $parent_id = $_GET['parent_id'] ?? null;

        try {

            if ($parent_id) {

                $stmt = $pdo->prepare("
                SELECT id,name,order_number
                FROM menus
                WHERE parent_id = ?
                ORDER BY order_number
            ");

                $stmt->execute([$parent_id]);
            } else {

                $stmt = $pdo->query("
                SELECT id,name,order_number
                FROM menus
                WHERE parent_id IS NULL
                ORDER BY order_number
            ");
            }

            $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                "success" => true,
                "data" => $menus
            ]);
        } catch (Exception $e) {

            echo json_encode([
                "success" => false,
                "message" => $e->getMessage()
            ]);
        }

        exit;
    }

    private function generateModel($model, $table, $pk, $columns, $relations)
    {
        $file = BASE_PATH . "/models/" . $model . "Model.php";

        $searchable = [];
        foreach ($columns as $col) {
            if (in_array(strtolower($col['DATA_TYPE']), ['varchar', 'text', 'char', 'longtext'])) {
                $searchable[] = $col['COLUMN_NAME'];
            }
        }

        $searchArrayExport = var_export($searchable, true);

        $code = "<?php
class {$model}Model {

    private \$pdo;
    private \$table = '{$table}';
    private \$pk = '{$pk}';
    private \$searchable = {$searchArrayExport};

    public function __construct(\$pdo){
        \$this->pdo = \$pdo;
    }

    public function paginate(\$search='', \$page=1, \$limit=10, \$sort=null, \$order='asc'){
        \$offset = (\$page-1)*\$limit;
        \$where = ' WHERE 1=1 ';
        \$params = [];

        if(!empty(\$search) && !empty(\$this->searchable)){
            \$like = [];
            foreach(\$this->searchable as \$field){
                \$like[] = \"{\$this->table}.\$field LIKE :search\";
            }
            \$where .= \" AND (\".implode(' OR ',\$like).\")\";
            \$params['search'] = \"%{\$search}%\";
        }

        // validasi sort
        \$allowedSort = \$this->searchable;
        \$allowedSort[] = \$this->pk;

        if(!\$sort || !in_array(\$sort,\$allowedSort)){
            \$sort = \$this->pk;
        }

        \$order = strtolower(\$order) === 'desc' ? 'DESC' : 'ASC';

        \$sql = \"SELECT * FROM {\$this->table} \".\$where.\"
                ORDER BY {\$sort} {\$order}
                LIMIT :limit OFFSET :offset\";

        \$stmt = \$this->pdo->prepare(\$sql);

        foreach(\$params as \$k=>\$v){
            \$stmt->bindValue(':'.\$k,\$v);
        }

        \$stmt->bindValue(':limit',(int)\$limit,PDO::PARAM_INT);
        \$stmt->bindValue(':offset',(int)\$offset,PDO::PARAM_INT);

        \$stmt->execute();
        return \$stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(\$search=''){
        \$where = ' WHERE 1=1 ';
        \$params = [];

        if(!empty(\$search) && !empty(\$this->searchable)){
            \$like = [];
            foreach(\$this->searchable as \$field){
                \$like[] = \"{\$this->table}.\$field LIKE :search\";
            }
            \$where .= \" AND (\".implode(' OR ',\$like).\")\";
            \$params['search'] = \"%{\$search}%\";
        }

        \$sql = \"SELECT COUNT(*) FROM {\$this->table} \".\$where;
        \$stmt = \$this->pdo->prepare(\$sql);
        foreach(\$params as \$k=>\$v){
            \$stmt->bindValue(':'.\$k,\$v);
        }
        \$stmt->execute();
        return \$stmt->fetchColumn();
    }

    public function find(\$id){
        \$stmt = \$this->pdo->prepare(
            \"SELECT * FROM {\$this->table} WHERE {\$this->pk} = :id\"
        );
        \$stmt->execute(['id'=>\$id]);
        return \$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert(\$data){
        if(empty(\$data)) return false;

        \$fields = array_keys(\$data);
        \$placeholders = array_map(fn(\$f)=>\":\$f\",\$fields);

        \$sql = \"INSERT INTO {\$this->table} (\".implode(',',\$fields).\")
                VALUES (\".implode(',',\$placeholders).\")\";

        \$stmt = \$this->pdo->prepare(\$sql);
        if(!\$stmt->execute(\$data)){
            return false;
        }

        return \$this->pdo->lastInsertId();
    }

    public function update(\$id,\$data){
        if(!\$id || empty(\$data)) return false;

        \$sets = [];
        foreach(\$data as \$k=>\$v){
            \$sets[] = \"\$k=:\$k\";
        }

        \$sql = \"UPDATE {\$this->table}
                SET \".implode(',',\$sets).\"
                WHERE {\$this->pk}=:pk_id\";

        \$stmt = \$this->pdo->prepare(\$sql);
        \$data['pk_id'] = \$id;

        if(!\$stmt->execute(\$data)){
            return false;
        }

        return \$stmt->rowCount();
    }

    public function delete(\$id){
        \$stmt = \$this->pdo->prepare(
            \"DELETE FROM {\$this->table} WHERE {\$this->pk}=:id\"
        );
        return \$stmt->execute(['id'=>\$id]);
    }
}
";

        file_put_contents($file, $code);
    }


    private function generateController($controller, $model, $view, $table, $pk)
    {
        $file = BASE_PATH . "/controllers/" . $controller . "Controller.php";

        $code = "<?php
require_once BASE_PATH.'/models/{$model}Model.php';
require_once BASE_PATH.'/controllers/BaseController.php';

class {$controller}Controller extends BaseController {

    private \$model;

    public function __construct(){
        global \$pdo;
        \$this->model = new {$model}Model(\$pdo);
    }

    public function index(){
        \$search = \$_GET['search'] ?? '';
        \$page   = (int)(\$_GET['page'] ?? 1);
        \$limit  = 10;

        \$sort  = \$_GET['sort'] ?? null;
        \$order = \$_GET['order'] ?? 'asc';

        \$data  = \$this->model->paginate(\$search,\$page,\$limit,\$sort,\$order);
        \$total = \$this->model->count(\$search);

        if(isset(\$_GET['ajax'])){
            header('Content-Type: application/json');
            echo json_encode([
                'data'=>\$data,
                'page'=>\$page,
                'limit'=>\$limit,
                'sort'=>\$sort,
                'order'=>\$order,
                'totalPages'=>ceil(\$total/\$limit)
            ]);
            exit;
        }

        \$this->render('{$view}/index',[], 'main');
    }

    public function create(){
        \$this->render('{$view}/form',[],null);
    }

    public function edit(){
        \$id = \$_GET['id'] ?? null;
        if(!\$id){
            http_response_code(400); exit;
        }

        \$data = \$this->model->find(\$id);
        if(!\$data){
            http_response_code(404); exit;
        }

        \$this->render('{$view}/form',['data'=>\$data],null);
    }

    public function store(){
        header('Content-Type: application/json');

        try{
            \$data = \$_POST;
            unset(\$data['{$pk}']);

            \$insertId = \$this->model->insert(\$data);

            echo json_encode([
                'success'=>\$insertId ? true:false,
                'insert_id'=>\$insertId
            ]);
        }catch(Throwable \$e){
            echo json_encode([
                'success'=>false,
                'message'=>\$e->getMessage()
            ]);
        }
        exit;
    }

    public function update(){
        header('Content-Type: application/json');

        try{
            \$id = \$_POST['{$pk}'] ?? null;
            if(!\$id){
                echo json_encode(['success'=>false,'message'=>'ID kosong']);
                exit;
            }

            \$data = \$_POST;
            unset(\$data['{$pk}']);

            \$updated = \$this->model->update(\$id,\$data);

            echo json_encode([
                'success'=>\$updated!==false
            ]);
        }catch(Throwable \$e){
            echo json_encode([
                'success'=>false,
                'message'=>\$e->getMessage()
            ]);
        }
        exit;
    }

    public function delete(){
        header('Content-Type: application/json');

        try{
            \$id = \$_POST['id'] ?? null;
            \$success = \$this->model->delete(\$id);

            echo json_encode(['success'=>\$success]);
        }catch(Throwable \$e){
            echo json_encode([
                'success'=>false,
                'message'=>\$e->getMessage()
            ]);
        }
        exit;
    }
}
";

        file_put_contents($file, $code);
    }

    private function generateViews($view, $table, $pk, $columns, $relations)
    {
        $folder = BASE_PATH . "/views/" . $view;
        if (!is_dir($folder)) {
            mkdir($folder, 0775, true);
        }

        /* ================= PRE-PROCESSING DATA ================= */
        $thead = "";
        $rowColumns = "";
        foreach ($columns as $col) {
            $colName = $col['COLUMN_NAME'];
            // Mengubah snake_case ke Label (contoh: user_name -> User Name)
            $label = ucwords(str_replace('_', ' ', $colName));
            $thead .= "<th data-sort='{$col['COLUMN_NAME']}' onclick=\"sortTable('{$col['COLUMN_NAME']}')\" style='cursor:pointer'>
            {$label} <span class='sort-icon'></span>
           </th>\n";
            $rowColumns .= "        html += `<td>\${row.{$colName} ?? ''}</td>`;\n";
        }
        $colspan = count($columns) + 2;

        /* ================= INDEX TEMPLATE (JS & HTML) ================= */
        $index = <<<'HTML'
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div>
                    <h4 class="fw-bold mb-0">Data {{label_table}}</h4>
                    <small class="text-muted">Kelola data melalui tabel di bawah ini</small>
                </div>
                <button onclick="openForm()" class="btn btn-primary shadow-sm">
                    <i class="bi bi-plus-lg"></i> Tambah Data
                </button>
            </div>

            <div class="card-body border-bottom">
                <div class="row">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text  border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0" 
                                placeholder="Cari data..." onkeyup="debounceLoad()">
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table text-secondary">
                        <tr>
                            <th width="60">#</th>
                            {{thead}}
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dataTable">
                        <tr><td colspan="{{colspan}}" class="text-center py-5">Memuat data...</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <ul class="list-unstyled d-flex align-items-center justify-content-end gap-2 mb-0 pagination-common-style"
                    id="paginationArea">
                </ul>
            </div>
        </div>

        <div class="modal fade" id="dataModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom-0">
                        <h5 class="modal-title fw-bold" id="modalTitle">Form Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="modalBody"></div>
                </div>
            </div>
        </div>

        <script>
        let currentPage = 1;
        let debounceTimer;


        let sortField = null;
        let sortOrder = 'asc';

        function debounceLoad(){
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => loadData(1), 400);
        }

        function loadData(page = 1){
            currentPage = page;
            let search = document.getElementById('searchInput').value;
            let tableBody = document.getElementById('dataTable');

            fetch(`?action={{view}}.index&ajax=1&page=${page}&search=${encodeURIComponent(search)}&sort=${sortField ?? ''}&order=${sortOrder}`)                .then(res => res.json())
                .then(res => {
                    renderTable(res.data || [], res.page, res.limit);
                    renderPagination(res.totalPages, res.page);
                    updateSortIcons();
                })
                .catch(err => {
                    console.error(err);
                    tableBody.innerHTML = `<tr><td colspan="{{colspan}}" class="text-center text-danger">Gagal memuat data</td></tr>`;
                });
        }

        function sortTable(field){

            if(sortField === field){
                sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
            }else{
                sortField = field;
                sortOrder = 'asc';
            }

            loadData(1);
        }

        function updateSortIcons(){

            document.querySelectorAll('th[data-sort]').forEach(th => {

                const field = th.dataset.sort;
                const icon  = th.querySelector('.sort-icon');

                if(!icon) return;

                if(field === sortField){

                    icon.innerHTML = sortOrder === 'asc'
                        ? '<i class="bi bi-caret-up-fill ms-1"></i>'
                        : '<i class="bi bi-caret-down-fill ms-1"></i>';

                }else{
                    icon.innerHTML = '';
                }

            });

        }

        function renderTable(rows, page, limit){
            let html = '';
            let start = (page - 1) * limit;

            if(rows.length === 0){
                html = `<tr><td colspan="{{colspan}}" class="text-center py-5 text-muted small">Tidak ada data ditemukan</td></tr>`;
            } else {
                rows.forEach((row, i) => {
                    html += `<tr><td>${start + i + 1}</td>`;
                    {{row_columns}}
                    html += `
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button onclick="editData(${row.{{pk}}})" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></button>
                                <button onclick="deleteData(${row.{{pk}}})" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('dataTable').innerHTML = html;
        }

        function renderPagination(totalPages, currentPage) {

            const ul = document.querySelector('.pagination-common-style');

            if (!ul) return;

            ul.innerHTML = '';

            if (totalPages <= 1) return;

            const createLi = (content, page = null, isActive = false, isDisabled = false) => {

                const li = document.createElement('li');
                const a  = document.createElement('a');

                a.href = "#";
                a.innerHTML = content;

                if (isActive) a.classList.add('active');
                if (isDisabled) a.classList.add('disabled');

                if (page !== null && !isActive && !isDisabled) {
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        loadData(page);
                    });
                }

                li.appendChild(a);
                return li;
            };

            // 🔙 PREV
            ul.appendChild(
                createLi(
                    '<i class="bi bi-arrow-left"></i>',
                    currentPage - 1,
                    false,
                    currentPage === 1
                )
            );

            let start = Math.max(1, currentPage - 2);
            let end   = Math.min(totalPages, currentPage + 2);

            // gap awal
            if (start > 1) {

                ul.appendChild(createLi('1', 1));

                if (start > 2) {
                    ul.appendChild(createLi('<i class="bi bi-three-dots"></i>', null, false, true));
                }
            }

            // page numbers
            for (let i = start; i <= end; i++) {

                ul.appendChild(
                    createLi(i, i, i === currentPage)
                );
            }

            // gap akhir
            if (end < totalPages) {

                if (end < totalPages - 1) {
                    ul.appendChild(createLi('<i class="bi bi-three-dots"></i>', null, false, true));
                }

                ul.appendChild(createLi(totalPages, totalPages));
            }

            // 🔜 NEXT
            ul.appendChild(
                createLi(
                    '<i class="bi bi-arrow-right"></i>',
                    currentPage + 1,
                    false,
                    currentPage === totalPages
                )
            );
        }

        function openForm(){
            document.getElementById('modalTitle').innerText = 'Tambah {{label_table}}';

            fetch('?action={{view}}.create&ajax=1')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('modalBody').innerHTML = html;

                    bindFormSubmit(); // 🔥 penting

                    new bootstrap.Modal(document.getElementById('dataModal')).show();
                });
        }

        function editData(id){
            document.getElementById('modalTitle').innerText = 'Edit {{label_table}}';

            fetch(`?action={{view}}.edit&id=${id}&ajax=1`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('modalBody').innerHTML = html;

                    bindFormSubmit(); // 🔥 penting

                    new bootstrap.Modal(document.getElementById('dataModal')).show();
                });
        }

        function deleteData(id){
            if(!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;

            fetch(`?action={{view}}.delete&ajax=1`, {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: `id=${id}`
            })
            .then(res => res.json())
            .then(res => {
                if(res.success){
                    loadData(currentPage);
                } else {
                    alert(res.message || 'Gagal menghapus data');
                }
            });
        }

        /* ===============================
        AUTO BIND FORM SUBMIT (GENERATOR)
        ================================= */

        function bindFormSubmit(){
            const form = document.getElementById('dataForm');
            if(!form) return;

            form.addEventListener('submit', function(e){
                e.preventDefault();

                let formData = new FormData(form);
                let id = formData.get('{{pk}}');
                let action = id ? 'update' : 'store';

                console.log('save', action);

                fetch('?action={{view}}.' + action + '&ajax=1', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(res => {

                    if(res.success){

                        let modalEl = document.getElementById('dataModal');
                        let modal = bootstrap.Modal.getInstance(modalEl);
                        modal.hide();

                        loadData(id ? currentPage : 1);

                    } else {
                        alert(res.message || 'Terjadi kesalahan saat menyimpan');
                    }

                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal terhubung ke server');
                });
            });
        }

        document.addEventListener("DOMContentLoaded", () => loadData());
        </script>
        HTML;

        // Placeholder replacement for Index
        $index = str_replace(
            ['{{table}}', '{{label_table}}', '{{view}}', '{{thead}}', '{{pk}}', '{{colspan}}', '{{row_columns}}'],
            [$table, ucwords(str_replace('_', ' ', $table)), $view, $thead, $pk, $colspan, $rowColumns],
            $index
        );

        file_put_contents($folder . '/index.php', $index);

        /* ================= FORM FIELDS GENERATOR (SMART VERSION) ================= */

        $formFields = '';

        foreach ($columns as $col) {

            $name  = $col['COLUMN_NAME'];
            $type  = strtolower($col['DATA_TYPE']);
            $ctype = strtolower($col['COLUMN_TYPE']);
            $label = ucwords(str_replace('_', ' ', $name));

            // Skip PK & Auto Increment
            if ($name === $pk || strpos(($col['EXTRA'] ?? ''), 'auto_increment') !== false) continue;

            $required = ($col['IS_NULLABLE'] === 'NO') ? 'required' : '';
            $readonly = in_array($name, ['created_at', 'updated_at']) ? 'readonly' : '';
            $placeholder = "placeholder='Masukkan {$label}'";

            /* ================= TYPE MAPPING ================= */

            // ENUM
            if ($type === 'enum') {

                preg_match("/^enum\((.*)\)$/", $col['COLUMN_TYPE'], $matches);
                $options = explode(',', $matches[1] ?? "''");

                $input = "<select name='{$name}' class='form-select' {$required}>";
                $input .= "<option value=''>-- Pilih {$label} --</option>";

                foreach ($options as $opt) {
                    $v = trim($opt, "'");
                    $input .= "
                        <option value='{$v}' <?= (isset(\$data['{$name}']) && \$data['{$name}']=='{$v}')?'selected':'' ?>>
                            " . ucfirst($v) . "
                        </option>";
                }

                $input .= "</select>";
            }

            // BOOLEAN tinyint(1)
            elseif ($type === 'tinyint' && strpos($ctype, '(1)') !== false) {

                $input = "
                    <div class='form-check form-switch'>
                        <input type='hidden' name='{$name}' value='0'>
                        <input class='form-check-input' type='checkbox' value='1' 
                            name='{$name}'
                            <?= (!empty(\$data['{$name}']))?'checked':'' ?>>
                        <label class='form-check-label'>Aktif</label>
                    </div>";
            }

            // NUMBER TYPES
            elseif (in_array($type, ['int', 'bigint', 'smallint', 'mediumint'])) {

                $min = (strpos($ctype, 'unsigned') !== false) ? "min='0'" : "";
                $input = "<input type='number' {$min} name='{$name}' 
                    class='form-control'
                    value='<?= \$data['{$name}'] ?? '' ?>'
                    {$placeholder} {$required}>";
            }

            // DECIMAL / FLOAT
            elseif (in_array($type, ['decimal', 'float', 'double'])) {

                preg_match('/\((\d+),(\d+)\)/', $ctype, $m);
                $step = isset($m[2]) ? "step='0." . str_repeat('0', $m[2] - 1) . "1'" : "step='any'";

                $input = "<input type='number' {$step}
                    name='{$name}'
                    class='form-control'
                    value='<?= \$data['{$name}'] ?? '' ?>'
                    {$placeholder} {$required}>";
            }

            // TEXTAREA
            elseif (in_array($type, ['text', 'longtext', 'mediumtext'])) {

                $input = "<textarea name='{$name}'
                    class='form-control'
                    rows='4'
                    {$placeholder} {$required}><?= htmlspecialchars(\$data['{$name}'] ?? '') ?></textarea>";
            }

            // DATE
            elseif ($type === 'date') {

                $input = "<input type='date'
                    name='{$name}'
                    class='form-control'
                    value='<?= \$data['{$name}'] ?? '' ?>'
                    {$required}>";
            }

            // DATETIME
            elseif (in_array($type, ['datetime', 'timestamp'])) {

                $input = "<input type='datetime-local'
                    name='{$name}'
                    class='form-control'
                    value='<?= isset(\$data['{$name}']) ? date(\"Y-m-d\TH:i\", strtotime(\$data['{$name}'])) : '' ?>'
                    {$required}>";
            }

            // EMAIL AUTO DETECT
            elseif (strpos($name, 'email') !== false) {

                $input = "<input type='email'
                    name='{$name}'
                    class='form-control'
                    value='<?= \$data['{$name}'] ?? '' ?>'
                    {$placeholder} {$required}>";
            }

            // PASSWORD AUTO DETECT
            elseif (strpos($name, 'password') !== false) {

                $input = "<input type='password'
                    name='{$name}'
                    class='form-control'
                    {$placeholder} {$required}>";
            }

            // IMAGE / FILE AUTO DETECT
            elseif (strpos($name, 'image') !== false || strpos($name, 'photo') !== false || strpos($name, 'file') !== false) {

                $input = "
                    <input type='file' name='{$name}' class='form-control' {$required}>
                    <?php if(!empty(\$data['{$name}'])): ?>
                        <small class='text-muted d-block mt-2'>File saat ini: <?= \$data['{$name}'] ?></small>
                    <?php endif; ?>";
            }

            // DEFAULT TEXT
            else {

                $input = "<input type='text'
                    name='{$name}'
                    class='form-control'
                    value='<?= htmlspecialchars(\$data['{$name}'] ?? '') ?>'
                    {$placeholder} {$required} {$readonly}>";
            }

            $formFields .= "
                <div class='mb-3'>
                    <label class='form-label small fw-bold'>{$label}</label>
                    {$input}
                </div>";
        }

        /* ================= FORM TEMPLATE ================= */
        $formTemplate = <<<HTML
            <form id="dataForm" enctype="multipart/form-data">

                <?php if(isset(\$data['{$pk}'])): ?>
                    <input type="hidden" name="{$pk}" value="<?= \$data['{$pk}'] ?>">
                <?php endif; ?>

                {$formFields}

                <hr>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        Simpan
                    </button>
                </div>

            </form>
        HTML;
        file_put_contents("{$folder}/form.php", $formTemplate);
    }
}
