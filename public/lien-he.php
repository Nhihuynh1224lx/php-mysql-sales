<?php

require_once '/var/www/src/config/session.php';
require_once '/var/www/src/config/database.php';
require_once '/var/www/src/config/brand.php';

/* ------------------------------------------------------------------
 * XỬ LÝ GỬI LIÊN HỆ
 * ------------------------------------------------------------------
 * Chỉ nhận POST. Kiểm tra dữ liệu rồi ghi vào bảng contact_messages để
 * quản trị viên xem lại, đồng thời lưu $successMessage để hiển thị.
 */

$errors = [];
$successMessage = '';

$fullName = '';
$email = '';
$phone = '';
$subject = '';
$message = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email    = trim((string) ($_POST['email'] ?? ''));
    $phone    = trim((string) ($_POST['phone'] ?? ''));
    $subject  = trim((string) ($_POST['subject'] ?? ''));
    $message  = trim((string) ($_POST['message'] ?? ''));

    /* ---------- Kiểm tra dữ liệu ---------- */

    if ($fullName === '') {
        $errors[] = 'Vui lòng nhập họ và tên.';
    }

    if ($email === '') {
        $errors[] = 'Vui lòng nhập email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ.';
    }

    if ($phone === '') {
        $errors[] = 'Vui lòng nhập số điện thoại.';
    } elseif (!preg_match('/^[0-9+\s.-]{8,20}$/', $phone)) {
        $errors[] = 'Số điện thoại không hợp lệ.';
    }

    if ($subject === '') {
        $errors[] = 'Vui lòng chọn chủ đề.';
    }

    if ($message === '') {
        $errors[] = 'Vui lòng nhập nội dung cần liên hệ.';
    } elseif (mb_strlen($message) < 10) {
        $errors[] = 'Nội dung cần ít nhất 10 ký tự.';
    }

    /* ---------- Ghi vào cơ sở dữ liệu ---------- */

    if (!$errors) {

        $sql = "
            INSERT INTO contact_messages
                (FullName, Email, Phone, Subject, Message)
            VALUES
                (?, ?, ?, ?, ?)
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssss', $fullName, $email, $phone, $subject, $message);

        if ($stmt->execute()) {
            $successMessage = 'Cảm ơn ' . $fullName
                . '! Chúng tôi đã nhận được liên hệ và sẽ phản hồi sớm nhất.';
            /* Xoá dữ liệu đã gửi để form trắng sau khi gửi thành công */
            $fullName = $email = $phone = $subject = $message = '';
        } else {
            $errors[] = 'Không gửi được liên hệ. Vui lòng thử lại sau.';
        }

        $stmt->close();
    }
}

/* ------------------------------------------------------------------
 * Chủ đề gợi ý cho ô chọn
 * ------------------------------------------------------------------ */

$subjects = [
    'Tư vấn sản phẩm',
    'Báo giá',
    'Bảo hành',
    'Khiếu nại',
    'Hợp tác',
    'Khác',
];

$pageTitle = 'Liên hệ';

require_once '/var/www/src/includes/frontend/header.php';
require_once '/var/www/src/includes/frontend/navbar.php';

?>

<!-- ============================================================
     TIÊU ĐỀ TRANG
     ============================================================ -->
<section class="page-hero">

    <div class="container">

        <nav aria-label="breadcrumb">

            <ol class="breadcrumb page-breadcrumb">

                <li class="breadcrumb-item">
                    <a href="/">Trang chủ</a>
                </li>

                <li class="breadcrumb-item active" aria-current="page">
                    Liên hệ
                </li>

            </ol>

        </nav>

        <h1 class="page-hero-title">Liên hệ với chúng tôi</h1>

        <p class="page-hero-text">
            Gửi câu hỏi hoặc yêu cầu báo giá, chúng tôi sẽ phản hồi
            trong thời gian sớm nhất.
        </p>

    </div>

</section>

<!-- ============================================================
     THÔNG TIN LIÊN HỆ + FORM
     ============================================================ -->
