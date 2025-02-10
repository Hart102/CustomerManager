<?php
    header("Content-Type: application/json");
    require "config.php";
    require "utils.php";
    session_start();

    // CHECK IF USER IS LOGGED IN
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(["isError" => true, 'message' => 'Unauthorized access. please login and try again!.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['isError' => true, 'message' => 'Method not allowed']);
        exit;
    }

    $customerCV = $_FILES['customer_cv'] ?? null;
    $customerName = $_POST['customer_name'] ?? '';
    $customerPhone = $_POST['customer_phone'] ?? '';
    $customerEmail = filter_var($_POST['customer_email'] ?? '', FILTER_VALIDATE_EMAIL);

    if (empty($customerName) || empty($customerPhone) || empty($customerCV)) {
        http_response_code(400);
        echo json_encode(['isError' => true, 'message' => 'All fields are required.']);
        exit;
    }

    if (!$customerEmail) {
        http_response_code(400);
        echo json_encode(['isError' => true, 'message' => 'Invalid email address.']);
        exit;
    }

    // SAVE CV
    $uploadDir = 'uploads/';
    $uniqueFileName = $uploadDir . uniqid() . '_' . basename($customerCV['name']);

    if (!move_uploaded_file($customerCV['tmp_name'], $uniqueFileName)) {
        http_response_code(500);
        echo json_encode(['isError' => true, 'message' => 'Failed to upload CV.']);
        exit;
    }

    // CHECK IF CUSTOMER EMAIL ALREADY EXISTS
    $checkEmailSql = 'SELECT COUNT(*) FROM customers WHERE customer_email = :email';
    $checkEmailStmt = $pdo->prepare($checkEmailSql);
    $checkEmailStmt->bindParam(':email', $customerEmail);
    $checkEmailStmt->execute();
    $emailExists = $checkEmailStmt->fetchColumn();

    if ($emailExists > 0) {
        unlink($uniqueFileName);
        http_response_code(409);
        echo json_encode(['isError' => true, 'message' => 'Email already exists.']);
        exit;
    }

    $sql = 'INSERT INTO customers (customer_name, customer_email, customer_phone, customer_cv) VALUES (:name, :email, :phone, :customer_cv)';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':name', $customerName);
    $stmt->bindParam(':email', $customerEmail);
    $stmt->bindParam(':phone', $customerPhone);
    $stmt->bindParam(':customer_cv', $uniqueFileName);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['isError' => false, 'message' => 'Customer created successfully.']);
    } else {
        unlink($uniqueFileName);
        http_response_code(500);
        echo json_encode(['isError' => true, 'message' => 'Failed to create customer.']);
    }
?>
