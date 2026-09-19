<?php

/*
 * Khởi tạo dùng chung cho khu vực bán hàng (storefront).
 *
 * Tách hoàn toàn khỏi khu vực quản trị: storefront chỉ nạp
 * Bootstrap Icons + storefront.css, không nạp Bootstrap CSS
 * để giữ giao diện nhẹ và khác biệt với dashboard quản trị.
 *
 * File này cung cấp:
 *   - Hằng số thông tin cửa hàng ($sfShop)
 *   - Menu điều hướng ($sfNav)
 *   - Các hàm định dạng/hiển thị riêng của storefront
 */

date_default_timezone_set('Asia/Ho_Chi_Minh');

require_once __DIR__ . '/../../includes/helpers.php';

/* ------------------------------------------------------------------
 * Thông tin cửa hàng
 * ------------------------------------------------------------------ */
if (!isset($sfShop)) {
    $sfShop = [
        'name'    => 'TechStore',
        'slogan'  => 'Công nghệ trong tầm tay',
        'hotline' => '1900 6868',
        'email'   => 'hotro@techstore.vn',
        'address' => '123 Nguyễn Văn Cừ, Phường 4, Quận 5, TP. Hồ Chí Minh',
        'hours'   => '08:00 - 21:30 (Tất cả các ngày)',
        'tax'     => '0312 345 678',
    ];
}

/* ------------------------------------------------------------------
 * Menu điều hướng chính
 * ------------------------------------------------------------------ */
if (!isset($sfNav)) {
    $sfNav = [
        ['label' => 'Trang chủ', 'href' => '/shop/',            'icon' => 'bi-house-door',   'key' => 'home'],
        ['label' => 'Sản phẩm',  'href' => '/shop/#san-pham',   'icon' => 'bi-grid',         'key' => 'products'],
        ['label' => 'Danh mục',  'href' => '/shop/#danh-muc',   'icon' => 'bi-diagram-3',    'key' => 'categories'],
        ['label' => 'Khuyến mãi','href' => '/shop/#khuyen-mai', 'icon' => 'bi-lightning-charge', 'key' => 'promo'],
        ['label' => 'Tin tức',   'href' => '/shop/#tin-tuc',    'icon' => 'bi-newspaper',    'key' => 'news'],
        ['label' => 'Liên hệ',   'href' => '/shop/#lien-he',    'icon' => 'bi-telephone',    'key' => 'contact'],
    ];
}

/* ------------------------------------------------------------------
 * Hàm hiển thị
 * ------------------------------------------------------------------ */

if (!function_exists('sf_image_url')) {
    /**
     * Đổi tên file ảnh trong product_images.ImageFile thành URL đầy đủ.
     * Trả về null nếu sản phẩm chưa có ảnh (khi đó giao diện dùng .sf-ph).
     */
    function sf_image_url(?string $file): ?string
    {
        if ($file === null || trim($file) === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $file)) {
            return $file;
        }

        return '/uploads/products/' . rawurlencode(basename($file));
    }
}

if (!function_exists('sf_category_icon')) {
    /**
     * Biểu tượng Bootstrap Icons theo tên danh mục.
     * Dùng cho thẻ danh mục và cho ảnh thay thế của sản phẩm.
     */
    function sf_category_icon(?string $name): string
    {
        $map = [
            'điện thoại'          => 'bi-phone',
            'máy tính'            => 'bi-pc-display',
            'laptop'              => 'bi-laptop',
            'tablet'              => 'bi-tablet',
            'tai nghe'            => 'bi-headset',
            'đồng hồ thông minh'  => 'bi-smartwatch',
            'màn hình'            => 'bi-display',
            'gaming'              => 'bi-controller',
            'linh kiện'           => 'bi-cpu',
            'phụ kiện'            => 'bi-mouse',
            'máy ảnh'             => 'bi-camera',
            'thiết bị mạng'       => 'bi-router',
        ];

        $key = mb_strtolower(trim((string) $name), 'UTF-8');

        return $map[$key] ?? 'bi-box-seam';
    }
}

