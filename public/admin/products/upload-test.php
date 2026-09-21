<?php
$pageTitle = 'Kiểm tra Upload File';
require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

$message = '';
$messageType = '';

// 1. Chỉ xử lý khi người dùng submit form (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 2. Kiểm tra xem có file gửi lên và không dính lỗi upload cơ bản từ PHP
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {

        $file = $_FILES['product_image'];

        // 3. VALIDATE 1: Kiểm tra dung lượng (Tối đa 2 MB)
        $maxSize = 2 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $message = 'File ảnh không được vượt quá 2 MB.';
            $messageType = 'danger';
        } else {

            // 4. VALIDATE 2: Kiểm tra MIME type thực tế của file bằng finfo
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            $extensionMap = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            if (!isset($extensionMap[$mimeType])) {
                $message = 'Chỉ cho phép file JPG, JPEG, PNG hoặc WebP.';
                $messageType = 'danger';
            } else {

                // 5. Đổi tên file ngẫu nhiên an toàn
                $extension = $extensionMap[$mimeType];
                $newFileName = 'product-' . bin2hex(random_bytes(8)) . '.' . $extension;
                $destination = '/var/www/html/uploads/products/' . $newFileName;

                // 6. Sau khi TẤT CẢ kiểm tra đều hợp lệ mới tiến hành lưu file
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $message = 'Upload file thành công! Tên file mới: ' . htmlspecialchars($newFileName);
                    $messageType = 'success';
                } else {
                    $message = 'Không thể lưu file. Kiểm tra lại đường dẫn hoặc phân quyền thư mục.';
                    $messageType = 'danger';
                }
            }
        }
    } else {
        $message = 'Vui lòng chọn một file ảnh hợp lệ.';
        $messageType = 'warning';
    }
}
?>

<div class="container mt-4">

    <h2>Kiểm tra Upload File</h2>

    <!-- Hiển thị thông báo trạng thái nếu có -->
    <?php if ($message !== ''): ?>
        <div class="alert alert-<?= $messageType ?> mt-3">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">

        <div class="mb-3">
            <label for="productImage" class="form-label">
                Chọn ảnh
            </label>

            <input
                type="file"
                class="form-control"
                id="productImage"
                name="product_image"
                accept="image/jpeg,image/png,image/webp"
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Gửi file
        </button>
    </form>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>

        <hr>
        <h4>Dữ liệu nhận được trong $_FILES</h4>
        <pre><?php print_r($_FILES); ?></pre>

    <?php endif; ?>

</div>

<?php
require_once '/var/www/src/includes/admin/footer.php';
?>