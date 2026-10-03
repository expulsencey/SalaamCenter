<?php if (!isset($pageTitle, $activePage) || !function_exists('escapeHtml')) { http_response_code(404); exit; } ?>
    <div class="top-bar">
        <div class="container top-bar-content">
            <ul class="contact-links" aria-label="Contact details">
                <li><a href="tel:+25321354317">(+253) 21 35 43 17</a></li>
                <li><a href="mailto:info@salaamcenter.net">info@salaamcenter.net</a></li>
            </ul>
            <ul class="social-links" aria-label="Social media">
                <li><a href="https://www.facebook.com/SalaamCenterDjibouti" aria-label="Salaam Center on Facebook">Facebook</a></li>
                <li><a href="https://www.linkedin.com/company/salaam-center/" aria-label="Salaam Center on LinkedIn">LinkedIn</a></li>
            </ul>
        </div>
    </div>

    <header class="site-header">
        <div class="container header-content">
            <a class="site-logo" href="index.php" aria-label="Salaam Center — Home">
                <img src="assets/images/logo/salaam-center-logo.webp" alt="Salaam Center" width="2271" height="761">
            </a>

            <button class="menu-toggle" type="button" aria-label="Open navigation menu" aria-controls="primary-navigation" aria-expanded="false" hidden>
                <span class="menu-toggle-icon" aria-hidden="true"><span></span><span></span><span></span></span>
            </button>

            <nav class="primary-navigation" id="primary-navigation" aria-label="Main navigation">
                <ul class="navigation-list" lang="en">
                    <li><a class="navigation-link" href="index.php"<?= $activePage === 'home' ? ' aria-current="page"' : '' ?>>Home</a></li>
                    <li><a class="navigation-link" href="about.php"<?= $activePage === 'about' ? ' aria-current="page"' : '' ?>>Salaam Center</a></li>
                    <li><details class="courses-menu">
                        <summary aria-controls="courses-submenu" class="navigation-link<?= in_array($activePage, ['courses', 'categories'], true) ? ' navigation-section-active' : '' ?>">Courses <span aria-hidden="true">▾</span></summary>
                        <ul class="courses-submenu" id="courses-submenu">
                            <li><a href="courses.php"<?= $activePage === 'courses' ? ' aria-current="page"' : '' ?>>All Courses</a></li>
                            <li><a href="categories.php"<?= $activePage === 'categories' ? ' aria-current="page"' : '' ?>>Categories</a></li>
                        </ul>
                    </details></li>
                    <li><a class="navigation-link" href="partners.php"<?= $activePage === 'partners' ? ' aria-current="page"' : '' ?>>Partners</a></li>
                    <li><a class="navigation-link" href="events.php"<?= $activePage === 'events' ? ' aria-current="page"' : '' ?>>Events</a></li>
                    <li><a class="navigation-link" href="sc-business.php"<?= $activePage === 'sc-business' ? ' aria-current="page"' : '' ?>>SC Business</a></li>
                    <li><a class="navigation-link" href="contact.php"<?= $activePage === 'contact' ? ' aria-current="page"' : '' ?>>Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

