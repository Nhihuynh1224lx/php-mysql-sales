<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?= htmlspecialchars($pageTitle ?? 'Sales Management') ?>
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!--
        FontAwesome 6 (CDN) — bộ icon dùng chung cho giao diện cửa hàng.
    -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        referrerpolicy="no-referrer"
    >

    <!--
        Giao diện cửa hàng. Đặt SAU Bootstrap để ghi đè được style mặc định.

        Phần ?v=... là "mã phiên bản" lấy từ thời điểm sửa file lần cuối. Mỗi
        lần storefront.css được sửa, mã này đổi theo, buộc trình duyệt tải bản
        mới thay vì dùng bản cũ trong bộ nhớ đệm (cache).

        LƯU Ý: DOCUMENT_ROOT rỗng khi chạy PHP ở chế độ dòng lệnh, nên có
        toán tử ?: 1 để đường dẫn vẫn hợp lệ trong mọi trường hợp.
    -->
    <?php $storefrontCssFile = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/assets/css/storefront.css'; ?>
    <link rel="stylesheet" href="/assets/css/storefront.css?v=<?= @filemtime($storefrontCssFile) ?: 1 ?>">

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js">
    </script>
</head>

<body>