<?php if (!isset($venues) || !function_exists('escapeHtml')) { http_response_code(404); exit; } ?>
<section class="home-section section-soft" id="our-spaces" aria-labelledby="our-spaces-title">
    <div class="container">
        <header class="section-heading"><p class="courses-eyebrow">Our Spaces</p><h2 id="our-spaces-title">Spaces Designed for Learning</h2><p>Discover Salaam Center’s spaces for training, meetings and professional exchange.</p></header>
        <nav class="spaces-actions" aria-label="Explore our spaces">
            <?php foreach ($venues as $slug => $venue): ?><a class="hero-button button-outline" href="#<?= escapeHtml($slug) ?>"><?= escapeHtml($venue['name']) ?></a><?php endforeach; ?>
        </nav>
    </div>
</section>
<section class="home-section" id="venue-hire" aria-labelledby="venue-hire-title">
    <div class="container">
        <header class="section-heading"><p class="courses-eyebrow">Venue Hire</p><h2 id="venue-hire-title">Professional Spaces for Training, Meetings &amp; Events</h2><p>Discover flexible, professionally equipped spaces at Salaam Center for training sessions, meetings, workshops and corporate events.</p></header>
        <div class="venue-list">
            <?php foreach ($venues as $slug => $venue): ?>
                <article class="venue-panel reveal" id="<?= escapeHtml($slug) ?>" aria-labelledby="<?= escapeHtml($slug) ?>-title">
                    <img class="venue-image" src="assets/images/hero/<?= escapeHtml($venue['image']) ?>" alt="<?= escapeHtml($venue['alt']) ?>" width="1080" height="<?= $venue['height'] ?>" loading="lazy" decoding="async">
                    <div class="venue-copy">
                        <h3 id="<?= escapeHtml($slug) ?>-title"><?= escapeHtml($venue['name']) ?></h3>
                        <p class="venue-capacity">Capacity: <?= escapeHtml($venue['capacity']) ?></p>
                        <p><?= escapeHtml($venue['description']) ?></p>
                        <h4>Facilities &amp; Features</h4>
                        <ul class="venue-features"><?php foreach ($venue['features'] as $feature): ?><li><?= escapeHtml($feature) ?></li><?php endforeach; ?></ul>
                        <h4>Rates</h4>
                        <dl class="venue-rates"><?php foreach ($venue['rates'] as $period => $price): ?><div><dt><?= escapeHtml($period) ?></dt><dd>$<?= number_format($price) ?></dd></div><?php endforeach; ?></dl>
                        <a class="hero-button spaces-primary" href="contact.php?space=<?= rawurlencode($slug) ?>" aria-label="Request This Space: <?= escapeHtml($venue['name']) ?>">Request This Space</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="venue-enquiry"><h3>Planning an Event or Training Session?</h3><p>Tell us about your requirements and our team will help you identify the appropriate space.</p><a class="hero-button button-outline" href="contact.php">Contact Us</a></div>
    </div>
</section>
