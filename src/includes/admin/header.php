<?php
/*
 * Khung đầu trang dùng chung cho mọi trang admin.
 *
 * File này làm 3 việc:
 *   1. Nạp thư viện: Bootstrap 5, FontAwesome 6, và assets/css/admin.css
 *   2. In khối Header (logo + tên hệ thống + thông tin nhanh)
 *   3. Mở thẻ <body> cho phần nội dung phía sau
 *
 * LƯU Ý: mọi màu sắc nằm trong public/assets/css/admin.css (khối :root).
 * Muốn đổi tông màu thì sửa ở đó, không sửa trong file này.
 *
 * Mỗi trang có thể đặt $pageTitle trước khi include file này.
 */

/*
 * Đặt múi giờ Việt Nam.
 * Container mặc định chạy UTC (chậm hơn 7 giờ), nên nếu không đặt thì
 * từ 00:00 đến 07:00 giờ Việt Nam, ngày hiển thị và ngày đơn hàng mới
 * sẽ bị lùi lại 1 ngày.
 */
date_default_timezone_set('Asia/Ho_Chi_Minh');

$appName = 'Hệ thống quản lý bán hàng';
$appSubtitle = 'Bán hàng và phân phối phụ kiện bida Hoàng Nhi';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars($pageTitle ?? 'Quản lý bán hàng') ?></title>

    <!-- Bootstrap 5: lưới, nút, form, navbar -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!--
        FontAwesome 6 (CDN) — thư viện icon dùng cho toàn bộ giao diện admin.
        Cách dùng: <i class="fa-solid fa-house"></i>    -> icon nét đặc
                   <i class="fa-brands fa-facebook"></i> -> icon thương hiệu
        Chọn bản 6.7.2 vì có đủ bộ icon cần dùng và CDN cdnjs tải ổn định.
    -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        referrerpolicy="no-referrer"
    >

    <!--
        Giao diện admin: bảng màu + bố cục header/menu.
        Đặt SAU Bootstrap để các quy tắc ở đây ghi đè được style mặc định.

        Phần ?v=... là "mã phiên bản" lấy từ thời điểm sửa file lần cuối.
        Mỗi lần admin.css được sửa, mã này đổi theo, buộc trình duyệt tải bản
        mới. Nếu không có phần này, trình duyệt có thể vẫn dùng bản CSS cũ
        trong bộ nhớ đệm (cache) và giao diện sẽ hiển thị sai.
    -->
    <?php $adminCssFile = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/assets/css/admin.css'; ?>
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= @filemtime($adminCssFile) ?: 1 ?>">

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>
</head>

<body>

<?php
/*
 * Khôi phục trạng thái thu gọn menu NGAY khi trang bắt đầu dựng.
 * Phải đặt ở đây (ngay sau <body>) chứ không phải ở footer.php: nếu để cuối
 * trang, menu sẽ hiện ra dạng mở rồi mới thu lại — nhìn bị "giật".
 * Trạng thái do đoạn JS ở cuối footer.php ghi vào localStorage.
 */
?>
<script>
(function () {
    try {
        if (window.localStorage.getItem('admin.menu.collapsed') === '1') {
            document.body.classList.add('app-nav-collapsed');
        }
    } catch (e) {
        /* Trình duyệt chặn localStorage (chế độ riêng tư) — bỏ qua, menu mở bình thường */
    }
})();
</script>

<!-- ===== HEADER: logo + tên hệ thống (bên trái), thông tin nhanh (bên phải) ===== -->
<header class="app-header">
    <div class="container app-header-inner">

        <div class="app-header-left">

            <!--
                Nút mở/đóng menu dọc:
                  - Màn hình lớn: thu gọn menu thành thanh icon rồi mở rộng lại.
                  - Màn hình nhỏ: trượt menu ra dạng ngăn kéo.
                Trạng thái thu gọn được ghi nhớ trong localStorage nên khi sang
                trang khác menu vẫn giữ nguyên như bạn đã chọn.
            -->
            <button
                type="button"
                class="app-sidebar-toggle"
                id="appSidebarToggle"
                aria-label="Đóng hoặc mở menu"
                aria-controls="appSidebar"
                aria-expanded="true"
            >
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="app-header-brand">
                <span class="app-header-logo">
                    <i class="fa-solid fa-store"></i>
                </span>

                <div>
                    <h1 class="app-header-title"><?= htmlspecialchars($appName) ?></h1>
                    <p class="app-header-sub"><?= htmlspecialchars($appSubtitle) ?></p>
                </div>
            </div>

        </div>

        <div class="app-header-meta">
            <span class="app-header-chip">
                <i class="fa-solid fa-calendar-day"></i>
                <?= date('d/m/Y') ?>
            </span>
        </div>

    </div>
</header>

<!--
    KHUNG BỐ CỤC CHÍNH: menu dọc bên trái + vùng nội dung bên phải.

    Thẻ <div class="app-layout"> được MỞ ở đây và ĐÓNG lại trong
    src/includes/admin/footer.php. Lý do phải tách ra hai file: phần nội dung
    của từng trang nằm giữa lúc include header.php và footer.php.

    Bên trong .app-layout lần lượt có:
        <aside class="app-sidebar">  -> do navbar.php in ra
        <div class="app-main">       -> do navbar.php mở, footer.php đóng
-->
<div class="app-layout">
