<?php

/*
 * THÊM TIN TỨC — khu vực quản trị
 *
 * Quy trình:
 *   1. Nhân viên điền form và bấm Lưu (POST)
 *   2. Kiểm tra dữ liệu: tiêu đề, nội dung, danh mục, ngày đăng
 *   3. Nếu có chọn ảnh: kiểm tra định dạng và dung lượng rồi lưu vào
 *      public/uploads/news/ với tên ngẫu nhiên (tránh trùng tên)
 *   4. Ghi một dòng vào bảng news, sau đó quay về danh sách
 *
 * Ảnh KHÔNG bắt buộc: bài viết không có ảnh vẫn hợp lệ, trang Tin tức
 * sẽ hiển thị khung ảnh giữ chỗ.
 */

$pageTitle = 'Thêm tin tức';

require_once '/var/www/src/config/database.php';

/*
 * Đặt múi giờ trước khi dùng date() để ngày mặc định trong ô "Ngày đăng"
 * đúng giờ Việt Nam. header.php cũng đặt lại giá trị này nhưng header được
 * nạp SAU khi xử lý POST, nên phải đặt ở đây mới kịp.
 */
date_default_timezone_set('Asia/Ho_Chi_Minh');

/* Danh mục bài viết — cố định theo nghiệp vụ của cửa hàng */
$categoryOptions = ['Tin tức', 'Kinh nghiệm', 'Hướng dẫn'];

$error = '';

