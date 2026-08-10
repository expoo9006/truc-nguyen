<?php

require 'connect.php';

header('Content-Type: application/json; charset=utf-8');

$batchId = $_GET['batch_id'] ?? 0;

try{

    if($batchId){

        $sql = "
            SELECT DISTINCT customer_name
            FROM sales
            WHERE batch_id = ?
              AND customer_name <> ''
              AND customer_name IS NOT NULL
            ORDER BY customer_name ASC
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([$batchId]);

    }else{

        $sql = "
            SELECT DISTINCT customer_name
            FROM sales
            WHERE customer_name <> ''
              AND customer_name IS NOT NULL
            ORDER BY customer_name ASC
        ";

        $stmt = $pdo->query($sql);

    }

    echo json_encode(
        $stmt->fetchAll(PDO::FETCH_ASSOC),
        JSON_UNESCAPED_UNICODE
    );

}catch(Throwable $e){

    http_response_code(500);

    echo json_encode([
        "error"=>$e->getMessage()
    ],JSON_UNESCAPED_UNICODE);

}