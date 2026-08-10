<?php
session_start();

require_once 'connect.php';

header('Content-Type: application/json');

$currentBatch =
(int)(
$_GET['batch_id']
??
$_SESSION['current_batch']
??
0
);

$stmt=$pdo->prepare("
SELECT

e.id,
e.name

FROM employees e

INNER JOIN employee_batches eb

ON e.id=eb.employee_id

WHERE eb.batch_id=?

ORDER BY e.name
");

$stmt->execute([
$currentBatch
]);

echo json_encode(
$stmt->fetchAll(PDO::FETCH_ASSOC)
);