/* Giá trị mặc định của form khi mở trang lần đầu */
$form = [
    'title'        => '',
    'category'     => 'Tin tức',
    'author'       => 'Bida Hoàng Nhi',
    'summary'      => '',
    'content'      => '',
    'published_at' => date('Y-m-d\TH:i'),
    'is_published' => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Lấy dữ liệu từ form. trim() để bỏ khoảng trắng thừa hai đầu. */
    $form['title']    = trim($_POST['title'] ?? '');
    $form['category'] = trim($_POST['category'] ?? '');
    $form['author']   = trim($_POST['author'] ?? '');
    $form['summary']  = trim($_POST['summary'] ?? '');
    $form['content']  = trim($_POST['content'] ?? '');

    $form['is_published'] = isset($_POST['is_published']) ? 1 : 0;

    $publishedInput = trim($_POST['published_at'] ?? '');
    $form['published_at'] = $publishedInput;

    /*
     * Ô datetime-local gửi lên dạng "2026-10-20T14:30".
     * MySQL cần "2026-10-20 14:30:00" nên phải đổi dấu T thành khoảng trắng
     * và bổ sung phần giây.
     */
    $publishedAt = str_replace('T', ' ', $publishedInput);

    if (strlen($publishedAt) === 16) {
        $publishedAt .= ':00';
    }

    /* ---------- Kiểm tra dữ liệu ---------- */

    if ($form['title'] === '') {

        $error = 'Tiêu đề bài viết không được để trống.';

    } elseif (mb_strlen($form['title']) > 255) {

        $error = 'Tiêu đề không được dài quá 255 ký tự.';

    } elseif (!in_array($form['category'], $categoryOptions, true)) {

        $error = 'Danh mục không hợp lệ.';

    } elseif (mb_strlen($form['summary']) > 500) {

        $error = 'Phần tóm tắt không được dài quá 500 ký tự.';

    } elseif ($form['content'] === '') {

        $error = 'Nội dung bài viết không được để trống.';

    } elseif (
        DateTime::createFromFormat('Y-m-d H:i:s', $publishedAt) === false
    ) {

        $error = 'Ngày đăng không hợp lệ.';

    } else {

        /*
         * ---------- Xử lý ảnh đại diện (không bắt buộc) ----------
         *
         * UPLOAD_ERR_NO_FILE nghĩa là người dùng không chọn ảnh — bỏ qua,
         * coi như bài viết không có ảnh.
         */
        $imageName = null;
        $file = $_FILES['news_image'] ?? null;

        if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {

            if ($file['error'] !== UPLOAD_ERR_OK) {

                $error = 'Tải ảnh lên không thành công, vui lòng thử lại.';

            } elseif ($file['size'] > 2 * 1024 * 1024) {

                $error = 'Ảnh không được vượt quá 2 MB.';

            } else {

                /*
                 * Không tin vào phần mở rộng của tên file (người dùng có thể
                 * đổi tên tuỳ ý), mà đọc loại nội dung thật của file.
                 */
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mimeType = $finfo->file($file['tmp_name']);

                $extensionMap = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                ];

                if (!isset($extensionMap[$mimeType])) {

                    $error = 'Chỉ cho phép ảnh JPG, PNG hoặc WebP.';

                } else {

                    $imageName =
                        'news-'
                        . bin2hex(random_bytes(8))
                        . '.'
                        . $extensionMap[$mimeType];

                    $targetPath = '/var/www/html/uploads/news/' . $imageName;

                    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {

                        $error = 'Không lưu được ảnh lên máy chủ.';
                        $imageName = null;
                    }
                }
            }
        }

        /* ---------- Ghi vào cơ sở dữ liệu ---------- */

        if ($error === '') {

            $sql = '
                INSERT INTO news
                (
                    Title,
                    Summary,
                    Content,
                    ImageFile,
                    Category,
                    Author,
                    IsPublished,
                    PublishedAt
                )
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?)
            ';

            $stmt = $conn->prepare($sql);

            /* 6 chuỗi, 1 số nguyên, 1 chuỗi ngày */
            $stmt->bind_param(
                'ssssssis',
                $form['title'],
                $form['summary'],
                $form['content'],
                $imageName,
                $form['category'],
                $form['author'],
                $form['is_published'],
                $publishedAt
            );

            if ($stmt->execute()) {

                $stmt->close();
                $conn->close();

                header('Location: /admin/news/?msg=created');
                exit;
            }

            $error = 'Không thể thêm bài viết.';
            $stmt->close();

            /*
             * Ghi cơ sở dữ liệu thất bại thì phải xoá ảnh vừa lưu,
             * nếu không thư mục uploads sẽ đầy file rác không ai dùng.
             */
            if ($imageName !== null) {
                @unlink('/var/www/html/uploads/news/' . $imageName);
            }
        }
    }
}

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4">

    <h2 class="mb-4">Thêm tin tức</h2>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- enctype bắt buộc phải có để trình duyệt gửi được file lên -->
    <form method="post" enctype="multipart/form-data">

        <div class="mb-3">
            <label for="title" class="form-label">
                Tiêu đề <span class="text-danger">*</span>
            </label>

            <input
                type="text"
                class="form-control"
                id="title"
                name="title"
                maxlength="255"
                value="<?= htmlspecialchars($form['title']) ?>"
                required
            >
        </div>

        <div class="row">

            <div class="col-md-4 mb-3">
                <label for="category" class="form-label">Danh mục</label>

                <select class="form-select" id="category" name="category">
                    <?php foreach ($categoryOptions as $option): ?>
                        <option
                            value="<?= htmlspecialchars($option) ?>"
                            <?= $form['category'] === $option ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($option) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label for="author" class="form-label">Tác giả</label>

                <input
                    type="text"
                    class="form-control"
                    id="author"
                    name="author"
                    maxlength="100"
                    value="<?= htmlspecialchars($form['author']) ?>"
                >
            </div>

            <div class="col-md-4 mb-3">
                <label for="publishedAt" class="form-label">Ngày đăng</label>

                <input
                    type="datetime-local"
                    class="form-control"
                    id="publishedAt"
                    name="published_at"
                    value="<?= htmlspecialchars($form['published_at']) ?>"
                    required
                >
            </div>

        </div>

        <div class="mb-3">
            <label for="summary" class="form-label">
                Tóm tắt
                <span class="text-muted small">(tối đa 500 ký tự, hiện ở thẻ bài viết)</span>
            </label>

            <textarea
                class="form-control"
                id="summary"
                name="summary"
                rows="3"
                maxlength="500"
            ><?= htmlspecialchars($form['summary']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">
                Nội dung <span class="text-danger">*</span>
            </label>

            <textarea
                class="form-control"
                id="content"
                name="content"
                rows="12"
                required
            ><?= htmlspecialchars($form['content']) ?></textarea>

            <div class="form-text">
                Xuống dòng trong ô này sẽ được giữ nguyên khi hiển thị ở trang chi tiết.
            </div>
        </div>

        <div class="mb-3">
            <label for="newsImage" class="form-label">
                Ảnh đại diện
                <span class="text-muted small">(không bắt buộc, tối đa 2 MB)</span>
            </label>

            <input
                type="file"
                class="form-control"
                id="newsImage"
                name="news_image"
                accept="image/jpeg,image/png,image/webp"
            >
        </div>

        <div class="form-check mb-4">
            <input
                class="form-check-input"
                type="checkbox"
                id="isPublished"
                name="is_published"
                value="1"
                <?= $form['is_published'] === 1 ? 'checked' : '' ?>
            >

            <label class="form-check-label" for="isPublished">
                Hiển thị bài viết ở trang Tin tức
            </label>
        </div>

        <button type="submit" class="btn btn-primary">Lưu</button>

        <a href="/admin/news/" class="btn btn-secondary">Hủy</a>

    </form>

</div>

<?php

require_once '/var/www/src/includes/admin/footer.php';