if (!function_exists('sf_category_slug')) {
    /**
     * Khoá rút gọn của danh mục, dùng làm hậu tố class CSS
     * cho ảnh thay thế (.sf-ph-<slug>) để mỗi nhóm có tông màu riêng.
     */
    function sf_category_slug(?string $name): string
    {
        $map = [
            'điện thoại'          => 'phone',
            'máy tính'            => 'computer',
            'laptop'              => 'laptop',
            'tablet'              => 'tablet',
            'tai nghe'            => 'audio',
            'đồng hồ thông minh'  => 'watch',
            'màn hình'            => 'monitor',
            'gaming'              => 'gaming',
            'linh kiện'           => 'parts',
            'phụ kiện'            => 'accessory',
            'máy ảnh'             => 'camera',
            'thiết bị mạng'       => 'network',
        ];

        $key = mb_strtolower(trim((string) $name), 'UTF-8');

        return $map[$key] ?? 'default';
    }
}

if (!function_exists('sf_discount_percent')) {
    /**
     * Phần trăm giảm giá, làm tròn. Trả về 0 nếu không giảm giá.
     */
    function sf_discount_percent($price, $oldPrice): int
    {
        $price    = (float) $price;
        $oldPrice = (float) $oldPrice;

        if ($oldPrice <= 0 || $price <= 0 || $oldPrice <= $price) {
            return 0;
        }

        return (int) round((($oldPrice - $price) / $oldPrice) * 100);
    }
}

if (!function_exists('sf_stars')) {
    /**
     * Trả về mảng 5 phần tử: 'full' | 'half' | 'off' để render sao đánh giá.
     */
    function sf_stars($rating): array
    {
        $rating = max(0.0, min(5.0, (float) $rating));
        $stars  = [];

        for ($i = 1; $i <= 5; $i++) {
            if ($rating >= $i) {
                $stars[] = 'full';
            } elseif ($rating >= $i - 0.5) {
                $stars[] = 'half';
            } else {
                $stars[] = 'off';
            }
        }

        return $stars;
    }
}

if (!function_exists('sf_stars_html')) {
    /**
     * Render 5 ngôi sao bằng Bootstrap Icons.
     */
    function sf_stars_html($rating): string
    {
        $icons = [
            'full' => 'bi-star-fill',
            'half' => 'bi-star-half',
            'off'  => 'bi-star',
        ];

        $html = '<span class="sf-stars" aria-hidden="true">';

        foreach (sf_stars($rating) as $state) {
            $class = $state === 'off' ? ' class="is-off"' : '';
            $html .= '<i class="bi ' . $icons[$state] . '"' . $class . '></i>';
        }

        return $html . '</span>';
    }
}

if (!function_exists('sf_badges')) {
    /**
     * Xác định nhãn hiển thị trên ảnh sản phẩm.
     *
     * Trả về mảng các cặp [nhãn, class, icon]. Tối đa 2 nhãn để thẻ
     * không bị rối: một nhãn "trạng thái" và một nhãn "giảm giá".
     *
     * Nguyên tắc: **chỉ gắn nhãn khi có căn cứ thật trong dữ liệu**.
     * Thứ tự xét: Hết hàng > Hot (>= 50 đánh giá) > Bán chạy (đã bán
     * được hàng hoặc được đánh dấu nổi bật) > Mới (thuộc nhóm mới nhất).
     * Sản phẩm không có căn cứ nào thì không gắn nhãn — không bịa ra
     * nhãn cho đẹp.
     *
     * @param array $p       Dòng sản phẩm
     * @param array $options ['newest' => true] khi sản phẩm thuộc nhóm mới nhất
     */
    function sf_badges(array $p, array $options = []): array
    {
        $badges = [];

        if ((int) ($p['StockQuantity'] ?? 0) <= 0) {
            return [['Hết hàng', 'sf-badge-out', 'bi-x-circle']];
        }

        $discount    = sf_discount_percent($p['Price'] ?? 0, $p['OldPrice'] ?? 0);
        $reviewCount = (int) ($p['ReviewCount'] ?? 0);
        $isFeatured  = (bool) ($p['IsFeatured'] ?? false);

        /* SoldQuantity chỉ có khi dòng sản phẩm đi qua sf_fetch_best_sellers().
           Có bán được hàng thật thì mới được gọi là "Bán chạy". */
        $sold = (int) ($p['SoldQuantity'] ?? 0);

        if ($reviewCount >= 50) {
            $badges[] = ['Hot', 'sf-badge-hot', 'bi-fire'];
        } elseif ($sold > 0 || $isFeatured) {
            $badges[] = ['Bán chạy', 'sf-badge-best', 'bi-trophy'];
        } elseif (!empty($options['newest'])) {
            $badges[] = ['Mới', 'sf-badge-new', 'bi-stars'];
        }

        if ($discount > 0) {
            $badges[] = ['Giảm ' . $discount . '%', 'sf-badge-sale', 'bi-tag'];
        }

        return $badges;
    }
}

