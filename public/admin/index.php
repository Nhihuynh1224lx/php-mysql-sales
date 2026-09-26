<?php
/*
 * TRANG TỔNG QUAN (dashboard) của khu vực quản trị.
 *
 * Trang này gom các con số quan trọng nhất của cửa hàng vào một chỗ:
 *   - 4 thẻ số liệu lớn: doanh thu, đơn hàng, sản phẩm, khách hàng
 *   - Số đơn theo từng trạng thái (Chờ xử lý / Hoàn thành / Đã hủy)
 *   - 6 đơn hàng mới nhất, có liên kết sang trang chi tiết
 *   - Cảnh báo sản phẩm sắp hết hàng
 *
 * QUY ƯỚC TRUY VẤN: theo đúng quy ước của môn học, các bảng được nối bằng
 * cách liệt kê trong FROM rồi đặt điều kiện khoá ngoại ở WHERE
 * (không dùng JOIN ... ON).
 */

$pageTitle = 'Tổng quan';

require_once '/var/www/src/config/database.php';
require_once '/var/www/src/includes/helpers.php';

/*
 * Đặt múi giờ Việt Nam trước khi tính "hôm nay" / "tháng này".
 * header.php cũng đặt lại, nhưng ở đây cần đúng ngay từ lúc truy vấn.
 */
date_default_timezone_set('Asia/Ho_Chi_Minh');

/* ------------------------------------------------------------------
 * 1. Các con số tổng quan
 * ------------------------------------------------------------------ */

