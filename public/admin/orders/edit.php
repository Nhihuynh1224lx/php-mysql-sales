<?php

require_once '/var/www/src/config/database.php';
require_once '/var/www/src/includes/helpers.php';

$orderID = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($orderID <= 0) {
    die('Mã đơn hàng không hợp lệ.');
}

/*
 * Đọc đơn hàng hiện tại.
 */
$sqlOrder = "
    SELECT
        OrderID,
        OrderDate,
        CustomerID,
        EmployeeID,
        ShipperID
    FROM orders
    WHERE OrderID = ?
";

$stmtOrder = $conn->prepare($sqlOrder);
$stmtOrder->bind_param('i', $orderID);
$stmtOrder->execute();

$order = $stmtOrder->get_result()->fetch_assoc();

$stmtOrder->close();

if (!$order) {
    die('Không tìm thấy đơn hàng.');
}

$pageTitle = 'Sửa đơn hàng #' . $orderID;

/*
 * Dữ liệu cho các ô chọn.
 */
$customers = [];
$customerResult = $conn->query(
    'SELECT CustomerID, CustomerName, City FROM customers ORDER BY CustomerName'
);

if ($customerResult) {
    while ($row = $customerResult->fetch_assoc()) {
        $customers[] = $row;
    }
}

$employees = [];
$employeeResult = $conn->query(
    'SELECT EmployeeID, LastName, FirstName FROM employees ORDER BY LastName, FirstName'
);

if ($employeeResult) {
    while ($row = $employeeResult->fetch_assoc()) {
        $employees[] = $row;
    }
}

$shippers = [];
$shipperResult = $conn->query(
    'SELECT ShipperID, ShipperName FROM shippers ORDER BY ShipperName'
);

if ($shipperResult) {
    while ($row = $shipperResult->fetch_assoc()) {
        $shippers[] = $row;
    }
}

/*
 * Sản phẩm đang kinh doanh.
 */
$products = [];
$productResult = $conn->query(
    'SELECT ProductID, ProductCode, ProductName, Unit, Price, StockQuantity
     FROM products
     WHERE IsActive = 1
     ORDER BY ProductName'
);

if ($productResult) {
    while ($row = $productResult->fetch_assoc()) {
        $products[] = $row;
    }
}

$productById = [];

foreach ($products as $product) {
    $productById[(int) $product['ProductID']] = $product;
}

/*
 * Mặt hàng hiện có của đơn.
 */
$sqlDetails = "
    SELECT ProductID, Quantity
    FROM orderdetail
    WHERE OrderID = ?
    ORDER BY OrderDetailID
";

$stmtDetails = $conn->prepare($sqlDetails);
$stmtDetails->bind_param('i', $orderID);
$stmtDetails->execute();

$detailResult = $stmtDetails->get_result();

$lines = [];

while ($row = $detailResult->fetch_assoc()) {
    $lines[] = [
        'product_id' => (int) $row['ProductID'],
        'quantity'   => (int) $row['Quantity']
    ];
}

$stmtDetails->close();

if ($lines === []) {
    $lines = [['product_id' => 0, 'quantity' => 1]];
}

$errors = [];

