<?php

$pageTitle = 'Quản lý đơn hàng';

require_once '/var/www/src/config/database.php';
require_once '/var/www/src/includes/helpers.php';

/*
 * Chỉ số tổng hợp cho phần đầu trang.
 */
$sqlStats = "
    SELECT
        (SELECT COUNT(*) FROM orders) AS TotalOrders,
        (SELECT COALESCE(SUM(Quantity * UnitPrice), 0) FROM orderdetail)
            AS TotalRevenue,
        (SELECT COALESCE(SUM(Quantity), 0) FROM orderdetail)
            AS TotalItems
";

$statsResult = $conn->query($sqlStats);
$stats = $statsResult ? $statsResult->fetch_assoc() : [];

$totalOrders = (int) ($stats['TotalOrders'] ?? 0);
$totalRevenue = (float) ($stats['TotalRevenue'] ?? 0);
$totalItems = (int) ($stats['TotalItems'] ?? 0);

$averageOrder = $totalOrders > 0
    ? $totalRevenue / $totalOrders
    : 0.0;

/*
 * Danh sách đơn hàng kèm tổng tiền.
 * LEFT JOIN để đơn chưa có mặt hàng vẫn hiển thị.
 */
$sql = "
    SELECT
        o.OrderID,
        o.OrderDate,
        cu.CustomerName,
        CONCAT(e.LastName, ' ', e.FirstName) AS EmployeeName,
        sh.ShipperName,
        COALESCE(SUM(od.Quantity), 0) AS TotalItems,
        COALESCE(SUM(od.Quantity * od.UnitPrice), 0) AS OrderTotal
    FROM orders AS o
    INNER JOIN customers AS cu
        ON cu.CustomerID = o.CustomerID
    INNER JOIN employees AS e
        ON e.EmployeeID = o.EmployeeID
    INNER JOIN shippers AS sh
        ON sh.ShipperID = o.ShipperID
    LEFT JOIN orderdetail AS od
        ON od.OrderID = o.OrderID
    GROUP BY
        o.OrderID,
        o.OrderDate,
        cu.CustomerName,
        e.LastName,
        e.FirstName,
        sh.ShipperName
    ORDER BY o.OrderDate DESC, o.OrderID DESC
";

$result = $conn->query($sql);

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="page">

    <div class="page-head">

        <div>
            <h2 class="page-title">
                <i class="fa-solid fa-receipt"></i>
                Quản lý đơn hàng
            </h2>
            <p class="page-subtitle">
                Theo dõi đơn hàng, khách mua và đơn vị vận chuyển
            </p>
        </div>

        <div class="page-head-actions">

            <a href="/orders/create.php" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i>
                Tạo đơn hàng
            </a>

        </div>

    </div>

    <div class="stat-grid">

        <div class="stat-card">

            <div class="stat-top">
                <span class="stat-label">Tổng đơn hàng</span>
                <span class="stat-icon is-success">
                    <i class="fa-solid fa-receipt"></i>
                </span>
            </div>

            <div class="stat-value">
                <?= number_format($totalOrders, 0, ',', '.') ?>
            </div>

            <div class="stat-meta">
                <i class="fa-solid fa-box-open"></i>
                <?= format_quantity($totalItems) ?> mặt hàng đã bán
            </div>

        </div>

        <div class="stat-card">

            <div class="stat-top">
                <span class="stat-label">Doanh thu</span>
                <span class="stat-icon is-success">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </span>
            </div>

            <div class="stat-value stat-value-sm">
                <?= vnd($totalRevenue) ?>
            </div>

            <div class="stat-meta">
                <i class="fa-solid fa-chart-line"></i>
                Trên toàn bộ đơn hàng
            </div>

        </div>

        <div class="stat-card">

            <div class="stat-top">
                <span class="stat-label">Giá trị trung bình</span>
                <span class="stat-icon is-info">
                    <i class="fa-solid fa-calculator"></i>
                </span>
            </div>

            <div class="stat-value stat-value-sm">
                <?= vnd($averageOrder) ?>
            </div>

            <div class="stat-meta">
                <i class="fa-solid fa-percent"></i>
                Mỗi đơn hàng
            </div>

        </div>

    </div>

    <div class="table-responsive">

        <table class="table align-middle">

            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Khách hàng</th>
                    <th>Nhân viên</th>
                    <th>Vận chuyển</th>
                    <th class="text-end">Mặt hàng</th>
                    <th class="text-end">Tổng tiền</th>
                    <th>Thao tác</th>
                </tr>
            </thead>

            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($order = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <span class="code-chip">
                                #<?= (int) $order['OrderID'] ?>
                            </span>
                        </td>

                        <td class="cell-muted">
                            <?= htmlspecialchars(
                                format_date($order['OrderDate'])
                            ) ?>
                        </td>

                        <td class="cell-strong">
                            <?= htmlspecialchars($order['CustomerName']) ?>
                        </td>

                        <td class="cell-muted">
                            <?= htmlspecialchars($order['EmployeeName']) ?>
                        </td>

                        <td class="cell-muted">
                            <?= htmlspecialchars($order['ShipperName']) ?>
                        </td>

                        <td class="text-end">
                            <?= format_quantity(
                                (float) $order['TotalItems']
                            ) ?>
                        </td>

                        <td class="text-end">
                            <?= vnd((float) $order['OrderTotal']) ?>
                        </td>

                        <td>

                            <a
                                href="/orders/show.php?id=<?= (int) $order['OrderID'] ?>"
                                class="btn btn-sm btn-outline-primary"
                                title="Xem chi tiết"
                            >
                                <i class="fa-solid fa-eye"></i>
                                Xem
                            </a>

                            <a
                                href="/orders/edit.php?id=<?= (int) $order['OrderID'] ?>"
                                class="btn btn-sm btn-outline-primary"
                                title="Sửa đơn hàng"
                            >
                                <i class="fa-solid fa-pen"></i>
                                Sửa
                            </a>

                            <form
                                action="/orders/delete.php"
                                method="post"
                                class="d-inline"
                                onsubmit="return confirm('Bạn có chắc muốn xóa đơn hàng này? Toàn bộ mặt hàng trong đơn cũng bị xóa.');"
                            >
                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $order['OrderID'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn btn-sm btn-outline-danger"
                                    title="Xóa đơn hàng"
                                >
                                    <i class="fa-solid fa-trash"></i>
                                    Xóa
                                </button>
                            </form>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="8" class="empty-cell">

                        <div class="empty-state">
                            <i class="fa-solid fa-receipt"></i>
                            <p>Chưa có đơn hàng nào trong hệ thống.</p>
                            <a href="/orders/create.php" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-plus"></i>
                                Tạo đơn hàng đầu tiên
                            </a>
                        </div>

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<?php

require_once '/var/www/src/includes/admin/footer.php';

$conn->close();
