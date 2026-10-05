<?php
require __DIR__ . '/includes/catalog.php';
$partners = require __DIR__ . '/data/partners.php';
$venues = require __DIR__ . '/data/venues.php';
$activePage = 'about';
$pageHeading = 'About Salaam Center';
$pageTitle = $pageHeading . ' — Salaam Center';
$pageIntroParagraphs = [
    'Salaam Center is a training, research and consultancy center based in Djibouti, dedicated to developing human capital through professional learning and capacity development.',
    'The Center provides professional training programs and practical workshops designed to strengthen knowledge, skills and capabilities. Its programs support individuals, businesses and organizations across a range of fields, including finance, compliance, management and digital skills.',
    'Through practical and theoretical learning, professional certifications and international examination opportunities, Salaam Center aims to equip learners with relevant skills while helping organizations strengthen their capabilities and performance.',
    'With a focus on quality, innovation and results-oriented learning, Salaam Center contributes to the development of skilled professionals in Djibouti and the wider region.',
];
$pageIntro = $pageIntroParagraphs[0];
$pageDescription = $pageIntro;
require __DIR__ . '/includes/head.php';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="institutional-about">
    <header class="about-hero">
        <div class="container">
            <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="index.php">Home</a><span aria-hidden="true">/</span><span aria-current="page">About Salaam Center</span></nav>
            <p class="courses-eyebrow">About Salaam Center</p>
            <div class="about-hero-heading">
                <h1>Building skills.<br>Strengthening organizations.</h1>
                <p><?= escapeHtml($pageIntro) ?></p>
            </div>
        </div>
        <div class="about-slideshow" role="region" aria-roledescription="carousel" aria-label="Salaam Center photographs">
            <img class="about-slide about-slide--reception is-current" src="assets/images/about/hero/salaam-reception.webp" alt="Salaam Center reception area" width="1597" height="720" fetchpriority="high" decoding="async">
            <img class="about-slide about-slide--individual-learning" src="assets/images/about/hero/salaam-individual-learning.webp" alt="A participant working on a laptop at Salaam Center" width="2000" height="1227" loading="lazy" aria-hidden="true" decoding="async">
            <img class="about-slide about-slide--classroom-training" src="assets/images/about/hero/salaam-classroom-training.webp" alt="A trainer presenting to participants in a Salaam Center classroom" width="1500" height="1001" loading="lazy" aria-hidden="true" decoding="async">
            <img class="about-slide about-slide--group-workshop" src="assets/images/about/hero/salaam-group-workshop.webp" alt="Participants exchanging ideas around a table at Salaam Center" width="768" height="513" loading="lazy" aria-hidden="true" decoding="async">
            <img class="about-slide about-slide--training-presentation" src="assets/images/about/hero/salaam-training-presentation.webp" alt="A trainer discussing a presentation with participants at Salaam Center" width="1000" height="1000" loading="lazy" aria-hidden="true" decoding="async">
            <div class="about-slideshow-controls" hidden>
                <button type="button" class="about-slide-dot" aria-label="Show photo 1: reception" aria-pressed="true"><span aria-hidden="true"></span></button>
                <button type="button" class="about-slide-dot" aria-label="Show photo 2: individual learning" aria-pressed="false"><span aria-hidden="true"></span></button>
                <button type="button" class="about-slide-dot" aria-label="Show photo 3: classroom training" aria-pressed="false"><span aria-hidden="true"></span></button>
                <button type="button" class="about-slide-dot" aria-label="Show photo 4: group workshop" aria-pressed="false"><span aria-hidden="true"></span></button>
                <button type="button" class="about-slide-dot" aria-label="Show photo 5: training presentation" aria-pressed="false"><span aria-hidden="true"></span></button>
                <button type="button" class="about-slide-pause" aria-label="Pause slideshow">Pause</button>
            </div>
        </div>
    </header>
    <section class="home-section" aria-labelledby="about-who-title">
        <div class="container about-overview">
            <div class="about-overview-copy">
                <p class="courses-eyebrow">Who We Are</p>
                <h2 id="about-who-title">Knowledge for people.<br>Ideas for organizations.</h2>
                <div class="about-introduction">
                    <?php foreach (array_slice($pageIntroParagraphs, 1) as $paragraph): ?><p><?= escapeHtml($paragraph) ?></p><?php endforeach; ?>
                </div>
            </div>
            <figure class="about-overview-image reveal"><img src="assets/images/SCValues.webp" alt="A trainer leading a professional learning session with participants at Salaam Center" width="1300" height="800" loading="lazy" decoding="async"></figure>
        </div>
    </section>
    <section class="home-section section-soft" aria-labelledby="about-work-title">
        <div class="container">
            <header class="section-heading"><p class="courses-eyebrow">What We Do</p><h2 id="about-work-title">Learning and expertise for professional development.</h2></header>
            <div class="about-disciplines">
                <article><span class="about-number" aria-hidden="true">01</span><h3>Professional Training</h3><p>Professional training programs and practical workshops designed to strengthen knowledge, skills and capabilities.</p></article>
                <article><span class="about-number" aria-hidden="true">02</span><h3>Research &amp; Consultancy</h3><p>Research and consultancy form part of Salaam Center's work alongside professional training.</p></article>
                <article><span class="about-number" aria-hidden="true">03</span><h3>Professional Certification</h3><p>Professional certification and international examination opportunities to support learners in developing relevant skills.</p></article>
            </div>
        </div>
    </section>
    <section class="home-section about-recognition" aria-labelledby="recognition-title">
        <div class="container">
            <header class="section-heading"><p class="courses-eyebrow">Recognition</p><h2 id="recognition-title">GIFA Excellence Award<br>Human Capital Development 2022</h2><p>Salaam Center was recognized with the GIFA Excellence Award for Human Capital Development 2022 during the Global Islamic Finance Summit and Global Islamic Finance Awards held in Djibouti.</p></header>
            <dl class="about-award-facts">
                <div><dt>Award</dt><dd>GIFA Excellence Award</dd></div><div><dt>Category</dt><dd>Human Capital Development</dd></div><div><dt>Year</dt><dd>2022</dd></div><div><dt>Event</dt><dd>Global Islamic Finance Summit / Global Islamic Finance Awards</dd></div><div><dt>Location</dt><dd>Djibouti</dd></div>
            </dl>
            <div class="about-award-gallery reveal">
                <img src="assets/images/about/recognition/gifa-human-capital-development-2022-01.jpg" alt="Group photograph at the 2022 Global Islamic Finance Awards in Djibouti" width="1280" height="900" loading="lazy" decoding="async">
                <img src="assets/images/about/recognition/gifa-human-capital-development-2022-02.jpg" alt="Salaam Center representatives with the GIFA Excellence Award for Human Capital Development 2022" width="1280" height="900" loading="lazy" decoding="async">
                <img src="assets/images/about/recognition/gifa-human-capital-development-2022-03.jpg" alt="Presentation of the GIFA Excellence Award for Human Capital Development 2022" width="1280" height="900" loading="lazy" decoding="async">
            </div>
            <p class="about-recognition-note">The recognition reflects Salaam Center's presence within the professional learning and human capital development landscape.</p>
        </div>
    </section>
    <section class="home-section" aria-labelledby="about-environment-title">
        <div class="container">
            <header class="section-heading"><p class="courses-eyebrow">Our Learning Environment</p><h2 id="about-environment-title">Designed for learning,<br>exchange and collaboration.</h2><p>Discover Salaam Center's spaces for training, meetings and professional exchange.</p></header>
            <div class="about-facility-gallery">
                <?php foreach ($venues as $venue): ?>
                    <figure><img src="assets/images/hero/<?= escapeHtml($venue['image']) ?>" alt="<?= escapeHtml($venue['alt']) ?>" width="1080" height="<?= (int) $venue['height'] ?>" loading="lazy" decoding="async"><figcaption><?= escapeHtml($venue['name']) ?></figcaption></figure>
                <?php endforeach; ?>
            </div>
            <a class="text-link partners-all" href="#venue-hire">Explore Our Spaces &rarr;</a>
        </div>
        <details class="about-venue-details">
            <summary class="container">Venue hire: spaces, facilities and rates</summary>
            <?php require __DIR__ . '/includes/venue-hire.php'; ?>
        </details>
    </section>
    <section class="home-section section-soft" aria-labelledby="about-values-title">
        <div class="container about-values-layout">
            <header class="section-heading"><p class="courses-eyebrow">What We Stand For</p><h2 id="about-values-title">Learning with purpose.</h2></header>
            <div class="about-values">
                <article><h3>Practical Learning</h3><p>Bringing practical and theoretical learning together to build relevant professional skills.</p></article>
                <article><h3>Quality</h3><p>A focus on quality in professional learning and capacity development.</p></article>
                <article><h3>Innovation</h3><p>Innovation as part of our approach to learning and professional development.</p></article>
                <article><h3>Results-Oriented Learning</h3><p>Helping learners develop skills and organizations strengthen their capabilities and performance.</p></article>
            </div>
        </div>
    </section>
    <section class="home-section" aria-labelledby="about-network-title">
        <div class="container">
            <header class="section-heading"><p class="courses-eyebrow">Our Network</p><h2 id="about-network-title">Connected through learning.</h2><p>Working with organizations across professional learning, certification and skills development.</p></header>
            <?php require __DIR__ . '/includes/partner-strip.php'; ?>
            <a class="text-link partners-all" href="partners.php">View Our Partners &rarr;</a>
        </div>
    </section>
    <section class="home-section final-cta about-closing" aria-labelledby="about-cta-title">
        <div class="container"><h2 id="about-cta-title">Ready to develop your skills?</h2><p>Explore professional learning opportunities at Salaam Center.</p><div class="cta-actions"><a class="hero-button" href="courses.php">Explore Courses</a><a class="hero-button" href="contact.php">Contact Us</a></div></div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
