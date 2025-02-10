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

    // GET ALL CUSTOMERS
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        try {
            $sql = 'SELECT * FROM customers';
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(['isError' => false, 'data' => $customers]);
        } catch (Throwable $th) {
            http_response_code(500);
            echo json_encode(['isError' => true, 'message' => 'Failed to fetch customers']);
            exit;
        }
    }


    //DELETE CUSTOMER
    if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['isError' => true, 'message' => 'Customer ID is required']);
            exit;
        }

        try {
            // Check if customer exists
            $sql = 'SELECT * FROM customers WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$customer) {
                http_response_code(404);
                echo json_encode(['isError' => true, 'message' => 'Customer not found']);
                exit;
            }

            // Delete customer
            $sql = 'DELETE FROM customers WHERE id = :id';
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $id]);

            unlink($customer["customer_cv"]);

            http_response_code(200);
            echo json_encode(['isError' => false, 'message' => 'Customer deleted successfully']);
        } catch (Throwable $th) {
            http_response_code(500);
            echo json_encode(['isError' => true, 'message' => 'Failed to delete customer']);
            exit;
        }
    }
?>