<?php

require_once '/var/www/src/config/session.php';
require_once '/var/www/src/config/database.php';
require_once '/var/www/src/config/brand.php';
require_once '/var/www/src/includes/helpers.php';

/* ------------------------------------------------------------------
 * Vài con số thật lấy từ cơ sở dữ liệu để trang có dữ liệu cụ thể
 * thay vì những lời giới thiệu chung chung.
 * ------------------------------------------------------------------ */

$totalProducts = (int) $conn
    ->query("SELECT COUNT(*) AS Total FROM products WHERE IsActive = 1")
    ->fetch_assoc()['Total'];

$totalCategories = (int) $conn
    ->query("SELECT COUNT(*) AS Total FROM categories")
    ->fetch_assoc()['Total'];

$totalCustomers = (int) $conn
    ->query("SELECT COUNT(*) AS Total FROM customers")
    ->fetch_assoc()['Total'];

$totalSuppliers = (int) $conn
    ->query("SELECT COUNT(*) AS Total FROM suppliers")
    ->fetch_assoc()['Total'];

$pageTitle = 'Giới thiệu';

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
                    Giới thiệu
                </li>

            </ol>

        </nav>

        <h1 class="page-hero-title">
            Về <?= htmlspecialchars($brand['name']) ?>
        </h1>

        <p class="page-hero-text">
            <?= htmlspecialchars($brand['tagline']) ?> —
            đồng hành cùng người chơi bida Việt Nam.
        </p>

    </div>

</section>

<!-- ============================================================
     CÂU CHUYỆN THƯƠNG HIỆU
     ============================================================ -->
<section class="container py-5">

    <div class="row g-4 g-lg-5 align-items-center">

        <div class="col-lg-6">

            <h2 class="section-title text-start">
                Chúng tôi là ai
            </h2>

            <p class="about-text">
                <strong><?= htmlspecialchars($brand['name']) ?></strong>
                là cửa hàng chuyên cung cấp trang thiết bị bida chính hãng:
                bàn bida, cơ, bao cơ, lơ, bóng, găng tay và phụ kiện.
                Chúng tôi phục vụ từ những quán bida mới mở cho đến
                các câu lạc bộ thi đấu chuyên nghiệp.
            </p>

            <p class="about-text">
                Mỗi sản phẩm bán ra đều được kiểm tra kỹ trước khi giao,
                đi kèm phiếu bảo hành từ nhà cung cấp và đội ngũ tư vấn
                có nhiều năm kinh nghiệm trong ngành.
            </p>

            <p class="about-text mb-0">
                Bên cạnh việc bán hàng, chúng tôi tư vấn chọn bàn theo
                diện tích mặt bằng, chọn cơ theo lối chơi, và hướng dẫn
                bảo quản để thiết bị bền lâu.
            </p>

        </div>

        <div class="col-lg-6">

            <div class="row g-3">

                <div class="col-6">
                    <div class="about-stat">
                        <span class="about-stat-value">
                            <?= $totalProducts ?>
                        </span>
                        <span class="about-stat-label">
                            Sản phẩm đang bán
                        </span>
                    </div>
                </div>

                <div class="col-6">
                    <div class="about-stat">
                        <span class="about-stat-value">
                            <?= $totalCategories ?>
                        </span>
                        <span class="about-stat-label">
                            Nhóm sản phẩm
                        </span>
                    </div>
                </div>

                <div class="col-6">
                    <div class="about-stat">
                        <span class="about-stat-value">
                            <?= $totalSuppliers ?>
                        </span>
                        <span class="about-stat-label">
                            Nhà cung cấp
                        </span>
                    </div>
                </div>

                <div class="col-6">
                    <div class="about-stat">
                        <span class="about-stat-value">
                            <?= $totalCustomers ?>
                        </span>
                        <span class="about-stat-label">
                            Khách hàng
                        </span>
                    </div>
                </div>

            </div>

        </div>

    </div>

</section>

<!-- ============================================================
     GIÁ TRỊ CỐT LÕI
     ============================================================ -->
