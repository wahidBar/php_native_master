<?php

class ApiAuthController
{
    public function login()
    {
        $json = json_decode(file_get_contents("php://input"), true);

        $email = $json['email'] ?? '';
        $password = $json['password'] ?? '';

        global $pdo;

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            json_response(["error" => "Invalid credentials"], 401);
        }

        if ($user['is_active'] == 0) {
            json_response(["error" => "Account inactive. Verify your email."], 403);
        }

        // Generate token
        $token = generate_api_token();

        // Save token to session (can be saved to DB if needed)
        $_SESSION['api_tokens'][$token] = [
            "id"        => $user['id'],
            "email"     => $user['email'],
            "role_id"   => $user['role_id'],
            "name"      => $user['name'],
        ];

        json_response([
            "message" => "Login success",
            "token"   => $token,
            "user"    => $_SESSION['api_tokens'][$token]
        ]);
    }


    public function logout()
    {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

        if ($token) {
            unset($_SESSION['api_tokens'][$token]);
        }

        json_response(["message" => "Logged out"]);
    }
}
