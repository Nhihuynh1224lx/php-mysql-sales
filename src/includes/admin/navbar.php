<?php
/*
 * Thanh menu ngang của khu vực admin.
 *
 * CÁCH CHỈNH SỬA MENU: chỉ cần thêm/xoá/sửa một dòng trong mảng $navItems
 * bên dưới. Mỗi mục gồm 3 phần:
 *     'href'  => đường dẫn
 *     'label' => chữ hiển thị
 *     'icon'  => tên icon FontAwesome (xem tại fontawesome.com/icons)
 *
 * Màu sắc của menu nằm trong public/assets/css/admin.css (khối :root).
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

/* Danh sách mục menu */
$navItems = [
    ['href' => '/',                  'label' => 'Trang chủ',           'icon' => 'fa-house'],
    ['href' => '/orders/',           'label' => 'Đơn hàng',            'icon' => 'fa-receipt'],
    ['href' => '/admin/products/',   'label' => 'Sản phẩm',            'icon' => 'fa-box-open'],
    ['href' => '/admin/categories/', 'label' => 'Danh mục',            'icon' => 'fa-tags'],
    ['href' => '/customers/',        'label' => 'Khách hàng',          'icon' => 'fa-users'],
    ['href' => '/suppliers/',        'label' => 'Nhà cung cấp',        'icon' => 'fa-truck-field'],
    ['href' => '/admin/shippers/',   'label' => 'Nhân viên giao hàng', 'icon' => 'fa-truck-fast'],
    ['href' => '/admin/employees/',  'label' => 'Nhân viên',           'icon' => 'fa-user-tie'],
];

/*
 * Kiểm tra một mục menu có đang được mở hay không.
 * Khớp cả trang con: đang ở /admin/products/edit.php thì /admin/products/ vẫn sáng.
 */
if (!function_exists('nav_is_active')) {
    function nav_is_active(string $href, string $currentPath): bool
    {
        $href = rtrim($href, '/');

        // Trang chủ: chỉ sáng khi đang đứng đúng ở trang chủ
        if ($href === '') {
            return $currentPath === '/';
        }

        return $currentPath === $href
            || str_starts_with($currentPath, $href . '/');
    }
}
?>

<!-- ===== THANH MENU ===== -->
<nav class="navbar navbar-expand-lg app-navbar">
    <div class="container">

        <!-- Nút mở menu trên màn hình nhỏ -->
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#mainNavbar"
            aria-label="Mở menu"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">

            <ul class="navbar-nav">
                <?php foreach ($navItems as $item): ?>
                    <?php $isActive = nav_is_active($item['href'], $currentPath); ?>

                    <li class="nav-item">
                        <a
                            class="nav-link<?= $isActive ? ' is-active' : '' ?>"
                            href="<?= htmlspecialchars($item['href']) ?>"
                            <?= $isActive ? 'aria-current="page"' : '' ?>
                        >
                            <i class="fa-solid <?= htmlspecialchars($item['icon']) ?>"></i>
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

        </div>

    </div>
</nav>
