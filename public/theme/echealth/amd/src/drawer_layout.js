/**
 * Keep the native block drawer toggle beside the dashboard customisation action.
 *
 * @module theme_echealth/drawer_layout
 */
export const init = () => {
    if (!document.body.classList.contains('pagelayout-mydashboard')) {
        return;
    }

    const toggle = document.querySelector('#page .drawer-right-toggle');
    const actions = document.querySelector('#page-header .w-100 > .d-flex.flex-wrap > .ms-auto.d-flex');
    if (toggle && actions) {
        actions.classList.add('ec-dashboard-header-actions');
        actions.insertBefore(toggle, actions.firstChild);
    }
};
