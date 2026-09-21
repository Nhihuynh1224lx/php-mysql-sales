<?php

$pageTitle = 'Sửa nhà cung cấp';

require_once '/var/www/src/config/database.php';

$error = '';

$supplierID = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($supplierID <= 0) {
    die('Mã nhà cung cấp không hợp lệ.');
}


/*
 * Đọc dữ liệu hiện tại của nhà cung cấp
 */
$sql = "
    SELECT
        SupplierID,
        SupplierName,
        ContactName,
        Address,
        City,
        PostalCode,
        Country,
        Phone
    FROM suppliers
    WHERE SupplierID = ?
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die('Không thể chuẩn bị câu lệnh truy vấn.');
}

$stmt->bind_param('i', $supplierID);
$stmt->execute();

$result = $stmt->get_result();
$supplier = $result->fetch_assoc();

$stmt->close();

if (!$supplier) {
    die('Không tìm thấy nhà cung cấp.');
}


/*
 * Xử lý khi người dùng gửi form
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $supplierName = trim($_POST['supplier_name'] ?? '');
    $contactName = trim($_POST['contact_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postalCode = trim($_POST['postal_code'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($supplierName === '') {

        $error = 'Tên nhà cung cấp không được để trống.';

    } else {

        $sql = "
            UPDATE suppliers
            SET
                SupplierName = ?,
                ContactName = ?,
                Address = ?,
                City = ?,
                PostalCode = ?,
                Country = ?,
                Phone = ?
            WHERE SupplierID = ?
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = 'Không thể chuẩn bị câu lệnh cập nhật.';

        } else {

            $stmt->bind_param(
                'sssssssi',
                $supplierName,
                $contactName,
                $address,
                $city,
                $postalCode,
                $country,
                $phone,
                $supplierID
            );

            if ($stmt->execute()) {

                header('Location: /suppliers/');
                exit;

            } else {

                $error = 'Không thể cập nhật nhà cung cấp.';
            }

            $stmt->close();
        }
    }
}

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4">

    <h2 class="mb-4">Sửa nhà cung cấp</h2>

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="post">

        <!-- Mã nhà cung cấp -->

        <div class="mb-3">

            <label class="form-label">
                Mã nhà cung cấp
            </label>

            <input
                type="text"
                class="form-control"
                value="<?= htmlspecialchars($supplier['SupplierID']) ?>"
                disabled
            >

        </div>


        <!-- Tên nhà cung cấp -->

        <div class="mb-3">

            <label for="supplierName" class="form-label">
                Tên nhà cung cấp
            </label>

            <input
                type="text"
                class="form-control"
                id="supplierName"
                name="supplier_name"
                maxlength="200"
                value="<?= htmlspecialchars(
                    $_POST['supplier_name']
                    ?? $supplier['SupplierName']
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
                    ?? $supplier['ContactName']
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
                    ?? $supplier['Address']
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
                    ?? $supplier['City']
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
                    ?? $supplier['PostalCode']
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
                    ?? $supplier['Country']
                    ?? ''
                ) ?>"
            >

        </div>


        <!-- Số điện thoại -->

        <div class="mb-3">

            <label for="phone" class="form-label">
                Số điện thoại
            </label>

            <input
                type="text"
                class="form-control"
                id="phone"
                name="phone"
                maxlength="20"
                value="<?= htmlspecialchars(
                    $_POST['phone']
                    ?? $supplier['Phone']
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
            href="/suppliers/"
            class="btn btn-secondary"
        >
            Hủy
        </a>

    </form>

</div>

<?php

require_once '/var/www/src/includes/admin/footer.php';

$conn->close();