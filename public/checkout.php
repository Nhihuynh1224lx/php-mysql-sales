<?php

require_once '/var/www/src/config/session.php';
require_once '/var/www/src/config/database.php';

$pageTitle = 'Đặt hàng';

/* ------------------------------------------------------------------
 * ĐIỀU CHỈNH SO VỚI TÀI LIỆU HANDS-ON 12
 * ------------------------------------------------------------------
 * Bảng `orders` trong database hiện tại chỉ có 5 cột:
 *
 *     OrderID, OrderDate (DATE), CustomerID, EmployeeID, ShipperID
 *
 * Tài liệu giả định bảng đã được ALTER để có thêm TotalAmount, Status,
 * OrderDate kiểu DATETIME và EmployeeID/ShipperID cho phép NULL.
 * Dự án này giữ nguyên cấu trúc database, nên code thích ứng như sau:
 *
 *   1. Không lưu TotalAmount  -> trang xác nhận tự tính lại bằng
 *      SUM(Quantity * UnitPrice) từ bảng orderdetail.
 *   2. Không lưu Status       -> trang xác nhận hiển thị nhãn cố định
 *      "Chờ xử lý" cho đơn vừa tạo.
 *   3. OrderDate là DATE và không có giá trị mặc định -> phải truyền
 *      ngày đặt hàng vào câu INSERT.
 *   4. EmployeeID và ShipperID là NOT NULL -> đơn của khách vãng lai
 *      được gán tạm nhân viên và nhân viên giao hàng đầu tiên, admin
 *      sẽ phân công lại sau.
 *
 * Phần nghiệp vụ quan trọng vẫn giữ nguyên như tài liệu:
 * transaction, SELECT ... FOR UPDATE, kiểm tra tồn kho, trừ tồn kho,
 * rollback khi lỗi và chỉ xóa giỏ hàng sau khi commit thành công.
 */

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header('Location: /cart.php');
    exit;
}

/* ------------------------------------------------------------------
 * Lấy thông tin sản phẩm trong giỏ để HIỂN THỊ
 * ------------------------------------------------------------------
 * Chỉ đọc ProductID và số lượng từ Session, còn tên, giá, tồn kho
 * đều truy vấn lại từ MySQL.
 */

function getCartItems($conn, $cart)
{
    $items = [];
    $total = 0;

    $sql = "
        SELECT
            p.ProductID,
            p.ProductCode,
            p.ProductName,
            p.Price,
            p.StockQuantity,
            (
                SELECT pi.ImageFile
                FROM product_images pi
                WHERE pi.ProductID = p.ProductID
                  AND pi.IsPrimary = 1
                LIMIT 1
            ) AS ImageFile
        FROM products p
        WHERE p.ProductID = ?
          AND p.IsActive = 1
    ";

    $stmt = $conn->prepare($sql);

    foreach ($cart as $productID => $quantity) {

        $productID = (int) $productID;
        $quantity = (int) $quantity;

        if ($productID <= 0 || $quantity <= 0) {
            continue;
        }

        $stmt->bind_param('i', $productID);
        $stmt->execute();

        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $result->free();

        if (!$product) {
            continue;
        }

        $product['Quantity'] = $quantity;
        $product['Subtotal'] =
            (float) $product['Price'] * $quantity;

        $total += $product['Subtotal'];
        $items[] = $product;
    }

    $stmt->close();

    return [
        'items' => $items,
        'total' => $total
    ];
}

$cartData = getCartItems($conn, $cart);
$cartItems = $cartData['items'];
$total = $cartData['total'];

/* Giỏ có ProductID nhưng sản phẩm đã ngừng bán -> quay về giỏ */
if (empty($cartItems)) {
    header('Location: /cart.php');
    exit;
}

