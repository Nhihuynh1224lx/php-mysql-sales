<?php

require_once '/var/www/src/config/session.php';
require_once '/var/www/src/config/database.php';
require_once '/var/www/src/config/brand.php';
require_once '/var/www/src/includes/helpers.php';

/* ------------------------------------------------------------------
 * BÀI VIẾT NỔI BẬT (bài mới nhất, hiển thị to ở đầu trang)
 * ------------------------------------------------------------------ */

$featuredResult = $conn->query("
    SELECT
        NewsID,
        Title,
        Summary,
        ImageFile,
        Category,
        PublishedAt

    FROM news

    WHERE IsPublished = 1

    ORDER BY
        PublishedAt DESC,
        NewsID DESC

    LIMIT 1
");

$featured = $featuredResult ? $featuredResult->fetch_assoc() : null;

/* ------------------------------------------------------------------
 * CÁC BÀI CÒN LẠI
 * ------------------------------------------------------------------
 * Bài nổi bật đã lấy ở trên nên loại nó ra khỏi danh sách, tránh
 * hiển thị trùng hai lần.
 */

$featuredID = $featured ? (int) $featured['NewsID'] : 0;

$sql = "
    SELECT
        NewsID,
        Title,
        Summary,
        ImageFile,
        Category,
        PublishedAt

    FROM news

    WHERE IsPublished = 1
";

if ($featuredID > 0) {
    $sql .= "
        AND NewsID <> ?
    ";
}

$sql .= "
    ORDER BY
        PublishedAt DESC,
        NewsID DESC
";

$stmt = $conn->prepare($sql);

if ($featuredID > 0) {
    $stmt->bind_param('i', $featuredID);
}

$stmt->execute();

$newsResult = $stmt->get_result();

/* ------------------------------------------------------------------
 * Bộ lọc theo chuyên mục
 * ------------------------------------------------------------------ */

$categoryResult = $conn->query("
    SELECT DISTINCT Category
    FROM news
    WHERE IsPublished = 1
    ORDER BY Category
");

$categories = [];

while ($row = $categoryResult->fetch_assoc()) {
    $categories[] = $row['Category'];
}

$categoryResult->free();

$pageTitle = 'Tin tức';

require_once '/var/www/src/includes/frontend/header.php';
require_once '/var/www/src/includes/frontend/navbar.php';

?>

<!-- ============================================================
     TIÊU ĐỀ TRANG
     ============================================================ -->
<section class="page-hero">

    <div class="container">

        <nav aria-label="breadcrumb">

            <ol class="breadcrumb page-breadcrumb">

                <li class="breadcrumb-item">
                    <a href="/">Trang chủ</a>
                </li>

                <li class="breadcrumb-item active" aria-current="page">
                    Tin tức
                </li>

            </ol>

        </nav>

        <h1 class="page-hero-title">Tin tức &amp; kiến thức bida</h1>

        <p class="page-hero-text">
            Kinh nghiệm chọn thiết bị, hướng dẫn bảo quản và tin tức
            từ <?= htmlspecialchars($brand['name']) ?>.
        </p>

    </div>

</section>

<?php if ($featured): ?>

    <!-- ============================================================
         BÀI NỔI BẬT
         ============================================================ -->
    <section class="container pt-5">

        <span class="featured-tag">
            <i class="fa-solid fa-star"></i>
            Bài nổi bật
        </span>

        <div class="row g-4 featured-article">

            <div class="col-md-5">

                <?php if (!empty($featured['ImageFile'])): ?>

                    <div class="featured-thumb">

                        <img
                            src="/uploads/news/<?=
                                htmlspecialchars($featured['ImageFile'])
                            ?>"
                            alt="<?=
                                htmlspecialchars($featured['Title'])
                            ?>"
                        >

                    </div>

                <?php else: ?>

                    <div class="featured-thumb is-empty">
                        <i class="fa-regular fa-newspaper"></i>
                    </div>

                <?php endif; ?>

            </div>

            <div class="col-md-7">

                <span class="news-category">
                    <?= htmlspecialchars($featured['Category']) ?>
                </span>

                <h2 class="featured-title">
                    <?= htmlspecialchars($featured['Title']) ?>
                </h2>

                <p class="news-date">
                    <i class="fa-regular fa-calendar"></i>
                    <?= htmlspecialchars(
                        format_date($featured['PublishedAt'])
                    ) ?>
                </p>

                <p class="news-summary">
                    <?= htmlspecialchars($featured['Summary']) ?>
                </p>

                <a
                    href="/tin-tuc-chi-tiet.php?id=<?=
                        (int) $featured['NewsID']
                    ?>"
                    class="btn btn-primary"
                >
                    Đọc bài viết
                    <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>

            </div>

        </div>

    </section>

<?php endif; ?>

<!-- ============================================================
     DANH SÁCH BÀI VIẾT
     ============================================================ -->
<section class="container py-5">

    <div class="section-heading">

        <div>
            <h2 class="section-title">Bài viết mới</h2>
            <p class="section-subtitle">
                Cập nhật kinh nghiệm và thông tin hữu ích cho người chơi.
            </p>
        </div>

        <?php if ($categories): ?>

            <div class="news-filter">

                <?php foreach ($categories as $category): ?>

                    <a
                        href="/tin-tuc.php?category=<?=
                            urlencode($category)
                        ?>"
                        class="news-filter-item"
                    >
                        <?= htmlspecialchars($category) ?>
                    </a>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

    <?php if ($newsResult && $newsResult->num_rows > 0): ?>

        <div class="row g-4">

            <?php while ($article = $newsResult->fetch_assoc()): ?>

                <div class="col-md-6 col-lg-4">

                    <article class="news-card">

                        <a
                            class="news-thumb"
                            href="/tin-tuc-chi-tiet.php?id=<?=
                                (int) $article['NewsID']
                            ?>"
                        >

                            <?php if (!empty($article['ImageFile'])): ?>

                                <img
                                    src="/uploads/news/<?=
                                        htmlspecialchars(
                                            $article['ImageFile']
                                        )
                                    ?>"
                                    alt="<?=
                                        htmlspecialchars(
                                            $article['Title']
                                        )
                                    ?>"
                                >

                            <?php else: ?>

                                <span class="news-thumb-placeholder">
                                    <i class="fa-regular fa-newspaper"></i>
                                </span>

                            <?php endif; ?>

                        </a>

                        <div class="news-card-body">

                            <span class="news-category">
                                <?= htmlspecialchars($article['Category']) ?>
                            </span>

                            <h3 class="news-card-title">
                                <a
                                    href="/tin-tuc-chi-tiet.php?id=<?=
                                        (int) $article['NewsID']
                                    ?>"
                                >
                                    <?= htmlspecialchars($article['Title']) ?>
                                </a>
                            </h3>

                            <p class="news-date">
                                <i class="fa-regular fa-calendar"></i>
                                <?= htmlspecialchars(
                                    format_date($article['PublishedAt'])
                                ) ?>
                            </p>

                            <p class="news-summary">
                                <?= htmlspecialchars($article['Summary']) ?>
                            </p>

                            <a
                                href="/tin-tuc-chi-tiet.php?id=<?=
                                    (int) $article['NewsID']
                                ?>"
                                class="news-more"
                            >
                                Đọc tiếp
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>

                        </div>

                    </article>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="alert alert-info">
            Hiện chưa có bài viết nào.
        </div>

    <?php endif; ?>

</section>

<?php
require_once '/var/www/src/includes/frontend/footer.php';
