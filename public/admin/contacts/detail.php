<?php

/*
 * CHI TIẾT TIN NHẮN LIÊN HỆ — khu vực quản trị
 *
 * Trang này làm 4 việc:
 *   1. Mở một tin nhắn và đánh dấu đã đọc ngay khi xem
 *   2. Cho phép chuyển lại trạng thái "chưa đọc" nếu cần xử lý sau
 *   3. Nút "Phản hồi": mở sẵn chương trình thư của máy với địa chỉ người
 *      gửi, tiêu đề và lời chào đã điền sẵn
 *   4. Nút "Xoá": chuyển sang delete.php (chỉ nhận POST)
 *
 * VÌ SAO PHẢN HỒI KHÔNG GỬI THẲNG TỪ WEBSITE:
 * Dự án chưa cấu hình máy chủ thư (SMTP) và không dùng thư viện gửi mail nào,
 * nên hàm mail() của PHP không gửi được. Cách làm hiện tại là mở sẵn trình
 * soạn thư trên máy nhân viên — vẫn trả lời được khách mà không cần thêm
 * dịch vụ bên ngoài. Muốn gửi tự động thì phải bổ sung SMTP, việc này được
 * nêu ở mục 5.2 Hướng phát triển của báo cáo.
 */

$pageTitle = 'Chi tiết tin nhắn';

require_once '/var/www/src/config/database.php';

$contactID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($contactID <= 0) {
    die('Mã tin nhắn không hợp lệ.');
}

/* ---------- Đổi trạng thái đọc / chưa đọc ---------- */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'unread') {

        $stmt = $conn->prepare('UPDATE contact_messages SET IsRead = 0 WHERE ContactID = ?');
        $stmt->bind_param('i', $contactID);
        $stmt->execute();
        $stmt->close();

        /*
         * Quay lại danh sách thay vì ở lại trang chi tiết, vì nếu ở lại
         * thì đoạn đánh dấu đã đọc bên dưới sẽ bật lại thành đã đọc ngay.
         */
        $conn->close();

        header('Location: /admin/contacts/');
        exit;
    }
}

/* ---------- Đọc tin nhắn ---------- */

$sql = '
    SELECT
        ContactID,
        FullName,
        Email,
        Phone,
        Subject,
        Message,
        IsRead,
        CreatedAt
    FROM contact_messages
    WHERE ContactID = ?
';

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $contactID);
$stmt->execute();

$message = $stmt->get_result()->fetch_assoc();

$stmt->close();

if (!$message) {
    die('Không tìm thấy tin nhắn này.');
}

/*
 * Mở tin nhắn nghĩa là đã đọc. Chỉ cập nhật khi trạng thái đang là chưa đọc
 * để không ghi vào cơ sở dữ liệu một cách vô ích mỗi lần tải lại trang.
 */
if ((int) $message['IsRead'] === 0) {

    $stmt = $conn->prepare('UPDATE contact_messages SET IsRead = 1 WHERE ContactID = ?');
    $stmt->bind_param('i', $contactID);
    $stmt->execute();
    $stmt->close();

    $message['IsRead'] = 1;
}

/*
 * Nội dung thư trả lời soạn sẵn. rawurlencode để dấu tiếng Việt và ký tự
 * xuống dòng không làm hỏng địa chỉ mailto.
 */
$replySubject = 'Phản hồi: ' . $message['Subject'];

$replyBody = "Chào " . $message['FullName'] . ",\n\n"
    . "Cảm ơn bạn đã liên hệ với Bida Hoàng Nhi.\n"
    . "Về nội dung bạn hỏi: \"" . $message['Subject'] . "\", chúng tôi xin phản hồi như sau:\n\n"
    . "\n\n"
    . "Trân trọng,\n"
    . "Bida Hoàng Nhi";

$mailtoLink = 'mailto:' . $message['Email']
    . '?subject=' . rawurlencode($replySubject)
    . '&body=' . rawurlencode($replyBody);

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h2 class="mb-0">Chi tiết tin nhắn</h2>

        <a href="/admin/contacts/" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left"></i>
            Quay lại hộp thư
        </a>

    </div>

    <div class="row">

        <div class="col-lg-8">

            <div class="card mb-3">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <span><?= htmlspecialchars($message['Subject']) ?></span>

                    <span class="badge bg-success">Đã đọc</span>

                </div>

                <div class="card-body">

                    <table class="table table-sm mb-3">

                        <tbody>

                            <tr>
                                <th style="width: 160px;">Người gửi</th>
                                <td><?= htmlspecialchars($message['FullName']) ?></td>
                            </tr>

                            <tr>
                                <th>Email</th>
                                <td>
                                    <a href="mailto:<?= htmlspecialchars($message['Email']) ?>">
                                        <?= htmlspecialchars($message['Email']) ?>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <th>Điện thoại</th>
                                <td><?= htmlspecialchars($message['Phone']) ?></td>
                            </tr>

                            <tr>
                                <th>Ngày gửi</th>
                                <td>
                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime($message['CreatedAt'])
                                    ) ?>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                    <h3 class="h6 text-muted">Nội dung</h3>

                    <!--
                        nl2br giữ lại các lần xuống dòng khách đã gõ;
                        htmlspecialchars chặn mọi thẻ HTML khách cố tình chèn vào.
                    -->
                    <div class="border rounded p-3 bg-light">
                        <?= nl2br(htmlspecialchars($message['Message'])) ?>
                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card">

                <div class="card-header">Thao tác</div>

                <div class="card-body d-grid gap-2">

                    <a href="<?= htmlspecialchars($mailtoLink) ?>" class="btn btn-primary">
                        <i class="fa-solid fa-reply"></i>
                        Phản hồi cho khách
                    </a>

                    <form method="post">
                        <input type="hidden" name="action" value="unread">

                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="fa-solid fa-envelope"></i>
                            Đánh dấu chưa đọc
                        </button>
                    </form>

                    <form
                        action="/admin/contacts/delete.php"
                        method="post"
                        onsubmit="return confirm('Bạn có chắc muốn xoá tin nhắn này?');"
                    >
                        <input type="hidden" name="id" value="<?= $contactID ?>">

                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fa-solid fa-trash"></i>
                            Xoá tin nhắn
                        </button>
                    </form>

                </div>

            </div>

            <p class="text-muted small mt-3 mb-0">
                Nút “Phản hồi” mở sẵn chương trình thư trên máy với địa chỉ và
                nội dung đã điền. Dự án chưa cấu hình máy chủ thư nên chưa gửi
                trực tiếp từ website được.
            </p>

        </div>

    </div>

</div>

<?php

require_once '/var/www/src/includes/admin/footer.php';

$conn->close();
