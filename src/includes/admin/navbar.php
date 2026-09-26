<?php
/*
 * MENU DỌC (sidebar) của khu vực admin.
 *
 * CÁCH CHỈNH SỬA MENU: chỉ cần thêm/xoá/sửa một dòng trong mảng $navGroups
 * bên dưới. Menu được chia thành từng nhóm, mỗi nhóm có một tiêu đề nhỏ.
 * Mỗi mục menu gồm 3 phần:
 *     'href'  => đường dẫn
 *     'label' => chữ hiển thị
 *     'icon'  => tên icon FontAwesome (xem tại fontawesome.com/icons)
 *
 * Màu sắc của menu nằm trong public/assets/css/admin.css (khối :root).
 *
 * File này in ra 2 phần:
 *   1. <aside class="app-sidebar">  — thanh menu dọc bên trái
 *   2. Mở <div class="app-main"> và <div class="app-content"> cho nội dung trang
 *      (hai thẻ này được đóng lại ở src/includes/admin/footer.php)
 */

/*
 * Đường dẫn hiện tại, dùng để tô sáng mục menu đang mở.
 * Ví dụ đang ở /admin/products/edit.php?id=3 thì mục "Sản phẩm" sẽ được sáng.
 */
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$currentPath = is_string($currentPath) ? rtrim($currentPath, '/') : '';

if ($currentPath === '' || $currentPath === '/index.php') {
    $currentPath = '/';
}

/*
 * Danh sách mục menu, xếp theo nhóm nghiệp vụ.
 * Thứ tự nhóm và thứ tự mục trong nhóm chính là thứ tự hiển thị trên menu.
 */
$navGroups = [
    [
        'title' => 'Tổng quan',
        'items' => [
            ['href' => '/admin/', 'label' => 'Trang chủ', 'icon' => 'fa-house'],
        ],
    ],
    [
        'title' => 'Bán hàng',
        'items' => [
            ['href' => '/admin/orders/',    'label' => 'Đơn hàng',   'icon' => 'fa-receipt'],
            ['href' => '/admin/customers/', 'label' => 'Khách hàng', 'icon' => 'fa-users'],
        ],
    ],
    [
        'title' => 'Kho hàng',
        'items' => [
            ['href' => '/admin/products/',   'label' => 'Sản phẩm',     'icon' => 'fa-box-open'],
            ['href' => '/admin/categories/', 'label' => 'Danh mục',     'icon' => 'fa-tags'],
            ['href' => '/admin/suppliers/',        'label' => 'Nhà cung cấp', 'icon' => 'fa-truck-field'],
        ],
    ],
    [
        'title' => 'Nhân sự',
        'items' => [
            ['href' => '/admin/employees/', 'label' => 'Nhân viên',           'icon' => 'fa-user-tie'],
            ['href' => '/admin/shippers/',  'label' => 'Nhân viên giao hàng', 'icon' => 'fa-truck-fast'],
        ],
    ],
];

/*
 * Kiểm tra một mục menu có đang được mở hay không.
 * Khớp cả trang con: đang ở /admin/products/edit.php thì /admin/products/ vẫn sáng.
 */
if (!function_exists('nav_is_active')) {
    function nav_is_active(string $href, string $currentPath): bool
    {
        $href = rtrim($href, '/');

        /*
         * Mục "trang chủ" của khu vực (đường dẫn / hoặc /admin) chỉ sáng khi đang
         * đứng ĐÚNG ở đó. Nếu dùng chung luật khớp tiền tố bên dưới thì khi vào
         * /admin/products/ sẽ có 2 mục cùng sáng (Trang chủ và Sản phẩm).
         */
        if ($href === '' || $href === '/admin') {
            return $currentPath === ($href === '' ? '/' : $href);
        }

        return $currentPath === $href
            || str_starts_with($currentPath, $href . '/');
    }
}
?>

<!-- ===== MENU DỌC ===== -->
<aside class="app-sidebar" id="appSidebar">

    <div class="app-sidebar-head">

        <p class="app-sidebar-label">
            <?php
            /*
             * Tên cửa hàng lấy từ src/config/brand.php — cùng nguồn với
             * giao diện bên ngoài nên đổi một chỗ là đổi cả hai bên.
             */
            require_once '/var/www/src/config/brand.php';

            echo htmlspecialchars($brand['name']);
            ?>
        </p>

        <!--
            Nút đóng menu. Chỉ hiện trên màn hình nhỏ, khi menu trượt ra
            dạng ngăn kéo (xem @media trong admin.css).
        -->
        <button
            type="button"
            class="app-sidebar-close"
            id="appSidebarClose"
            aria-label="Đóng menu"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>

    </div>

    <nav class="app-nav" aria-label="Menu quản trị">
        <?php foreach ($navGroups as $group): ?>

            <div class="app-nav-group">

                <p class="app-nav-group-title"><?= htmlspecialchars($group['title']) ?></p>

                <ul class="app-nav-list">
                    <?php foreach ($group['items'] as $item): ?>
                        <?php $isActive = nav_is_active($item['href'], $currentPath); ?>

                        <li class="app-nav-item">
                            <a
                                class="app-nav-link<?= $isActive ? ' is-active' : '' ?>"
                                href="<?= htmlspecialchars($item['href']) ?>"
                                title="<?= htmlspecialchars($item['label']) ?>"
                                <?= $isActive ? 'aria-current="page"' : '' ?>
                            >
                                <i class="fa-solid <?= htmlspecialchars($item['icon']) ?>"></i>
                                <span><?= htmlspecialchars($item['label']) ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

            </div>

        <?php endforeach; ?>
    </nav>

</aside>

<!-- ===== MỞ VÙNG NỘI DUNG BÊN PHẢI (đóng ở footer.php) ===== -->
<div class="app-main">
<div class="app-content">
