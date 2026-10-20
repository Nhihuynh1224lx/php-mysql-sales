<?php

/*
 * HỘP THƯ LIÊN HỆ — khu vực quản trị
 *
 * Khác với các module quản lý khác ở chỗ: nhân viên KHÔNG tạo tin nhắn.
 * Tin nhắn do khách gửi từ trang Liên hệ (public/lien-he.php) và được ghi
 * vào bảng contact_messages. Vì vậy trang này chỉ có 2 thao tác:
 *   - Xem chi tiết và đánh dấu đã đọc  (detail.php)
 *   - Xoá những tin đã xử lý xong       (delete.php)
 *
 * Bộ lọc "Chưa đọc" giúp nhân viên nhìn nhanh còn bao nhiêu tin cần trả lời.
 */

$pageTitle = 'Hộp thư liên hệ';

require_once '/var/www/src/config/database.php';
require_once '/var/www/src/includes/helpers.php';

$noticeMap = [
    'deleted' => 'Đã xoá tin nhắn.',
];

$notice = $noticeMap[$_GET['msg'] ?? ''] ?? '';

/* Bộ lọc: all = tất cả, unread = chỉ tin chưa đọc */
$filter = ($_GET['filter'] ?? 'all') === 'unread' ? 'unread' : 'all';

/* Số tin chưa đọc, hiện ở tiêu đề và trên nút lọc */
$unreadResult = $conn->query('SELECT COUNT(*) AS total FROM contact_messages WHERE IsRead = 0');
$unreadTotal = (int) ($unreadResult->fetch_assoc()['total'] ?? 0);

$sql = '
    SELECT
        ContactID,
        FullName,
        Email,
        Phone,
        Subject,
        IsRead,
        CreatedAt
    FROM contact_messages
';

if ($filter === 'unread') {
    $sql .= ' WHERE IsRead = 0 ';
}

$sql .= ' ORDER BY CreatedAt DESC, ContactID DESC ';

$result = $conn->query($sql);

$total = $result ? $result->num_rows : 0;

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4">

    <?php if ($notice !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h2 class="mb-1">Hộp thư liên hệ</h2>
            <p class="text-muted mb-0">
                <?= $total ?> tin nhắn
                <?php if ($filter === 'unread'): ?>
                    chưa đọc
                <?php else: ?>
                    — trong đó <strong><?= $unreadTotal ?></strong> tin chưa đọc
                <?php endif; ?>
            </p>
        </div>

        <div class="btn-group">

            <a
                href="/admin/contacts/"
                class="btn btn-outline-secondary<?= $filter === 'all' ? ' active' : '' ?>"
            >
                Tất cả
            </a>

            <a
                href="/admin/contacts/?filter=unread"
                class="btn btn-outline-secondary<?= $filter === 'unread' ? ' active' : '' ?>"
            >
                Chưa đọc (<?= $unreadTotal ?>)
            </a>

        </div>

    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-striped align-middle">

            <thead class="table-dark">
                <tr>
                    <th style="width: 60px;">STT</th>
                    <th style="width: 170px;">Người gửi</th>
                    <th>Tiêu đề</th>
                    <th style="width: 180px;">Email</th>
                    <th style="width: 120px;">Ngày gửi</th>
                    <th style="width: 110px;">Trạng thái</th>
                    <th style="width: 150px;">Thao tác</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($total === 0): ?>

                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <?php if ($filter === 'unread'): ?>
                            Không còn tin nhắn nào chưa đọc.
                        <?php else: ?>
                            Hộp thư đang trống. Tin nhắn khách gửi từ trang Liên hệ sẽ hiện ở đây.
                        <?php endif; ?>
                    </td>
                </tr>

            <?php endif; ?>

            <?php $rowNumber = 0; ?>

            <?php while ($message = $result->fetch_assoc()): ?>

                <?php $rowNumber++; ?>

                <tr class="<?= (int) $message['IsRead'] === 0 ? 'fw-semibold' : '' ?>">

                    <td><?= $rowNumber ?></td>

                    <td><?= htmlspecialchars($message['FullName']) ?></td>

                    <td>
                        <a href="/admin/contacts/detail.php?id=<?= (int) $message['ContactID'] ?>">
                            <?= htmlspecialchars($message['Subject']) ?>
                        </a>
                    </td>

                    <td class="small"><?= htmlspecialchars($message['Email']) ?></td>

                    <td><?= format_date($message['CreatedAt']) ?></td>

                    <td>
                        <?php if ((int) $message['IsRead'] === 0): ?>
                            <span class="badge bg-warning text-dark">Chưa đọc</span>
                        <?php else: ?>
                            <span class="badge bg-success">Đã đọc</span>
                        <?php endif; ?>
                    </td>

                    <td>

                        <a
                            href="/admin/contacts/detail.php?id=<?= (int) $message['ContactID'] ?>"
                            class="btn btn-sm btn-warning"
                        >
                            Xem
                        </a>

                        <form
                            action="/admin/contacts/delete.php"
                            method="post"
                            class="d-inline"
                            onsubmit="return confirm('Bạn có chắc muốn xoá tin nhắn này?');"
                        >
                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $message['ContactID'] ?>"
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

</div>

<?php

require_once '/var/www/src/includes/admin/footer.php';

$conn->close();