<section class="about-band">

    <div class="container py-5">

        <div class="section-heading section-heading--center">

            <div>
                <h2 class="section-title section-title--center text-center">
                    Vì sao chọn chúng tôi
                </h2>
                <p class="section-subtitle text-center">
                    Bốn điều chúng tôi luôn giữ đúng với mọi đơn hàng.
                </p>
            </div>

        </div>

        <div class="row g-4">

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <span class="feature-icon">
                        <i class="fa-solid fa-certificate"></i>
                    </span>
                    <h3 class="feature-title">Hàng chính hãng</h3>
                    <p class="feature-text">
                        Nhập trực tiếp từ nhà phân phối, có phiếu bảo hành
                        đầy đủ.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <span class="feature-icon">
                        <i class="fa-solid fa-comments"></i>
                    </span>
                    <h3 class="feature-title">Tư vấn đúng nhu cầu</h3>
                    <p class="feature-text">
                        Gợi ý theo mặt bằng và ngân sách, không chạy theo
                        sản phẩm đắt tiền.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <span class="feature-icon">
                        <i class="fa-solid fa-truck-fast"></i>
                    </span>
                    <h3 class="feature-title">Giao hàng toàn quốc</h3>
                    <p class="feature-text">
                        Đóng gói cẩn thận, miễn phí vận chuyển cho đơn
                        từ 5 triệu đồng.
                    </p>
                </div>
            </div>

            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <span class="feature-icon">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </span>
                    <h3 class="feature-title">Hỗ trợ sau bán</h3>
                    <p class="feature-text">
                        Hướng dẫn lắp đặt, bảo quản và xử lý khi thiết bị
                        gặp sự cố.
                    </p>
                </div>
            </div>

        </div>

    </div>

</section>

<!-- ============================================================
     QUY TRÌNH MUA HÀNG
     ============================================================ -->
<section class="container py-5">

    <div class="section-heading">

        <div>
            <h2 class="section-title">Quy trình mua hàng</h2>
            <p class="section-subtitle">
                Bốn bước đơn giản từ lúc chọn hàng đến khi nhận hàng.
            </p>
        </div>

    </div>

    <div class="row g-4">

        <div class="col-md-6 col-lg-3">
            <div class="step-card">
                <span class="step-number">1</span>
                <h3 class="step-title">Chọn sản phẩm</h3>
                <p class="step-text">
                    Xem danh mục hoặc tìm theo tên, mã sản phẩm.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="step-card">
                <span class="step-number">2</span>
                <h3 class="step-title">Thêm vào giỏ</h3>
                <p class="step-text">
                    Chọn số lượng rồi thêm vào giỏ hàng của bạn.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="step-card">
                <span class="step-number">3</span>
                <h3 class="step-title">Điền thông tin</h3>
                <p class="step-text">
                    Nhập tên, số điện thoại và địa chỉ nhận hàng.
                </p>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="step-card">
                <span class="step-number">4</span>
                <h3 class="step-title">Nhận hàng</h3>
                <p class="step-text">
                    Chúng tôi xác nhận đơn và giao đến tận nơi.
                </p>
            </div>
        </div>

    </div>

</section>

<!-- ============================================================
     DẢI KÊU GỌI HÀNH ĐỘNG
     ============================================================ -->
<section class="container pb-5">

    <div class="cta-band">

        <div>
            <h2 class="cta-title">
                Cần tư vấn thêm trước khi mua?
            </h2>
            <p class="cta-text">
                Gọi <?= htmlspecialchars($brand['phone']) ?>
                hoặc gửi tin nhắn cho chúng tôi.
            </p>
        </div>

        <div class="cta-actions">

            <a href="/lien-he.php" class="btn btn-light btn-lg">
                <i class="fa-solid fa-envelope-open-text"></i>
                Liên hệ ngay
            </a>

            <a href="/products.php" class="btn btn-outline-light btn-lg">
                Xem sản phẩm
            </a>

        </div>

    </div>

</section>

<?php
require_once '/var/www/src/includes/frontend/footer.php';
