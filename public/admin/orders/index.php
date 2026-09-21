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

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h2>Quản lý đơn hàng</h2>

        <a href="/admin/orders/create.php" class="btn btn-primary">
            Tạo đơn hàng
        </a>

    </div>

    <!-- Ba thẻ số liệu tổng quan, dùng thẻ (card) có sẵn của Bootstrap -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="text-muted">
                        Tổng đơn hàng
                    </div>

                    <div class="fs-3 fw-bold">
                        <?= number_format($totalOrders, 0, ',', '.') ?>
                    </div>

                    <div class="text-muted small">
                        <?= format_quantity($totalItems) ?> mặt hàng đã bán
                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="text-muted">
                        Doanh thu
                    </div>

                    <div class="fs-5 fw-bold">
                        <?= vnd($totalRevenue) ?>
                    </div>

                    <div class="text-muted small">
                        Trên toàn bộ đơn hàng
                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card h-100">

                <div class="card-body">

                    <div class="text-muted">
                        Giá trị trung bình
                    </div>

                    <div class="fs-5 fw-bold">
                        <?= vnd($averageOrder) ?>
                    </div>

                    <div class="text-muted small">
                        Mỗi đơn hàng
                    </div>

                </div>

            </div>

        </div>

    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-striped">

            <thead class="table-dark">

                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Khách hàng</th>
                    <th>Nhân viên</th>
                    <th>Vận chuyển</th>
                    <th class="text-end">Mặt hàng</th>
                    <th class="text-end">Tổng tiền</th>
                    <th class="text-nowrap">Thao tác</th>
                </tr>

            </thead>

            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>

                <?php while ($order = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            #<?= (int) $order['OrderID'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                format_date($order['OrderDate'])
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order['CustomerName']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($order['EmployeeName']) ?>
                        </td>

                        <td>
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

                        <td class="text-nowrap">

                            <a
                                href="/admin/orders/show.php?id=<?= (int) $order['OrderID'] ?>"
                                class="btn btn-sm btn-info"
                            >
                                Xem
                            </a>

                            <a
                                href="/admin/orders/edit.php?id=<?= (int) $order['OrderID'] ?>"
                                class="btn btn-sm btn-warning"
                            >
                                Sửa
                            </a>

                            <form
                                action="/admin/orders/delete.php"
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
                                    class="btn btn-sm btn-danger"
                                >
                                    Xóa
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td colspan="8" class="text-center">
                        Chưa có đơn hàng nào trong hệ thống.
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
