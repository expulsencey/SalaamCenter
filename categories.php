<?php
require __DIR__ . '/includes/catalog.php';
$pageTitle = 'Course Categories — Salaam Center';
$activePage = 'categories';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="catalog-page">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="./">Home</a><span aria-hidden="true">/</span><a href="courses.php">All Courses</a><span aria-hidden="true">/</span><span aria-current="page">Categories</span></nav>
        <header class="catalog-heading"><p class="courses-eyebrow">Our Courses</p><h1>Course Categories</h1><p>Explore our training programs by subject.</p></header>
        <ul class="catalog-grid">
            <?php foreach ($courseCategories as $key => $category): ?>
                <li><article class="course-card">
                    <a class="course-image" href="category.php?slug=<?= escapeHtml($key) ?>" tabindex="-1" aria-hidden="true"><img src="<?= escapeHtml($category['image']) ?>" alt="<?= escapeHtml($category['image_alt']) ?>" width="1000" height="560" loading="lazy"></a>
                    <div class="course-content"><h2 class="course-card-title"><a href="category.php?slug=<?= escapeHtml($key) ?>"><?= escapeHtml($category['label']) ?></a></h2></div>
                </article></li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
