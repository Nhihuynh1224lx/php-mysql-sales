<?php

$pageTitle = 'Tạo đơn hàng';

require_once '/var/www/src/config/database.php';
require_once '/var/www/src/includes/helpers.php';

/*
 * Nạp dữ liệu cho các ô chọn.
 * Đọc vào mảng vừa để hiển thị vừa để kiểm tra dữ liệu gửi lên.
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
 * Sản phẩm kèm giá hiện tại.
 * Giá này là nguồn duy nhất để tính tiền, không tin giá từ trình duyệt.
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
 * Dữ liệu biểu mẫu.
 */
$errors = [];

$form = [
    'order_date'  => date('Y-m-d'),
    'customer_id' => '',
    'employee_id' => '',
    'shipper_id'  => ''
];

/*
 * Các dòng mặt hàng: [['product_id' => int, 'quantity' => int], ...]
 * Mặc định một dòng trống cho lần mở đầu tiên.
 */
$lines = [
    ['product_id' => 0, 'quantity' => 1]
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

    /*
     * Gộp các dòng đã chọn. Bỏ qua dòng để trống sản phẩm.
     */
    $lines = [];

    foreach ($rawProductIds as $index => $rawProductId) {

        $productId = (int) $rawProductId;

        if ($productId <= 0) {
            continue;
        }

        $quantity = (int) ($rawQuantities[$index] ?? 0);

        $lines[] = [
            'product_id' => $productId,
            'quantity'   => $quantity
        ];
    }

    if ($lines === []) {
        $lines = [['product_id' => 0, 'quantity' => 1]];
    }

    /*
     * Kiểm tra phần thông tin chung.
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

        $unitPrice = (float) $productById[$productId]['Price'];

        $orderLines[] = [
            'product_id' => $productId,
            'quantity'   => $quantity,
            'unit_price' => $unitPrice
        ];
    }

    /*
     * Nếu mọi thứ hợp lệ thì ghi vào database trong một transaction.
     */
    if ($errors === []) {

        try {

            $conn->begin_transaction();

            $sqlOrder = "
                INSERT INTO orders
                    (OrderDate, CustomerID, EmployeeID, ShipperID)
                VALUES
                    (?, ?, ?, ?)
            ";

            $stmtOrder = $conn->prepare($sqlOrder);

            $stmtOrder->bind_param(
                'siii',
                $form['order_date'],
                $customerId,
                $employeeId,
                $shipperId
            );

            if (!$stmtOrder->execute()) {
                throw new Exception('Không thể tạo đơn hàng.');
            }

            $orderId = $conn->insert_id;

            $stmtOrder->close();

            $sqlDetail = "
                INSERT INTO orderdetail
                    (Quantity, UnitPrice, OrderID, ProductID)
                VALUES
                    (?, ?, ?, ?)
            ";

            $stmtDetail = $conn->prepare($sqlDetail);

            foreach ($orderLines as $line) {

                $quantity = $line['quantity'];
                $unitPrice = $line['unit_price'];
                $productId = $line['product_id'];

                $stmtDetail->bind_param(
                    'idii',
                    $quantity,
                    $unitPrice,
                    $orderId,
                    $productId
                );

                if (!$stmtDetail->execute()) {
                    throw new Exception(
                        'Không thể lưu mặt hàng trong đơn.'
                    );
                }
            }

            $stmtDetail->close();

            $conn->commit();

            header('Location: /orders/show.php?id=' . $orderId);
            exit;

        } catch (Throwable $e) {

            $conn->rollback();

            $errors[] = $e->getMessage();
        }
    }
}

/*
 * Tổng tiền tạm tính để hiển thị bên dưới bảng.
 */
$previewTotal = 0.0;

foreach ($lines as $line) {
    if (isset($productById[$line['product_id']])) {
        $previewTotal += (float) $productById[$line['product_id']]['Price']
            * (int) $line['quantity'];
    }
}

require_once '/var/www/src/includes/header.php';
require_once '/var/www/src/includes/navbar.php';

?>

<div class="page">

    <div class="page-head">

        <div>
            <h2 class="page-title">
                <i class="bi bi-receipt"></i>
                Tạo đơn hàng
            </h2>
            <p class="page-subtitle">
                Chọn khách hàng, nhân viên, đơn vị vận chuyển và các mặt hàng
            </p>
        </div>

        <div class="page-head-actions">

            <a href="/orders/" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i>
                Quay lại danh sách
            </a>

        </div>

    </div>

    <?php if ($errors !== []): ?>

        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill"></i>

            <div>
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>

        </div>

    <?php endif; ?>

    <?php if ($customers === [] || $employees === [] || $shippers === []): ?>

        <div class="alert alert-warning">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                Cần có ít nhất một khách hàng, một nhân viên và một đơn vị
                vận chuyển trước khi tạo đơn hàng.
            </div>
        </div>

    <?php endif; ?>

    <form method="post" class="form-wide">

        <div class="form-section">

            <div class="form-section-title">
                <i class="bi bi-info-circle"></i>
                Thông tin đơn hàng
            </div>

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

        <div class="form-section">

            <div class="form-section-title">
                <i class="bi bi-box-seam"></i>
                Mặt hàng trong đơn
            </div>

            <div class="table-responsive order-items">

                <table class="table align-middle">

                    <thead>
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

                            <td class="text-end cell-muted line-unit">
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
                                    <i class="bi bi-x-lg"></i>
                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <div class="order-items-foot">

                <button
                    type="button"
                    class="btn btn-outline-primary btn-sm"
                    id="addItem"
                >
                    <i class="bi bi-plus-lg"></i>
                    Thêm dòng
                </button>

                <div class="order-total">
                    <span class="order-total-label">Tổng cộng</span>
                    <span class="order-total-value" id="grandTotal">
                        <?= vnd($previewTotal) ?>
                    </span>
                </div>

            </div>

            <p class="form-text mt-2">
                <i class="bi bi-info-circle"></i>
                Đơn giá được lấy theo giá hiện tại của sản phẩm và
                được hệ thống tính lại khi lưu.
            </p>

        </div>

        <div class="form-actions-inline">

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg"></i>
                Tạo đơn hàng
            </button>

            <a href="/orders/" class="btn btn-secondary">
                <i class="bi bi-x-lg"></i>
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

        <td class="text-end cell-muted line-unit">—</td>

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
                <i class="bi bi-x-lg"></i>
            </button>

        </td>

    </tr>

</template>

<script src="/assets/js/order-items.js"></script>

<?php

require_once '/var/www/src/includes/footer.php';

$conn->close();