if (!function_exists('sf_render_badges')) {
    /**
     * Render khối nhãn trên ảnh sản phẩm. Không có nhãn nào thì trả về
     * chuỗi rỗng để không tạo thẻ div trống.
     */
    function sf_render_badges(array $p, array $options = []): string
    {
        $badges = sf_badges($p, $options);

        if ($badges === []) {
            return '';
        }

        $html = '<div class="sf-badges">';

        foreach ($badges as [$label, $class, $icon]) {
            $html .= '<span class="sf-badge ' . $class . '">'
                . '<i class="bi ' . $icon . '"></i>' . htmlspecialchars($label)
                . '</span>';
        }

        return $html . '</div>';
    }
}

if (!function_exists('sf_product_card')) {
    /**
     * Render một thẻ sản phẩm trong lưới.
     *
     * @param array  $p       Dòng sản phẩm (kèm CategoryName, ImageFile)
     * @param string $base    Đường dẫn gốc của storefront, ví dụ '/shop/'
     * @param array  $options ['newest' => true] khi sản phẩm thuộc nhóm mới nhất
     */
    function sf_product_card(array $p, string $base = '/shop/', array $options = []): string
    {
        $id       = (int) $p['ProductID'];
        $name     = (string) $p['ProductName'];
        $code     = (string) $p['ProductCode'];
        $catName  = (string) ($p['CategoryName'] ?? '');
        $price    = (float) $p['Price'];
        $oldPrice = $p['OldPrice'] !== null ? (float) $p['OldPrice'] : 0.0;
        $rating   = $p['Rating'] !== null ? (float) $p['Rating'] : 0.0;
        $reviews  = (int) ($p['ReviewCount'] ?? 0);
        $stock    = (int) ($p['StockQuantity'] ?? 0);

        $discount = sf_discount_percent($price, $oldPrice);
        $image    = sf_image_url($p['ImageFile'] ?? null);
        $href     = $base . 'product.php?id=' . $id;

        ob_start();
        ?>
        <article class="sf-card">
            <div class="sf-card-media">
                <?= sf_render_badges($p, $options) ?>

                <button
                    type="button"
                    class="sf-wish"
                    data-wish="<?= $id ?>"
                    aria-label="Thêm <?= htmlspecialchars($name) ?> vào yêu thích"
                    title="Thêm vào yêu thích"
                ><i class="bi bi-heart"></i></button>

                <a href="<?= $href ?>" aria-label="<?= htmlspecialchars($name) ?>">
                    <?php if ($image !== null): ?>
                        <img
                            src="<?= htmlspecialchars($image) ?>"
                            alt="<?= htmlspecialchars($name) ?>"
                            loading="lazy"
                        >
                    <?php else: ?>
                        <span class="sf-ph sf-ph-<?= sf_category_slug($catName) ?>">
                            <i class="bi <?= sf_category_icon($catName) ?>"></i>
                            <span class="sf-ph-code"><?= htmlspecialchars($code) ?></span>
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <div class="sf-card-body">
                <h3 class="sf-card-name">
                    <a href="<?= $href ?>"><?= htmlspecialchars($name) ?></a>
                </h3>

                <div class="sf-card-code">Mã: <?= htmlspecialchars($code) ?></div>

                <div class="sf-rating">
                    <?= sf_stars_html($rating) ?>
                    <span class="sf-rating-count">
                        <?= $reviews > 0
                            ? number_format($rating, 1, ',', '.') . ' (' . number_format($reviews, 0, ',', '.') . ')'
                            : 'Chưa có đánh giá' ?>
                    </span>
                </div>

                <div class="sf-card-price">
                    <span class="sf-price"><?= vnd($price) ?></span>
                    <?php if ($oldPrice > $price): ?>
                        <span class="sf-price-old"><?= vnd($oldPrice) ?></span>
                        <span class="sf-discount">-<?= $discount ?>%</span>
                    <?php endif; ?>
                </div>

                <div class="sf-card-stock<?= $stock > 0 && $stock <= 10 ? ' is-low' : '' ?>">
                    <?php if ($stock <= 0): ?>
                        <i class="bi bi-x-circle"></i> Tạm hết hàng
                    <?php elseif ($stock <= 10): ?>
                        <i class="bi bi-exclamation-triangle"></i> Chỉ còn <?= $stock ?> sản phẩm
                    <?php else: ?>
                        <i class="bi bi-check2-circle"></i> Còn <?= $stock ?> sản phẩm
                    <?php endif; ?>
                </div>

                <div class="sf-card-cta">
                    <button
                        type="button"
                        class="sf-btn sf-btn-primary"
                        data-add-cart="<?= $id ?>"
                        data-add-cart-name="<?= htmlspecialchars($name) ?>"
                        data-add-cart-price="<?= (int) $price ?>"
                        data-add-cart-code="<?= htmlspecialchars($code) ?>"
                        <?= $stock <= 0 ? 'disabled' : '' ?>
                    >
                        <i class="bi bi-cart-plus"></i>
                        <?= $stock <= 0 ? 'Hết hàng' : 'Thêm vào giỏ' ?>
                    </button>
                </div>
            </div>
        </article>
        <?php
        return (string) ob_get_clean();
    }
}

