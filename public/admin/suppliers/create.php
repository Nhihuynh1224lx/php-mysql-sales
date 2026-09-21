<?php 
 
$pageTitle = 'Thêm nhà cung cấp'; 
 
require_once '/var/www/src/config/database.php'; 
 
$error = ''; 
 
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
            INSERT INTO suppliers
                (SupplierName, ContactName, Address, City, PostalCode, Country, Phone)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        "; 
 
        $stmt = $conn->prepare($sql); 
 
        if (!$stmt) { 
 
            $error = 'Không thể chuẩn bị câu lệnh thêm dữ liệu.'; 
 
        } else { 
 
            $stmt->bind_param( 
                'sssssss',
                $supplierName,
                $contactName,
                $address,
                $city,
                $postalCode,
                $country,
                $phone
            ); 
 
            if ($stmt->execute()) { 
 
                header('Location: /admin/suppliers/'); 
                exit; 
 
            } else { 
 
                $error = 'Không thể thêm nhà cung cấp.'; 
            } 
 
            $stmt->close(); 
        } 
    } 
} 
 
require_once '/var/www/src/includes/admin/header.php'; 
require_once '/var/www/src/includes/admin/navbar.php'; 
 
?> 
 
<div class="container mt-4"> 
 
    <h2 class="mb-4">Thêm nhà cung cấp</h2> 
 
    <?php if ($error !== ''): ?> 
 
        <div class="alert alert-danger"> 
            <?= htmlspecialchars($error) ?> 
        </div> 
 
    <?php endif; ?> 
 
    <form method="post"> 
 
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
                value="<?= htmlspecialchars($_POST['supplier_name'] ?? '') ?>" 
                required 
            > 
 
        </div>

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
                value="<?= htmlspecialchars($_POST['contact_name'] ?? '') ?>" 
            > 
 
        </div>

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
                value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" 
            > 
 
        </div>

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
                value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" 
            > 
 
        </div>

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
                value="<?= htmlspecialchars($_POST['postal_code'] ?? '') ?>" 
            > 
 
        </div>

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
                value="<?= htmlspecialchars($_POST['country'] ?? '') ?>" 
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
            > 
 
        </div> 
 
        <button type="submit" class="btn btn-primary"> 
            Lưu 
        </button> 
 
        <a href="/admin/suppliers/" class="btn btn-secondary"> 
            Hủy 
        </a> 
 
    </form> 
 
</div> 
 
<?php 
 
require_once '/var/www/src/includes/admin/footer.php';