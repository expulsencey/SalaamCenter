<?php
require __DIR__ . '/includes/catalog.php';
$slug = $_GET['slug'] ?? '';
$course = is_string($slug) ? ($courses[$slug] ?? null) : null;
if (!$course) {
    http_response_code(404);
}
$pageTitle = $course ? $course['name'] . ' — Salaam Center' : 'Course not found — Salaam Center';
$activePage = 'courses';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="course-page">
    <div class="container">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="./">Home</a><span aria-hidden="true">/</span><a href="courses.php">Courses</a><span aria-hidden="true">/</span><?php if ($course): ?><a href="category.php?slug=<?= escapeHtml($course['category_slug']) ?>"><?= escapeHtml($course['category']) ?></a><span aria-hidden="true">/</span><span aria-current="page" lang="<?= escapeHtml($course['title_language']) ?>"><?= escapeHtml($course['name']) ?></span><?php else: ?><span aria-current="page">Course Details</span><?php endif; ?></nav>
        <?php if (!$course): ?>
            <section class="course-not-found">
                <h1>Course not found</h1>
                <p>This course is not available. Browse the catalog to find a training program.</p>
                <a class="hero-button courses-catalog" href="courses.php">View All Courses</a>
            </section>
        <?php else: ?>
            <header class="course-page-heading">
                <p class="courses-eyebrow"><?= escapeHtml($course['category']) ?></p>
                <h1 lang="<?= escapeHtml($course['title_language']) ?>"><?= escapeHtml($course['name']) ?></h1>
                <?php if (!empty($course['subtitle'])): ?><p class="course-subtitle" lang="fr"><?= escapeHtml($course['subtitle']) ?></p><?php endif; ?>
            </header>
            <div class="course-detail-layout">
                <div class="course-detail-content">
                    <img class="course-detail-image" src="<?= escapeHtml($course['image']) ?>" alt="<?= escapeHtml($course['image_alt']) ?>" width="1000" height="560" fetchpriority="high">
                    <?php if (!empty($course['brand_image'])): ?><img class="course-brand" src="<?= escapeHtml($course['brand_image']) ?>" alt="<?= escapeHtml($course['brand_alt']) ?>" loading="lazy"><?php endif; ?>
                    <?php if ($course['description']): ?>
                        <section class="course-detail-section"><h2>Course Description</h2><p><?= escapeHtml($course['description']) ?></p></section>
                    <?php endif; ?>
                    <?php foreach (['outcomes' => 'Learning Outcomes', 'includes' => 'Course Includes', 'certificate' => 'Certificate', 'requirements' => 'Requirements', 'exam' => 'Exam & Evaluation'] as $key => $heading): ?>
                        <?php if (!empty($course[$key])): ?>
                            <section class="course-detail-section">
                                <h2><?= escapeHtml($heading) ?></h2>
                                <ul><?php foreach ($course[$key] as $item): ?><li><?= escapeHtml($item) ?></li><?php endforeach; ?></ul>
                            </section>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <aside class="course-enquiry" aria-label="Course information and enquiries">
                    <?php if (courseFacts($course)): ?>
                    <h2>Course Information</h2>
                        <dl class="course-detail-facts">
                            <?php foreach (courseFacts($course) as $label => $value): ?><div><dt><?= escapeHtml($label) ?></dt><dd><?= escapeHtml($value) ?></dd></div><?php endforeach; ?>
                        </dl>
                    <?php endif; ?>
                    <h2 class="enquiry-title">Interested in this course?</h2>
                    <a class="hero-button courses-catalog" href="mailto:info@salaamcenter.net?subject=<?= escapeHtml(rawurlencode('Course enquiry: ' . $course['name'])) ?>">Contact Salaam Center</a>
                    <a class="course-call" href="tel:+25321354317">Call (+253) 21 35 43 17</a>
                </aside>
            </div>
            <a class="catalog-back" href="courses.php">← View All Courses</a>
        <?php endif; ?>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
