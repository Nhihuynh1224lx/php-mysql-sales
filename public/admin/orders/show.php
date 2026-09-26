<?php

require_once '/var/www/src/config/database.php';
require_once '/var/www/src/includes/helpers.php';

$orderID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($orderID <= 0) {
    die('Mã đơn hàng không hợp lệ.');
}

/*
 * Thông tin chung của đơn hàng.
 * employees và shippers dùng LEFT JOIN vì đơn khách tự đặt online
 * chưa có nhân viên xử lý và người giao hàng (EmployeeID/ShipperID = NULL);
 * INNER JOIN sẽ khiến trang chi tiết báo "Không tìm thấy đơn hàng.".
 */
$sqlOrder = "
    SELECT
        o.OrderID,
        o.OrderDate,
        cu.CustomerID,
        cu.CustomerName,
        cu.ContactName,
        cu.Address,
        cu.City,
        cu.Country,
        e.EmployeeID,
        e.LastName,
        e.FirstName,
        sh.ShipperID,
        sh.ShipperName,
        sh.Phone AS ShipperPhone
    FROM orders AS o
    INNER JOIN customers AS cu
        ON cu.CustomerID = o.CustomerID
    LEFT JOIN employees AS e
        ON e.EmployeeID = o.EmployeeID
    LEFT JOIN shippers AS sh
        ON sh.ShipperID = o.ShipperID
    WHERE o.OrderID = ?
";

$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param('i', $orderID);
$stmtOrder->execute();

$order = $stmtOrder->get_result()->fetch_assoc();

$stmtOrder->close();

if (!$order) {
    die('Không tìm thấy đơn hàng.');
}

$pageTitle = 'Đơn hàng #' . $orderID;

/*
 * Các mặt hàng trong đơn.
 */
$sqlDetails = "
    SELECT
        od.OrderDetailID,
        od.Quantity,
        od.UnitPrice,
        (od.Quantity * od.UnitPrice) AS LineTotal,
        p.ProductID,
        p.ProductCode,
        p.ProductName,
        p.Unit,
        (
            SELECT pi.ImageFile
            FROM product_images AS pi
            WHERE pi.ProductID = p.ProductID
            ORDER BY pi.IsPrimary DESC, pi.SortOrder ASC
            LIMIT 1
        ) AS ImageFile,
        (
            SELECT pi.AltText
            FROM product_images AS pi
            WHERE pi.ProductID = p.ProductID
            ORDER BY pi.IsPrimary DESC, pi.SortOrder ASC
            LIMIT 1
        ) AS AltText
    FROM orderdetail AS od
    INNER JOIN products AS p
        ON p.ProductID = od.ProductID
    WHERE od.OrderID = ?
    ORDER BY od.OrderDetailID
";

$stmtDetails = $conn->prepare($sqlDetails);
$stmtDetails->bind_param('i', $orderID);
$stmtDetails->execute();

$details = $stmtDetails->get_result();

$detailRows = [];
$orderTotal = 0.0;
$totalQuantity = 0;

while ($row = $details->fetch_assoc()) {
    $detailRows[] = $row;
    $orderTotal += (float) $row['LineTotal'];
    $totalQuantity += (int) $row['Quantity'];
}

$stmtDetails->close();

/*
 * Đơn khách tự đặt online chưa được phân công nên nhân viên và
 * đơn vị vận chuyển có thể NULL -> hiển thị dấu gạch ngang.
 */
