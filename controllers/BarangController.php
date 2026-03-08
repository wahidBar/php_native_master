<?php

require_once BASE_PATH . '/models/BarangModel.php';
require_once BASE_PATH . '/controllers/BaseController.php';

class BarangController extends BaseController
{
    public function index()
    {
        require_permission('barang.view');
        global $pdo;

        $limit  = 10;
        $page   = max(1, intval($_GET['page'] ?? 1));
        $search = trim($_GET['search'] ?? '');
        $tipe   = intval($_GET['tipe'] ?? 1);

        // 🔥 SORTING
        // $allowedSort  = ['kodebarang', 'namabarang', 'rak', 'idsatuan', 'isbrg_aktif'];
        $allowedSort  = ['kodebarang', 'isbrg_aktif'];
        $allowedOrder = ['ASC', 'DESC'];

        $sort  = $_GET['sort'] ?? 'kodebarang';
        $order = strtoupper($_GET['order'] ?? 'ASC');

        if (!in_array($sort, $allowedSort)) {
            $sort = 'kodebarang';
        }

        if (!in_array($order, $allowedOrder)) {
            $order = 'ASC';
        }

        $offset = ($page - 1) * $limit;

        $where  = ["tipe = ?"];
        $params = [$tipe];

        if ($search !== '') {
            $where[] = "(kodebarang LIKE ? OR namabarang LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereSQL = "WHERE " . implode(" AND ", $where);

        // COUNT
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM as_ms_barang $whereSQL");
        $stmt->execute($params);
        $totalRows  = $stmt->fetchColumn();
        $totalPages = max(1, ceil($totalRows / $limit));

        // DATA (🔥 dynamic order)
        $stmt = $pdo->prepare("
        SELECT idbarang, kodebarang, namabarang, level, rak, idsatuan, isbrg_aktif
        FROM as_ms_barang
        $whereSQL
        ORDER BY $sort $order
        LIMIT $limit OFFSET $offset
    ");
        $stmt->execute($params);
        $barang = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'data' => $barang,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'sort' => $sort,
                'order' => $order
            ]);
            exit;
        }