$form = [
    'order_date'  => $order['OrderDate'],
    'customer_id' => (string) $order['CustomerID'],
    'employee_id' => (string) $order['EmployeeID'],
    'shipper_id'  => (string) $order['ShipperID']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $form['order_date'] = trim($_POST['order_date'] ?? '');
    $form['customer_id'] = trim($_POST['customer_id'] ?? '');
    $form['employee_id'] = trim($_POST['employee_id'] ?? '');
    $form['shipper_id'] = trim($_POST['shipper_id'] ?? '');

    $rawProductIds = $_POST['product_id'] ?? [];
    $rawQuantities = $_POST['quantity'] ?? [];

    if (!is_array($rawProductIds)) {
        $rawProductIds = [];
    }

    if (!is_array($rawQuantities)) {
        $rawQuantities = [];
    }

    $lines = [];

    foreach ($rawProductIds as $index => $rawProductId) {

        $productId = (int) $rawProductId;

        if ($productId <= 0) {
            continue;
        }

        $lines[] = [
            'product_id' => $productId,
            'quantity'   => (int) ($rawQuantities[$index] ?? 0)
        ];
    }

    if ($lines === []) {
        $lines = [['product_id' => 0, 'quantity' => 1]];
    }

    /*
     * Kiểm tra thông tin chung.
     */
    $orderDate = DateTimeImmutable::createFromFormat(
        'Y-m-d',
        $form['order_date']
    );

    if (
        $orderDate === false
        || $orderDate->format('Y-m-d') !== $form['order_date']
    ) {
        $errors[] = 'Ngày đặt hàng không hợp lệ.';
    }

    $customerId = (int) $form['customer_id'];
    $employeeId = (int) $form['employee_id'];
    $shipperId = (int) $form['shipper_id'];

    $customerIds = array_map(
        static fn(array $row): int => (int) $row['CustomerID'],
        $customers
    );

    $employeeIds = array_map(
        static fn(array $row): int => (int) $row['EmployeeID'],
        $employees
    );

    $shipperIds = array_map(
        static fn(array $row): int => (int) $row['ShipperID'],
        $shippers
    );

    if (!in_array($customerId, $customerIds, true)) {
        $errors[] = 'Vui lòng chọn khách hàng.';
    }

    if (!in_array($employeeId, $employeeIds, true)) {
        $errors[] = 'Vui lòng chọn nhân viên phụ trách.';
    }

    if (!in_array($shipperId, $shipperIds, true)) {
        $errors[] = 'Vui lòng chọn đơn vị vận chuyển.';
    }

    /*
     * Kiểm tra từng dòng mặt hàng.
     */
    $seenProductIds = [];
    $orderLines = [];

    foreach ($lines as $index => $line) {

        $position = $index + 1;
        $productId = $line['product_id'];
        $quantity = $line['quantity'];

        if (!isset($productById[$productId])) {
            $errors[] = 'Dòng ' . $position . ': sản phẩm không hợp lệ.';
            continue;
        }

        if ($quantity < 1) {
            $errors[] = 'Dòng ' . $position . ': số lượng phải lớn hơn 0.';
            continue;
        }

        if (isset($seenProductIds[$productId])) {
            $errors[] = 'Dòng ' . $position
                . ': sản phẩm đã có ở dòng khác, hãy gộp lại.';
            continue;
        }

        $seenProductIds[$productId] = true;

        $orderLines[] = [
            'product_id' => $productId,
            'quantity'   => $quantity,
            'unit_price' => (float) $productById[$productId]['Price']
        ];
    }

    /*
     * Ghi thay đổi trong một transaction.
     * Cách làm: cập nhật phần thông tin chung, xóa toàn bộ dòng cũ
     * rồi ghi lại danh sách dòng mới.
     */
    if ($errors === []) {

        try {

            $conn->begin_transaction();

            $sqlUpdate = "
                UPDATE orders
                SET
                    OrderDate = ?,
                    CustomerID = ?,
                    EmployeeID = ?,
                    ShipperID = ?
                WHERE OrderID = ?
            ";

            $stmtUpdate = $conn->prepare($sqlUpdate);

            $stmtUpdate->bind_param(
                'siiii',
                $form['order_date'],
                $customerId,
                $employeeId,
                $shipperId,
                $orderID
            );

            if (!$stmtUpdate->execute()) {
                throw new Exception('Không thể cập nhật đơn hàng.');
            }

            $stmtUpdate->close();

            $stmtDelete = $conn->prepare(
                'DELETE FROM orderdetail WHERE OrderID = ?'
            );

            $stmtDelete->bind_param('i', $orderID);

            if (!$stmtDelete->execute()) {
                throw new Exception('Không thể xóa mặt hàng cũ.');
            }

            $stmtDelete->close();

            $stmtInsert = $conn->prepare(
                'INSERT INTO orderdetail (Quantity, UnitPrice, OrderID, ProductID)
                 VALUES (?, ?, ?, ?)'
            );

            foreach ($orderLines as $line) {

                $quantity = $line['quantity'];
                $unitPrice = $line['unit_price'];
                $productId = $line['product_id'];

                $stmtInsert->bind_param(
                    'idii',
                    $quantity,
                    $unitPrice,
                    $orderID,
                    $productId
                );

                if (!$stmtInsert->execute()) {
                    throw new Exception('Không thể lưu mặt hàng trong đơn.');
                }
            }

            $stmtInsert->close();

            $conn->commit();

            header('Location: /admin/orders/show.php?id=' . $orderID . '&updated=1');
            exit;

        } catch (Throwable $e) {

            $conn->rollback();

            $errors[] = $e->getMessage();
        }
    }
}