<section class="container py-5">

    <div class="row g-4 g-lg-5">

        <!-- ---------- Cột trái: thông tin liên hệ ---------- -->
        <div class="col-lg-5">

            <h2 class="section-title text-start">
                Thông tin liên hệ
            </h2>

            <p class="about-text">
                Bạn có thể gọi điện trực tiếp, gửi email hoặc điền
                form bên cạnh. Chúng tôi trả lời mọi liên hệ.
            </p>

            <ul class="contact-list">

                <li>
                    <span class="contact-icon">
                        <i class="fa-solid fa-location-dot"></i>
                    </span>
                    <div>
                        <span class="contact-label">Địa chỉ</span>
                        <span class="contact-value">
                            <?= htmlspecialchars($brand['address']) ?>
                        </span>
                    </div>
                </li>

                <li>
                    <span class="contact-icon">
                        <i class="fa-solid fa-phone"></i>
                    </span>
                    <div>
                        <span class="contact-label">Điện thoại</span>
                        <a
                            class="contact-value"
                            href="tel:<?= htmlspecialchars(
                                str_replace(' ', '', $brand['phone'])
                            ) ?>"
                        >
                            <?= htmlspecialchars($brand['phone']) ?>
                        </a>
                    </div>
                </li>

                <li>
                    <span class="contact-icon">
                        <i class="fa-solid fa-envelope"></i>
                    </span>
                    <div>
                        <span class="contact-label">Email</span>
                        <a
                            class="contact-value"
                            href="mailto:<?= htmlspecialchars($brand['email']) ?>"
                        >
                            <?= htmlspecialchars($brand['email']) ?>
                        </a>
                    </div>
                </li>

                <li>
                    <span class="contact-icon">
                        <i class="fa-solid fa-clock"></i>
                    </span>
                    <div>
                        <span class="contact-label">Giờ làm việc</span>
                        <span class="contact-value">
                            <?= htmlspecialchars($brand['hours']) ?>
                        </span>
                    </div>
                </li>

            </ul>

            <!-- Khung bản đồ: dùng Google Maps nhúng, không cần API key -->
            <div class="contact-map">

                <iframe
                    src="https://www.google.com/maps?q=Soc%20Trang%2C%20Can%20Tho%2C%20Vietnam&output=embed"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Bản đồ <?= htmlspecialchars($brand['name']) ?>"
                ></iframe>

            </div>

        </div>

        <!-- ---------- Cột phải: form liên hệ ---------- -->
        <div class="col-lg-7">

            <div class="contact-form-card">

                <h2 class="section-title text-start">
                    Gửi liên hệ
                </h2>

                <p class="section-subtitle mb-4">
                    Các ô có dấu <span class="text-danger">*</span>
                    là bắt buộc.
                </p>

                <?php if ($successMessage !== ''): ?>

                    <div class="alert alert-success">

                        <i class="fa-solid fa-circle-check me-1"></i>

                        <?= htmlspecialchars($successMessage) ?>

                    </div>

                <?php endif; ?>

                <?php if ($errors): ?>

                    <div class="alert alert-danger">

                        <i class="fa-solid fa-triangle-exclamation me-1"></i>

                        <strong>Vui lòng kiểm tra lại:</strong>

                        <ul class="mb-0 mt-2">

                            <?php foreach ($errors as $error): ?>

                                <li><?= htmlspecialchars($error) ?></li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                <?php endif; ?>

                <form method="post" action="/lien-he.php" novalidate>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label for="full_name" class="form-label">
                                Họ và tên
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                id="full_name"
                                class="form-control"
                                value="<?= htmlspecialchars($fullName) ?>"
                                placeholder="Nguyễn Văn A"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label for="phone" class="form-label">
                                Số điện thoại
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="phone"
                                id="phone"
                                class="form-control"
                                value="<?= htmlspecialchars($phone) ?>"
                                placeholder="0909 123 456"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label for="email" class="form-label">
                                Email
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control"
                                value="<?= htmlspecialchars($email) ?>"
                                placeholder="ban@example.com"
                                required
                            >

                        </div>

                        <div class="col-md-6">

                            <label for="subject" class="form-label">
                                Chủ đề
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="subject"
                                id="subject"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    -- Chọn chủ đề --
                                </option>

                                <?php foreach ($subjects as $option): ?>

                                    <option
                                        value="<?= htmlspecialchars($option) ?>"
                                        <?=
                                            $subject === $option
                                                ? 'selected'
                                                : ''
                                        ?>
                                    >
                                        <?= htmlspecialchars($option) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-12">

                            <label for="message" class="form-label">
                                Nội dung
                                <span class="text-danger">*</span>
                            </label>

                            <textarea
                                name="message"
                                id="message"
                                class="form-control"
                                rows="6"
                                placeholder="Mô tả chi tiết yêu cầu của bạn..."
                                required
                            ><?= htmlspecialchars($message) ?></textarea>

                        </div>

                        <div class="col-12">

                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-paper-plane me-1"></i>
                                Gửi liên hệ
                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</section>

<?php
require_once '/var/www/src/includes/frontend/footer.php';
