<?php

require_once '/var/www/src/config/session.php';
require_once '/var/www/src/config/database.php';

/* ------------------------------------------------------------------
 * 1. Danh mục nổi bật (hiển thị ở khối "Danh mục sản phẩm")
 * ------------------------------------------------------------------ */

$sqlCategories = "
    SELECT
        c.CategoryID,
        c.CategoryName,
        c.Description,

        (
            SELECT COUNT(*)
            FROM products p
            WHERE p.CategoryID = c.CategoryID
              AND p.IsActive = 1
        ) AS TotalProducts

    FROM
        categories c

    ORDER BY
        c.CategoryName
";

$categoryResult = $conn->query($sqlCategories);

$categories = [];

while ($category = $categoryResult->fetch_assoc()) {
    $categories[] = $category;
}

$categoryResult->free();

/* ------------------------------------------------------------------
 * 2. Sản phẩm mới nhất (8 sản phẩm)
 * ------------------------------------------------------------------
 * Ảnh chính lấy bằng truy vấn con, cùng cách làm với products.php.
 */

$sqlProducts = "
    SELECT
        p.ProductID,
        p.ProductCode,
        p.ProductName,
        p.Price,
        c.CategoryName,

        (
            SELECT pi.ImageFile
            FROM product_images pi
            WHERE pi.ProductID = p.ProductID
              AND pi.IsPrimary = 1
            LIMIT 1
        ) AS ImageFile

    FROM
        products p,
        categories c

    WHERE
        p.CategoryID = c.CategoryID
        AND p.IsActive = 1

    ORDER BY
        p.ProductID DESC

    LIMIT 8
";

$productResult = $conn->query($sqlProducts);

/* ------------------------------------------------------------------
 * 3. Một vài con số tổng quan cho khối thống kê
 * ------------------------------------------------------------------ */

$totalProducts = (int) $conn
    ->query("SELECT COUNT(*) AS Total FROM products WHERE IsActive = 1")
    ->fetch_assoc()['Total'];

$totalCategories = count($categories);

$pageTitle = 'Trang chủ';

require_once '/var/www/src/includes/frontend/header.php';
require_once '/var/www/src/includes/frontend/navbar.php';
?>

<!-- ============================================================
     KHỐI GIỚI THIỆU ĐẦU TRANG
     ============================================================ -->
<section class="hero">

    <div class="container">

        <div class="row align-items-center g-5">

            <div class="col-lg-7">

                <span class="hero-badge">
                    <i class="fa-solid fa-award"></i>
                    Cửa hàng dụng cụ bida uy tín
                </span>

                <h1 class="hero-title">
                    Trang thiết bị bida
                    <span class="hero-highlight">
                        chính hãng
                    </span>
                </h1>

                <p class="hero-text">
                    Bàn bida, cơ, bao cơ, lơ và phụ kiện đến từ những
                    thương hiệu hàng đầu. Sản phẩm được tuyển chọn kỹ,
                    bảo hành đầy đủ, giao hàng toàn quốc.
                </p>

                <div class="hero-actions">

                    <a href="/products.php" class="btn btn-light btn-lg">
                        <i class="fa-solid fa-bag-shopping"></i>
                        Mua sắm ngay
                    </a>

                    <a
                        href="/products.php?category=1"
                        class="btn btn-outline-light btn-lg"
                    >
                        Xem bàn bida
                    </a>

                </div>

            </div>

            <div class="col-lg-5">

                <div class="row g-3 hero-stats">

                    <div class="col-6">
                        <div class="hero-stat">
                            <span class="hero-stat-value">
                                <?= $totalProducts ?>
                            </span>
                            <span class="hero-stat-label">
                                Sản phẩm
                            </span>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="hero-stat">
                            <span class="hero-stat-value">
                                <?= $totalCategories ?>
                            </span>
                            <span class="hero-stat-label">
                                Danh mục
                            </span>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="hero-stat">
                            <span class="hero-stat-value">
                                100%
                            </span>
                            <span class="hero-stat-label">
                                Chính hãng
                            </span>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="hero-stat">
                            <span class="hero-stat-value">
                                24/7
                            </span>
                            <span class="hero-stat-label">
                                Hỗ trợ
                            </span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

<!-- ============================================================
     KHỐI CAM KẾT DỊCH VỤ
     ============================================================ -->
<section class="container py-5">

    <div class="row g-4">

        <div class="col-md-6 col-lg-3">
            <div class="feature-card">
                <span class="feature-icon">
                    <i class="fa-solid fa-truck-fast"></i>
                </span>
                <h3 class="feature-title">Giao hàng toàn quốc</h3>
                <p class="feature-text">
                    Miễn phí vận chuyển cho đơn hàng từ 5 triệu đồng.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="feature-card">
                <span class="feature-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
                <h3 class="feature-title">Bảo hành chính hãng</h3>
                <p class="feature-text">
                    Mọi sản phẩm đều có phiếu bảo hành từ nhà cung cấp.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="feature-card">
                <span class="feature-icon">
                    <i class="fa-solid fa-rotate-left"></i>
                </span>
                <h3 class="feature-title">Đổi trả 7 ngày</h3>
                <p class="feature-text">
                    Đổi mới nếu sản phẩm lỗi do nhà sản xuất.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="feature-card">
                <span class="feature-icon">
                    <i class="fa-solid fa-headset"></i>
                </span>
                <h3 class="feature-title">Tư vấn tận tâm</h3>
                <p class="feature-text">
                    Đội ngũ nhiều năm kinh nghiệm trong ngành bida.
                </p>
            </div>
        </div>

    </div>

