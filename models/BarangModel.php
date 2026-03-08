<?php

class BarangModel
{
    protected $pdo;
    protected $table = 'as_ms_barang';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function paginate($search = null, $tipe = null, $limit = 10, $offset = 0)
    {
        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(kodebarang LIKE :search OR namabarang LIKE :search)";
            $params['search'] = "%$search%";
        }

        if ($tipe !== null && $tipe !== '') {
            $where[] = "tipe = :tipe";
            $params['tipe'] = $tipe;
        }

        $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT * FROM {$this->table}
                $whereSql
                ORDER BY kodebarang
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }

        $stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", (int)$offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count($search = null, $tipe = null)
    {
        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(kodebarang LIKE :search OR namabarang LIKE :search)";
            $params['search'] = "%$search%";
        }

        if ($tipe !== null && $tipe !== '') {
            $where[] = "tipe = :tipe";
            $params['tipe'] = $tipe;
        }

        $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM {$this->table} $whereSql");
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE idbarang = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = "INSERT INTO as_ms_barang
                (kodebarang, namabarang, tipebarang, level, rak,
                 idsatuan, tipe, isbrg_aktif, aktiva_id, dakspi,
                 t_userid, t_updatetime, t_ipaddress)
                VALUES
                (:kodebarang, :namabarang, :tipebarang, :level, :rak,
                 :idsatuan, :tipe, :aktif, :aktiva_id, :dakspi,
                 :user, NOW(), :ip)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE as_ms_barang SET
                kodebarang=:kodebarang,
                namabarang=:namabarang,
                tipebarang=:tipebarang,
                level=:level,
                rak=:rak,
                idsatuan=:idsatuan,
                tipe=:tipe,
                isbrg_aktif=:aktif,
                aktiva_id=:aktiva_id,
                dakspi=:dakspi,
                t_userid=:user,
                t_updatetime=NOW(),
                t_ipaddress=:ip
                WHERE idbarang=:id";

        $data['id'] = $id;

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM as_ms_barang WHERE idbarang = ?");
        return $stmt->execute([$id]);
    }

    // TREE MODEL
    public function treeByTipe($tipe)
    {
        $stmt = $this->pdo->prepare("
            SELECT idbarang, kodebarang, namabarang, level
            FROM as_ms_barang
            WHERE tipe = ?
            ORDER BY kodebarang
        ");
        $stmt->execute([$tipe]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
