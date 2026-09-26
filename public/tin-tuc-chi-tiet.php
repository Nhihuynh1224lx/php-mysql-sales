<?php

require_once '/var/www/src/config/session.php';
require_once '/var/www/src/config/database.php';
require_once '/var/www/src/config/brand.php';
require_once '/var/www/src/includes/helpers.php';

/* ------------------------------------------------------------------
 * Lấy bài viết theo id trên URL
 * ------------------------------------------------------------------ */

$newsID = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;

if ($newsID <= 0) {
    header('Location: /tin-tuc.php');
    exit;
}

$sql = "
    SELECT
        NewsID,
        Title,
        Summary,
        Content,
        ImageFile,
        Category,
        Author,
        PublishedAt

    FROM news

    WHERE NewsID = ?
      AND IsPublished = 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $newsID);
$stmt->execute();
$result = $stmt->get_result();
$article = $result->fetch_assoc();
$result->free();
$stmt->close();

/* Không tìm thấy thì quay về danh sách */
if (!$article) {
    header('Location: /tin-tuc.php');
    exit;
}

/* ------------------------------------------------------------------
 * Ba bài viết cùng chuyên mục, hiển thị ở cuối trang
 * ------------------------------------------------------------------ */

$sqlRelated = "
    SELECT
        NewsID,
        Title,
        Summary,
        ImageFile,
        Category,
        PublishedAt

    FROM news

    WHERE IsPublished = 1
      AND Category = ?
      AND NewsID <> ?

    ORDER BY
        PublishedAt DESC,
        NewsID DESC

    LIMIT 3
";

$stmtRelated = $conn->prepare($sqlRelated);
$stmtRelated->bind_param('si', $article['Category'], $newsID);
$stmtRelated->execute();
$relatedResult = $stmtRelated->get_result();

$pageTitle = $article['Title'];

require_once '/var/www/src/includes/frontend/header.php';
require_once '/var/www/src/includes/frontend/navbar.php';

?>

<!-- ============================================================
     TIÊU ĐỀ BÀI VIẾT
     ============================================================ -->
<section class="page-hero page-hero--article">

    <div class="container">

        <nav aria-label="breadcrumb">

            <ol class="breadcrumb page-breadcrumb">

                <li class="breadcrumb-item">
                    <a href="/">Trang chủ</a>
                </li>

                <li class="breadcrumb-item">
                    <a href="/tin-tuc.php">Tin tức</a>
                </li>

                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($article['Category']) ?>
                </li>

            </ol>

        </nav>

        <span class="news-category">
            <?= htmlspecialchars($article['Category']) ?>
        </span>

        <h1 class="page-hero-title">
            <?= htmlspecialchars($article['Title']) ?>
        </h1>

        <p class="article-meta">

            <span>
                <i class="fa-regular fa-calendar"></i>
                <?= htmlspecialchars(
                    format_date($article['PublishedAt'])
                ) ?>
            </span>

            <?php if (!empty($article['Author'])): ?>

                <span>
                    <i class="fa-regular fa-user"></i>
                    <?= htmlspecialchars($article['Author']) ?>
                </span>

            <?php endif; ?>

        </p>

    </div>

</section>

<!-- ============================================================
     NỘI DUNG BÀI VIẾT
     ============================================================ -->
<article class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-9">

            <?php if (!empty($article['ImageFile'])): ?>

                <div class="article-cover">

                    <img
                        src="/uploads/news/<?=
                            htmlspecialchars($article['ImageFile'])
                        ?>"
                        alt="<?= htmlspecialchars($article['Title']) ?>"
                    >

                </div>

            <?php endif; ?>

            <?php if (!empty($article['Summary'])): ?>

                <p class="article-lead">
                    <?= htmlspecialchars($article['Summary']) ?>
                </p>

            <?php endif; ?>

            <?php
            /*
             * Nội dung bài viết được nhập ở dạng văn bản thuần, mỗi đoạn
             * cách nhau bằng dòng trống. Tách thành từng đoạn rồi bọc
             * trong thẻ <p> để hiển thị đúng, KHÔNG dùng nl2br vì như vậy
             * khoảng cách giữa các đoạn sẽ không đều.
             *
             * Mọi đoạn đều đi qua htmlspecialchars() trước khi in ra.
             */
            $paragraphs = preg_split(
                '/\n\s*\n/',
                (string) $article['Content']
            );

            foreach ($paragraphs as $paragraph):

                $paragraph = trim($paragraph);

                if ($paragraph === '') {
                    continue;
                }

                ?>

                <p class="article-paragraph">
                    <?= htmlspecialchars($paragraph) ?>
                </p>

                <?php

            endforeach;
            ?>

            <div class="article-footer">

                <a href="/tin-tuc.php" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Về danh sách tin tức
                </a>

            </div>

        </div>

    </div>

</article>

<?php if ($relatedResult && $relatedResult->num_rows > 0): ?>

    <!-- ============================================================
         BÀI VIẾT LIÊN QUAN
         ============================================================ -->
    <section class="container pb-5">

        <div class="section-heading">

            <div>
                <h2 class="section-title">Bài viết liên quan</h2>
                <p class="section-subtitle">
                    Cùng chuyên mục
                    "<?= htmlspecialchars($article['Category']) ?>".
                </p>
            </div>

        </div>

        <div class="row g-4">

            <?php while ($related = $relatedResult->fetch_assoc()): ?>

                <div class="col-md-6 col-lg-4">

                    <article class="news-card">

                        <a
                            class="news-thumb"
                            href="/tin-tuc-chi-tiet.php?id=<?=
                                (int) $related['NewsID']
                            ?>"
                        >

                            <?php if (!empty($related['ImageFile'])): ?>

                                <img
                                    src="/uploads/news/<?=
                                        htmlspecialchars(
                                            $related['ImageFile']
                                        )
                                    ?>"
                                    alt="<?=
                                        htmlspecialchars(
                                            $related['Title']
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
                                <?= htmlspecialchars($related['Category']) ?>
                            </span>

                            <h3 class="news-card-title">
                                <a
                                    href="/tin-tuc-chi-tiet.php?id=<?=
                                        (int) $related['NewsID']
                                    ?>"
                                >
                                    <?= htmlspecialchars($related['Title']) ?>
                                </a>
                            </h3>

                            <p class="news-date">
                                <i class="fa-regular fa-calendar"></i>
                                <?= htmlspecialchars(
                                    format_date($related['PublishedAt'])
                                ) ?>
                            </p>

                            <p class="news-summary">
                                <?= htmlspecialchars($related['Summary']) ?>
                            </p>

                        </div>

                    </article>

                </div>

            <?php endwhile; ?>

        </div>

    </section>

<?php endif; ?>

<?php
require_once '/var/www/src/includes/frontend/footer.php';
