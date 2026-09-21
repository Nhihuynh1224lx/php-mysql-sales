<?php

$pageTitle = 'Sửa khách hàng';

require_once '/var/www/src/config/database.php';

$error = '';

$customerID = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($customerID <= 0) {
    die('Mã khách hàng không hợp lệ.');
}


/*
 * Đọc dữ liệu hiện tại của khách hàng
 */
$sql = "
    SELECT
        CustomerID,
        CustomerName,
        ContactName,
        Address,
        City,
        PostalCode,
        Country
    FROM customers
    WHERE CustomerID = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Không thể chuẩn bị câu lệnh truy vấn.');
}

$stmt->bind_param('i', $customerID);
$stmt->execute();

$result = $stmt->get_result();
$customer = $result->fetch_assoc();

$stmt->close();

if (!$customer) {
    die('Không tìm thấy khách hàng.');
}


/*
 * Xử lý khi người dùng gửi form
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $customerName = trim($_POST['customer_name'] ?? '');
    $contactName = trim($_POST['contact_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $country = trim($_POST['country'] ?? '');

    if ($customerName === '') {

        $error = 'Tên khách hàng không được để trống.';

    } else {

        $sql = "
            UPDATE customers
            SET
                CustomerName = ?,
                ContactName = ?,
                Address = ?,
                City = ?,
                PostalCode = ?,
                Country = ?
            WHERE CustomerID = ?
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = 'Không thể chuẩn bị câu lệnh cập nhật.';

        } else {

            $stmt->bind_param(
                'ssssssi',
                $customerName,
                $contactName,
                $address,
                $city,
                $postalCode,
                $country,
                $customerID
            );

            if ($stmt->execute()) {

                header('Location: /admin/customers/');
                exit;

            } else {

                $error = 'Không thể cập nhật khách hàng.';
            }

            $stmt->close();
        }
    }
}

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4">

    <h2 class="mb-4">Sửa khách hàng</h2>

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="post">

        <!-- Mã khách hàng -->

        <div class="mb-3">

            <label class="form-label">
                Mã khách hàng
            </label>

            <input
                type="text"
                class="form-control"
                value="<?= htmlspecialchars($customer['CustomerID']) ?>"
                disabled
            >

        </div>


        <!-- Tên khách hàng -->

        <div class="mb-3">

            <label for="customerName" class="form-label">
                Tên khách hàng
            </label>

            <input
                type="text"
                class="form-control"
                id="customerName"
                name="customer_name"
                maxlength="100"
                value="<?= htmlspecialchars(
                    $_POST['customer_name']
                    ?? $customer['CustomerName']
                ) ?>"
                required
            >

        </div>


        <!-- Người liên hệ -->

        <div class="mb-3">

            <label for="contactName" class="form-label">
                Người liên hệ
            </label>

            <input
                type="text"
                class="form-control"
                id="contactName"
                name="contact_name"
                maxlength="100"
                value="<?= htmlspecialchars(
                    $_POST['contact_name']
                    ?? $customer['ContactName']
                    ?? ''
                ) ?>"
            >

        </div>


        <!-- Địa chỉ -->

        <div class="mb-3">

            <label for="address" class="form-label">
                Địa chỉ
            </label>

            <input
                type="text"
                class="form-control"
                id="address"
                name="address"
                maxlength="200"
                value="<?= htmlspecialchars(
                    $_POST['address']
                    ?? $customer['Address']
                    ?? ''
                ) ?>"
            >

        </div>


        <!-- Thành phố -->

        <div class="mb-3">

            <label for="city" class="form-label">
                Thành phố
            </label>

            <input
                type="text"
                class="form-control"
                id="city"
                name="city"
                maxlength="100"
                value="<?= htmlspecialchars(
                    $_POST['city']
                    ?? $customer['City']
                    ?? ''
                ) ?>"
            >

        </div>


        <!-- Mã bưu điện -->

        <div class="mb-3">

            <label for="postalCode" class="form-label">
                Mã bưu điện
            </label>

            <input
                type="text"
                class="form-control"
                id="postalCode"
                name="postal_code"
                maxlength="20"
                value="<?= htmlspecialchars(
                    $_POST['postal_code']
                    ?? $customer['PostalCode']
                    ?? ''
                ) ?>"
            >

        </div>


        <!-- Quốc gia -->

        <div class="mb-3">

            <label for="country" class="form-label">
                Quốc gia
            </label>

            <input
                type="text"
                class="form-control"
                id="country"
                name="country"
                maxlength="100"
                value="<?= htmlspecialchars(
                    $_POST['country']
                    ?? $customer['Country']
                    ?? ''
                ) ?>"
            >

        </div>


        <!-- Nút -->

        <button
            type="submit"
            class="btn btn-warning"
        >
            Cập nhật
        </button>

        <a
            href="/admin/customers/"
            class="btn btn-secondary"
        >
            Hủy
        </a>

    </form>

</div>

<?php

require_once '/var/www/src/includes/admin/footer.php';

$conn->close();