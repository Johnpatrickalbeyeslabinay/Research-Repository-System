// Shared entry for every layout (resources/views/partials/head.blade.php).
//
// Bootstrap and Lucide used to come from three CDNs on every page — one of
// them an unversioned unpkg.com/lucide@latest redirect that shipped the
// whole icon set for the 11 icons this app uses. Bundling them here gives
// one hashed, long-cached file instead.
//
// Turbo Drive makes navigation instant: links are prefetched on hover and
// pages swap in without a full reload. That changes one rule for all page
// JS — DOMContentLoaded fires only once per real load, so anything that
// binds to the DOM must run on 'turbo:load' (see onPageLoad below) and be
// safe to run again on the next page.
import * as bootstrap from 'bootstrap';
import '@hotwired/turbo';
import {
    ArrowLeft,
    ArrowRight,
    BookOpen,
    createIcons,
    Copy,
    Download,
    Eye,
    FileText,
    FileQuestion,
    Fish,
    FolderOpen,
    History,
    LayoutDashboard,
    LogIn,
    MessageCircleQuestion,
    RotateCcw,
    Search,
    Send,
    SlidersHorizontal,
    Square,
    SquarePen,
    X,
} from 'lucide';

// research-submission.js and inline view scripts reach for window.bootstrap.
window.bootstrap = bootstrap;

const icons = {
    ArrowLeft,
    ArrowRight,
    BookOpen,
    Copy,
    Download,
    Eye,
    FileText,
    FileQuestion,
    Fish,
    FolderOpen,
    History,
    LayoutDashboard,
    LogIn,
    MessageCircleQuestion,
    RotateCcw,
    Search,
    Send,
    SlidersHorizontal,
    Square,
    SquarePen,
    X,
};

// Same call shape as the old CDN global, so chat.js's
// `window.lucide.createIcons()` keeps working unchanged.
window.lucide = { createIcons: () => createIcons({ icons }) };

// Page features are split into their own chunks and only fetched on pages
// that actually have the markup for them — an anonymous visitor to /public
// never downloads the submission wizard or the charting library.
const features = [
    { selector: '#ai-chat-widget', load: () => import('./chat.js') },
    {
        selector: '[data-dropzone], [data-keyword-input], [data-researcher-input], [data-wizard], [data-review-summary], [data-analyze-btn], [data-submit-btn]',
        load: () => import('./research-submission.js'),
    },
    { selector: '[data-admin-charts]', load: () => import('./admin-charts.js') },
];

function onPageLoad() {
    window.lucide.createIcons();

    document.querySelectorAll('[data-flash-toast]').forEach((el) =>
        bootstrap.Toast.getOrCreateInstance(el, { delay: 4000 }).show()
    );

    for (const { selector, load } of features) {
        if (!document.querySelector(selector)) continue;

        load()
            .then((module) => module.init())
            .catch((error) => console.error(`[app] failed to load feature for ${selector}`, error));
    }
}

// Fires on the first load and after every Turbo navigation.
document.addEventListener('turbo:load', onPageLoad);

// Turbo snapshots the page for its back/forward cache when you leave it. An
// open modal, offcanvas or dropdown would be frozen into that snapshot —
// including its backdrop, which then blocks every click on the restored
// page — so close them first.
document.addEventListener('turbo:before-cache', () => {
    document.querySelectorAll('.modal.show').forEach((el) => bootstrap.Modal.getInstance(el)?.hide());
    document.querySelectorAll('.offcanvas.show').forEach((el) => bootstrap.Offcanvas.getInstance(el)?.hide());
    document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach((el) => el.remove());
    // A flash message belongs to the response that set it — don't replay it
    // when this page is restored with the back button.
    document.querySelectorAll('[data-flash-toast]').forEach((el) => el.closest('.toast-container')?.remove());
    document.body.classList.remove('modal-open');
    document.body.style.removeProperty('overflow');
    document.body.style.removeProperty('padding-right');
});

// Double-submit guard for every Turbo form (approve/reject/feedback/…):
// the clicked button is disabled with a spinner until the response lands.
// Forms that manage their own button state (the research submission
// wizard's [data-submit-btn]) are left alone.
document.addEventListener('turbo:submit-start', ({ detail }) => {
    const button = detail.formSubmission.submitter;
    if (!button || button.hasAttribute('data-submit-btn')) return;

    button.dataset.originalHtml = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>' + button.innerHTML;
});

document.addEventListener('turbo:submit-end', ({ detail }) => {
    const button = detail.formSubmission.submitter;
    if (!button || button.dataset.originalHtml === undefined) return;

    button.disabled = false;
    button.innerHTML = button.dataset.originalHtml;
    delete button.dataset.originalHtml;
});

// Live search on the repository listings (repository/public.blade.php):
// typing or changing a filter submits the form into the results
// turbo-frame after a short pause — only the result list is replaced, so
// the search box keeps focus and the cursor.
const LIVE_SEARCH_DELAY_MS = 300;

document.addEventListener('input', (event) => {
    const form = event.target.closest?.('form[data-live-search]');
    if (!form || event.target.type !== 'search') return;

    clearTimeout(form.liveSearchTimer);
    form.liveSearchTimer = setTimeout(() => form.requestSubmit(), LIVE_SEARCH_DELAY_MS);
});

document.addEventListener('change', (event) => {
    const form = event.target.closest?.('form[data-live-search]');
    if (!form || event.target.tagName !== 'SELECT') return;

    clearTimeout(form.liveSearchTimer);
    form.requestSubmit();
});

// The big "N Research Papers" counter sits outside the results frame;
// sync it from the count the frame's response carries.
document.addEventListener('turbo:frame-load', ({ target }) => {
    const total = target.querySelector('[data-results-total]');
    const display = document.querySelector('[data-results-total-display]');
    const label = target.querySelector('[data-results-label]');
    const labelDisplay = document.querySelector('[data-results-label-display]');

    if (total && display) {
        const count = Number(total.textContent);
        display.textContent = Number.isFinite(count) ? new Intl.NumberFormat().format(count) : total.textContent;
    }
    if (label && labelDisplay) labelDisplay.textContent = label.textContent;
});
