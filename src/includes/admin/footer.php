<?php
/*
 * Footer dùng chung cho mọi trang admin.
 *
 * CÁCH CHỈNH SỬA: mọi liên kết nằm trong mảng bên dưới, thêm/xoá chỉ cần sửa 1 dòng.
 * Màu sắc của footer nằm trong public/assets/css/admin.css (khối :root).
 */

/* Hai cột liên kết. Mỗi mục gồm: href (đường dẫn), label (chữ), icon (FontAwesome) */
$footerColumns = [
    [
        'title' => 'Danh mục',
        'links' => [
            ['href' => '/',                  'label' => 'Trang chủ', 'icon' => 'fa-house'],
            ['href' => '/admin/products/',   'label' => 'Sản phẩm',  'icon' => 'fa-box-open'],
            ['href' => '/admin/categories/', 'label' => 'Danh mục',  'icon' => 'fa-tags'],
            ['href' => '/orders/',           'label' => 'Đơn hàng',  'icon' => 'fa-receipt'],
        ],
    ],
    [
        'title' => 'Đối tác',
        'links' => [
            ['href' => '/customers/',        'label' => 'Khách hàng',          'icon' => 'fa-users'],
            ['href' => '/suppliers/',        'label' => 'Nhà cung cấp',        'icon' => 'fa-truck-field'],
            ['href' => '/admin/shippers/',   'label' => 'Nhân viên giao hàng', 'icon' => 'fa-truck-fast'],
            ['href' => '/admin/employees/',  'label' => 'Nhân viên',           'icon' => 'fa-user-tie'],
        ],
    ],
];

/* Cột thông tin liên hệ */
$footerContacts = [
    ['icon' => 'fa-envelope',     'text' => 'support@quanlybanhang.com'],
    ['icon' => 'fa-phone',        'text' => '0123 456 789'],
    ['icon' => 'fa-location-dot', 'text' => 'Việt Nam'],
];
?>

<!-- ===== FOOTER ===== -->
<footer class="app-footer mt-5">
    <div class="container py-5">

        <div class="row g-4">

            <!-- Cột 1: logo + giới thiệu hệ thống -->
            <div class="col-lg-4 col-md-6">

                <div class="app-footer-brand">
                    <span class="app-footer-logo">
                        <i class="fa-solid fa-store"></i>
                    </span>
                    <h5 class="app-footer-title">Quản lý bán hàng</h5>
                </div>

                <p class="app-footer-desc">
                    Hệ thống quản lý bán hàng hỗ trợ quản lý sản phẩm, khách hàng,
                    nhà cung cấp, nhân viên giao hàng và đơn hàng một cách nhanh
                    chóng và hiệu quả.
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
            <div class="col-lg-2 col-md-6">

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
                    © 2026 Nhihuynh1224lx. All rights reserved.
                </p>
            </div>

            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">

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
                    href="#"
                    class="app-footer-social"
                    title="Email"
                >
                    <i class="fa-solid fa-envelope"></i>
                </a>

            </div>

        </div>

    </div>
</footer>
