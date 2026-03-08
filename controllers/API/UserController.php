<?php

class ApiUserController
{
    public function index()
    {
        require_api_token();

        global $pdo;

        $data = $pdo->query("SELECT id, name, email, role_id FROM users ORDER BY id DESC")
                    ->fetchAll(PDO::FETCH_ASSOC);

        json_response($data);
    }

    public function show()
    {
        require_api_token();

        $id = $_GET['id'] ?? null;

        global $pdo;
        $stmt = $pdo->prepare("SELECT id, name, email, role_id FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) json_response(["error" => "User not found"], 404);

        json_response($user);
    }

    public function store()
    {
        require_api_token();

        $json = json_decode(file_get_contents("php://input"), true);

        global $pdo;
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role_id)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $json['name'],
            $json['email'],
            password_hash($json['password'], PASSWORD_BCRYPT),
            $json['role_id']
        ]);

        json_response(["message" => "User created"]);
    }

    public function update()
    {
        require_api_token();

        $id   = $_GET['id'] ?? null;
        $json = json_decode(file_get_contents("php://input"), true);

        global $pdo;

        $stmt = $pdo->prepare("
            UPDATE users SET name=?, email=?, role_id=? WHERE id=?
        ");
        $stmt->execute([
            $json['name'],
            $json['email'],
            $json['role_id'],
            $id
        ]);

        json_response(["message" => "User updated"]);
    }

    public function delete()
    {
        require_api_token();

        $id = $_GET['id'] ?? null;

        global $pdo;
        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$id]);

        json_response(["message" => "User deleted"]);
    }
}
