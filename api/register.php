<?php
    header("Content-Type: application/json");
    require "config.php";
    require "utils.php";

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

    $password = $data["password"] ?? '';
    $name = sanitize_input($data["name"] ?? '');
    $phone = sanitize_input($data["phone"] ?? '');
    $email = filter_var($data["email"] ?? '', FILTER_VALIDATE_EMAIL);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if (!$name || !$phone || !$password) {
        http_response_code(400);
        echo json_encode(["error" => "All fields are required"]);
        exit;
    }

    if (!$email) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid email address"]);
        exit;
    }

    if (strlen($password) < 4) {
        http_response_code(400);
        echo json_encode(["error" => "Password must be at least 4 characters long"]);
        exit;
    }

    //CHICK IF EMAIL IS ALREADY REGISTERED
    $query = "SELECT * FROM users WHERE email = :email";
    $statement = $pdo->prepare($query);
    $statement->execute(["email" => $email]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    try {
        if (!$user) {
            $query = "INSERT INTO users (name, email, phone, password) VALUES (:name, :email, :phone, :password)";
            $statement = $pdo->prepare($query);
            $statement->execute([
                "name" => $name,
                "email" => $email,
                "phone" => $phone,
                "password" => $passwordHash,
            ]);
        
            http_response_code(201);
            echo json_encode(["isError" => false, "message" => "User created successfully"]);
        } else {
            http_response_code(400);
            echo json_encode(["isError" => true, "message" => "Email already exist"]);
        }
    } catch (Throwable $th) {
        echo json_encode(["isError" => true, "message" => "Failed to create user: " . $th->getMessage()]);
    }
   
    // <!-- CREATE TABLE `userDB`.`users` (`id` INT NOT NULL AUTO_INCREMENT , `name` VARCHAR(100) NOT NULL , `email` VARCHAR(255) NOT NULL , `phone` VARCHAR(20) NOT NULL , `password` VARCHAR(100) NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB; -->
?>
