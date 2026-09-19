<?php

require_once '/var/www/src/config/database.php';
require_once '/var/www/src/includes/helpers.php';

$orderID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($orderID <= 0) {
    die('Mã đơn hàng không hợp lệ.');
}

/*
 * Thông tin chung của đơn hàng.
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
    INNER JOIN employees AS e
        ON e.EmployeeID = o.EmployeeID
    INNER JOIN shippers AS sh
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

require_once '/var/www/src/includes/header.php';
require_once '/var/www/src/includes/navbar.php';

?>

<div class="page">

    <div class="page-head">

        <div>
            <h2 class="page-title">
                <i class="bi bi-receipt"></i>
                Đơn hàng #<?= (int) $order['OrderID'] ?>
            </h2>
            <p class="page-subtitle">
                Đặt ngày <?= htmlspecialchars(format_date($order['OrderDate'])) ?>
                · <?= count($detailRows) ?> mặt hàng
                · <?= format_quantity($totalQuantity) ?> sản phẩm
            </p>
        </div>

        <div class="page-head-actions">

            <a
                href="/orders/edit.php?id=<?= (int) $order['OrderID'] ?>"
                class="btn btn-primary"
            >
                <i class="bi bi-pencil"></i>
                Sửa đơn hàng
            </a>

            <a href="/orders/" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Danh sách
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

                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash"></i>
                    Xóa đơn
                </button>
            </form>

        </div>

    </div>

    <?php if (isset($_GET['updated']) && $_GET['updated'] === '1'): ?>

        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <div>Đã cập nhật đơn hàng thành công.</div>
        </div>

    <?php endif; ?>

    <section class="grid-2">

        <div class="panel">

            <div class="panel-head">

                <div>
                    <h3 class="panel-title">
                        <i class="bi bi-box-seam"></i>
                        Mặt hàng trong đơn
                    </h3>
                    <p class="panel-sub">
                        Đơn giá được ghi nhận tại thời điểm đặt hàng
                    </p>
                </div>

            </div>

            <div class="table-responsive">

                <table class="table align-middle">

                    <thead>
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

                                    <div class="product-cell">

                                        <?php if (!empty($row['ImageFile'])): ?>

                                            <img
                                                src="/uploads/products/<?= htmlspecialchars(
                                                    $row['ImageFile']
                                                ) ?>"
                                                alt="<?= htmlspecialchars(
                                                    $row['AltText']
                                                    ?? $row['ProductName']
                                                ) ?>"
                                                class="thumb"
                                            >

                                        <?php else: ?>

                                            <span
                                                class="thumb d-grid"
                                                style="place-items: center; color: #b6bccb;"
                                            >
                                                <i class="bi bi-image"></i>
                                            </span>

                                        <?php endif; ?>

                                        <span class="product-cell-text">

                                            <span class="product-cell-name">
                                                <?= htmlspecialchars(
                                                    $row['ProductName']
                                                ) ?>
                                            </span>

                                            <span class="product-cell-meta">
                                                <span class="code-chip">
                                                    <?= htmlspecialchars(
                                                        $row['ProductCode']
                                                    ) ?>
                                                </span>
                                                <?= htmlspecialchars(
                                                    $row['Unit'] ?? ''
                                                ) ?>
                                            </span>

                                        </span>

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

                            <td colspan="4" class="empty-cell">

                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p>Đơn hàng này chưa có mặt hàng nào.</p>
                                    <a
                                        href="/orders/edit.php?id=<?= (int) $order['OrderID'] ?>"
                                        class="btn btn-primary btn-sm"
                                    >
                                        <i class="bi bi-plus-lg"></i>
                                        Thêm mặt hàng
                                    </a>
                                </div>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                    <?php if ($detailRows !== []): ?>

                        <tfoot>
                            <tr class="invoice-total-row">
                                <td colspan="3" class="text-end">
                                    Tổng cộng
                                </td>
                                <td class="text-end">
                                    <?= vnd($orderTotal) ?>
                                </td>
                            </tr>
                        </tfoot>

                    <?php endif; ?>

                </table>

            </div>

        </div>

        <div class="stack">

            <div class="panel">

                <div class="panel-head">

                    <div>
                        <h3 class="panel-title">
                            <i class="bi bi-person"></i>
                            Khách hàng
                        </h3>
                    </div>

                </div>

                <div class="panel-body">

                    <dl class="meta-list">

                        <div class="meta-row">
                            <dt>Tên khách hàng</dt>
                            <dd><?= htmlspecialchars($order['CustomerName']) ?></dd>
                        </div>

                        <div class="meta-row">
                            <dt>Người liên hệ</dt>
                            <dd><?= htmlspecialchars($order['ContactName'] ?? '—') ?></dd>
                        </div>

                        <div class="meta-row">
                            <dt>Địa chỉ</dt>
                            <dd><?= htmlspecialchars($order['Address'] ?? '—') ?></dd>
                        </div>

                        <div class="meta-row">
                            <dt>Thành phố</dt>
                            <dd><?= htmlspecialchars($order['City'] ?? '—') ?></dd>
                        </div>

                        <div class="meta-row">
                            <dt>Quốc gia</dt>
                            <dd><?= htmlspecialchars($order['Country'] ?? '—') ?></dd>
                        </div>

                    </dl>

                </div>

            </div>

            <div class="panel">

                <div class="panel-head">

                    <div>
                        <h3 class="panel-title">
                            <i class="bi bi-truck"></i>
                            Xử lý &amp; vận chuyển
                        </h3>
                    </div>

                </div>

                <div class="panel-body">

                    <dl class="meta-list">

                        <div class="meta-row">
                            <dt>Ngày đặt</dt>
                            <dd><?= htmlspecialchars(
                                format_date($order['OrderDate'])
                            ) ?></dd>
                        </div>

                        <div class="meta-row">
                            <dt>Nhân viên</dt>
                            <dd><?= htmlspecialchars(
                                $order['LastName'] . ' ' . $order['FirstName']
                            ) ?></dd>
                        </div>

                        <div class="meta-row">
                            <dt>Đơn vị vận chuyển</dt>
                            <dd><?= htmlspecialchars($order['ShipperName']) ?></dd>
                        </div>

                        <div class="meta-row">
                            <dt>Điện thoại</dt>
                            <dd><?= htmlspecialchars($order['ShipperPhone'] ?? '—') ?></dd>
                        </div>

                        <div class="meta-row">
                            <dt>Tổng tiền</dt>
                            <dd><?= vnd($orderTotal) ?></dd>
                        </div>

                    </dl>

                </div>

            </div>

        </div>

    </section>

</div>

<?php

require_once '/var/www/src/includes/footer.php';

$conn->close();