</section>

<!-- ============================================================
     KHỐI DANH MỤC SẢN PHẨM
     ============================================================ -->
<section class="container py-4">

    <div class="section-heading">

        <div>
            <h2 class="section-title">Danh mục sản phẩm</h2>
            <p class="section-subtitle">
                Chọn nhóm sản phẩm bạn đang quan tâm.
            </p>
        </div>

        <a href="/products.php" class="section-link">
            Xem tất cả
            <i class="fa-solid fa-arrow-right"></i>
        </a>

    </div>

    <div class="row g-4">

        <?php foreach ($categories as $category): ?>

            <div class="col-6 col-md-4 col-lg-2">

                <a
                    class="category-card"
                    href="/products.php?category=<?=
                        (int) $category['CategoryID']
                    ?>"
                >

                    <span class="category-name">
                        <?=
                            htmlspecialchars(
                                $category['CategoryName']
                            )
                        ?>
                    </span>

                    <span class="category-count">
                        <?= (int) $category['TotalProducts'] ?>
                        sản phẩm
                    </span>

                </a>

            </div>

        <?php endforeach; ?>

    </div>

</section>

<!-- ============================================================
     KHỐI SẢN PHẨM MỚI NHẤT
     ============================================================ -->
<section class="container py-5">

    <div class="section-heading">

        <div>
            <h2 class="section-title">Sản phẩm mới nhất</h2>
            <p class="section-subtitle">
                Những sản phẩm vừa được cập nhật tại cửa hàng.
            </p>
        </div>

        <a href="/products.php" class="section-link">
            Xem tất cả
            <i class="fa-solid fa-arrow-right"></i>
        </a>

    </div>

    <?php if ($productResult->num_rows > 0): ?>

        <div class="row g-4">

            <?php while ($product = $productResult->fetch_assoc()): ?>

                <div class="col-6 col-lg-3">

                    <div class="card h-100">

                        <?php if (!empty($product['ImageFile'])): ?>

                            <div class="product-thumb">

                                <img
                                    src="/uploads/products/<?=
                                        htmlspecialchars(
                                            $product['ImageFile']
                                        )
                                    ?>"
                                    alt="<?=
                                        htmlspecialchars(
                                            $product['ProductName']
                                        )
                                    ?>"
                                >

                            </div>

                        <?php else: ?>

                            <div class="product-thumb is-empty">
                                Chưa có hình ảnh
                            </div>

                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">

                            <p class="text-muted small mb-1">
                                <?=
                                    htmlspecialchars(
                                        $product['CategoryName']
                                    )
                                ?>
                            </p>

                            <h5 class="card-title">
                                <?=
                                    htmlspecialchars(
                                        $product['ProductName']
                                    )
                                ?>
                            </h5>

                            <p class="text-muted small">
                                Mã sản phẩm:
                                <?=
                                    htmlspecialchars(
                                        $product['ProductCode']
                                    )
                                ?>
                            </p>

                            <p class="fw-bold fs-5 mb-3">
                                <?=
                                    number_format(
                                        (float) $product['Price'],
                                        0,
                                        ',',
                                        '.'
                                    )
                                ?> đ
                            </p>

                            <a
                                href="/product-detail.php?id=<?=
                                    (int) $product['ProductID']
                                ?>"
                                class="btn btn-outline-primary mt-auto"
                            >
                                Xem chi tiết
                            </a>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="alert alert-info">
            Hiện chưa có sản phẩm nào để hiển thị.
        </div>

    <?php endif; ?>

</section>

<!-- ============================================================
     KHỐI KÊU GỌI HÀNH ĐỘNG
     ============================================================ -->
<section class="container pb-5">

    <div class="cta-band">

        <div>
            <h2 class="cta-title">
                Cần tư vấn chọn bàn hoặc cơ phù hợp?
            </h2>
            <p class="cta-text">
                Liên hệ đội ngũ của chúng tôi để được hỗ trợ nhanh nhất.
            </p>
        </div>

        <div class="cta-actions">

            <a href="/products.php" class="btn btn-light btn-lg">
                Xem sản phẩm
            </a>

            <a href="/cart.php" class="btn btn-outline-light btn-lg">
                <i class="fa-solid fa-cart-shopping"></i>
                Giỏ hàng
            </a>

        </div>

    </div>

</section>

<?php
require_once '/var/www/src/includes/frontend/footer.php';
