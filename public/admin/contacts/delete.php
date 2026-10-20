<?php

/*
 * XOÁ TIN NHẮN LIÊN HỆ — khu vực quản trị
 *
 * Chỉ nhận POST. Bảng contact_messages không có khóa ngoại nên xoá một dòng
 * không ảnh hưởng tới bảng nào khác.
 */

require_once '/var/www/src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/contacts/');
    exit;
}

$contactID = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($contactID <= 0) {
    header('Location: /admin/contacts/');
    exit;
}

$stmt = $conn->prepare('DELETE FROM contact_messages WHERE ContactID = ?');
$stmt->bind_param('i', $contactID);
$stmt->execute();
$stmt->close();

$conn->close();

header('Location: /admin/contacts/?msg=deleted');
exit;
