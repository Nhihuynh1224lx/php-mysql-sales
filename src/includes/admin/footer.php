<?php
/*
 * Footer dùng chung cho mọi trang admin.
 *
 * CÁCH CHỈNH SỬA: mọi liên kết nằm trong mảng bên dưới, thêm/xoá chỉ cần sửa 1 dòng.
 * Màu sắc của footer nằm trong public/assets/css/admin.css (khối :root).
 *
 * Thông tin thương hiệu (tên, địa chỉ, điện thoại, email) lấy từ
 * src/config/brand.php để dùng chung với giao diện cửa hàng.
 */
require_once '/var/www/src/config/brand.php';

/* Hai cột liên kết. Mỗi mục gồm: href (đường dẫn), label (chữ), icon (FontAwesome) */
$footerColumns = [
    [
        'title' => 'Danh mục',
        'links' => [
            ['href' => '/admin/',            'label' => 'Trang chủ', 'icon' => 'fa-house'],
            ['href' => '/admin/products/',   'label' => 'Sản phẩm',  'icon' => 'fa-box-open'],
            ['href' => '/admin/categories/', 'label' => 'Danh mục',  'icon' => 'fa-tags'],
            ['href' => '/admin/orders/',           'label' => 'Đơn hàng',  'icon' => 'fa-receipt'],
        ],
    ],
    [
        'title' => 'Đối tác',
        'links' => [
            ['href' => '/admin/customers/',        'label' => 'Khách hàng',          'icon' => 'fa-users'],
            ['href' => '/admin/suppliers/',        'label' => 'Nhà cung cấp',        'icon' => 'fa-truck-field'],
            ['href' => '/admin/shippers/',   'label' => 'Nhân viên giao hàng', 'icon' => 'fa-truck-fast'],
            ['href' => '/admin/employees/',  'label' => 'Nhân viên',           'icon' => 'fa-user-tie'],
        ],
    ],
];

/* Cột thông tin liên hệ — lấy trực tiếp từ src/config/brand.php */
$footerContacts = [
    ['icon' => 'fa-envelope',     'text' => $brand['email']],
    ['icon' => 'fa-phone',        'text' => $brand['phone']],
    ['icon' => 'fa-location-dot', 'text' => $brand['address']],
];
?>

