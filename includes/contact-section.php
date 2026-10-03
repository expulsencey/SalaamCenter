<?php if (!function_exists('escapeHtml')) { http_response_code(404); exit; } ?>
<section class="home-section contact-section" id="contact" aria-labelledby="contact-title">
    <div class="container split-layout">
        <div class="section-copy">
            <p class="courses-eyebrow">Contact</p>
            <h2 id="contact-title">Let’s talk about your next step.</h2>
            <p>Contact our team about training, consultancy or your organization’s learning needs.</p>
            <address class="contact-details">
                <a href="mailto:info@salaamcenter.net<?= !empty($venueTopic) ? '?subject=' . escapeHtml(rawurlencode($venueTopic)) : '' ?>">info@salaamcenter.net</a>
                <a href="tel:+25321354317">(+253) 21 35 43 17</a>
                <p>Salaam Tower, floor 10<br>Saline Ouest, Djibouti</p>
            </address>
        </div>
        <form class="contact-form" action="contact.php" method="post" aria-describedby="contact-note">
            <p id="contact-note" class="form-note">Online messaging is not available yet. Please <a href="mailto:info@salaamcenter.net<?= !empty($venueTopic) ? '?subject=' . escapeHtml(rawurlencode($venueTopic)) : '' ?>">email our team</a> or call us.</p>
            <div class="form-grid">
                <div><label for="contact-name">Name</label><input id="contact-name" name="name" autocomplete="name" maxlength="120" required></div>
                <div><label for="contact-email">Email</label><input id="contact-email" type="email" name="email" autocomplete="email" maxlength="254" required></div>
                <div><label for="contact-phone">Phone <span>(optional)</span></label><input id="contact-phone" type="tel" name="phone" autocomplete="tel" maxlength="40"></div>
                <div><label for="contact-topic">Topic</label><select id="contact-topic" name="topic" required><option value="">Choose a topic</option><option value="training">Training</option><option value="skill-assessment">Skill Assessment</option><option value="partnership">Partnership</option><option value="application">Application</option><option value="venue-hire"<?= !empty($venueTopic) ? ' selected' : '' ?>><?= escapeHtml($venueTopic ?? 'Venue Hire') ?: 'Venue Hire' ?></option></select></div>
                <div class="form-message"><label for="contact-message">Message</label><textarea id="contact-message" name="message" rows="5" maxlength="4000" required></textarea></div>
            </div>
            <button class="hero-button courses-catalog" type="submit" disabled aria-describedby="contact-note">Send Message</button>
        </form>
    </div>
</section>
