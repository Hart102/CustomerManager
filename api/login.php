<?php
    header("Content-Type: application/json");
    require "config.php";
    require "utils.php";
    session_start();

    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data)) {
        error_log("No input data received");
        http_response_code(400);
        echo json_encode(["error" => "No data provided"]);
        exit;
    }

    $email = filter_var($data["email"] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $data["password"] ?? '';

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(["isError" => true, "message" => "Email and password are required"]);
        exit;
    }

    $query = "SELECT * FROM users WHERE email = :email";
    $statement = $pdo->prepare($query);
    $statement->execute(["email" => $email]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["user_email"] = $user["email"];
        unset($user["password"]);
        http_response_code(200);
        echo json_encode(["isError" => false, "message" => "Login successful", "data" => $user]);
    } else {
        http_response_code(401);
        echo json_encode(["isError" => true, "message" => "Invalid email or password"]);
    }
?>
