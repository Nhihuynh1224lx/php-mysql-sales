<?php

require_once '/var/www/src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /customers/');
    exit;
}

$customerID = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;

if ($customerID <= 0) {
    header('Location: /customers/');
    exit;
}

$sql = "
    DELETE FROM customers
    WHERE CustomerID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $customerID);

$stmt->execute();

$stmt->close();
$conn->close();

header('Location: /customers/');
exit;