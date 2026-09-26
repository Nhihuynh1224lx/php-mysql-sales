<?php
/*
 * MENU NGANG của giao diện cửa hàng.
 *
 * BỐ CỤC (3 phần rõ ràng, không chồng chéo):
 *   - Bên trái : thương hiệu (chữ lồng HN + tên cửa hàng)
 *   - Ở giữa   : menu điều hướng chính (Trang chủ, Sản phẩm, Giới thiệu,
 *                Tin tức, Liên hệ)
 *   - Bên phải : nhóm tiện ích (Giỏ hàng (n), tài khoản, Quản trị)
 *
 * LƯU Ý VỀ "GIỎ HÀNG": mục này CHỈ nằm ở nhóm tiện ích bên phải (kèm số
 * lượng). Trước đây nó vừa nằm trong menu chính vừa nằm bên phải nên vừa
 * trùng lặp vừa chiếm chỗ, làm menu bị chen chúc và bẻ chữ thành 2 dòng.
 * Vì vậy KHÔNG thêm lại "Giỏ hàng" vào danh sách menu chính bên dưới.
 *
 * NGƯỠNG XỔ NGANG: thẻ <nav> dùng class "navbar-expand" (KHÔNG kèm mức lg/xl/xxl).
 * Ngưỡng thật được quyết định trong storefront.css (mục 14) là 1320px, vì đo
 * thực tế cho thấy phải tới ~1320px thì 5 mục menu + 4 nút tiện ích mới vừa.
 * Bootstrap chỉ có lg (992) / xl (1200) / xxl (1400) — không mức nào khớp.
 * Đổi ngưỡng thì sửa @media trong CSS, KHÔNG sửa class ở đây.
 */

require_once '/var/www/src/config/brand.php';

/* ------------------------------------------------------------------
 * Tổng số lượng sản phẩm đang có trong giỏ.
 * array_sum() cộng tất cả "Số lượng" trong $_SESSION['cart'],
 * nên đây là TỔNG SỐ SẢN PHẨM, không phải số dòng sản phẩm.
 * ------------------------------------------------------------------ */

$cartCount = array_sum(
    $_SESSION['cart'] ?? []
);

$isLoggedIn = isset($_SESSION['customer_id']);

$customerName =
    $_SESSION['customer_name'] ?? '';

/* ------------------------------------------------------------------
 * Xác định mục menu đang mở, dựa vào đường dẫn hiện tại, để tô đậm
 * mục tương ứng trên thanh điều hướng.
 * ------------------------------------------------------------------ */

$currentPath = parse_url(
    $_SERVER['REQUEST_URI'] ?? '/',
    PHP_URL_PATH
);

if ($currentPath === '/' || $currentPath === '/index.php') {
    $activeMenu = 'home';
} elseif (strpos($currentPath, '/products.php') === 0
    || strpos($currentPath, '/product-detail.php') === 0) {
    $activeMenu = 'products';
} elseif (strpos($currentPath, '/gioi-thieu.php') === 0) {
    $activeMenu = 'about';
} elseif (strpos($currentPath, '/tin-tuc') === 0) {
    $activeMenu = 'news';
} elseif (strpos($currentPath, '/lien-he.php') === 0) {
    $activeMenu = 'contact';
} else {
    $activeMenu = '';
}

/*
 * Menu chính, khai báo tập trung để dễ thêm/bớt.
 * Thứ tự trong mảng chính là thứ tự hiển thị trên thanh menu.
 * KHÔNG khai báo 'cart' ở đây — xem ghi chú đầu file.
 */
$mainMenu = [
    [
        'key'   => 'home',
        'href'  => '/',
        'label' => 'Trang chủ',
        'icon'  => 'fa-solid fa-house',
    ],
    [
        'key'   => 'products',
        'href'  => '/products.php',
        'label' => 'Sản phẩm',
        'icon'  => 'fa-solid fa-layer-group',
    ],
    [
        'key'   => 'about',
        'href'  => '/gioi-thieu.php',
        'label' => 'Giới thiệu',
        'icon'  => 'fa-solid fa-circle-info',
    ],
    [
        'key'   => 'news',
        'href'  => '/tin-tuc.php',
        'label' => 'Tin tức',
        'icon'  => 'fa-regular fa-newspaper',
    ],
    [
        'key'   => 'contact',
        'href'  => '/lien-he.php',
        'label' => 'Liên hệ',
        'icon'  => 'fa-solid fa-envelope-open-text',
    ],
];

?>

<nav class="navbar navbar-expand bg-dark navbar-dark">

    <div class="container">

        <a class="navbar-brand" href="/">
            <span class="brand-mark">
                <?= htmlspecialchars($brand['initials']) ?>
            </span>
            <?= htmlspecialchars($brand['name']) ?>
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#frontendNavbar"
            aria-controls="frontendNavbar"
            aria-expanded="false"
            aria-label="Mở menu"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div
            class="collapse navbar-collapse"
            id="frontendNavbar"
        >

            <!-- ================= MENU CHÍNH ================= -->
            <!--
                me-auto đẩy nhóm tiện ích sang tận bên phải, tạo hai khối
                tách bạch thay vì dồn hết vào một cụm chật chội.
            -->
            <ul class="navbar-nav me-auto">

                <?php foreach ($mainMenu as $item): ?>

                    <li class="nav-item">

                        <a
                            class="nav-link <?=
                                $activeMenu === $item['key']
                                    ? 'active'
                                    : ''
                            ?>"
                            href="<?= htmlspecialchars($item['href']) ?>"
                            <?= $activeMenu === $item['key']
                                    ? 'aria-current="page"'
                                    : '' ?>
                        >
                            <i class="<?= htmlspecialchars($item['icon']) ?>"></i>
                            <?= htmlspecialchars($item['label']) ?>
                        </a>

                    </li>

                <?php endforeach; ?>

            </ul>

            <!-- ============== NHÓM TIỆN ÍCH BÊN PHẢI ============== -->
            <div class="navbar-tools d-flex align-items-center gap-2 mt-3 mt-xl-0">

                <a
                    class="btn btn-outline-light btn-sm"
                    href="/cart.php"
                >
                    <i class="fa-solid fa-cart-shopping"></i>
                    Giỏ hàng (<?= (int) $cartCount ?>)
                </a>

                <?php if ($isLoggedIn): ?>

                    <div class="dropdown">

                        <button
                            class="btn btn-outline-light btn-sm dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                        >
                            <i class="fa-solid fa-user"></i>
                            <?= htmlspecialchars($customerName) ?>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>
                                <a class="dropdown-item" href="/cart.php">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                    Giỏ hàng của tôi
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            <li>
                                <a class="dropdown-item" href="/logout.php">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    Đăng xuất
                                </a>
                            </li>

                        </ul>

                    </div>

                <?php else: ?>

                    <a
                        class="btn btn-outline-light btn-sm"
                        href="/login.php"
                    >
                        <i class="fa-solid fa-right-to-bracket"></i>
                        Đăng nhập
                    </a>

                    <a
                        class="btn btn-light btn-sm"
                        href="/register.php"
                    >
                        <i class="fa-solid fa-user-plus"></i>
                        Đăng ký
                    </a>

                <?php endif; ?>

                <a
                    class="btn btn-outline-light btn-sm"
                    href="/admin/"
                >
                    <i class="fa-solid fa-gauge-high"></i>
                    Quản trị
                </a>

            </div>

        </div>

    </div>

</nav>
