"use strict";

const menuToggle = document.querySelector(".menu-toggle");
const navigation = document.querySelector("#primary-navigation");

if (menuToggle && navigation) {
    const mobileViewport = window.matchMedia("(max-width: 68rem)");

    function setMenuOpen(isOpen) {
        menuToggle.setAttribute("aria-expanded", String(isOpen));
        menuToggle.setAttribute("aria-label", isOpen ? "Close navigation menu" : "Open navigation menu");
        navigation.hidden = mobileViewport.matches && !isOpen;
    }

    function syncViewport() {
        const focusedElement = document.activeElement;
        menuToggle.hidden = !mobileViewport.matches;
        setMenuOpen(false);

        if (mobileViewport.matches && navigation.contains(focusedElement)) {
            menuToggle.focus();
        } else if (!mobileViewport.matches && focusedElement === menuToggle) {
            navigation.querySelector("a[href]").focus();
        }
    }

    menuToggle.addEventListener("click", () => {
        setMenuOpen(menuToggle.getAttribute("aria-expanded") !== "true");
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && mobileViewport.matches && menuToggle.getAttribute("aria-expanded") === "true") {
            setMenuOpen(false);
            menuToggle.focus();
        }
    });

    navigation.addEventListener("click", (event) => {
        if (mobileViewport.matches && event.target.closest("a[href]")) {
            setMenuOpen(false);
            menuToggle.focus();
        }
    });

    mobileViewport.addEventListener("change", syncViewport);
    syncViewport();
}

const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
const slideshow = document.querySelector(".slideshow");

if (slideshow) {
    const AUTOPLAY_DELAY = 5500;
    const slides = Array.from(slideshow.querySelectorAll(".slide"));
    let currentSlide = 0;
    let timer;

    function showSlide(index) {
        if (!slides.length) return;
        currentSlide = (index + slides.length) % slides.length;
        slides.forEach((slide, position) => {
            const active = position === currentSlide;
            slide.classList.toggle("is-active", active);
            slide.setAttribute("aria-hidden", String(!active));
            slide.setAttribute("aria-label", `${position + 1} of ${slides.length}`);
            slide.inert = !active;
        });
    }

    function updatePlayback() {
        window.clearInterval(timer);
        // Pause while visitors focus a Hero link.
        const hasFocus = slideshow.contains(document.activeElement);
        if (!reducedMotion.matches && !document.hidden && !hasFocus && slides.length > 1) {
            timer = window.setInterval(() => showSlide(currentSlide + 1), AUTOPLAY_DELAY);
        }
    }

    slideshow.addEventListener("focusin", updatePlayback);
    slideshow.addEventListener("focusout", () => window.setTimeout(updatePlayback, 0));
    document.addEventListener("visibilitychange", updatePlayback);
    reducedMotion.addEventListener("change", updatePlayback);

    if (slides.length) {
        showSlide(0);
        slides.forEach((slide) => { slide.hidden = false; });
        updatePlayback();
    }
}

function initCoursesMenu() {
    const menu = document.querySelector('.courses-menu');
    const summary = menu?.querySelector('summary');
    if (!menu || !summary) return;
    const syncExpanded = () => summary.setAttribute('aria-expanded', String(menu.open));
    menu.addEventListener('toggle', syncExpanded);
    syncExpanded();
    menu.addEventListener('keydown', event => {
        if (event.key === 'Escape' && menu.open) {
            event.stopPropagation();
            menu.open = false;
            summary.focus();
        }
    });
    document.addEventListener('click', event => {
        if (!menu.contains(event.target)) menu.open = false;
    });
    menu.addEventListener('focusout', () => {
        setTimeout(() => { if (!menu.contains(document.activeElement)) menu.open = false; }, 0);
    });
}
initCoursesMenu();

// Ajouter .reveal aux futures sections : une seule apparition douce par élément.
const revealElements = document.querySelectorAll(".reveal");
if ("IntersectionObserver" in window && !reducedMotion.matches) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("is-visible");
                entry.target.classList.remove("reveal-pending");
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0, rootMargin: "0px 0px -24px 0px" });
    revealElements.forEach((element) => {
        // Ne pas masquer une section déjà visible au chargement.
        if (element.getBoundingClientRect().top < window.innerHeight) {
            element.classList.add("is-visible");
        } else {
            element.classList.add("reveal-pending");
            requestAnimationFrame(() => element.classList.add("reveal-ready"));
            observer.observe(element);
        }
    });
    reducedMotion.addEventListener("change", () => {
        if (reducedMotion.matches) {
            observer.disconnect();
            revealElements.forEach((element) => {
                element.classList.remove("reveal-pending");
                element.classList.add("is-visible");
            });
        }
    });
}