if (!function_exists('sf_fetch_products')) {
    /**
     * Lấy danh sách sản phẩm kèm tên danh mục và ảnh chính.
     *
     * Ảnh chính lấy bằng truy vấn con thay vì JOIN: sản phẩm chưa có
     * ảnh vẫn hiện ra (giao diện dùng .sf-ph), JOIN sẽ làm mất dòng.
     *
     * @param mysqli $conn
     * @param string $where  Mệnh đề WHERE (không kèm từ khoá WHERE), dùng ? cho tham số
     * @param string $types  Chuỗi kiểu bind_param, ví dụ 'ss'
     * @param array  $params Tham số tương ứng
     * @param int    $limit  Số dòng tối đa, 0 = không giới hạn
     * @param string $order  Mệnh đề ORDER BY
     *
     * @return array<int, array<string, mixed>>
     */
    function sf_fetch_products(
        mysqli $conn,
        string $where = '1',
        string $types = '',
        array $params = [],
        int $limit = 8,
        string $order = 'p.ProductID DESC'
    ): array {
        $sql = "SELECT
                    p.ProductID,
                    p.ProductCode,
                    p.ProductName,
                    p.Description,
                    p.Unit,
                    p.Price,
                    p.OldPrice,
                    p.Rating,
                    p.ReviewCount,
                    p.StockQuantity,
                    p.IsActive,
                    p.IsFeatured,
                    p.CategoryID,
                    p.SupplierID,
                    c.CategoryName,
                    (SELECT pi.ImageFile
                       FROM product_images pi
                      WHERE pi.ProductID = p.ProductID
                      ORDER BY pi.IsPrimary DESC, pi.SortOrder ASC, pi.ProductImageID ASC
                      LIMIT 1) AS ImageFile
                FROM products p
                LEFT JOIN categories c ON c.CategoryID = p.CategoryID
                WHERE " . $where . "
                ORDER BY " . $order;

        if ($limit > 0) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            return [];
        }

        if ($types !== '' && $params !== []) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}

if (!function_exists('sf_fetch_best_sellers')) {
    /**
     * Sản phẩm bán chạy — xếp hạng theo số lượng đã bán thật trong
     * bảng orderdetail, KHÔNG dựa vào cột IsFeatured do người dùng tự bật.
     *
     * Sản phẩm chưa bán được lần nào vẫn nằm trong kết quả (LEFT JOIN)
     * và được xếp sau, lần lượt theo IsFeatured, số đánh giá, rồi mã mới
     * nhất. Nhờ vậy khối "Sản phẩm nổi bật" luôn có nội dung thật kể cả
     * khi cửa hàng chưa phát sinh đơn hàng nào.
     *
     * @return array<int, array<string, mixed>> Mỗi dòng có thêm khoá SoldQuantity
     */
    function sf_fetch_best_sellers(mysqli $conn, int $limit = 8): array
    {
        if ($limit <= 0) {
            return [];
        }

        $sql = "SELECT
                    p.ProductID,
                    p.ProductCode,
                    p.ProductName,
                    p.Description,
                    p.Unit,
                    p.Price,
                    p.OldPrice,
                    p.Rating,
                    p.ReviewCount,
                    p.StockQuantity,
                    p.IsActive,
                    p.IsFeatured,
                    p.CategoryID,
                    p.SupplierID,
                    c.CategoryName,
                    COALESCE(SUM(od.Quantity), 0) AS SoldQuantity,
                    (SELECT pi.ImageFile
                       FROM product_images pi
                      WHERE pi.ProductID = p.ProductID
                      ORDER BY pi.IsPrimary DESC, pi.SortOrder ASC, pi.ProductImageID ASC
                      LIMIT 1) AS ImageFile
                FROM products p
                LEFT JOIN categories c ON c.CategoryID = p.CategoryID
                LEFT JOIN orderdetail od ON od.ProductID = p.ProductID
                WHERE p.IsActive = 1
                GROUP BY p.ProductID
                ORDER BY SoldQuantity DESC,
                         p.IsFeatured DESC,
                         p.ReviewCount DESC,
                         p.ProductID DESC
                LIMIT ?";

        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            return [];
        }

        $stmt->bind_param('i', $limit);
        $stmt->execute();

        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}