        $this->render('barang/index', compact(
            'barang',
            'totalPages',
            'page',
            'tipe',
            'sort',
            'order'
        ), 'main');
    }


    public function create()
    {
        require_permission('barang.create');
        global $pdo;

        $tipeList = [
            'TT' => 'Aset Tetap',
            'HP' => 'Aset Lancar',
            'JS' => 'Bahan dan Jasa',
            'KH' => 'Khusus'
        ];

        $tipe       = intval($_GET['tipe'] ?? 1);
        // default tipe kalau dari GET angka
        if (is_numeric($tipe)) {
            $tipe = 'TT'; // default aman
        }
        $parentKode = $_GET['parent'] ?? null;

        $nextKode = null;
        $barang   = []; // default kosong untuk form

        // Generate kode berikutnya jika parent diberikan
        if ($parentKode) {
            $stmt = $pdo->prepare("
            SELECT MAX(kodebarang) as maxkode
            FROM as_ms_barang
            WHERE kodebarang LIKE ?
        ");
            $stmt->execute([$parentKode . '%']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $last = $row['maxkode'] ?? null;

            if ($last) {
                $number   = substr($last, strlen($parentKode));
                $next     = intval($number) + 1;
                $nextKode = $parentKode . str_pad($next, 3, '0', STR_PAD_LEFT);
            } else {
                $nextKode = $parentKode . '001';
            }
        }

        // kode induk
        $kodeInduk = $pdo->query("
            SELECT idbarang, kodebarang, namabarang
            FROM as_ms_barang
            WHERE level = 1
            ORDER BY kodebarang ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Ambil data aktiva
        $aktiva = $pdo->query("
        SELECT id_aktiva, aktiva 
        FROM as_ms_aktiva 
        ORDER BY aktiva ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

        // Ambil data satuan
        $satuan = $pdo->query("
        SELECT idsatuan 
        FROM as_ms_satuan 
        ORDER BY idsatuan ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

        $mode = 'create';

        $this->render('barang/form', compact(
            'mode',
            'barang',
            'tipe',
            'tipeList',
            'nextKode',
            'aktiva',
            'satuan',
            'kodeInduk'
        ), 'main');
    }

    public function edit()
    {
        require_permission('barang.update');
        global $pdo;

        $id = intval($_GET['id'] ?? 0);

        $stmt = $pdo->prepare("SELECT * FROM as_ms_barang WHERE idbarang = ?");
        $stmt->execute([$id]);
        $barang = $stmt->fetch(PDO::FETCH_ASSOC);
        // dd('success', $barang);

        if (!$barang) {
            redirect('?action=barang.index');
        }
        // kode induk
        $kodeInduk = $pdo->query("
            SELECT idbarang, kodebarang, namabarang
            FROM as_ms_barang
            WHERE level = 1
            ORDER BY kodebarang ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Ambil data aktiva
        $aktiva = $pdo->query("
        SELECT id_aktiva, aktiva 
        FROM as_ms_aktiva 
        ORDER BY aktiva ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

        // Ambil data satuan
        $satuan = $pdo->query("
        SELECT idsatuan 
        FROM as_ms_satuan 
        ORDER BY idsatuan ASC
    ")->fetchAll(PDO::FETCH_ASSOC);

        $tipeList = [
            'TT' => 'Aset Tetap',
            'HP' => 'Aset Lancar',
            'JS' => 'Bahan dan Jasa',
            'KH' => 'Khusus'
        ];

        $mode = 'edit';
        $tipe = $barang['tipe'];
        $nextKode = null;
        $this->render('barang/form', compact(
            'mode',
            'barang',
            'tipe',
            'tipeList',
            'nextKode',
            'aktiva',
            'satuan',
            'kodeInduk'
        ), 'main');
    }

    public function save()
    {
        require_permission('barang.create');

        global $pdo;

        try {

            $pdo->beginTransaction();

            $id = $_POST['idbarang'] ?? null;

            // LEVEL
            $parent_id = $_POST['parent_id'] ?? null;

            // Default level
            $level = 1;

            if ($id) {
                $stmt = $pdo->prepare("
                    SELECT level
                    FROM as_ms_barang
                    WHERE idbarang=?
                ");

                $stmt->execute([$id]);

                $levelLama = $stmt->fetchColumn();
                if (empty($parent_id)) {

                    $level = $levelLama;
                } else {
                    $level = 2;
                }
            } else {

                if (!empty($parent_id)) {

                    $level = 2;
                } else {

                    $level = 1;
                }
            }

            // TIPE BARANG
            $tipebarang = $_POST['tipebarang'] ?? '';

            switch (strtolower($tipebarang)) {

                case 'aset tetap':
                    $tipebarang = 'TT';
                    break;

                case 'aset lancar':
                    $tipebarang = 'HP';
                    break;

                case 'bahan dan jasa':
                    $tipebarang = 'JS';
                    break;

                case 'khusus':
                    $tipebarang = 'KH';
                    break;
            }


            $mapTipe = [
                'TT' => 1,
                'HP' => 2,
                'JS' => 3,
                'KH' => 4
            ];

            $tipeInt = $mapTipe[$_POST['tipebarang']] ?? 0;

            $data = [
                $_POST['kodebarang'],
                $_POST['namabarang'],
                $tipebarang,

                $_POST['cboAktiva'] ?? null,

                $level,

                $_POST['rak'] ?? '',

                $_POST['metodedepresiasi'] ?? 'NN',
                $_POST['stddeppct'] ?? 0,
                $_POST['lifetime'] ?? 0,

                $_POST['akunaset'] ?? '',
                $_POST['akunpengadaan'] ?? '',
                $_POST['akunpemakaian'] ?? '',
                $_POST['akunhapus'] ?? '',

                $_POST['idsatuan'],

                $_SESSION['auth']['id'],

                $_SERVER['REMOTE_ADDR'],

                $tipeInt,

                isset($_POST['aktif']) ? 1 : 0,

                $_POST['dakspi'] ?? 0
            ];

            // INSERT
            if (!$id) {

                $sql = "INSERT INTO as_ms_barang
            (
                kodebarang,
                namabarang,
                tipebarang,
                aktiva_id,
                level,
                rak,
                metodedepresiasi,
                stddeppct,
                lifetime,
                akunaset,
                akunpengadaan,
                akunpemakaian,
                akunhapus,
                idsatuan,
                t_userid,
                t_ipaddress,
                tipe,
                isbrg_aktif,
                dakspi
            )
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

                $pdo->prepare($sql)->execute($data);
            }
            // UPDATE
            else {

                $sql = "UPDATE as_ms_barang SET

                kodebarang=?,
                namabarang=?,
                tipebarang=?,
                aktiva_id=?,
                level=?,
                rak=?,
                metodedepresiasi=?,
                stddeppct=?,
                lifetime=?,
                akunaset=?,
                akunpengadaan=?,
                akunpemakaian=?,
                akunhapus=?,
                idsatuan=?,
                t_userid=?,
                t_ipaddress=?,
                tipe=?,
                isbrg_aktif=?,
                dakspi=?

                WHERE idbarang=?";

                $data[] = $id;

                $pdo->prepare($sql)->execute($data);
            }
            
            $pdo->commit();

            flash("Barang berhasil save!", "success");
            redirect('?action=barang.index&tipe=' . $_POST['tipe']);
        }
        catch (Exception $e) {

            $pdo->rollBack();
            flash("Error: " . $e->getMessage(), "error");
            redirect_back();
        }
    }



    public function delete()
    {

        require_permission('barang.delete');

        global $pdo;

        try {

            $pdo->beginTransaction();

            $id = intval($_GET['id'] ?? 0);

            if ($id <= 0) {

                throw new Exception("ID tidak valid");
            }

            $stmt = $pdo->prepare("
                DELETE FROM as_ms_barang
                WHERE idbarang=?
            ");

            $stmt->execute([$id]);

            $pdo->commit();

            redirect('?action=barang.index');
        } catch (Exception $e) {

            $pdo->rollBack();
            flash("Gagal Hapus: " . $e->getMessage(), "error");
            redirect_back();
        }
    }

    public function show()
    {
        require_permission('barang.view');
        global $pdo;

        $id = intval($_GET['id'] ?? 0);

        if ($id <= 0) {
            redirect('?action=barang.index');
        }

        $stmt = $pdo->prepare("
        SELECT b.*, a.aktiva
        FROM as_ms_barang b
        LEFT JOIN as_ms_aktiva a ON b.aktiva_id = a.id_aktiva
        WHERE b.idbarang = ?
    ");
        $stmt->execute([$id]);

        $barang = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$barang) {
            redirect('?action=barang.index');
        }

        $this->render('barang/show', compact('barang'), 'main');
    }

    public function kodeIndukBaru()
    {
        global $pdo;

        $row = $pdo->query("
        SELECT MAX(kodebarang) as maxkode
        FROM as_ms_barang
        WHERE level = 1
    ")->fetch();

        $last = $row['maxkode'] ?? 'A';

        $next = chr(ord($last) + 1);

        echo $next;
    }

    public function kodeChildBaru()
    {
        global $pdo;

        $parent = $_GET['parent'] ?? '';

        $stmt = $pdo->prepare("
        SELECT MAX(kodebarang) as maxkode
        FROM as_ms_barang
        WHERE kodebarang LIKE ?
    ");

        $stmt->execute([$parent . '%']);

        $row = $stmt->fetch();

        $last = $row['maxkode'] ?? null;

        if ($last) {

            // ambil angka belakang
            $angka = preg_replace('/[^0-9]/', '', $last);

            $next = intval($angka) + 1;

            echo $parent .
                str_pad($next, 4, '0', STR_PAD_LEFT);
        } else {

            echo $parent . '001';
        }
    }
}