// Manual progression keeps testimonials still and readable for every motion preference.
function initTestimonials() {
    const carousel = document.querySelector('.testimonials');
    if (!carousel) return;
    const slides = Array.from(carousel.querySelectorAll('.testimonial-slide'));
    const controls = carousel.querySelector('.testimonial-controls');
    const previous = carousel.querySelector('.testimonial-previous');
    const next = carousel.querySelector('.testimonial-next');
    const status = carousel.querySelector('.testimonial-status');
    if (slides.length < 2 || !controls || !previous || !next || !status) return;
    let index = 0;
    function showSlide(nextIndex) {
        index = (nextIndex + slides.length) % slides.length;
        slides.forEach((slide, position) => { slide.hidden = position !== index; });
        status.textContent = `${index + 1} / ${slides.length}`;
    }
    previous.addEventListener('click', () => showSlide(index - 1));
    next.addEventListener('click', () => showSlide(index + 1));
    controls.hidden = false;
    showSlide(0);
}
initTestimonials();

// The server validates every request; this only prevents accidental repeated clicks.
document.querySelectorAll('.contact-form').forEach(form => {
    const button = form.querySelector('button[type="submit"]');
    form.addEventListener('submit', event => {
        if (form.dataset.submitting) { event.preventDefault(); return; }
        form.dataset.submitting = 'true';
        if (button) { button.disabled = true; button.textContent = 'Sending...'; }
    });
    window.addEventListener('pageshow', () => {
        delete form.dataset.submitting;
        if (button) { button.disabled = false; button.textContent = 'Send Message'; }
    });
});


function initEventVideo() {
    const video = document.querySelector('[data-event-video]');
    if (!video) return;
    let visible = false;
    video.muted = true;
    function updatePlayback() {
        if (!visible || document.hidden || reducedMotion.matches) {
            video.pause();
            return;
        }
        const pending = video.play();
        if (pending) pending.catch(() => { /* Browser policy can prevent autoplay. */ });
    }
    document.addEventListener('visibilitychange', updatePlayback);
    reducedMotion.addEventListener('change', updatePlayback);
    // Playback needs ongoing visibility updates; reveal unobserves after entrance.
    if ('IntersectionObserver' in window) {
        const playbackObserver = new IntersectionObserver(entries => {
            visible = entries[0].isIntersecting && entries[0].intersectionRatio >= 0.2;
            updatePlayback();
        }, { threshold: [0, 0.2] });
        playbackObserver.observe(video);
    } else {
        visible = true;
    }
    updatePlayback();
}
initEventVideo();



function initAboutSlideshow() {
    const root = document.querySelector('.about-slideshow');
    if (!root) return;
    const slides = [...root.querySelectorAll('.about-slide')];
    if (slides.length < 2) return;
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let current = 0;
    let timer;
    let busy = false;

    function updatePlayback() {
        window.clearInterval(timer);
        if (!motion.matches && !document.hidden) {
            timer = window.setInterval(() => show((current + 1) % slides.length), 4000);
        }
    }

    async function show(index) {
        if (busy || index === current) return;
        busy = true;
        const next = slides[index];
        next.loading = 'eager';
        try {
            await next.decode();
        } catch (_) {
            busy = false;
            return; // Retain the current photograph if the next one cannot load.
        }
        const previous = slides[current];
        next.classList.add('is-incoming');
        // Establish the transparent starting frame before beginning the dissolve.
        void next.offsetWidth;
        next.classList.add('is-current');
        previous.setAttribute('aria-hidden', 'true');
        next.removeAttribute('aria-hidden');
        current = index;
        window.setTimeout(() => {
            previous.classList.remove('is-current');
            next.classList.remove('is-incoming');
            slides[(current + 1) % slides.length].loading = 'eager';
            busy = false;
        }, motion.matches ? 0 : 1200);
    }

    document.addEventListener('visibilitychange', updatePlayback);
    motion.addEventListener('change', updatePlayback);
    slides[1].loading = 'eager';
    updatePlayback();
}
initAboutSlideshow();
