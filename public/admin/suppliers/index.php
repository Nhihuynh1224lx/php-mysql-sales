<?php 

$pageTitle = 'Quản lý nhà cung cấp'; 

require_once '/var/www/src/config/database.php'; 

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
    ORDER BY SupplierID 
"; 

$result = $conn->query($sql); 

require_once '/var/www/src/includes/admin/header.php'; 
require_once '/var/www/src/includes/admin/navbar.php'; 

?> 

<div class="container mt-4"> 

    <div class="d-flex justify-content-between align-items-center mb-3"> 

        <h2>Quản lý nhà cung cấp</h2> 

        <a href="/admin/suppliers/create.php" class="btn btn-primary"> 
            Thêm nhà cung cấp 
        </a> 

    </div> 

    <div class="table-responsive"> 

        <table class="table table-bordered table-striped"> 

            <thead class="table-dark"> 

                <tr> 
                    <th>ID</th> 
                    <th>Tên nhà cung cấp</th> 
                    <th>Người liên hệ</th>
                    <th>Địa chỉ</th>
                    <th>Thành phố</th>
                    <th>Mã bưu điện</th>
                    <th>Quốc gia</th>
                    <th>Số điện thoại</th> 
                    <th>Thao tác</th> 
                </tr> 

            </thead> 

            <tbody> 

                <?php if ($result && $result->num_rows > 0): ?> 

                    <?php while ($supplier = $result->fetch_assoc()): ?> 

                        <tr> 

                            <td> 
                                <?= htmlspecialchars($supplier['SupplierID']) ?> 
                            </td> 

                            <td> 
                                <?= htmlspecialchars($supplier['SupplierName']) ?> 
                            </td>

                            <td>
                                <?= htmlspecialchars($supplier['ContactName'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($supplier['Address'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($supplier['City'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($supplier['PostalCode'] ?? '') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($supplier['Country'] ?? '') ?>
                            </td>

                            <td> 
                                <?= htmlspecialchars($supplier['Phone'] ?? '') ?> 
                            </td> 

                            <td> 

                                <a 
                                    href="/admin/suppliers/edit.php?id=<?= $supplier['SupplierID'] ?>" 
                                    class="btn btn-sm btn-warning" 
                                > 
                                    Sửa 
                                </a> 

                                <form 
                                    action="/admin/suppliers/delete.php" 
                                    method="post" 
                                    class="d-inline" 
                                    onsubmit="return confirm('Bạn có chắc muốn xóa nhà cung cấp này?');" 
                                > 

                                    <input 
                                        type="hidden" 
                                        name="id" 
                                        value="<?= $supplier['SupplierID'] ?>" 
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

                        <td colspan="9" class="text-center"> 
                            Chưa có nhà cung cấp. 
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