$employeeName = trim(($order['LastName'] ?? '') . ' ' . ($order['FirstName'] ?? ''));
$shipperName  = trim($order['ShipperName'] ?? '');

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h2>Đơn hàng #<?= (int) $order['OrderID'] ?></h2>

        <div>

            <a
                href="/admin/orders/edit.php?id=<?= (int) $order['OrderID'] ?>"
                class="btn btn-warning"
            >
                Sửa đơn hàng
            </a>

            <a href="/admin/orders/" class="btn btn-secondary">
                Danh sách
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

                <button type="submit" class="btn btn-danger">
                    Xóa đơn
                </button>
            </form>

        </div>

    </div>

    <p class="text-muted">
        Đặt ngày <?= htmlspecialchars(format_datetime($order['OrderDate'])) ?>
        · <?= count($detailRows) ?> mặt hàng
        · <?= format_quantity($totalQuantity) ?> sản phẩm
    </p>

    <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>

        <div class="alert alert-success">
            Đã cập nhật đơn hàng thành công.
        </div>

    <?php endif; ?>

    <div class="row g-4">

        <!-- Cột trái: danh sách mặt hàng -->
        <div class="col-lg-8">

            <h3 class="h5 mb-3">Mặt hàng trong đơn</h3>

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead class="table-dark">

                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">SL</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php if ($detailRows !== []): ?>

                        <?php foreach ($detailRows as $row): ?>

                            <tr>

                                <td>

                                    <div class="d-flex align-items-center gap-2">

                                        <?php if (!empty($row['ImageFile'])): ?>

                                            <img
                                                src="/uploads/products/<?= htmlspecialchars(
                                                    $row['ImageFile']
                                                ) ?>"
                                                alt="<?= htmlspecialchars(
                                                    $row['AltText']
                                                    ?? $row['ProductName']
                                                ) ?>"
                                                width="56"
                                                class="img-thumbnail"
                                            >

                                        <?php endif; ?>

                                        <div>

                                            <div>
                                                <?= htmlspecialchars(
                                                    $row['ProductName']
                                                ) ?>
                                            </div>

                                            <div class="text-muted small">
                                                <?= htmlspecialchars(
                                                    $row['ProductCode']
                                                ) ?>
                                                <?php if (!empty($row['Unit'])): ?>
                                                    · <?= htmlspecialchars($row['Unit']) ?>
                                                <?php endif; ?>
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                <td class="text-end">
                                    <?= vnd((float) $row['UnitPrice']) ?>
                                </td>

                                <td class="text-end">
                                    <?= (int) $row['Quantity'] ?>
                                </td>

                                <td class="text-end">
                                    <?= vnd((float) $row['LineTotal']) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>

                            <td colspan="4" class="text-center">
                                Đơn hàng này chưa có mặt hàng nào.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                    <?php if ($detailRows !== []): ?>

                        <tfoot>

                            <tr>
                                <td colspan="3" class="text-end fw-bold">
                                    Tổng cộng
                                </td>
                                <td class="text-end fw-bold">
                                    <?= vnd($orderTotal) ?>
                                </td>
                            </tr>

                        </tfoot>

                    <?php endif; ?>

                </table>

            </div>

        </div>

        <!-- Cột phải: thông tin khách hàng và vận chuyển -->
        <div class="col-lg-4">

            <h3 class="h5 mb-3">Khách hàng</h3>

            <table class="table table-bordered table-striped mb-4">

                <tbody>

                    <tr>
                        <th>Tên khách hàng</th>
                        <td><?= htmlspecialchars($order['CustomerName']) ?></td>
                    </tr>

                    <tr>
                        <th>Người liên hệ</th>
                        <td><?= htmlspecialchars($order['ContactName'] ?? '—') ?></td>
                    </tr>

                    <tr>
                        <th>Địa chỉ</th>
                        <td><?= htmlspecialchars($order['Address'] ?? '—') ?></td>
                    </tr>

                    <tr>
                        <th>Thành phố</th>
                        <td><?= htmlspecialchars($order['City'] ?? '—') ?></td>
                    </tr>

                    <tr>
                        <th>Quốc gia</th>
                        <td><?= htmlspecialchars($order['Country'] ?? '—') ?></td>
                    </tr>

                </tbody>

            </table>

            <h3 class="h5 mb-3">Xử lý &amp; vận chuyển</h3>

            <table class="table table-bordered table-striped">

                <tbody>

                    <tr>
                        <th>Ngày đặt</th>
                        <td><?= htmlspecialchars(
                            format_datetime($order['OrderDate'])
                        ) ?></td>
                    </tr>

                    <tr>
                        <th>Nhân viên</th>
                        <td><?= htmlspecialchars($employeeName !== '' ? $employeeName : '—') ?></td>
                    </tr>

                    <tr>
                        <th>Đơn vị vận chuyển</th>
                        <td><?= htmlspecialchars($shipperName !== '' ? $shipperName : '—') ?></td>
                    </tr>

                    <tr>
                        <th>Điện thoại</th>
                        <td><?= htmlspecialchars($order['ShipperPhone'] ?? '—') ?></td>
                    </tr>

                    <tr>
                        <th>Tổng tiền</th>
                        <td class="fw-bold"><?= vnd($orderTotal) ?></td>
                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php

require_once '/var/www/src/includes/admin/footer.php';

$conn->close();
