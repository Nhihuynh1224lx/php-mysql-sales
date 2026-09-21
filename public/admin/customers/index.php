<?php

$pageTitle = 'Quản lý khách hàng';

require_once '/var/www/src/config/database.php';

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
    ORDER BY CustomerID
";

$result = $conn->query($sql);

require_once '/var/www/src/includes/admin/header.php';
require_once '/var/www/src/includes/admin/navbar.php';

?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h2>Quản lý khách hàng</h2>

        <a href="/admin/customers/create.php" class="btn btn-primary">
            Thêm khách hàng
        </a>

    </div>

    <div class="table-responsive">

        <table class="table table-bordered table-striped">

            <thead class="table-dark">

                <tr>
                    <th>ID</th>
                 <th>Tên khách hàng</th>
                 <th>Người liên hệ</th>
                 <th>Địa chỉ</th>
                <th>Thành phố</th>
                <th>Mã bưu điện</th>
                <th>Quốc gia</th>
        <th>Thao tác</th>
                </tr>

            </thead>

            <tbody>

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($customer = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($customer['CustomerID']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($customer['CustomerName']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($customer['ContactName'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($customer['Address'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($customer['City'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($customer['PostalCode'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($customer['Country'] ?? '') ?>
                            </td>

                            <td>

                                <a
                                    href="/admin/customers/edit.php?id=<?= $customer['CustomerID'] ?>"
                                    class="btn btn-sm btn-warning"
                                >
                                    Sửa
                                </a>

                                <form
                                    action="/admin/customers/delete.php"
                                    method="post"
                                    class="d-inline"
                                    onsubmit="return confirm('Bạn có chắc muốn xóa khách hàng này?');"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= $customer['CustomerID'] ?>"
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

                        <td colspan="4" class="text-center">
                            Chưa có khách hàng.
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