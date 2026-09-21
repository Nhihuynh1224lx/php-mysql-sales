<?php

require_once '/var/www/src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/orders/');
    exit;
}

$orderID = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($orderID <= 0) {
    header('Location: /admin/orders/');
    exit;
}

/*
 * Các dòng trong orderdetail sẽ tự bị xóa theo
 * ràng buộc khóa ngoại ON DELETE CASCADE.
 */
$sql = "
    DELETE FROM orders
    WHERE OrderID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $orderID);

$stmt->execute();

$stmt->close();
$conn->close();

header('Location: /admin/orders/');
exit;
