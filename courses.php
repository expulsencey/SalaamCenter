<?php
require __DIR__ . '/includes/catalog.php';
$pageTitle = 'All Courses — Salaam Center';
$activePage = 'courses';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="catalog-page">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="./">Home</a><span aria-hidden="true">/</span><span aria-current="page">Courses</span></nav>
        <header class="catalog-heading">
            <p class="courses-eyebrow">Our Courses</p>
            <h1>All Courses</h1>
            <p>Browse our courses and explore the available sessions, fees and course details.</p>
        </header>
        <p class="catalog-status"><?= count($courses) ?> courses · <a href="categories.php">Browse Categories</a></p>
        <ul class="catalog-grid" id="catalog-grid">
            <?php $cardHeading = 'h2'; foreach ($courses as $course): ?>
                <li data-category="<?= escapeHtml($course['category_slug']) ?>"><?php require __DIR__ . '/includes/course-card.php'; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