if (!function_exists('sf_fetch_newest_ids')) {
    /**
     * Tập ID của N sản phẩm mới nhất, dùng để gắn nhãn "Mới".
     * Trả về mảng [ProductID => true] để tra cứu nhanh.
     *
     * Hiện chưa nơi nào gọi: khối "Sản phẩm mới nhất" đã tự biết toàn bộ
     * danh sách của nó là mới nên chỉ cần truyền ['newest' => true].
     * Giữ lại vì vẫn hữu ích khi cần gắn nhãn "Mới" trên một danh sách
     * trộn (ví dụ lưới sản phẩm chung).
     *
     * @return array<int, bool>
     */
    function sf_fetch_newest_ids(mysqli $conn, int $limit = 8): array
    {
        if ($limit <= 0) {
            return [];
        }

        $sql = "SELECT ProductID
                  FROM products
                 WHERE IsActive = 1
                 ORDER BY ProductID DESC
                 LIMIT ?";

        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            return [];
        }

        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $ids = [];

        while ($row = $result->fetch_assoc()) {
            $ids[(int) $row['ProductID']] = true;
        }

        $stmt->close();

        return $ids;
    }
}

/* ------------------------------------------------------------------
 * Tin tức
 *
 * Schema hiện tại chưa có bảng bài viết nên phần này là nội dung mẫu
 * khai báo tĩnh — KHÔNG phải dữ liệu đọc từ MySQL.
 *
 * Cố ý chọn các chủ đề tư vấn/kinh nghiệm chung, không nhắc tới sản phẩm
 * cụ thể nào, để không tạo cảm giác cửa hàng đang bán những mặt hàng
 * thực tế không có trong database. Ngày được tính lùi từ hôm nay nên
 * không bị cũ.
 *
 * Khi bổ sung bảng `posts`, chỉ cần thay biến này bằng truy vấn MySQL,
 * phần render trong index.php giữ nguyên.
 * ------------------------------------------------------------------ */
if (!isset($sfPosts)) {
    $sfPosts = [
        [
            'cat'   => 'Hướng dẫn sử dụng',
            'icon'  => 'bi-cpu',
            'date'  => date('Y-m-d', strtotime('-4 days')),
            'title' => 'Chọn thiết bị theo nhu cầu: học tập, văn phòng hay đồ hoạ?',
            'desc'  => 'Mỗi nhu cầu cần một cấu hình khác nhau. Bài viết phân tích những thông số thực sự ảnh hưởng tới trải nghiệm và gợi ý cách cân đối ngân sách.',
        ],
        [
            'cat'   => 'Tư vấn mua hàng',
            'icon'  => 'bi-shield-check',
            'date'  => date('Y-m-d', strtotime('-9 days')),
            'title' => 'Mua thiết bị công nghệ cũ: nên kiểm tra những gì?',
            'desc'  => 'Từ tình trạng pin, số lần sạc đến khả năng còn bảo hành — danh sách kiểm tra giúp bạn tránh mua phải thiết bị đã qua sửa chữa.',
        ],
        [
            'cat'   => 'Kinh nghiệm',
            'icon'  => 'bi-battery-charging',
            'date'  => date('Y-m-d', strtotime('-15 days')),
            'title' => '5 thói quen giúp pin thiết bị bền hơn theo thời gian',
            'desc'  => 'Giới hạn mức sạc, tránh nhiệt độ cao và tắt ứng dụng chạy nền là những thay đổi nhỏ nhưng giúp tuổi thọ pin tăng đáng kể.',
        ],
        [
            'cat'   => 'Chính sách',
            'icon'  => 'bi-patch-check',
            'date'  => date('Y-m-d', strtotime('-22 days')),
            'title' => 'Chính sách bảo hành, đổi trả và vận chuyển tại TechStore',
            'desc'  => 'Tổng hợp điều kiện bảo hành, thời hạn đổi trả và phạm vi giao hàng để bạn yên tâm khi mua sắm.',
        ],
    ];
}
