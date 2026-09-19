<?php

/*
 * Đầu trang khu vực bán hàng (storefront).
 *
 * Trang gọi có thể đặt trước khi include:
 *   $pageTitle      Tiêu đề <title>
 *   $sfActive       Khoá menu đang active: home|products|categories|promo|news|contact
 *   $sfCartCount    Số sản phẩm trong giỏ
 *   $sfCartTotal    Tổng tiền trong giỏ
 *   $sfWishCount    Số sản phẩm yêu thích
 *   $sfKeyword      Từ khoá đang tìm (đổ lại vào ô tìm kiếm)
 *   $sfCategories   Mảng danh mục cho menu "Danh mục sản phẩm"
 *
 * Chỉ nạp Bootstrap Icons, KHÔNG nạp Bootstrap CSS: storefront dùng
 * hệ thống thiết kế riêng trong storefront.css để không giống dashboard.
 */

require_once __DIR__ . '/init.php';

if (!isset($pageTitle) || $pageTitle === '') {
    $pageTitle = $sfShop['name'] . ' — ' . $sfShop['slogan'];
}

$sfActive    = $sfActive    ?? 'home';
$sfCartCount = (int) ($sfCartCount ?? 0);
$sfCartTotal = (float) ($sfCartTotal ?? 0);
$sfWishCount = (int) ($sfWishCount ?? 0);
$sfKeyword   = (string) ($sfKeyword ?? '');
$sfCategories = $sfCategories ?? [];

$sfTel = preg_replace('/\s+/', '', $sfShop['hotline']);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($sfShop['name'] . ' — ' . $sfShop['slogan'] . '. Thiết bị công nghệ chính hãng, giá tốt mỗi ngày.') ?>">
    <meta name="theme-color" content="#1259e8">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet"
    >

    <link href="/assets/css/storefront.css" rel="stylesheet">
</head>

<body class="sf-body">

<a class="sf-skip" href="#sf-main">Bỏ qua điều hướng, đến nội dung chính</a>

<!-- ============ Thanh tiện ích ============ -->
<div class="sf-topbar">
    <div class="sf-container sf-topbar-inner">
        <ul class="sf-topbar-list">
            <li>
                <i class="bi bi-telephone-fill"></i>
                Hotline: <a href="tel:<?= htmlspecialchars($sfTel) ?>"><?= htmlspecialchars($sfShop['hotline']) ?></a>
            </li>
            <li><i class="bi bi-truck"></i> Miễn phí giao hàng cho đơn từ 500.000đ</li>
            <li><i class="bi bi-shield-check"></i> Bảo hành chính hãng đến 24 tháng</li>
        </ul>

        <ul class="sf-topbar-list">
            <li><i class="bi bi-clock"></i> <?= htmlspecialchars($sfShop['hours']) ?></li>
            <li>
                <a class="sf-topbar-admin" href="/">
                    <i class="bi bi-speedometer2"></i> Trang quản trị
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- ============ Header ============ -->
<header class="sf-header" id="sfHeader">
    <div class="sf-container sf-header-main">

        <a class="sf-logo" href="/shop/">
            <span class="sf-logo-mark"><i class="bi bi-lightning-charge-fill"></i></span>
            <span class="sf-logo-text">
                <span class="sf-logo-name">Tech<span>Store</span></span>
                <span class="sf-logo-slogan"><?= htmlspecialchars($sfShop['slogan']) ?></span>
            </span>
        </a>

        <div class="sf-search">
            <form class="sf-search-form" action="/shop/" method="get" role="search">
                <span class="sf-search-icon"><i class="bi bi-search"></i></span>
                <input
                    class="sf-search-input"
                    type="search"
                    name="q"
                    value="<?= htmlspecialchars($sfKeyword) ?>"
                    placeholder="Nhập tên sản phẩm, mã sản phẩm..."
                    aria-label="Tìm kiếm sản phẩm"
                    autocomplete="off"
                >
                <button class="sf-search-submit" type="submit">Tìm kiếm</button>
            </form>
        </div>

        <div class="sf-actions">

            <a class="sf-action sf-action-account" href="/shop/login.php">
                <span class="sf-action-icon"><i class="bi bi-person"></i></span>
                <span class="sf-action-text">
                    <span class="sf-action-label">Tài khoản</span>
                    <span class="sf-action-value">Đăng nhập</span>
                </span>
            </a>

            <a class="sf-action sf-action-wish" href="/shop/wishlist.php">
                <span class="sf-action-icon">
                    <i class="bi bi-heart"></i>
                    <span class="sf-count" id="sfWishCount"<?= $sfWishCount <= 0 ? ' hidden' : '' ?>><?= $sfWishCount ?></span>
                </span>
                <span class="sf-action-text">
                    <span class="sf-action-label">Yêu thích</span>
                    <span class="sf-action-value"><?= $sfWishCount > 0 ? $sfWishCount . ' sản phẩm' : 'Trống' ?></span>
                </span>
            </a>

            <a class="sf-action sf-action-cart" href="/shop/cart.php">
                <span class="sf-action-icon">
                    <i class="bi bi-cart3"></i>
                    <span class="sf-count" id="sfCartCount"<?= $sfCartCount <= 0 ? ' hidden' : '' ?>><?= $sfCartCount ?></span>
                </span>
                <span class="sf-action-text">
                    <span class="sf-action-label">Giỏ hàng</span>
                    <span class="sf-action-value" id="sfCartTotal"><?= $sfCartCount > 0 ? vnd($sfCartTotal) : 'Trống' ?></span>
                </span>
            </a>

            <button
                type="button"
                class="sf-burger"
                id="sfBurger"
                aria-label="Mở menu"
                aria-expanded="false"
                aria-controls="sfMobileNav"
            ><i class="bi bi-list"></i></button>

        </div>
    </div>
