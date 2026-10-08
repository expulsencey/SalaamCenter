<?php
// Requires $homeContent, $events, $partners, $site and escapeHtml().
// The caller must load contact-handler.php before output for the nested contact form.
 if (!isset($homeContent) || !function_exists('escapeHtml')) { http_response_code(404); exit; } ?>
<section class="home-section section-soft" id="about" aria-labelledby="about-title">
    <div class="container split-layout reveal">
        <figure class="section-visual"><img class="about-reception-image" src="assets/images/about/salaam-center-reception.webp" alt="Salaam Center reception area" width="1597" height="720" loading="lazy"></figure>
        <div class="section-copy"><p class="courses-eyebrow">About Salaam Center</p><h2 id="about-title">Knowledge for people.<br>Ideas for organizations.</h2><p><?= escapeHtml($homeContent['about']) ?></p><a class="hero-button courses-catalog" href="about.php">Learn More <span aria-hidden="true">→</span></a></div>
    </div>
</section>
<section class="home-section" aria-labelledby="benefits-title">
    <div class="container reveal">
        <header class="section-heading"><p class="courses-eyebrow">Why Learn With Salaam Center</p><h2 id="benefits-title">Training built around your goals.</h2></header>
        <div class="benefits-grid">
            <?php foreach ($homeContent['benefits'] as $benefit): ?>
                <article class="benefit-card">
                    <img class="benefit-background" src="<?= escapeHtml($benefit['image']) ?>" alt="" loading="lazy" decoding="async">
                    <h3><?= escapeHtml($benefit['title']) ?></h3><p><?= escapeHtml($benefit['text']) ?></p></article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="home-section section-soft" aria-labelledby="testimonials-title">
    <div class="container reveal testimonial-layout">
        <header class="section-heading"><p class="courses-eyebrow">What Our Learners Say</p><h2 id="testimonials-title">In their own words.</h2><p>Experiences shared on the Salaam Center website.</p></header>
        <div class="testimonials" aria-label="Learner testimonials" aria-roledescription="carousel">
            <div id="testimonial-slides" aria-live="polite">
                <?php foreach ($homeContent['testimonials'] as $position => $testimonial): ?>
                    <figure class="testimonial-slide">
                        <?php if ($testimonial['image']): ?><img class="testimonial-portrait" src="<?= escapeHtml($testimonial['image']) ?>" alt="Ayane, pictured with her testimonial on the Salaam Center website" width="743" height="456" loading="lazy"><?php endif; ?>
                        <blockquote><p>“<?= escapeHtml($testimonial['quote']) ?>”</p></blockquote>
                        <figcaption><?= escapeHtml($testimonial['name']) ?></figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
            <div class="testimonial-controls" hidden><button type="button" class="testimonial-previous" aria-label="Previous testimonial" aria-controls="testimonial-slides">←</button><span class="testimonial-status" aria-live="polite"></span><button type="button" class="testimonial-next" aria-label="Next testimonial" aria-controls="testimonial-slides">→</button></div>
        </div>
    </div>
</section>
<section class="home-section" aria-labelledby="news-title">
    <div class="container reveal">
        <header class="section-heading heading-with-link"><div><p class="courses-eyebrow">Latest From Salaam Center</p><h2 id="news-title">Learning in Action.</h2><p>Discover recent training activities, events and professional learning experiences at Salaam Center.</p></div><a class="text-link" href="events.php">View All Events <span aria-hidden="true">→</span></a></header>
        <div class="event-list home-event-grid"><?php $eventCardClickable = true; foreach (array_slice($events, 0, 2) as $event) require __DIR__ . '/event-card.php'; unset($eventCardClickable); ?></div>
    </div>
</section>
<section class="home-section section-soft spaces-section" id="our-spaces" aria-labelledby="spaces-title">
    <div class="container">
        <header class="section-heading reveal">
            <p class="courses-eyebrow">Our Spaces</p>
            <h2 id="spaces-title">Spaces Designed for Learning</h2>
            <p>Discover Salaam Center’s spaces for training, meetings and professional exchange.</p>
        </header>
        <div class="spaces-gallery">
            <figure class="space-photo space-photo-main reveal">
                <img src="assets/images/hero/salaam-training-classroom.webp" alt="Salaam Center classroom with training tables and the center logo on a blue wall" width="1080" height="679" loading="lazy" decoding="async">
                <figcaption>Training Rooms</figcaption>
            </figure>
            <figure class="space-photo reveal">
                <img src="assets/images/hero/salaam-computer-lab.webp" alt="Desktop computers arranged at individual desks in the Salaam Center computer lab" width="1080" height="719" loading="lazy" decoding="async">
                <figcaption>Computer Lab</figcaption>
            </figure>
            <figure class="space-photo reveal">
                <img src="assets/images/hero/salaam-conference-hall.webp" alt="Salaam Center conference hall with rows of chairs beside large windows" width="1080" height="720" loading="lazy" decoding="async">
                <figcaption>Conference &amp; Meeting Spaces</figcaption>
            </figure>
        </div>
        <div class="spaces-actions reveal">
            <a class="hero-button spaces-primary" href="about.php#our-spaces">Explore Our Spaces</a>
            <a class="text-link" href="contact.php">Contact Us</a>
            <a class="text-link" href="about.php#venue-hire">Venue Hire</a>
        </div>
    </div>
</section>
<section class="home-section" aria-labelledby="partners-title">
    <div class="container">
        <header class="section-heading"><p class="courses-eyebrow">Our Partners</p><h2 id="partners-title">Connected through learning.</h2><p>Working with organizations across professional learning, certification and skills development.</p></header>
        <?php require __DIR__ . '/partner-strip.php'; ?>
        <a class="text-link partners-all" href="partners.php">View All Partners <span aria-hidden="true">→</span></a>
    </div>
</section>
<section class="home-section final-cta" aria-labelledby="cta-title">
    <div class="container"><p class="courses-eyebrow">Ready to Develop Your Skills?</p><h2 id="cta-title">Find the right training for your professional goals.</h2><div class="cta-actions"><a class="hero-button courses-catalog" href="courses.php">Explore Courses</a><a class="hero-button button-outline" href="contact.php">Contact Us</a></div></div>
</section>
<?php require __DIR__ . '/contact-section.php'; ?>
