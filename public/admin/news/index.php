<?php

/*
 * DANH SÁCH TIN TỨC — khu vực quản trị
 *
 * Trang này làm 3 việc:
 *   1. Liệt kê toàn bộ bài viết, mới nhất lên trước
 *   2. Cho phép tìm theo tiêu đề (ô tìm kiếm ở góc phải)
 *   3. Chia trang, mỗi trang 10 bài
 *
 * Dữ liệu đọc từ bảng news. Bài viết đang ẩn (IsPublished = 0) vẫn hiện
 * ở đây để nhân viên bật lại được, nhưng trang Tin tức bên ngoài thì không.
 */

$pageTitle = 'Quản lý tin tức';

require_once '/var/www/src/config/database.php';
require_once '/var/www/src/includes/helpers.php';

/*
 * Thông báo sau khi chuyển trang.
 * Truyền qua địa chỉ dạng ?msg=created nên không cần session.
 */
$noticeMap = [
    'created' => 'Đã thêm bài viết mới.',
    'updated' => 'Đã cập nhật bài viết.',
    'deleted' => 'Đã xoá bài viết.',
];

$notice = $noticeMap[$_GET['msg'] ?? ''] ?? '';

/*
 * Từ khoá tìm kiếm và số trang hiện tại.
 */
$keyword = trim($_GET['q'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

/*
 * Điều kiện WHERE dùng chung cho cả câu đếm và câu lấy dữ liệu,
 * nhờ đó số trang luôn khớp với số dòng thật sự có.
 */
$where = '';
$params = [];
$types = '';

if ($keyword !== '') {
    $where = ' WHERE Title LIKE ? ';
    $params[] = '%' . $keyword . '%';
    $types = 's';
}

/*
 * Bước 1: đếm tổng số bài để biết chia bao nhiêu trang.
 */
$stmt = $conn->prepare('SELECT COUNT(*) AS total FROM news' . $where);

if ($types !== '') {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$total = (int) ($stmt->get_result()->fetch_assoc()['total'] ?? 0);
$stmt->close();

$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

/*
 * Bước 2: lấy đúng 10 bài của trang hiện tại.
 * Sắp xếp theo ngày đăng giảm dần; hai bài cùng ngày thì bài mới nhập
 * (NewsID lớn hơn) lên trước.
 */
$sql = '
    SELECT
        NewsID,
        Title,
        Category,
        Author,
        ImageFile,
        IsPublished,
        PublishedAt
    FROM news
' . $where . '
    ORDER BY PublishedAt DESC, NewsID DESC
    LIMIT ? OFFSET ?
';

$stmt = $conn->prepare($sql);

$values = array_merge($params, [$perPage, $offset]);
$stmt->bind_param($types . 'ii', ...$values);
$stmt->execute();

$newsList = $stmt->get_result();

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4">

    <?php if ($notice !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h2 class="mb-1">Quản lý tin tức</h2>
            <p class="text-muted mb-0">
                Tổng cộng <?= $total ?> bài viết
                <?php if ($keyword !== ''): ?>
                    — đang lọc theo từ khoá “<?= htmlspecialchars($keyword) ?>”
                <?php endif; ?>
            </p>
        </div>

        <a href="/admin/news/create.php" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i>
            Thêm tin tức
        </a>

    </div>

    <!-- Ô tìm kiếm: gửi bằng GET để địa chỉ giữ được từ khoá khi chuyển trang -->
    <form method="get" class="row g-2 mb-3">

        <div class="col-sm-6 col-md-4">
            <input
                type="text"
                class="form-control"
                name="q"
                value="<?= htmlspecialchars($keyword) ?>"
                placeholder="Tìm theo tiêu đề bài viết"
            >
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary">
                <i class="fa-solid fa-magnifying-glass"></i>
                Tìm
            </button>
        </div>

        <?php if ($keyword !== ''): ?>
            <div class="col-auto">
                <a href="/admin/news/" class="btn btn-link">Bỏ lọc</a>
            </div>
        <?php endif; ?>

    </form>

    <div class="table-responsive">

        <table class="table table-bordered table-striped align-middle">

            <thead class="table-dark">
                <tr>
                    <th style="width: 90px;">Ảnh</th>
                    <th>Tiêu đề</th>
                    <th style="width: 130px;">Danh mục</th>
                    <th style="width: 150px;">Tác giả</th>
                    <th style="width: 120px;">Ngày đăng</th>
                    <th style="width: 110px;">Trạng thái</th>
                    <th style="width: 150px;">Thao tác</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($total === 0): ?>

                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <?php if ($keyword !== ''): ?>
                            Không tìm thấy bài viết nào khớp từ khoá.
                        <?php else: ?>
                            Chưa có bài viết nào. Bấm “Thêm tin tức” để tạo bài đầu tiên.
                        <?php endif; ?>
                    </td>
                </tr>

            <?php endif; ?>

            <?php while ($article = $newsList->fetch_assoc()): ?>

                <tr>

                    <td>
                        <?php if (!empty($article['ImageFile'])): ?>
                            <img
                                src="/uploads/news/<?= htmlspecialchars($article['ImageFile']) ?>"
                                alt="<?= htmlspecialchars($article['Title']) ?>"
                                style="width: 72px; height: 48px; object-fit: cover;"
                                class="rounded border"
                            >
                        <?php else: ?>
                            <span class="text-muted small">Không có</span>
                        <?php endif; ?>
                    </td>

                    <td>
                        <a href="/admin/news/edit.php?id=<?= (int) $article['NewsID'] ?>">
                            <?= htmlspecialchars($article['Title']) ?>
                        </a>
                    </td>

                    <td><?= htmlspecialchars($article['Category']) ?></td>

                    <td><?= htmlspecialchars($article['Author'] ?? '') ?></td>

                    <td><?= format_date($article['PublishedAt']) ?></td>

                    <td>
                        <?php if ((int) $article['IsPublished'] === 1): ?>
                            <span class="badge bg-success">Đang hiện</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Đang ẩn</span>
                        <?php endif; ?>
                    </td>

                    <td>

                        <a
                            href="/admin/news/edit.php?id=<?= (int) $article['NewsID'] ?>"
                            class="btn btn-sm btn-warning"
                        >
                            Sửa
                        </a>

                        <form
                            action="/admin/news/delete.php"
                            method="post"
                            class="d-inline"
                            onsubmit="return confirm('Bạn có chắc muốn xoá bài viết này?');"
                        >
                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $article['NewsID'] ?>"
                            >

                            <button type="submit" class="btn btn-sm btn-danger">
                                Xoá
                            </button>
                        </form>

                    </td>

                </tr>

            <?php endwhile; ?>

            </tbody>

        </table>

    </div>

    <?php if ($totalPages > 1): ?>

        <nav aria-label="Chia trang tin tức">

            <ul class="pagination justify-content-center">

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                    <?php
                    /*
                     * Giữ lại từ khoá khi chuyển trang, nếu không sang trang 2
                     * sẽ mất bộ lọc và hiện toàn bộ bài viết.
                     */
                    $query = ['page' => $i];

                    if ($keyword !== '') {
                        $query['q'] = $keyword;
                    }

                    $link = '/admin/news/?' . http_build_query($query);
                    ?>

                    <li class="page-item<?= $i === $page ? ' active' : '' ?>">
                        <a class="page-link" href="<?= htmlspecialchars($link) ?>">
                            <?= $i ?>
                        </a>
                    </li>

                <?php endfor; ?>

            </ul>

        </nav>

    <?php endif; ?>

</div>

<?php

require_once '/var/www/src/includes/admin/footer.php';

$newsList->free();
$conn->close();