/*
 * Tổng tiền tạm tính.
 */
$previewTotal = 0.0;

foreach ($lines as $line) {
    if (isset($productById[$line['product_id']])) {
        $previewTotal += (float) $productById[$line['product_id']]['Price']
            * (int) $line['quantity'];
    }
}

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h2>Sửa đơn hàng #<?= (int) $orderID ?></h2>

        <div>

            <a
                href="/admin/orders/show.php?id=<?= (int) $orderID ?>"
                class="btn btn-info"
            >
                Xem chi tiết
            </a>

            <a href="/admin/orders/" class="btn btn-secondary">
                Danh sách
            </a>

        </div>

    </div>

    <p class="text-muted">
        Cập nhật thông tin đơn và danh sách mặt hàng.
    </p>

    <?php if ($errors !== []): ?>

        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation"></i>

            <div>
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>

        </div>

    <?php endif; ?>

    <div class="alert alert-info">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            Khi lưu, toàn bộ mặt hàng của đơn sẽ được thay bằng danh sách
            bên dưới. Đơn giá được tính lại theo giá hiện tại của sản phẩm.
        </div>
    </div>

    <form method="post">

        <div class="mb-4">

            <h5 class="mb-3">Thông tin đơn hàng</h5>

            <div class="row">

                <div class="col-md-3 mb-3">

                    <label for="orderDate" class="form-label">
                        Ngày đặt hàng
                    </label>

                    <input
                        type="date"
                        class="form-control"
                        id="orderDate"
                        name="order_date"
                        value="<?= htmlspecialchars($form['order_date']) ?>"
                        required
                    >

                </div>

                <div class="col-md-3 mb-3">

                    <label for="customerId" class="form-label">
                        Khách hàng
                    </label>

                    <select
                        class="form-select"
                        id="customerId"
                        name="customer_id"
                        required
                    >
                        <option value="">-- Chọn khách hàng --</option>

                        <?php foreach ($customers as $customer): ?>

                            <option
                                value="<?= (int) $customer['CustomerID'] ?>"
                                <?= ((string) $customer['CustomerID']
                                    === (string) $form['customer_id'])
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= htmlspecialchars($customer['CustomerName']) ?>
                                <?= $customer['City']
                                    ? ' — ' . htmlspecialchars($customer['City'])
                                    : '' ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-md-3 mb-3">

                    <label for="employeeId" class="form-label">
                        Nhân viên phụ trách
                    </label>

                    <select
                        class="form-select"
                        id="employeeId"
                        name="employee_id"
                        required
                    >
                        <option value="">-- Chọn nhân viên --</option>

                        <?php foreach ($employees as $employee): ?>

                            <option
                                value="<?= (int) $employee['EmployeeID'] ?>"
                                <?= ((string) $employee['EmployeeID']
                                    === (string) $form['employee_id'])
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= htmlspecialchars(
                                    $employee['LastName']
                                    . ' '
                                    . $employee['FirstName']
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="col-md-3 mb-3">

                    <label for="shipperId" class="form-label">
                        Đơn vị vận chuyển
                    </label>

                    <select
                        class="form-select"
                        id="shipperId"
                        name="shipper_id"
                        required
                    >
                        <option value="">-- Chọn đơn vị --</option>

                        <?php foreach ($shippers as $shipper): ?>

                            <option
                                value="<?= (int) $shipper['ShipperID'] ?>"
                                <?= ((string) $shipper['ShipperID']
                                    === (string) $form['shipper_id'])
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= htmlspecialchars($shipper['ShipperName']) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>

        </div>

        <div class="mb-4">

            <h5 class="mb-3">Mặt hàng trong đơn</h5>

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead class="table-dark">

                        <tr>
                            <th style="min-width: 320px;">Sản phẩm</th>
                            <th class="text-end">Đơn giá</th>
                            <th style="width: 130px;">Số lượng</th>
                            <th class="text-end">Thành tiền</th>
                            <th style="width: 60px;"></th>
                        </tr>
                    </thead>

                    <tbody id="itemsBody">

                    <?php foreach ($lines as $line): ?>

                        <?php
                        $selectedId = (int) $line['product_id'];
                        $quantity = (int) $line['quantity'];

                        if ($quantity < 1) {
                            $quantity = 1;
                        }

                        $selectedProduct = $productById[$selectedId] ?? null;
                        ?>

                        <tr class="order-line">

                            <td>

                                <select
                                    name="product_id[]"
                                    class="form-select product-select"
                                >
                                    <option value="">-- Chọn sản phẩm --</option>

                                    <?php foreach ($products as $product): ?>

                                        <option
                                            value="<?= (int) $product['ProductID'] ?>"
                                            data-price="<?= htmlspecialchars(
                                                (string) $product['Price']
                                            ) ?>"
                                            <?= ((int) $product['ProductID']
                                                === $selectedId)
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            <?= htmlspecialchars(
                                                $product['ProductCode']
                                                . ' — '
                                                . $product['ProductName']
                                            ) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </td>

                            <td class="text-end line-unit">
                                <?= $selectedProduct
                                    ? vnd((float) $selectedProduct['Price'])
                                    : '—' ?>
                            </td>

                            <td>

                                <input
                                    type="number"
                                    name="quantity[]"
                                    class="form-control line-qty"
                                    min="1"
                                    step="1"
                                    value="<?= $quantity ?>"
                                >

                            </td>

                            <td class="text-end line-total">
                                <?= $selectedProduct
                                    ? vnd(
                                        (float) $selectedProduct['Price']
                                        * $quantity
                                    )
                                    : '0 đ' ?>
                            </td>

                            <td class="text-end">

                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-danger remove-item"
                                    title="Xóa dòng"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">

                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    id="addItem"
                >
                    <i class="fa-solid fa-plus"></i>
                    Thêm dòng
                </button>

                <div class="fs-5">
                    <span class="text-muted me-2">Tổng cộng</span>
                    <span class="fw-bold" id="grandTotal">
                        <?= vnd($previewTotal) ?>
                    </span>
                </div>

            </div>

        </div>

        <div class="mt-4">

            <button type="submit" class="btn btn-warning">
                Cập nhật đơn hàng
            </button>

            <a
                href="/admin/orders/show.php?id=<?= (int) $orderID ?>"
                class="btn btn-secondary"
            >
                Hủy
            </a>

        </div>

    </form>

</div>

<template id="itemRowTemplate">

    <tr class="order-line">

        <td>

            <select name="product_id[]" class="form-select product-select">
                <option value="">-- Chọn sản phẩm --</option>

                <?php foreach ($products as $product): ?>

                    <option
                        value="<?= (int) $product['ProductID'] ?>"
                        data-price="<?= htmlspecialchars(
                            (string) $product['Price']
                        ) ?>"
                    >
                        <?= htmlspecialchars(
                            $product['ProductCode']
                            . ' — '
                            . $product['ProductName']
                        ) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </td>

        <td class="text-end line-unit">—</td>

        <td>

            <input
                type="number"
                name="quantity[]"
                class="form-control line-qty"
                min="1"
                step="1"
                value="1"
            >

        </td>

        <td class="text-end line-total">0 đ</td>

        <td class="text-end">

            <button
                type="button"
                class="btn btn-sm btn-outline-danger remove-item"
                title="Xóa dòng"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </td>

    </tr>

</template>

<script src="/assets/js/order-items.js"></script>

<?php

require_once '/var/www/src/includes/admin/footer.php';

$conn->close();
