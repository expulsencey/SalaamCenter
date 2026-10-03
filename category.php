<?php
require __DIR__ . '/includes/catalog.php';
$slug = $_GET['slug'] ?? '';
$category = is_string($slug) ? ($courseCategories[$slug] ?? null) : null;
if (!$category) http_response_code(404);
$categoryCourses = $category ? array_filter($courses, static fn($course) => $course['category_slug'] === $slug) : [];
$pageTitle = ($category ? $category['label'] : 'Category not found') . ' — Salaam Center';
$activePage = 'categories';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="catalog-page">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="./">Home</a><span aria-hidden="true">/</span><a href="categories.php">Categories</a><span aria-hidden="true">/</span><span aria-current="page"><?= escapeHtml($category ? $category['label'] : 'Category not found') ?></span></nav>
        <header class="catalog-heading"><p class="courses-eyebrow">Our Courses</p><h1><?= escapeHtml($category ? $category['label'] : 'Category not found') ?></h1></header>
        <?php if ($category): ?>
            <p class="catalog-status"><?= count($categoryCourses) ?> <?= count($categoryCourses) === 1 ? 'course' : 'courses' ?></p>
            <ul class="catalog-grid">
                <?php $cardHeading = 'h2'; foreach ($categoryCourses as $course): ?>
                    <li><?php require __DIR__ . '/includes/course-card.php'; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?><p>This category is not available.</p><?php endif; ?>
        <a class="catalog-back" href="categories.php">← View All Categories</a>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
