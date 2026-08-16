<?php

require_once '/var/www/src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /suppliers/');
    exit;
}

$supplierID = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;

if ($supplierID <= 0) {
    header('Location: /suppliers/');
    exit;
}

$sql = "
    DELETE FROM suppliers
    WHERE SupplierID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $supplierID);

$stmt->execute();

$stmt->close();
$conn->close();

header('Location: /suppliers/');
exit;