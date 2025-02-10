<?php
    header("Content-Type: application/json");
    require "config.php";
    require "utils.php";

    //GET CUSTOMER BY ID
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['isError' => true, 'message' => 'Customer ID is required']);
            exit;
        }

        try {
            $sql = 'SELECT * FROM customers WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$customer) {
                http_response_code(404);
                echo json_encode(['isError' => true, 'message' => 'Customer not found']);
                exit;
            }

            http_response_code(200);
            echo json_encode(['isError' => false, 'data' => $customer]);
        } catch (Throwable $th) {
            http_response_code(500);
            echo json_encode(['isError' => true, 'message' => 'Failed to fetch customer']);
            exit;
        }
    }
?>