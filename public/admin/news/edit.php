<?php

/*
 * SỬA TIN TỨC — khu vực quản trị
 *
 * Khác với trang thêm mới ở 3 điểm:
 *   1. Form được điền sẵn dữ liệu hiện tại của bài viết
 *   2. Ảnh đại diện có thể giữ nguyên, thay bằng ảnh mới, hoặc gỡ bỏ
 *   3. Sau khi lưu thì quay lại danh sách chứ không tạo thêm dòng mới
 *
 * Khi thay ảnh mới, file ảnh cũ bị xoá khỏi thư mục uploads để tránh
 * để lại file rác không còn dòng nào trong cơ sở dữ liệu trỏ tới.
 */

$pageTitle = 'Sửa tin tức';

require_once '/var/www/src/config/database.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');

$categoryOptions = ['Tin tức', 'Kinh nghiệm', 'Hướng dẫn'];

$uploadDir = '/var/www/html/uploads/news/';

$error = '';

$newsID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($newsID <= 0) {
    die('Mã bài viết không hợp lệ.');
}

/* ---------- Đọc bài viết hiện tại ---------- */

$sql = '
    SELECT
        NewsID,
        Title,
        Summary,
        Content,
        ImageFile,
        Category,
        Author,
        IsPublished,
        PublishedAt
    FROM news
    WHERE NewsID = ?
';

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $newsID);
$stmt->execute();

$article = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$article) {
    die('Không tìm thấy bài viết này.');
}

/*
 * Ô datetime-local cần dạng "2026-10-20T14:30", trong khi MySQL trả về
 * "2026-10-20 14:30:00". Cắt 16 ký tự đầu rồi đổi dấu cách thành T.
 */
$publishedForInput = substr($article['PublishedAt'], 0, 16);
$publishedForInput = str_replace(' ', 'T', $publishedForInput);

/* Giá trị hiển thị trên form, khởi tạo từ dữ liệu trong cơ sở dữ liệu */
$form = [
    'title'        => $article['Title'],
    'category'     => $article['Category'],
    'author'       => $article['Author'] ?? '',
    'summary'      => $article['Summary'] ?? '',
    'content'      => $article['Content'],
    'published_at' => $publishedForInput,
    'is_published' => (int) $article['IsPublished'],
];

/* Ảnh đang dùng, có thể bị thay hoặc gỡ trong lần lưu này */
$currentImage = $article['ImageFile'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $form['title']    = trim($_POST['title'] ?? '');
    $form['category'] = trim($_POST['category'] ?? '');
    $form['author']   = trim($_POST['author'] ?? '');
    $form['summary']  = trim($_POST['summary'] ?? '');
    $form['content']  = trim($_POST['content'] ?? '');

    $form['is_published'] = isset($_POST['is_published']) ? 1 : 0;

    $publishedInput = trim($_POST['published_at'] ?? '');
    $form['published_at'] = $publishedInput;

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

        /* ---------- Ảnh: thay mới, gỡ bỏ, hoặc giữ nguyên ---------- */

        $newImageName = null;   /* tên file mới, nếu có chọn ảnh */
        $removeImage = isset($_POST['remove_image']);

        $file = $_FILES['news_image'] ?? null;

        if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {

            if ($file['error'] !== UPLOAD_ERR_OK) {

                $error = 'Tải ảnh lên không thành công, vui lòng thử lại.';

            } elseif ($file['size'] > 2 * 1024 * 1024) {

                $error = 'Ảnh không được vượt quá 2 MB.';

            } else {

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

                    $newImageName =
                        'news-'
                        . bin2hex(random_bytes(8))
                        . '.'
                        . $extensionMap[$mimeType];

                    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $newImageName)) {

                        $error = 'Không lưu được ảnh lên máy chủ.';
                        $newImageName = null;
                    }
                }
            }
        }

        /* ---------- Cập nhật cơ sở dữ liệu ---------- */

        if ($error === '') {

            /*
             * Ảnh sau khi lưu:
             *   - có ảnh mới  -> dùng ảnh mới (ảnh cũ sẽ bị xoá bên dưới)
             *   - tick gỡ ảnh -> không còn ảnh
             *   - còn lại     -> giữ nguyên ảnh đang dùng
             */
            if ($newImageName !== null) {

                $imageToSave = $newImageName;

            } elseif ($removeImage) {

                $imageToSave = null;

            } else {

                $imageToSave = $currentImage;
            }

            $sql = '
                UPDATE news
                SET
                    Title = ?,
                    Summary = ?,
                    Content = ?,
                    ImageFile = ?,
                    Category = ?,
                    Author = ?,
                    IsPublished = ?,
                    PublishedAt = ?
                WHERE NewsID = ?
            ';

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                'ssssssisi',
                $form['title'],
                $form['summary'],
                $form['content'],
                $imageToSave,
                $form['category'],
                $form['author'],
                $form['is_published'],
                $publishedAt,
                $newsID
            );

            if ($stmt->execute()) {

                $stmt->close();

                /*
                 * Xoá file ảnh cũ khi nó đã bị thay hoặc bị gỡ, nhưng chỉ
                 * xoá sau khi cơ sở dữ liệu cập nhật thành công — nếu xoá
                 * trước mà lệnh UPDATE lỗi thì bài viết sẽ mất ảnh oan.
                 */
                if (
                    $currentImage !== null
                    && $currentImage !== ''
                    && $imageToSave !== $currentImage
                    && file_exists($uploadDir . $currentImage)
                ) {
                    @unlink($uploadDir . $currentImage);
                }

                $conn->close();

                header('Location: /admin/news/?msg=updated');
                exit;
            }

            $error = 'Không thể cập nhật bài viết.';
            $stmt->close();

            if ($newImageName !== null) {
                @unlink($uploadDir . $newImageName);
            }
        }
    }
}

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4">

    <h2 class="mb-4">Sửa tin tức</h2>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

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
                <span class="text-muted small">(tối đa 500 ký tự)</span>
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
        </div>

        <div class="mb-3">

            <label for="newsImage" class="form-label">
                Ảnh đại diện
                <span class="text-muted small">(không bắt buộc, tối đa 2 MB)</span>
            </label>

            <?php if (!empty($currentImage)): ?>

                <div class="d-flex align-items-center gap-3 mb-2">

                    <img
                        src="/uploads/news/<?= htmlspecialchars($currentImage) ?>"
                        alt="Ảnh hiện tại"
                        class="rounded border"
                        style="width: 160px; height: 100px; object-fit: cover;"
                    >

                    <div class="form-check">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="removeImage"
                            name="remove_image"
                            value="1"
                        >

                        <label class="form-check-label" for="removeImage">
                            Gỡ ảnh này
                        </label>
                    </div>

                </div>

            <?php else: ?>

                <p class="text-muted small mb-2">Bài viết chưa có ảnh đại diện.</p>

            <?php endif; ?>

            <input
                type="file"
                class="form-control"
                id="newsImage"
                name="news_image"
                accept="image/jpeg,image/png,image/webp"
            >

            <div class="form-text">
                Chọn ảnh mới nếu muốn thay ảnh hiện tại. Để trống thì giữ nguyên.
            </div>

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

        <button type="submit" class="btn btn-primary">Lưu thay đổi</button>

        <a href="/admin/news/" class="btn btn-secondary">Hủy</a>

        <a
            href="/tin-tuc-chi-tiet.php?id=<?= $newsID ?>"
            class="btn btn-outline-secondary"
            target="_blank"
            rel="noopener"
        >
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            Xem trên website
        </a>

    </form>

</div>

<?php

require_once '/var/www/src/includes/admin/footer.php';

$conn->close();
