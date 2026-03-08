<?php
require_once BASE_PATH.'/models/CategoriesModel.php';
require_once BASE_PATH.'/controllers/BaseController.php';

class CategoriesController extends BaseController {

    private $model;

    public function __construct(){
        global $pdo;
        $this->model = new CategoriesModel($pdo);
    }

    public function index(){
        $search = $_GET['search'] ?? '';
        $page   = (int)($_GET['page'] ?? 1);
        $limit  = 10;

        $sort  = $_GET['sort'] ?? null;
        $order = $_GET['order'] ?? 'asc';

        $data  = $this->model->paginate($search,$page,$limit,$sort,$order);
        $total = $this->model->count($search);

        if(isset($_GET['ajax'])){
            header('Content-Type: application/json');
            echo json_encode([
                'data'=>$data,
                'page'=>$page,
                'limit'=>$limit,
                'sort'=>$sort,
                'order'=>$order,
                'totalPages'=>ceil($total/$limit)
            ]);
            exit;
        }

        $this->render('categories/index',[], 'main');
    }

    public function create(){
        $this->render('categories/form',[],null);
    }

    public function edit(){
        $id = $_GET['id'] ?? null;
        if(!$id){
            http_response_code(400); exit;
        }

        $data = $this->model->find($id);
        if(!$data){
            http_response_code(404); exit;
        }

        $this->render('categories/form',['data'=>$data],null);
    }

    public function store(){
        header('Content-Type: application/json');

        try{
            $data = $_POST;
            unset($data['id']);

            $insertId = $this->model->insert($data);

            echo json_encode([
                'success'=>$insertId ? true:false,
                'insert_id'=>$insertId
            ]);
        }catch(Throwable $e){
            echo json_encode([
                'success'=>false,
                'message'=>$e->getMessage()
            ]);
        }
        exit;
    }

    public function update(){
        header('Content-Type: application/json');

        try{
            $id = $_POST['id'] ?? null;
            if(!$id){
                echo json_encode(['success'=>false,'message'=>'ID kosong']);
                exit;
            }

            $data = $_POST;
            unset($data['id']);

            $updated = $this->model->update($id,$data);

            echo json_encode([
                'success'=>$updated!==false
            ]);
        }catch(Throwable $e){
            echo json_encode([
                'success'=>false,
                'message'=>$e->getMessage()
            ]);
        }
        exit;
    }

    public function delete(){
        header('Content-Type: application/json');

        try{
            $id = $_POST['id'] ?? null;
            $success = $this->model->delete($id);

            echo json_encode(['success'=>$success]);
        }catch(Throwable $e){
            echo json_encode([
                'success'=>false,
                'message'=>$e->getMessage()
            ]);
        }
        exit;
    }
}
