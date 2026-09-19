<?php

require_once '/var/www/src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /employees/');
    exit;
}

$employeeID = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($employeeID <= 0) {
    header('Location: /employees/');
    exit;
}

$sql = "
    DELETE FROM employees
    WHERE EmployeeID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $employeeID);

$stmt->execute();

$stmt->close();
$conn->close();

header('Location: /employees/');
exit;
