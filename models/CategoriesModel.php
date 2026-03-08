<?php
class CategoriesModel {

    private $pdo;
    private $table = 'as_kategori_po';
    private $pk = 'id';
    private $searchable = array (
  0 => 'kategori',
);

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    public function paginate($search='', $page=1, $limit=10, $sort=null, $order='asc'){
        $offset = ($page-1)*$limit;
        $where = ' WHERE 1=1 ';
        $params = [];

        if(!empty($search) && !empty($this->searchable)){
            $like = [];
            foreach($this->searchable as $field){
                $like[] = "{$this->table}.$field LIKE :search";
            }
            $where .= " AND (".implode(' OR ',$like).")";
            $params['search'] = "%{$search}%";
        }

        // validasi sort
        $allowedSort = $this->searchable;
        $allowedSort[] = $this->pk;

        if(!$sort || !in_array($sort,$allowedSort)){
            $sort = $this->pk;
        }

        $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC';

        $sql = "SELECT * FROM {$this->table} ".$where."
                ORDER BY {$sort} {$order}
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);

        foreach($params as $k=>$v){
            $stmt->bindValue(':'.$k,$v);
        }

        $stmt->bindValue(':limit',(int)$limit,PDO::PARAM_INT);
        $stmt->bindValue(':offset',(int)$offset,PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count($search=''){
        $where = ' WHERE 1=1 ';
        $params = [];

        if(!empty($search) && !empty($this->searchable)){
            $like = [];
            foreach($this->searchable as $field){
                $like[] = "{$this->table}.$field LIKE :search";
            }
            $where .= " AND (".implode(' OR ',$like).")";
            $params['search'] = "%{$search}%";
        }

        $sql = "SELECT COUNT(*) FROM {$this->table} ".$where;
        $stmt = $this->pdo->prepare($sql);
        foreach($params as $k=>$v){
            $stmt->bindValue(':'.$k,$v);
        }
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function find($id){
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$this->table} WHERE {$this->pk} = :id"
        );
        $stmt->execute(['id'=>$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($data){
        if(empty($data)) return false;

        $fields = array_keys($data);
        $placeholders = array_map(fn($f)=>":$f",$fields);

        $sql = "INSERT INTO {$this->table} (".implode(',',$fields).")
                VALUES (".implode(',',$placeholders).")";

        $stmt = $this->pdo->prepare($sql);
        if(!$stmt->execute($data)){
            return false;
        }

        return $this->pdo->lastInsertId();
    }

    public function update($id,$data){
        if(!$id || empty($data)) return false;

        $sets = [];
        foreach($data as $k=>$v){
            $sets[] = "$k=:$k";
        }

        $sql = "UPDATE {$this->table}
                SET ".implode(',',$sets)."
                WHERE {$this->pk}=:pk_id";

        $stmt = $this->pdo->prepare($sql);
        $data['pk_id'] = $id;

        if(!$stmt->execute($data)){
            return false;
        }

        return $stmt->rowCount();
    }

    public function delete($id){
        $stmt = $this->pdo->prepare(
            "DELETE FROM {$this->table} WHERE {$this->pk}=:id"
        );
        return $stmt->execute(['id'=>$id]);
    }
}
