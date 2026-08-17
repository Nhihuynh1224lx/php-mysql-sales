<?php

$pageTitle = 'Thêm nhân viên giao hàng';

require_once '/var/www/src/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $shipperName = trim($_POST['shipper_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($shipperName === '') {

        $error = 'Tên nhân viên giao hàng không được để trống.';

    } else {

        $sql = "
            INSERT INTO shippers (ShipperName, Phone)
            VALUES (?, ?)
        ";

        $stmt = $conn->prepare($sql);

        if (!$stmt) {

            $error = 'Không thể chuẩn bị câu lệnh thêm dữ liệu.';

        } else {

            $stmt->bind_param(
                'ss',
                $shipperName,
                $phone
            );

            if ($stmt->execute()) {

                header('Location: /shippers/');
                exit;

            } else {

                $error = 'Không thể thêm nhân viên giao hàng.';
            }

            $stmt->close();
        }
    }
}

require_once '/var/www/src/includes/header.php';
require_once '/var/www/src/includes/navbar.php';

?>

<div class="container mt-4">

    <h2 class="mb-4">Thêm nhân viên giao hàng</h2>

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <form method="post">

        <div class="mb-3">

            <label for="shipperName" class="form-label">
                Tên nhân viên giao hàng
            </label>

            <input
                type="text"
                class="form-control"
                id="shipperName"
                name="shipper_name"
                maxlength="100"
                value="<?= htmlspecialchars($_POST['shipper_name'] ?? '') ?>"
                required
            >

        </div>

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
                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                required
            >

        </div>

        <button type="submit" class="btn btn-primary">
            Lưu
        </button>

        <a href="/shippers/" class="btn btn-secondary">
            Hủy
        </a>

    </form>

</div>

<?php

require_once '/var/www/src/includes/footer.php';