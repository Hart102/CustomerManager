<?php
    // $host = '127.0.0.1';
    // $db = 'userDB';
    // $user = 'root';
    // $password = '';
    // $charset = 'utf8mb4';

    $host = $_ENV['DG_HOST'];
    $db = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASSWORD'];;
    $charset = 'utf8mb4';

    // $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $password, $options);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["isError" => true, "message" => "Database connection failed", "error" => $e->getMessage()]);
        exit;
    }
?>
