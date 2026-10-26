/**
 * Accessible keyboard behavior for the mobile primary navigation drawer.
 *
 * @module     theme_echealth/navigation
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import Drawers from 'theme_boost/drawers';

const focusableSelector = 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])';

/**
 * Initialise focus trapping and Escape handling for the Moodle drawer.
 */
export const init = () => {
    const accordions = Array.from(document.querySelectorAll('[data-ec-accordion]'));
    accordions.forEach(details => {
        const label = details.querySelector(':scope > summary')?.textContent?.trim().replace(/\s+/g, ' ');
        if (!label) {
            return;
        }
        const key = `theme_echealth_navigation_${details.dataset.ecAccordion}_${label}`;
        const active = details.querySelector('a[aria-current="page"]') !== null;
        try {
            const saved = window.localStorage.getItem(key);
            if (active) {
                details.open = true;
            } else if (saved !== null) {
                details.open = saved === 'open';
            }
        } catch (error) {
            // Browsers can disable storage; the native disclosure still works.
        }
        details.addEventListener('toggle', () => {
            try {
                window.localStorage.setItem(key, details.open ? 'open' : 'closed');
            } catch (error) {
                // Keep the disclosure usable when storage is unavailable.
            }
        });
    });

    const drawer = document.getElementById('theme_boost-drawers-primary');
    const toggle = document.querySelector('[data-target="theme_boost-drawers-primary"][data-action="toggle"]');
    if (!drawer || !toggle) {
        return;
    }

    const focusableElements = () => Array.from(drawer.querySelectorAll(focusableSelector))
        .filter(element => !element.hasAttribute('hidden') && element.getClientRects().length > 0);

    const syncState = () => {
        const open = drawer.classList.contains('show');
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            drawer.setAttribute('role', 'dialog');
            drawer.setAttribute('aria-modal', 'true');
            drawer.setAttribute('aria-label', drawer.querySelector('.ec-navigation')?.getAttribute('aria-label') || 'Main navigation');
        } else {
            drawer.removeAttribute('role');
            drawer.removeAttribute('aria-modal');
            drawer.removeAttribute('aria-label');
        }
    };

    drawer.addEventListener(Drawers.eventTypes.drawerShown, syncState);
    drawer.addEventListener(Drawers.eventTypes.drawerHidden, syncState);
    syncState();

    document.addEventListener('keydown', event => {
        if (!drawer.classList.contains('show')) {
            return;
        }
        if (event.key === 'Escape') {
            event.preventDefault();
            Drawers.getDrawerInstanceForNode(drawer).closeDrawer();
            return;
        }
        if (event.key !== 'Tab') {
            return;
        }
        const focusable = focusableElements();
        if (!focusable.length) {
            event.preventDefault();
            drawer.focus();
            return;
        }
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && (document.activeElement === first || !drawer.contains(document.activeElement))) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && (document.activeElement === last || !drawer.contains(document.activeElement))) {
            event.preventDefault();
            first.focus();
        }
    });

    document.addEventListener('focusin', event => {
        if (!drawer.classList.contains('show') || drawer.contains(event.target) || event.target === toggle) {
            return;
        }
        event.stopImmediatePropagation();
        const first = focusableElements()[0];
        if (first) {
            first.focus();
        }
    }, true);
};