$totalRevenue = (float) $conn
    ->query("
        SELECT COALESCE(SUM(TotalAmount), 0) AS Total
        FROM orders
        WHERE Status <> 'Cancelled'
    ")
    ->fetch_assoc()['Total'];

$totalOrders = (int) $conn
    ->query("SELECT COUNT(*) AS Total FROM orders")
    ->fetch_assoc()['Total'];

$totalProducts = (int) $conn
    ->query("SELECT COUNT(*) AS Total FROM products WHERE IsActive = 1")
    ->fetch_assoc()['Total'];

$totalCustomers = (int) $conn
    ->query("SELECT COUNT(*) AS Total FROM customers")
    ->fetch_assoc()['Total'];

/* Doanh thu chỉ tính trong tháng hiện tại, để thẻ số liệu có thêm ngữ cảnh */
$monthRevenue = (float) $conn
    ->query("
        SELECT COALESCE(SUM(TotalAmount), 0) AS Total
        FROM orders
        WHERE Status <> 'Cancelled'
          AND YEAR(OrderDate) = YEAR(CURDATE())
          AND MONTH(OrderDate) = MONTH(CURDATE())
    ")
    ->fetch_assoc()['Total'];

/* Số đơn đặt trong tháng hiện tại */
$monthOrders = (int) $conn
    ->query("
        SELECT COUNT(*) AS Total
        FROM orders
        WHERE YEAR(OrderDate) = YEAR(CURDATE())
          AND MONTH(OrderDate) = MONTH(CURDATE())
    ")
    ->fetch_assoc()['Total'];

/* ------------------------------------------------------------------
 * 2. Số đơn theo từng trạng thái
 * ------------------------------------------------------------------ */

$statusCounts = [
    'Pending'   => 0,
    'Completed' => 0,
    'Cancelled' => 0,
];

$statusResult = $conn->query("
    SELECT Status, COUNT(*) AS Total
    FROM orders
    GROUP BY Status
");

while ($row = $statusResult->fetch_assoc()) {
    $statusCounts[$row['Status']] = (int) $row['Total'];
}

$statusResult->free();

/*
 * Nhãn tiếng Việt và màu badge cho từng trạng thái.
 * Giữ đúng bộ giá trị mà module Đơn hàng đang dùng.
 */
$statusLabels = [
    'Pending'   => ['label' => 'Chờ xử lý',  'badge' => 'text-bg-warning'],
    'Completed' => ['label' => 'Hoàn thành', 'badge' => 'text-bg-success'],
    'Cancelled' => ['label' => 'Đã hủy',     'badge' => 'text-bg-danger'],
];

/* ------------------------------------------------------------------
 * 3. Sáu đơn hàng mới nhất
 * ------------------------------------------------------------------
 * CustomerID là NOT NULL nên nối bảng customers bằng INNER JOIN là an toàn.
 * EmployeeID / ShipperID cho phép NULL (đơn online chưa phân công) nên
 * KHÔNG nối tới hai bảng đó ở đây.
 */

$recentOrders = $conn->query("
    SELECT
        o.OrderID,
        o.OrderDate,
        o.TotalAmount,
        o.Status,
        c.CustomerName

    FROM
        orders o,
        customers c

    WHERE
        o.CustomerID = c.CustomerID

    ORDER BY
        o.OrderID DESC

    LIMIT 6
");

/* ------------------------------------------------------------------
 * 4. Sản phẩm sắp hết hàng (tồn kho thấp nhất)
 * ------------------------------------------------------------------ */

$lowStock = $conn->query("
    SELECT
        p.ProductID,
        p.ProductCode,
        p.ProductName,
        p.StockQuantity

    FROM
        products p

    WHERE
        p.IsActive = 1
        AND p.StockQuantity <= 10

    ORDER BY
        p.StockQuantity ASC,
        p.ProductName ASC

    LIMIT 6
");

$lowStockCount = (int) $conn
    ->query("
        SELECT COUNT(*) AS Total
        FROM products
        WHERE IsActive = 1 AND StockQuantity <= 10
    ")
    ->fetch_assoc()['Total'];

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h2 class="mb-1">Tổng quan</h2>

            <p class="text-muted mb-0">
                Tình hình bán hàng tính đến
                <?= date('d/m/Y H:i') ?>.
            </p>

        </div>

    </div>

    <!-- ============================================================
         BỐN THẺ SỐ LIỆU LỚN
         ============================================================ -->
    <div class="row g-3 mb-4">

        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 stat-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <p class="stat-card-label">
                                Doanh thu
                            </p>

                            <h3 class="stat-card-value">
                                <?= vnd($totalRevenue) ?>
                            </h3>

                            <p class="stat-card-hint">
                                Tháng này: <?= vnd($monthRevenue) ?>
                            </p>

                        </div>

                        <span class="stat-card-icon stat-card-icon--revenue">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </span>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 stat-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <p class="stat-card-label">
                                Đơn hàng
                            </p>

                            <h3 class="stat-card-value">
                                <?= $totalOrders ?>
                            </h3>

                            <p class="stat-card-hint">
                                Tháng này: <?= $monthOrders ?>
                            </p>

                        </div>

                        <span class="stat-card-icon stat-card-icon--order">
                            <i class="fa-solid fa-receipt"></i>
                        </span>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 stat-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <p class="stat-card-label">
                                Sản phẩm
                            </p>

                            <h3 class="stat-card-value">
                                <?= $totalProducts ?>
                            </h3>

                            <p class="stat-card-hint">
                                Đang bán
                            </p>

                        </div>

                        <span class="stat-card-icon stat-card-icon--product">
                            <i class="fa-solid fa-box-open"></i>
                        </span>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-sm-6 col-xl-3">

            <div class="card h-100 stat-card">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start">

                        <div>

                            <p class="stat-card-label">
                                Khách hàng
                            </p>

                            <h3 class="stat-card-value">
                                <?= $totalCustomers ?>
                            </h3>

                            <p class="stat-card-hint">
                                Đã đăng ký
                            </p>

                        </div>

                        <span class="stat-card-icon stat-card-icon--customer">
                            <i class="fa-solid fa-users"></i>
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- ============================================================
         ĐƠN THEO TRẠNG THÁI + TRUY CẬP NHANH
         ============================================================ -->
    <div class="row g-3 mb-4">

        <div class="col-lg-7">

            <div class="card h-100">

                <div class="card-header">

                    <span>
                        <i class="fa-solid fa-chart-simple me-1"></i>
                        Đơn hàng theo trạng thái
                    </span>

                </div>

                <div class="card-body">

                    <?php foreach ($statusLabels as $key => $info): ?>

                        <?php
                        $count = $statusCounts[$key] ?? 0;

                        /* Tỉ lệ phần trăm trên tổng số đơn, tránh chia cho 0 */
                        $percent = $totalOrders > 0
                            ? round($count * 100 / $totalOrders)
                            : 0;
                        ?>

                        <div class="status-row">

                            <div class="d-flex justify-content-between align-items-center mb-1">

                                <span class="status-row-label">
                                    <?= htmlspecialchars($info['label']) ?>
                                </span>

                                <span class="status-row-count">
                                    <?= $count ?> đơn
                                    <span class="text-muted">
                                        (<?= $percent ?>%)
                                    </span>
                                </span>

                            </div>

                            <div class="progress status-bar">

                                <div
                                    class="progress-bar <?= $info['badge'] ?>"
                                    role="progressbar"
                                    style="width: <?= $percent ?>%;"
                                    aria-valuenow="<?= $percent ?>"
                                    aria-valuemin="0"
                                    aria-valuemax="100"
                                ></div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                    <div class="d-flex gap-2 mt-3">

                        <a href="/admin/orders/" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-list me-1"></i>
                            Xem tất cả đơn hàng
                        </a>

                        <?php if (($statusCounts['Pending'] ?? 0) > 0): ?>

                            <span class="btn btn-outline-warning btn-sm disabled">
                                <i class="fa-solid fa-clock me-1"></i>
                                <?= $statusCounts['Pending'] ?> đơn chờ xử lý
                            </span>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-5">

            <div class="card h-100">

                <div class="card-header">

                    <span>
                        <i class="fa-solid fa-bolt me-1"></i>
                        Truy cập nhanh
                    </span>

                </div>

                <div class="card-body">

                    <div class="row g-2">

                        <div class="col-6">
                            <a
                                href="/admin/products/create.php"
                                class="quick-link"
                            >
                                <i class="fa-solid fa-plus"></i>
                                <span>Thêm sản phẩm</span>
                            </a>
                        </div>

                        <div class="col-6">
                            <a
                                href="/admin/employees/create.php"
                                class="quick-link"
                            >
                                <i class="fa-solid fa-user-plus"></i>
                                <span>Thêm nhân viên</span>
                            </a>
                        </div>

                        <div class="col-6">
                            <a
                                href="/admin/customers/"
                                class="quick-link"
                            >
                                <i class="fa-solid fa-users"></i>
                                <span>Khách hàng</span>
                            </a>
                        </div>

                        <div class="col-6">
                            <a
                                href="/admin/categories/"
                                class="quick-link"
                            >
                                <i class="fa-solid fa-tags"></i>
                                <span>Danh mục</span>
                            </a>
                        </div>

                        <div class="col-6">
                            <a
                                href="/admin/suppliers/"
                                class="quick-link"
                            >
                                <i class="fa-solid fa-truck-field"></i>
                                <span>Nhà cung cấp</span>
                            </a>
                        </div>

                        <div class="col-6">
                            <a
                                href="/admin/shippers/"
                                class="quick-link"
                            >
                                <i class="fa-solid fa-truck"></i>
                                <span>Giao hàng</span>
                            </a>
                        </div>

                    </div>

                    <hr>

                    <a
                        href="/"
                        class="btn btn-outline-secondary btn-sm w-100"
                    >
                        <i class="fa-solid fa-store me-1"></i>
                        Mở cửa hàng
                    </a>

                </div>

            </div>

        </div>

    </div>

    <!-- ============================================================
         ĐƠN HÀNG MỚI NHẤT + SẢN PHẨM SẮP HẾT HÀNG
         ============================================================ -->
    <div class="row g-3">

        <div class="col-lg-7">

            <div class="card h-100">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <span>
                        <i class="fa-solid fa-clock-rotate-left me-1"></i>
                        Đơn hàng mới nhất
                    </span>

                    <a
                        href="/admin/orders/"
                        class="small text-decoration-none"
                    >
                        Xem tất cả
                    </a>

                </div>

                <div class="table-responsive">

                    <table class="table table-bordered table-striped mb-0">

                        <thead class="table-dark">

                            <tr>
                                <th>Mã đơn</th>
                                <th>Khách hàng</th>
                                <th>Ngày đặt</th>
                                <th class="text-end">Tổng tiền</th>
                                <th class="text-center">Trạng thái</th>
                                <th class="text-center">Thao tác</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if ($recentOrders && $recentOrders->num_rows > 0): ?>

                                <?php while ($order = $recentOrders->fetch_assoc()): ?>

                                    <?php
                                    $info = $statusLabels[$order['Status']]
                                        ?? [
                                            'label' => $order['Status'],
                                            'badge' => 'text-bg-secondary',
                                        ];
                                    ?>

                                    <tr>

                                        <td>
                                            #<?= (int) $order['OrderID'] ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $order['CustomerName']
                                            ) ?>
                                        </td>

                                        <td class="text-nowrap">
                                            <?=
                                                htmlspecialchars(
                                                    $order['OrderDate']
                                                )
                                            ?>
                                        </td>

                                        <td class="text-end text-nowrap">
                                            <?= vnd((float) $order['TotalAmount']) ?>
                                        </td>

                                        <td class="text-center text-nowrap">

                                            <span class="badge <?= $info['badge'] ?>">
                                                <?= htmlspecialchars($info['label']) ?>
                                            </span>

                                        </td>

                                        <td class="text-center text-nowrap">

                                            <a
                                                href="/admin/orders/detail.php?id=<?=
                                                    (int) $order['OrderID']
                                                ?>"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                Chi tiết
                                            </a>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Chưa có đơn hàng nào.
                                    </td>
                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="col-lg-5">

            <div class="card h-100">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <span>
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Sản phẩm sắp hết hàng
                    </span>

                    <span class="badge text-bg-warning">
                        <?= $lowStockCount ?>
                    </span>

                </div>

                <div class="card-body">

                    <?php if ($lowStock && $lowStock->num_rows > 0): ?>

                        <ul class="lowstock-list">

                            <?php while ($item = $lowStock->fetch_assoc()): ?>

                                <?php
                                /* Tồn kho <= 3 coi như cạn, tô đỏ để dễ nhận ra */
                                $isCritical = (int) $item['StockQuantity'] <= 3;
                                ?>

                                <li class="lowstock-item">

                                    <div class="lowstock-info">

                                        <a
                                            href="/admin/products/edit.php?id=<?=
                                                (int) $item['ProductID']
                                            ?>"
                                            class="lowstock-name"
                                        >
                                            <?= htmlspecialchars(
                                                $item['ProductName']
                                            ) ?>
                                        </a>

                                        <span class="lowstock-code">
                                            <?= htmlspecialchars(
                                                $item['ProductCode']
                                            ) ?>
                                        </span>

                                    </div>

                                    <span class="badge <?=
                                        $isCritical
                                            ? 'text-bg-danger'
                                            : 'text-bg-warning'
                                    ?>">
                                        còn <?= (int) $item['StockQuantity'] ?>
                                    </span>

                                </li>

                            <?php endwhile; ?>

                        </ul>

                        <a
                            href="/admin/products/"
                            class="btn btn-outline-secondary btn-sm w-100 mt-3"
                        >
                            Quản lý kho hàng
                        </a>

                    <?php else: ?>

                        <p class="text-muted mb-0">
                            <i class="fa-solid fa-circle-check text-success me-1"></i>
                            Tất cả sản phẩm đều còn đủ hàng.
                        </p>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>

<?php
require_once '/var/www/src/includes/admin/footer.php';
