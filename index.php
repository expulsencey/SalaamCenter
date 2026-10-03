<?php
require __DIR__ . '/includes/catalog.php';
$homeContent = require __DIR__ . '/data/home.php';
$events = require __DIR__ . '/data/events.php';
$partners = require __DIR__ . '/data/partners.php';
$pageTitle = 'Salaam Center';
$pageDescription = 'Explore professional training, research and consultancy at Salaam Center in Djibouti.';
$activePage = 'home';
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>
    <main>
        <section class="slideshow" aria-label="Discover Salaam Center" aria-roledescription="carousel">
            <div class="slideshow-images" id="slideshow-images" aria-live="off">
                <div class="slide slide-panoramic-room is-active" role="group" aria-roledescription="slide" aria-label="1 of 5">
                    <img src="assets/images/hero/salaam-panoramic-room.webp" alt="Panoramic training hall with round tables and city views." width="1080" height="618" fetchpriority="high">
                </div>
                <div class="slide slide-computer-lab" role="group" aria-roledescription="slide" aria-label="2 of 5" hidden>
                    <img src="assets/images/hero/salaam-computer-lab.webp" alt="Computer workstations in the Salaam Center training lab." width="1080" height="719" loading="lazy" decoding="async">
                </div>
                <div class="slide slide-training-classroom" role="group" aria-roledescription="slide" aria-label="3 of 5" hidden>
                    <img src="assets/images/hero/salaam-training-classroom.webp" alt="Classroom prepared for training, with the Salaam Center logo on the blue wall." width="1080" height="679" loading="lazy" decoding="async">
                </div>
                <div class="slide slide-conference-hall" role="group" aria-roledescription="slide" aria-label="4 of 5" hidden>
                    <img src="assets/images/hero/salaam-conference-hall.webp" alt="Conference hall with rows of chairs and lounge seating." width="1080" height="720" loading="lazy" decoding="async">
                </div>
                <div class="slide slide-meeting-room" role="group" aria-roledescription="slide" aria-label="5 of 5" hidden>
                    <img src="assets/images/hero/salaam-meeting-room.webp" alt="Meeting room with U-shaped tables and the Salaam Center wall logo." width="1080" height="720" loading="lazy" decoding="async">
                </div>
            </div>
            <div class="hero-content">
                <p class="hero-eyebrow">Welcome to Salaam Center</p>
                <h1 class="hero-title">A space to learn and exchange ideas.</h1>
                <p class="hero-description">Let’s discuss your training and professional development goals.</p>
                <div class="hero-actions">
                    <a class="hero-button" href="courses.php">Explore Courses</a>
                    <a class="hero-button hero-button-secondary" href="about.php">About Salaam Center</a>
                </div>
            </div>
        </section>
        <section class="featured-courses reveal" aria-labelledby="courses-title">
            <div class="container">
                <header class="courses-heading">
                    <p class="courses-eyebrow">Our Courses</p>
                    <h2 id="courses-title">Explore Our Training Programs</h2>
                    <p class="courses-introduction">Discover a selection of our training programs, then explore all courses and categories.</p>
                </header>

                <ul class="catalog-grid featured-grid" aria-label="Featured courses">
                    <?php $cardHeading = 'h3'; foreach ($featuredCourses as $course): ?>
                        <li><?php require __DIR__ . '/includes/course-card.php'; ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="courses-footer">
                    <a class="hero-button courses-catalog" href="courses.php">View All Courses <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </section>
        <?php require __DIR__ . '/includes/home-sections.php'; ?>
    </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
