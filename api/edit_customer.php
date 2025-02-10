<?php
    header("Content-Type: application/json");
    require "config.php";
    require "utils.php";
    session_start();

    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(["isError" => true, 'message' => 'Unauthorized access. please login and try again!.']);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $customer_id = $_GET['id'] ?? null;
        $newFile = $_FILES['customer_cv'] ?? null;

        $data = [
            "customer_name" => $_POST['customer_name'] ?? null,
            "customer_email" => $_POST['customer_email'] ?? null,
            "customer_phone" => $_POST['customer_phone'] ?? null,
        ];

        $customerName = sanitize_input($data["customer_name"] ?? '');
        $customerPhone = sanitize_input($data["customer_phone"] ?? '');
        $customerEmail = filter_var($data["customer_email"] ?? '', FILTER_VALIDATE_EMAIL);
    
        if (!$customer_id) {
            http_response_code(400);
            echo json_encode(['isError' => true, 'message' => 'Customer ID is required']);
            exit;
        }
    
        if (!$customerName || !$customerPhone) {
            http_response_code(400);
            echo json_encode(['isError' => true, 'message' => 'All fields are required']);
            exit;
        }

        if (!$customerEmail) {
            http_response_code(400);
            echo json_encode(['isError' => true, 'message' => 'Invalid email address']);
            exit;
        }
    
        $sql = 'SELECT * FROM customers WHERE id = :customer_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['customer_id' => $customer_id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$customer) {
            echo json_encode(['isError' => true, 'message' => "Customer record not found!"]);
            exit;
        }
    
        if ($newFile["name"] !== '') {
            if (file_exists($customer["customer_cv"])) {
                unlink($customer["customer_cv"]);
            }

            $uplaodDir = 'uploads/';
            $uniqueFileName = $uplaodDir . uniqid() . '_' . basename($newFile['name']);
        
            if ($newFile['size'] > 1000000) {
                http_response_code(400);
                echo json_encode(['isError' => true, 'message' => 'File size is too large. Maximum file size is 1MB']);
                exit;
            }
        
            if (!move_uploaded_file($newFile['tmp_name'], $uniqueFileName)) {
                http_response_code(500);
                echo json_encode(['isError' => true, 'message' => 'Failed to upload CV']);
                exit;
            }

            // Update with CV
            try {
                $sql = 'UPDATE customers SET customer_name = :customer_name, customer_email = :customer_email, customer_phone = :customer_phone, customer_cv = :customer_cv WHERE id = :customer_id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'customer_phone' => $customerPhone,
                    'customer_cv' => $uniqueFileName,
                    'customer_id' => $customer_id
                ]);
        
                http_response_code(200);
                echo json_encode(['isError' => false, 'message' => 'Customer record updated successfully', "data" => "with CV"]);
            } catch (Throwable $e) {
                http_response_code(500);
                echo json_encode(['isError' => true, 'message' => 'Failed to update customer record', 'data' => $e->getMessage()]);
            }

        }else{
            // Update without CV
            try {
                $sql = 'UPDATE customers SET customer_name = :customer_name, customer_email = :customer_email, customer_phone = :customer_phone WHERE id = :customer_id';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'customer_name' => $customerName,
                    'customer_email' => $customerEmail,
                    'customer_phone' => $customerPhone,
                    'customer_id' => $customer_id
                ]);
        
                http_response_code(200);
                echo json_encode(['isError' => false, 'message' => 'Customer record updated successfully', "data" => "without CV"]);
            } catch (Throwable $e) {
                http_response_code(500);
                echo json_encode(['isError' => true, 'message' => 'Failed to update customer record', 'data' => $e->getMessage()]);
            }
        }
    }

?>
