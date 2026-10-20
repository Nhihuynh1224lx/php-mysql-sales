<?php

/*
 * XOÁ TIN TỨC — khu vực quản trị
 *
 * Chỉ nhận POST. Nếu ai gõ thẳng địa chỉ /admin/news/delete.php trên thanh
 * trình duyệt (GET) thì bị đẩy về danh sách, tránh việc xoá dữ liệu chỉ vì
 * một cú bấm nhầm hay một đường link lạ.
 *
 * Trước khi xoá dòng trong cơ sở dữ liệu, đọc lại tên file ảnh để xoá luôn
 * file trên đĩa — nếu không thư mục uploads sẽ còn ảnh của bài đã mất.
 */

require_once '/var/www/src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/news/');
    exit;
}

$newsID = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($newsID <= 0) {
    header('Location: /admin/news/');
    exit;
}

$uploadDir = '/var/www/html/uploads/news/';

/* Lấy tên ảnh trước khi xoá dòng */
$stmt = $conn->prepare('SELECT ImageFile FROM news WHERE NewsID = ?');
$stmt->bind_param('i', $newsID);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    $conn->close();

    header('Location: /admin/news/');
    exit;
}

/* Xoá dòng trong bảng news */
$stmt = $conn->prepare('DELETE FROM news WHERE NewsID = ?');
$stmt->bind_param('i', $newsID);
$stmt->execute();
$stmt->close();

$conn->close();

/* Xoá file ảnh đi kèm (nếu có) */
if (!empty($row['ImageFile']) && file_exists($uploadDir . $row['ImageFile'])) {
    @unlink($uploadDir . $row['ImageFile']);
}

header('Location: /admin/news/?msg=deleted');
exit;
