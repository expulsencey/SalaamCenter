<?php
// Requires $site and escapeHtml(); load contact-handler.php before any output to prepare
// $contactValues, $contactErrors, $contactSuccess, $contactTopics and the session CSRF token.
// Optional $venueTopic supplies the existing venue label and email subject.
 if (!function_exists('escapeHtml')) { http_response_code(404); exit; } ?>
<section class="home-section contact-section" id="contact" aria-labelledby="contact-title">
    <div class="container split-layout">
        <div class="section-copy">
            <p class="courses-eyebrow">Contact</p>
            <h2 id="contact-title">Let’s talk about your next step.</h2>
            <p>Contact our team about training, consultancy or your organization’s learning needs.</p>
            <address class="contact-details">
                <a href="mailto:<?= escapeHtml($site['email']) ?><?= !empty($venueTopic) ? '?subject=' . escapeHtml(rawurlencode($venueTopic)) : '' ?>"><?= escapeHtml($site['email']) ?></a>
                <a href="<?= escapeHtml($site['phone_uri']) ?>"><?= escapeHtml($site['phone']) ?></a>
                <p><?= implode('<br>', array_map('escapeHtml', $site['address_lines'])) ?></p>
            </address>
        </div>
        <form class="contact-form" action="contact.php" method="post" aria-describedby="contact-note">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION['contact_csrf']) ?>">
            <p id="contact-note" class="form-note">Send your enquiry to our team using the form below.</p>
            <?php if ($contactSuccess): ?>
                <p class="form-feedback" role="status">Thank you. Your message has been sent successfully.</p>
            <?php endif; ?>
            <?php if ($contactErrors): ?>
                <div class="form-feedback" role="alert" id="contact-errors" tabindex="-1">
                    <p>Please review your message:</p>
                    <ul><?php foreach ($contactErrors as $field => $error): ?><li><?php if ($field !== 'form'): ?><a href="#contact-<?= escapeHtml($field) ?>"><?= escapeHtml($error) ?></a><?php else: ?><?= escapeHtml($error) ?><?php endif; ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>
            <div class="form-grid">
                <div><label for="contact-name">Name</label><input id="contact-name" name="name" value="<?= escapeHtml($contactValues['name']) ?>"<?= isset($contactErrors['name']) ? ' aria-invalid="true" aria-describedby="contact-errors"' : '' ?> autocomplete="name" maxlength="100" required></div>
                <div><label for="contact-email">Email</label><input id="contact-email" type="email" name="email" value="<?= escapeHtml($contactValues['email']) ?>"<?= isset($contactErrors['email']) ? ' aria-invalid="true" aria-describedby="contact-errors"' : '' ?> autocomplete="email" maxlength="255" required></div>
                <div><label for="contact-phone">Phone <span>(optional)</span></label><input id="contact-phone" type="tel" name="phone" value="<?= escapeHtml($contactValues['phone']) ?>"<?= isset($contactErrors['phone']) ? ' aria-invalid="true" aria-describedby="contact-errors"' : '' ?> autocomplete="tel" maxlength="30"></div>
                <div><label for="contact-topic">Topic</label><select id="contact-topic" name="topic" required<?= isset($contactErrors['topic']) ? ' aria-invalid="true" aria-describedby="contact-errors"' : '' ?>><option value="">Choose a topic</option><?php foreach ($contactTopics as $value => $label): ?><option value="<?= escapeHtml($value) ?>"<?= ($contactValues['topic'] === $value || ($_SERVER['REQUEST_METHOD'] !== 'POST' && !empty($venueTopic) && $value === 'venue-hire')) ? ' selected' : '' ?>><?= escapeHtml($value === 'venue-hire' && !empty($venueTopic) ? $venueTopic : $label) ?></option><?php endforeach; ?></select></div>
                <div class="form-message"><label for="contact-message">Message</label><textarea id="contact-message" name="message"<?= isset($contactErrors['message']) ? ' aria-invalid="true" aria-describedby="contact-errors"' : '' ?> rows="5" maxlength="4000" required><?= escapeHtml($contactValues['message']) ?></textarea></div>
            </div>
            <button class="hero-button courses-catalog" type="submit" aria-describedby="contact-note">Send Message</button>
        </form>
    </div>
</section>
