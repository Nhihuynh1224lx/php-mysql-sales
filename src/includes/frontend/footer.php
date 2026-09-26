<?php

require_once '/var/www/src/config/brand.php';

?>

<footer class="site-footer">

    <div class="container">

        <div class="row g-4 site-footer-top">

            <div class="col-lg-4">

                <a class="site-footer-brand" href="/">
                    <span class="brand-mark">
                        <?= htmlspecialchars($brand['initials']) ?>
                    </span>
                    <?= htmlspecialchars($brand['name']) ?>
                </a>

                <p class="site-footer-text">
                    <?= htmlspecialchars($brand['about']) ?>
                </p>

                <div class="site-footer-social">

                    <a href="<?= htmlspecialchars($brand['facebook']) ?>" aria-label="Facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>

                    <a href="<?= htmlspecialchars($brand['youtube']) ?>" aria-label="YouTube">
                        <i class="fa-brands fa-youtube"></i>
                    </a>

                    <a href="mailto:<?= htmlspecialchars($brand['email']) ?>" aria-label="Email">
                        <i class="fa-solid fa-envelope"></i>
                    </a>

                    <a href="<?= htmlspecialchars($brand['phone']) ?>" aria-label="Điện thoại">
                        <i class="fa-solid fa-phone"></i>
                    </a>

                </div>

            </div>

            <div class="col-6 col-lg-2">

                <h6 class="site-footer-heading">Mua sắm</h6>

                <ul class="site-footer-list">
                    <li><a href="/">Trang chủ</a></li>
                    <li><a href="/products.php">Sản phẩm</a></li>
                    <li><a href="/cart.php">Giỏ hàng</a></li>
                    <li><a href="/checkout.php">Đặt hàng</a></li>
                </ul>

            </div>

            <div class="col-6 col-lg-2">

                <h6 class="site-footer-heading">Giới thiệu</h6>

                <ul class="site-footer-list">
                    <li><a href="/gioi-thieu.php">Về chúng tôi</a></li>
                    <li><a href="/tin-tuc.php">Tin tức</a></li>
                    <li><a href="/lien-he.php">Liên hệ</a></li>
                    <li><a href="/admin/">Quản trị</a></li>
                </ul>

            </div>

            <div class="col-lg-4">

                <h6 class="site-footer-heading">Liên hệ</h6>

                <ul class="site-footer-list site-footer-contact">
                    <li>
                        <i class="fa-solid fa-location-dot"></i>
                        <span><?= htmlspecialchars($brand['address']) ?></span>
                    </li>
                    <li>
                        <i class="fa-solid fa-phone"></i>
                        <span><?= htmlspecialchars($brand['phone']) ?></span>
                    </li>
                    <li>
                        <i class="fa-solid fa-envelope"></i>
                        <span><?= htmlspecialchars($brand['email']) ?></span>
                    </li>
                    <li>
                        <i class="fa-solid fa-clock"></i>
                        <span><?= htmlspecialchars($brand['hours']) ?></span>
                    </li>
                </ul>

            </div>

        </div>

    </div>

    <div class="site-footer-bottom">

        <div class="container">

            <span>
                &copy; <?= date('Y') ?>
                <?= htmlspecialchars($brand['name']) ?> —
                <?= htmlspecialchars($brand['tagline']) ?>.
            </span>

            <span class="site-footer-tech">
                Xây dựng bằng PHP &amp; MySQL
            </span>

        </div>

    </div>

</footer>

</body>
</html>
