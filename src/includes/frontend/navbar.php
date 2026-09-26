<?php

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
} elseif (strpos($currentPath, '/cart.php') === 0) {
    $activeMenu = 'cart';
} elseif (strpos($currentPath, '/gioi-thieu.php') === 0) {
    $activeMenu = 'about';
} elseif (strpos($currentPath, '/tin-tuc') === 0) {
    $activeMenu = 'news';
} elseif (strpos($currentPath, '/lien-he.php') === 0) {
    $activeMenu = 'contact';
} else {
    $activeMenu = '';
}

?>

<nav class="navbar navbar-expand-lg bg-dark navbar-dark">

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

            <ul class="navbar-nav">

                <li class="nav-item">
                    <a
                        class="nav-link <?=
                            $activeMenu === 'home'
                                ? 'active'
                                : ''
                        ?>"
                        href="/"
                    >
                        <i class="fa-solid fa-house"></i>
                        Trang chủ
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link <?=
                            $activeMenu === 'products'
                                ? 'active'
                                : ''
                        ?>"
                        href="/products.php"
                    >
                        <i class="fa-solid fa-layer-group"></i>
                        Sản phẩm
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link <?=
                            $activeMenu === 'about'
                                ? 'active'
                                : ''
                        ?>"
                        href="/gioi-thieu.php"
                    >
                        <i class="fa-solid fa-circle-info"></i>
                        Giới thiệu
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link <?=
                            $activeMenu === 'news'
                                ? 'active'
                                : ''
                        ?>"
                        href="/tin-tuc.php"
                    >
                        <i class="fa-regular fa-newspaper"></i>
                        Tin tức
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link <?=
                            $activeMenu === 'contact'
                                ? 'active'
                                : ''
                        ?>"
                        href="/lien-he.php"
                    >
                        <i class="fa-solid fa-envelope-open-text"></i>
                        Liên hệ
                    </a>
                </li>

                <li class="nav-item">
                    <a
                        class="nav-link <?=
                            $activeMenu === 'cart'
                                ? 'active'
                                : ''
                        ?>"
                        href="/cart.php"
                    >
                        <i class="fa-solid fa-cart-shopping"></i>
                        Giỏ hàng
                    </a>
                </li>

            </ul>

            <!-- Nhóm tiện ích bên phải: tài khoản + giỏ + lối vào quản trị -->
            <div class="d-flex align-items-center gap-2 ms-auto mt-3 mt-lg-0">

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
