<?php
    function sanitize_input($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input);
        return $input;
    }

    function send_response($response) {
        echo json_encode($response);
        exit;
    }
?>