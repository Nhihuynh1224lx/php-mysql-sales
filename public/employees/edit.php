<?php

$pageTitle = 'Sửa nhân viên';

require_once '/var/www/src/config/database.php';

$error = '';

$employeeID = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($employeeID <= 0) {
    die('Mã nhân viên không hợp lệ.');
}

/*
 * Đọc dữ liệu hiện tại của nhân viên
 */
$sql = "
    SELECT
        EmployeeID,
        LastName,
        FirstName,
        BirthDate,
        Photo,
        Notes
    FROM employees
    WHERE EmployeeID = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Không thể chuẩn bị câu lệnh truy vấn.');
}

$stmt->bind_param('i', $employeeID);
$stmt->execute();

$result = $stmt->get_result();
$employee = $result->fetch_assoc();

$stmt->close();

if (!$employee) {
    die('Không tìm thấy nhân viên.');
}

/*
 * Xử lý khi người dùng gửi form
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $lastName = trim($_POST['last_name'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $birthDate = trim($_POST['birth_date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    // Nếu ngày sinh rỗng, gán thành NULL để lưu vào DB (tránh lỗi 0000-00-00)
    if ($birthDate === '') {
        $birthDate = null;
    }

    if ($lastName === '' || $firstName === '') {

        $error = 'Họ và tên nhân viên không được để trống.';

    } else {
        
        // Mặc định giữ lại tên ảnh cũ
        $photoToSave = $employee['Photo'];

        // Xử lý Upload Ảnh Mới (Nếu có)
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            
            $file = $_FILES['photo'];
            $maxSize = 2 * 1024 * 1024; // 2MB

            if ($file['size'] > $maxSize) {
                $error = 'File ảnh không được vượt quá 2 MB.';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);

                $extensionMap = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp'
                ];

                if (!isset($extensionMap[$mimeType])) {
                    $error = 'Chỉ cho phép file JPG, PNG hoặc WebP.';
                } else {
                    $extension = $extensionMap[$mimeType];
                    $newFileName = 'emp-' . bin2hex(random_bytes(8)) . '.' . $extension;
                    
                    // ĐÃ SỬA: Đường dẫn lưu file mới
                    $destination = '/var/www/html/employees/upload/' . $newFileName;

                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        $photoToSave = $newFileName; // Cập nhật tên ảnh mới để lưu vào DB
                        
                        // Tùy chọn: Xóa ảnh cũ khỏi server để tiết kiệm dung lượng
                        if (!empty($employee['Photo'])) {
                            // ĐÃ SỬA: Đường dẫn trỏ tới file ảnh cũ để xóa
                            $oldFilePath = '/var/www/html/employees/upload/' . $employee['Photo'];
                            if (file_exists($oldFilePath)) {
                                unlink($oldFilePath);
                            }
                        }
                    } else {
                        $error = 'Không thể lưu file ảnh mới lên server.';
                    }
                }
            }
        }

        // Nếu không có lỗi trong quá trình upload, tiến hành cập nhật DB
        if ($error === '') {
            $sql = "
                UPDATE employees
                SET
                    LastName = ?,
                    FirstName = ?,
                    BirthDate = ?,
                    Photo = ?,
                    Notes = ?
                WHERE EmployeeID = ?
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $error = 'Không thể chuẩn bị câu lệnh cập nhật.';
            } else {
                $stmt->bind_param(
                    'sssssi',
                    $lastName,
                    $firstName,
                    $birthDate,
                    $photoToSave, // Tên ảnh (mới hoặc cũ)
                    $notes,
                    $employeeID
                );

                if ($stmt->execute()) {
                    header('Location: /employees/');
                    exit;
                } else {
                    $error = 'Không thể cập nhật nhân viên.';
                }

                $stmt->close();
            }
        }
    }
}

require_once '/var/www/src/includes/header.php';
require_once '/var/www/src/includes/navbar.php';

?>

<div class="container mt-4 mb-5">

    <h2 class="mb-4">Sửa nhân viên</h2>

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <!-- BẮT BUỘC: Thêm enctype="multipart/form-data" để upload file -->
    <form method="post" enctype="multipart/form-data">

        <!-- Mã nhân viên -->
        <div class="mb-3">
            <label class="form-label">Mã nhân viên</label>
            <input
                type="text"
                class="form-control"
                value="<?= htmlspecialchars($employee['EmployeeID']) ?>"
                disabled
            >
        </div>

        <div class="row">
            <!-- Họ -->
            <div class="col-md-6 mb-3">
                <label for="lastName" class="form-label">Họ</label>
                <input
                    type="text"
                    class="form-control"
                    id="lastName"
                    name="last_name"
                    maxlength="50"
                    value="<?= htmlspecialchars(
                        $_POST['last_name'] ?? $employee['LastName']
                    ) ?>"
                    required
                >
            </div>

            <!-- Tên -->
            <div class="col-md-6 mb-3">
                <label for="firstName" class="form-label">Tên</label>
                <input
                    type="text"
                    class="form-control"
                    id="firstName"
                    name="first_name"
                    maxlength="50"
                    value="<?= htmlspecialchars(
                        $_POST['first_name'] ?? $employee['FirstName']
                    ) ?>"
                    required
                >
            </div>
        </div>

        <!-- Ngày sinh -->
        <div class="mb-3">
            <label for="birthDate" class="form-label">Ngày sinh</label>
            <input
                type="date"
                class="form-control"
                id="birthDate"
                name="birth_date"
                value="<?= htmlspecialchars(
                    $_POST['birth_date'] ?? $employee['BirthDate'] ?? ''
                ) ?>"
            >
        </div>

        <!-- Ảnh đại diện -->
        <div class="mb-3">
            <label for="photo" class="form-label">Ảnh đại diện (Bỏ trống nếu không muốn đổi)</label>
            
            <div class="mb-2">
                <!-- Hiển thị ảnh hiện tại nếu có -->
                <?php if (!empty($employee['Photo'])): ?>
                    <!-- ĐÃ SỬA: Đường dẫn hiển thị ảnh -->
                    <img 
                        src="/employees/upload/<?= htmlspecialchars($employee['Photo']) ?>" 
                        alt="Current Photo" 
                        class="img-thumbnail" 
                        style="width: 150px; height: 150px; object-fit: cover;"
                    >
                <?php else: ?>
                    <span class="text-muted fst-italic">Chưa có ảnh</span>
                <?php endif; ?>
            </div>

            <input
                type="file"
                class="form-control"
                id="photo"
                name="photo"
                accept="image/jpeg,image/png,image/webp"
            >
        </div>

        <!-- Ghi chú -->
        <div class="mb-4">
            <label for="notes" class="form-label">Ghi chú</label>
            <textarea
                class="form-control"
                id="notes"
                name="notes"
                rows="4"
            ><?= htmlspecialchars(
                $_POST['notes'] ?? $employee['Notes'] ?? ''
            ) ?></textarea>
        </div>

        <!-- Nút -->
        <button type="submit" class="btn btn-warning">
            Cập nhật
        </button>

        <a href="/employees/" class="btn btn-secondary">
            Hủy
        </a>

    </form>

</div>

<?php

require_once '/var/www/src/includes/footer.php';

$conn->close();
?>