<!-- ===== FOOTER ===== -->
<footer class="app-footer mt-5">
    <div class="container py-5">

        <div class="row g-4">

            <!-- Cột 1: logo + giới thiệu hệ thống -->
            <div class="col-lg-3 col-md-6">

                <div class="app-footer-brand">
                    <span class="app-footer-logo">
                        <?= htmlspecialchars($brand['initials']) ?>
                    </span>
                    <h5 class="app-footer-title">
                        <?= htmlspecialchars($brand['name']) ?>
                    </h5>
                </div>

                <p class="app-footer-desc">
                    <?= htmlspecialchars($brand['about']) ?>
                </p>

            </div>

            <!-- Cột 2, 3: các nhóm liên kết -->
            <?php foreach ($footerColumns as $column): ?>
                <div class="col-lg-3 col-md-6 col-6">

                    <h6 class="app-footer-heading">
                        <?= htmlspecialchars($column['title']) ?>
                    </h6>

                    <ul class="app-footer-links">
                        <?php foreach ($column['links'] as $link): ?>
                            <li>
                                <a href="<?= htmlspecialchars($link['href']) ?>">
                                    <i class="fa-solid <?= htmlspecialchars($link['icon']) ?>"></i>
                                    <?= htmlspecialchars($link['label']) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
            <?php endforeach; ?>

            <!-- Cột 4: thông tin liên hệ -->
            <div class="col-lg-3 col-md-6">

                <h6 class="app-footer-heading">Liên hệ</h6>

                <ul class="app-footer-contact">
                    <?php foreach ($footerContacts as $contact): ?>
                        <li>
                            <i class="fa-solid <?= htmlspecialchars($contact['icon']) ?>"></i>
                            <span><?= htmlspecialchars($contact['text']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

            </div>

        </div>

        <hr class="app-footer-line">

        <!-- Dòng cuối: bản quyền + liên kết mạng xã hội -->
        <div class="row align-items-center">

            <div class="col-md-6 text-center text-md-start">
                <p class="app-footer-copy">
                    © 2026 <?= htmlspecialchars($brand['name']) ?>. All rights reserved.
                </p>
            </div>

            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">

                <!--
                    Liên kết mạng xã hội.
                    Facebook/GitHub hiện để href="#" vì đồ án chưa có trang thật;
                    khi triển khai chỉ cần thay bằng địa chỉ thật.
                    Email đã dùng mailto: khớp với mục "Liên hệ" ở trên.
                -->
                <a
                    href="#"
                    class="app-footer-social me-2"
                    title="Facebook"
                >
                    <i class="fa-brands fa-facebook-f"></i>
                </a>

                <a
                    href="#"
                    class="app-footer-social me-2"
                    title="GitHub"
                >
                    <i class="fa-brands fa-github"></i>
                </a>

                <a
                    href="mailto:support@quanlybanhang.com"
                    class="app-footer-social"
                    title="Email"
                >
                    <i class="fa-solid fa-envelope"></i>
                </a>

            </div>

        </div>

    </div>
</footer>

</div><!-- /.app-content (mở ở navbar.php) -->
</div><!-- /.app-main (mở ở navbar.php) -->
</div><!-- /.app-layout (mở ở header.php) -->

<!-- Lớp phủ mờ, chỉ hiện khi menu dọc mở ra trên màn hình nhỏ -->
<div class="app-sidebar-backdrop" id="appSidebarBackdrop"></div>

<script>
/*
 * Điều khiển menu dọc. Nút trên header làm hai việc tuỳ theo bề rộng màn hình:
 *
 *   - Màn hình lớn (>= 992px): THU GỌN menu thành thanh chỉ có icon, bấm lại
 *     để mở rộng. Trạng thái được ghi vào localStorage nên khi sang trang khác
 *     menu vẫn giữ nguyên (xem đoạn khôi phục ở đầu header.php).
 *
 *   - Màn hình nhỏ (< 992px): TRƯỢT menu ra dạng ngăn kéo, che một phần nội dung.
 *     Đóng bằng nút X, bấm vùng mờ, hoặc nhấn phím Esc.
 */
(function () {
    var STORAGE_KEY = 'admin.menu.collapsed';
    var DESKTOP_MIN = 992;

    var sidebar = document.getElementById('appSidebar');
    var toggle = document.getElementById('appSidebarToggle');
    var closeButton = document.getElementById('appSidebarClose');
    var backdrop = document.getElementById('appSidebarBackdrop');

    if (!sidebar || !toggle || !backdrop) {
        return;
    }

    function isDesktop() {
        return window.innerWidth >= DESKTOP_MIN;
    }

    /* ---------- Màn hình nhỏ: ngăn kéo ---------- */

    function openDrawer() {
        sidebar.classList.add('is-open');
        backdrop.classList.add('is-visible');
        document.body.classList.add('app-nav-open');
        toggle.setAttribute('aria-expanded', 'true');
    }

    function closeDrawer() {
        sidebar.classList.remove('is-open');
        backdrop.classList.remove('is-visible');
        document.body.classList.remove('app-nav-open');
        toggle.setAttribute('aria-expanded', 'false');
    }

    /* ---------- Màn hình lớn: thu gọn / mở rộng ---------- */

    function setCollapsed(collapsed) {
        document.body.classList.toggle('app-nav-collapsed', collapsed);
        toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        toggle.setAttribute('aria-label', collapsed ? 'Mở rộng menu' : 'Thu gọn menu');

        try {
            window.localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
        } catch (e) {
            /* Trình duyệt chặn localStorage — menu vẫn dùng được, chỉ không ghi nhớ */
        }
    }

    /*
     * Đồng bộ nhãn nút với trạng thái đã khôi phục ở header.php.
     * (header.php gắn lớp vào <body> trước khi trang vẽ, còn nhãn nút thì phải
     *  cập nhật ở đây vì lúc đó nút chưa tồn tại.)
     */
    if (isDesktop()) {
        setCollapsed(document.body.classList.contains('app-nav-collapsed'));
    } else {
        toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', function () {
        if (isDesktop()) {
            setCollapsed(!document.body.classList.contains('app-nav-collapsed'));
        } else {
            openDrawer();
        }
    });

    if (closeButton) {
        closeButton.addEventListener('click', closeDrawer);
    }

    // Bấm ra vùng mờ hoặc nhấn Esc cũng đóng ngăn kéo
    backdrop.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDrawer();
        }
    });

    // Đổi bề rộng cửa sổ: đóng ngăn kéo cho khỏi kẹt trạng thái,
    // đồng thời cập nhật lại nhãn nút cho đúng chế độ đang dùng.
    window.addEventListener('resize', function () {
        if (isDesktop()) {
            closeDrawer();
            setCollapsed(document.body.classList.contains('app-nav-collapsed'));
        }
    });
})();
</script>

</body>
</html>
