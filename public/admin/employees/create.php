<?php

$pageTitle = 'Thêm nhân viên';

require_once '/var/www/src/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $lastName = trim($_POST['last_name'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $birthDate = trim($_POST['birth_date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($lastName === '' || $firstName === '') {

        $error = 'Họ và tên nhân viên không được để trống.';

    } else {

        $photoToSave = null;
        $uploadedFilePath = null;

        // Xử lý Upload Ảnh nếu người dùng có chọn file
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            
            if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['photo'];
                $maxSize = 2 * 1024 * 1024; // 2MB

                if ($file['size'] > $maxSize) {
                    $error = 'File ảnh không được vượt quá 2 MB.';
                } else {
                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->file($file['tmp_name']);

                    $allowedTypes = [
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/webp' => 'webp'
                    ];

                    if (!isset($allowedTypes[$mimeType])) {
                        $error = 'Chỉ cho phép file JPG, PNG hoặc WebP.';
                    } else {
                        $extension = $allowedTypes[$mimeType];
                        $newFileName = 'emp-' . bin2hex(random_bytes(8)) . '.' . $extension;
                        
                        // Đường dẫn vật lý tới thư mục upload của module nhân viên
                        $destination = '/var/www/html/admin/employees/upload/' . $newFileName;

                        if (move_uploaded_file($file['tmp_name'], $destination)) {
                            $photoToSave = $newFileName;
                            $uploadedFilePath = $destination;
                        } else {
                            $error = 'Không thể lưu file ảnh lên server. Vui lòng kiểm tra thư mục có tồn tại hay không.';
                        }
                    }
                }
            } else {
                $error = 'Có lỗi xảy ra trong quá trình tải ảnh lên.';
            }
        }

        // Nếu việc kiểm tra thông tin và upload ảnh không có lỗi -> Tiến hành lưu DB
        if ($error === '') {
            
            // Xử lý ngày sinh rỗng thành NULL
            $birthDateValue = ($birthDate === '') ? null : $birthDate;
            $notesValue = ($notes === '') ? null : $notes;

            $sql = "
                INSERT INTO employees
                    (LastName, FirstName, BirthDate, Photo, Notes)
                VALUES (?, ?, ?, ?, ?)
            ";

            $stmt = $conn->prepare($sql);

            if (!$stmt) {

                $error = 'Không thể chuẩn bị câu lệnh thêm dữ liệu.';
                // Nếu DB lỗi mà ảnh đã lỡ lưu thành công thì xóa ảnh đó đi
                if ($uploadedFilePath && file_exists($uploadedFilePath)) {
                    unlink($uploadedFilePath);
                }

            } else {

                $stmt->bind_param(
                    'sssss',
                    $lastName,
                    $firstName,
                    $birthDateValue,
                    $photoToSave,
                    $notesValue
                );

                if ($stmt->execute()) {

                    header('Location: /admin/employees/');
                    exit;

                } else {

                    $error = 'Không thể thêm nhân viên.';
                    if ($uploadedFilePath && file_exists($uploadedFilePath)) {
                        unlink($uploadedFilePath);
                    }
                }

                $stmt->close();
            }
        }
    }
}

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4 mb-5">

    <h2 class="mb-4">Thêm nhân viên</h2>

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <!-- Bắt buộc thêm enctype="multipart/form-data" để gửi file -->
    <form method="post" enctype="multipart/form-data">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="lastName" class="form-label">
                    Họ
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="lastName"
                    name="last_name"
                    maxlength="50"
                    value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>"
                    required
                >
            </div>

            <div class="col-md-6 mb-3">
                <label for="firstName" class="form-label">
                    Tên
                </label>
                <input
                    type="text"
                    class="form-control"
                    id="firstName"
                    name="first_name"
                    maxlength="50"
                    value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                    required
                >
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="birthDate" class="form-label">
                    Ngày sinh
                </label>
                <input
                    type="date"
                    class="form-control"
                    id="birthDate"
                    name="birth_date"
                    value="<?= htmlspecialchars($_POST['birth_date'] ?? '') ?>"
                >
            </div>

            <div class="col-md-6 mb-3">
                <label for="photo" class="form-label">
                    Ảnh đại diện (Tùy chọn)
                </label>
                <input
                    type="file"
                    class="form-control"
                    id="photo"
                    name="photo"
                    accept="image/jpeg,image/png,image/webp"
                >
            </div>
        </div>

        <div class="mb-4">
            <label for="notes" class="form-label">
                Ghi chú
            </label>
            <textarea
                class="form-control"
                id="notes"
                name="notes"
                rows="4"
            ><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">
            Lưu
        </button>

        <a href="/admin/employees/" class="btn btn-secondary">
            Hủy
        </a>

    </form>

</div>

<?php

require_once '/var/www/src/includes/admin/footer.php';

$conn->close();
?>