$errorMessage = '';
$customerName = '';
$phone = '';
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['place_order'])) {

    $customerName = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($customerName === ''
        || $phone === ''
        || $address === '') {

        $errorMessage =
            'Vui lòng nhập đầy đủ thông tin khách hàng.';

    } else {

        try {

            $conn->begin_transaction();

            $orderItems = [];

            /* ------------------------------------------------------
             * Bước 1: kiểm tra lại sản phẩm và tồn kho
             * ------------------------------------------------------
             * FOR UPDATE khóa các bản ghi vừa đọc cho tới khi
             * transaction kết thúc, nhờ vậy giữa lúc kiểm tra tồn kho
             * và lúc trừ tồn kho không có transaction khác chen vào.
             *
             * Giá cũng đọc lại từ MySQL chứ không lấy từ trình duyệt.
             */

            $sqlProduct = "
                SELECT
                    ProductID,
                    ProductName,
                    Price,
                    StockQuantity
                FROM products
                WHERE ProductID = ?
                  AND IsActive = 1
                FOR UPDATE
            ";

            $stmtProduct = $conn->prepare($sqlProduct);

            foreach ($cart as $productID => $quantity) {

                $productID = (int) $productID;
                $quantity = (int) $quantity;

                if ($productID <= 0 || $quantity <= 0) {
                    throw new Exception(
                        'Dữ liệu giỏ hàng không hợp lệ.'
                    );
                }

                $stmtProduct->bind_param('i', $productID);
                $stmtProduct->execute();

                $productResult = $stmtProduct->get_result();
                $product = $productResult->fetch_assoc();
                $productResult->free();

                if (!$product) {
                    throw new Exception(
                        'Có sản phẩm không còn khả dụng.'
                    );
                }

                if ($quantity > (int) $product['StockQuantity']) {
                    throw new Exception(
                        'Sản phẩm "'
                        . $product['ProductName']
                        . '" không đủ số lượng tồn kho.'
                    );
                }

                $orderItems[] = [
                    'ProductID' => $productID,
                    'Quantity' => $quantity,
                    'UnitPrice' => (float) $product['Price']
                ];
            }

            $stmtProduct->close();

            /* ------------------------------------------------------
             * Bước 2: lưu khách hàng
             * ------------------------------------------------------ */

            $sqlCustomer = "
                INSERT INTO customers
                    (CustomerName, Address, Phone)
                VALUES (?, ?, ?)
            ";

            $stmtCustomer = $conn->prepare($sqlCustomer);
            $stmtCustomer->bind_param(
                'sss',
                $customerName,
                $address,
                $phone
            );
            $stmtCustomer->execute();

            $customerID = $conn->insert_id;
            $stmtCustomer->close();

            /* ------------------------------------------------------
             * Bước 3: lưu đơn hàng
             * ------------------------------------------------------
             * Bảng orders không có cột TotalAmount và Status nên chỉ
             * lưu được ngày đặt, khách hàng, nhân viên và shipper.
             */

            $sqlDefaultEmployee = "
                SELECT
                    MIN(EmployeeID) AS EmployeeID
                FROM employees
            ";

            $employeeResult = $conn->query($sqlDefaultEmployee);
            $employeeRow = $employeeResult->fetch_assoc();
            $employeeResult->free();

            $defaultEmployeeID = (int) ($employeeRow['EmployeeID'] ?? 0);

            $sqlDefaultShipper = "
                SELECT
                    MIN(ShipperID) AS ShipperID
                FROM shippers
            ";

            $shipperResult = $conn->query($sqlDefaultShipper);
            $shipperRow = $shipperResult->fetch_assoc();
            $shipperResult->free();

            $defaultShipperID = (int) ($shipperRow['ShipperID'] ?? 0);

            if ($defaultEmployeeID <= 0 || $defaultShipperID <= 0) {
                throw new Exception(
                    'Chưa có nhân viên hoặc nhân viên giao hàng '
                    . 'để gán cho đơn hàng.'
                );
            }

            /* Cột OrderDate kiểu DATE và không có mặc định */
            $orderDate = date('Y-m-d');

            $sqlOrder = "
                INSERT INTO orders
                    (OrderDate, CustomerID, EmployeeID, ShipperID)
                VALUES (?, ?, ?, ?)
            ";

            $stmtOrder = $conn->prepare($sqlOrder);
            $stmtOrder->bind_param(
                'siii',
                $orderDate,
                $customerID,
                $defaultEmployeeID,
                $defaultShipperID
            );
            $stmtOrder->execute();

            $orderID = $conn->insert_id;
            $stmtOrder->close();

            /* ------------------------------------------------------
             * Bước 4: lưu chi tiết đơn và trừ tồn kho
             * ------------------------------------------------------
             * UnitPrice lưu giá TẠI THỜI ĐIỂM ĐẶT HÀNG, vì giá sản
             * phẩm có thể thay đổi về sau.
             */

            $sqlDetail = "
                INSERT INTO orderdetail
                    (Quantity, UnitPrice, OrderID, ProductID)
                VALUES (?, ?, ?, ?)
            ";

            $stmtDetail = $conn->prepare($sqlDetail);

            $sqlStock = "
                UPDATE products
                SET StockQuantity = StockQuantity - ?
                WHERE ProductID = ?
            ";

            $stmtStock = $conn->prepare($sqlStock);

            foreach ($orderItems as $item) {

                $quantity = $item['Quantity'];
                $unitPrice = $item['UnitPrice'];
                $productID = $item['ProductID'];

                $stmtDetail->bind_param(
                    'idii',
                    $quantity,
                    $unitPrice,
                    $orderID,
                    $productID
                );
                $stmtDetail->execute();

                $stmtStock->bind_param(
                    'ii',
                    $quantity,
                    $productID
                );
                $stmtStock->execute();
            }

            $stmtDetail->close();
            $stmtStock->close();

            $conn->commit();

            /* Chỉ xóa giỏ hàng SAU KHI commit thành công */
            $_SESSION['cart'] = [];

            header(
                'Location: /order-success.php?id=' . $orderID
            );
            exit;

        } catch (Throwable $e) {

            $conn->rollback();
            $errorMessage = $e->getMessage();
        }
    }
}

