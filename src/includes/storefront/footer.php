<?php

/*
 * Chân trang khu vực bán hàng.
 *
 * Đóng <main>, in khối footer 5 cột (thương hiệu + 4 cột thông tin),
 * thanh bản quyền, nút lên đầu trang, khay thông báo và nạp storefront.js.
 */

require_once __DIR__ . '/init.php';

$sfYear = date('Y');

?>
</main>

<!-- ============ Footer ============ -->
<footer class="sf-footer" id="lien-he">
    <div class="sf-container">
        <div class="sf-footer-grid">

            <!-- Cột 1: thương hiệu -->
            <div>
                <div class="sf-footer-brand">
                    <span class="sf-logo-mark"><i class="bi bi-lightning-charge-fill"></i></span>
                    <span class="sf-logo-text">
                        <span class="sf-logo-name">Tech<span>Store</span></span>
                        <span class="sf-logo-slogan"><?= htmlspecialchars($sfShop['slogan']) ?></span>
                    </span>
                </div>

                <p class="sf-footer-about">
                    Hệ thống bán lẻ thiết bị công nghệ chính hãng: laptop, điện thoại,
                    tablet, tai nghe và phụ kiện. Cam kết giá tốt mỗi ngày cùng dịch vụ
                    hậu mãi tận tâm.
                </p>

                <div class="sf-socials">
                    <a class="sf-social" href="#" aria-label="Facebook TechStore"><i class="bi bi-facebook"></i></a>
                    <a class="sf-social" href="#" aria-label="Zalo TechStore"><i class="bi bi-chat-dots-fill"></i></a>
                    <a class="sf-social" href="#" aria-label="YouTube TechStore"><i class="bi bi-youtube"></i></a>
                    <a class="sf-social" href="#" aria-label="Instagram TechStore"><i class="bi bi-instagram"></i></a>
                </div>
            </div>

            <!-- Cột 2 -->
            <div>
                <h3 class="sf-footer-title">Thông tin cửa hàng</h3>
                <ul class="sf-footer-links">
                    <li><a href="/shop/gioi-thieu.php">Giới thiệu</a></li>
                    <li><a href="/shop/ve-techstore.php">Về TechStore</a></li>
                    <li><a href="/shop/he-thong-cua-hang.php">Hệ thống cửa hàng</a></li>
                    <li><a href="/shop/lien-he.php">Liên hệ</a></li>
                </ul>
            </div>

            <!-- Cột 3 -->
            <div>
                <h3 class="sf-footer-title">Chính sách</h3>
                <ul class="sf-footer-links">
                    <li><a href="/shop/chinh-sach-bao-hanh.php">Chính sách bảo hành</a></li>
                    <li><a href="/shop/chinh-sach-doi-tra.php">Chính sách đổi trả</a></li>
                    <li><a href="/shop/chinh-sach-van-chuyen.php">Chính sách vận chuyển</a></li>
                    <li><a href="/shop/chinh-sach-bao-mat.php">Chính sách bảo mật</a></li>
                </ul>
            </div>

            <!-- Cột 4 -->
            <div>
                <h3 class="sf-footer-title">Hỗ trợ khách hàng</h3>
                <ul class="sf-footer-links">
                    <li><a href="/shop/huong-dan-mua-hang.php">Hướng dẫn mua hàng</a></li>
                    <li><a href="/shop/phuong-thuc-thanh-toan.php">Phương thức thanh toán</a></li>
                    <li><a href="/shop/cau-hoi-thuong-gap.php">Câu hỏi thường gặp</a></li>
                    <li><a href="/shop/tra-cuu-don-hang.php">Tra cứu đơn hàng</a></li>
                </ul>
            </div>

            <!-- Cột 5 -->
            <div>
                <h3 class="sf-footer-title">Liên hệ</h3>
                <ul class="sf-footer-contact">
                    <li>
                        <i class="bi bi-geo-alt-fill"></i>
                        <span><?= htmlspecialchars($sfShop['address']) ?></span>
                    </li>
                    <li>
                        <i class="bi bi-telephone-fill"></i>
                        <span>
                            Hotline:
                            <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $sfShop['hotline'])) ?>"><?= htmlspecialchars($sfShop['hotline']) ?></a>
                        </span>
                    </li>
                    <li>
                        <i class="bi bi-envelope-fill"></i>
                        <span>
                            Email:
                            <a href="mailto:<?= htmlspecialchars($sfShop['email']) ?>"><?= htmlspecialchars($sfShop['email']) ?></a>
                        </span>
                    </li>
                    <li>
                        <i class="bi bi-clock-fill"></i>
                        <span><?= htmlspecialchars($sfShop['hours']) ?></span>
                    </li>
                    <li>
                        <i class="bi bi-receipt"></i>
                        <span>MST: <?= htmlspecialchars($sfShop['tax']) ?></span>
                    </li>
                </ul>
            </div>

        </div>

        <div class="sf-footer-bottom">
            <div>
                &copy; <?= $sfYear ?> <?= htmlspecialchars($sfShop['name']) ?>. All Rights Reserved.
            </div>

            <div class="sf-payments">
                <span class="sf-payment">VISA</span>
                <span class="sf-payment">Mastercard</span>
                <span class="sf-payment">ATM nội địa</span>
                <span class="sf-payment">COD</span>
                <span class="sf-payment">Ví điện tử</span>
            </div>
        </div>
    </div>
</footer>

<!-- ============ Nút lên đầu trang ============ -->
<button type="button" class="sf-top" id="sfTop" aria-label="Lên đầu trang">
    <i class="bi bi-arrow-up"></i>
</button>

<!-- ============ Khay thông báo ============ -->
<div class="sf-toast" id="sfToast" role="status" aria-live="polite">
    <i class="bi bi-check-circle-fill"></i>
    <span id="sfToastText"></span>
</div>

<script src="/assets/js/storefront.js" defer></script>
</body>
</html>
