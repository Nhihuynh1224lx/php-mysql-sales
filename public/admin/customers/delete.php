<?php

require_once '/var/www/src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/customers/');
    exit;
}

$customerID = isset($_POST['id'])
    ? (int) $_POST['id']
    : 0;

if ($customerID <= 0) {
    header('Location: /admin/customers/');
    exit;
}

/*
 * Kiểm tra khách hàng đã có đơn hàng hay chưa.
 * Nếu đã có đơn hàng thì không được xóa
 * vì orders.CustomerID đang tham chiếu đến customers.CustomerID.
 */
$checkSql = "
    SELECT COUNT(*)
    FROM orders
    WHERE CustomerID = ?
";

$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param('i', $customerID);
$checkStmt->execute();
$checkStmt->bind_result($orderCount);
$checkStmt->fetch();
$checkStmt->close();

if ($orderCount > 0) {
    $conn->close();

    echo "<script>
        alert('Không thể xóa khách hàng vì khách hàng này đã có đơn hàng.');
        window.location.href = '/admin/customers/';
    </script>";
    exit;
}

/*
 * Nếu khách hàng chưa có đơn hàng thì cho phép xóa.
 */
$sql = "
    DELETE FROM customers
    WHERE CustomerID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $customerID);

$stmt->execute();

$stmt->close();
$conn->close();

header('Location: /admin/customers/');
exit;