require_once '/var/www/src/includes/frontend/header.php';
require_once '/var/www/src/includes/frontend/navbar.php';
?>

<div class="container py-4">

    <h1 class="h3 mb-4">Đặt hàng</h1>

    <?php if ($errorMessage !== ''): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">

                    <h2 class="h5 mb-3">
                        Thông tin khách hàng
                    </h2>

                    <form method="post">

                        <div class="mb-3">
                            <label
                                for="customer_name"
                                class="form-label"
                            >
                                Họ tên
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="customer_name"
                                name="customer_name"
                                value="<?= htmlspecialchars($customerName) ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label
                                for="phone"
                                class="form-label"
                            >
                                Số điện thoại
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="phone"
                                name="phone"
                                value="<?= htmlspecialchars($phone) ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label
                                for="address"
                                class="form-label"
                            >
                                Địa chỉ
                            </label>
                            <textarea
                                class="form-control"
                                id="address"
                                name="address"
                                rows="3"
                                required
                            ><?= htmlspecialchars($address) ?></textarea>
                        </div>

                        <button
                            type="submit"
                            name="place_order"
                            class="btn btn-success"
                        >
                            Xác nhận đặt hàng
                        </button>

                    </form>

                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">

                    <h2 class="h5 mb-3">
                        Đơn hàng của bạn
                    </h2>

                    <?php foreach ($cartItems as $item): ?>
                        <div
                            class="d-flex
                                   justify-content-between
                                   border-bottom
                                   py-2"
                        >
                            <div>
                                <strong>
                                    <?= htmlspecialchars($item['ProductName']) ?>
                                </strong>
                                <div class="small text-muted">
                                    <?= (int) $item['Quantity'] ?>
                                    ×
                                    <?= number_format(
                                        (float) $item['Price'],
                                        0,
                                        ',',
                                        '.'
                                    ) ?> đ
                                </div>
                            </div>

                            <div>
                                <?= number_format(
                                    (float) $item['Subtotal'],
                                    0,
                                    ',',
                                    '.'
                                ) ?> đ
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div
                        class="d-flex
                               justify-content-between
                               fw-bold
                               fs-5
                               pt-3"
                    >
                        <span>Tổng cộng</span>
                        <span>
                            <?= number_format(
                                (float) $total,
                                0,
                                ',',
                                '.'
                            ) ?> đ
                        </span>
                    </div>

                    <a
                        href="/cart.php"
                        class="btn btn-outline-secondary mt-3"
                    >
                        Quay lại giỏ hàng
                    </a>

                </div>
            </div>
        </div>

    </div>

</div>

<?php
require_once '/var/www/src/includes/frontend/footer.php';