</header>

<!-- ============ Menu điều hướng ============ -->
<nav class="sf-nav" aria-label="Điều hướng chính">
    <div class="sf-container sf-nav-inner">

        <button
            type="button"
            class="sf-nav-all"
            id="sfMegaToggle"
            aria-expanded="false"
            aria-controls="sfMega"
        >
            <i class="bi bi-grid-3x3-gap-fill"></i>
            Danh mục sản phẩm
            <i class="bi bi-chevron-down"></i>
        </button>

        <ul class="sf-nav-links">
            <?php foreach ($sfNav as $item): ?>
                <li>
                    <a
                        class="sf-nav-link<?= $sfActive === $item['key'] ? ' is-active' : '' ?>"
                        href="<?= htmlspecialchars($item['href']) ?>"
                    ><?= htmlspecialchars($item['label']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="sf-hotline">
            <span class="sf-hotline-icon"><i class="bi bi-headset"></i></span>
            <span class="sf-hotline-text">
                <span class="sf-hotline-label">Tư vấn miễn phí</span>
                <a class="sf-hotline-number" href="tel:<?= htmlspecialchars($sfTel) ?>"><?= htmlspecialchars($sfShop['hotline']) ?></a>
            </span>
        </div>
    </div>

    <?php if (!empty($sfCategories)): ?>
        <div class="sf-mega" id="sfMega">
            <div class="sf-container">
                <div class="sf-mega-grid">
                    <?php foreach ($sfCategories as $cat): ?>
                        <a class="sf-mega-item" href="/shop/?cat=<?= (int) $cat['CategoryID'] ?>">
                            <span class="sf-mega-icon">
                                <i class="bi <?= sf_category_icon($cat['CategoryName']) ?>"></i>
                            </span>
                            <span>
                                <span class="sf-mega-name"><?= htmlspecialchars($cat['CategoryName']) ?></span>
                                <span class="sf-mega-count"><?= (int) ($cat['ProductCount'] ?? 0) ?> sản phẩm</span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</nav>

<!-- ============ Menu mobile ============ -->
<div class="sf-mobile-nav" id="sfMobileNav">
    <div class="sf-container">
        <?php foreach ($sfNav as $item): ?>
            <a href="<?= htmlspecialchars($item['href']) ?>">
                <i class="bi <?= htmlspecialchars($item['icon']) ?>"></i>
                <?= htmlspecialchars($item['label']) ?>
            </a>
        <?php endforeach; ?>

        <a href="/shop/cart.php">
            <i class="bi bi-cart3"></i> Giỏ hàng (<?= $sfCartCount ?>)
        </a>
        <a href="/shop/wishlist.php">
            <i class="bi bi-heart"></i> Yêu thích (<?= $sfWishCount ?>)
        </a>
        <a href="/">
            <i class="bi bi-speedometer2"></i> Trang quản trị
        </a>
    </div>
</div>

<main id="sf-main">
