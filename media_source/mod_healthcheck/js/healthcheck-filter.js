/**
 * @package     Joomla.Administrator
 * @subpackage  mod_healthcheck
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    const FILTER_ALIASES = {
        alert: 'warning',
        danger: 'critical',
        error: 'critical',
        info: 'healthy',
        ok: 'healthy',
        success: 'healthy',
        warn: 'warning',
    };

    const filterBars = document.querySelectorAll('.healthcheck-filters');

    if (filterBars.length === 0) {
        return;
    }

    function normalizeStatus(value) {
        const status = String(value || '').trim().toLowerCase();

        if (status === '') {
            return 'healthy';
        }

        return FILTER_ALIASES[status] || status;
    }

    function getItemStatus(item) {
        if (item.hasAttribute('data-healthcheck-status')) {
            return normalizeStatus(item.getAttribute('data-healthcheck-status'));
        }

        return 'healthy';
    }

    function setItemVisibility(item, shouldShow) {
        item.classList.toggle('d-none', !shouldShow);
        item.hidden = !shouldShow;
        item.style.display = shouldShow ? '' : 'none';
        item.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
    }

    function initFilterBar(filterBar) {
        const filterButtons = filterBar.querySelectorAll('[data-filter]');
        const moduleRoot = filterBar.parentElement || document;
        const healthCheckItems = moduleRoot.querySelectorAll('[data-healthcheck-status]');

        if (filterButtons.length === 0 || healthCheckItems.length === 0) {
            return;
        }

        function setActiveButton(activeButton) {
            filterButtons.forEach(function(button) {
                button.classList.toggle('active', button === activeButton);
                button.setAttribute('aria-pressed', button === activeButton ? 'true' : 'false');
            });
        }

        function filterItems(filterType) {
            const normalizedFilter = normalizeStatus(filterType);

            healthCheckItems.forEach(function(item) {
                const itemStatus = getItemStatus(item);
                const shouldShow = normalizedFilter === 'all' || itemStatus === normalizedFilter;

                setItemVisibility(item, shouldShow);
            });
        }

        filterBar.addEventListener('click', function(event) {
            const button = event.target.closest('button');

            if (!button || !filterBar.contains(button)) {
                return;
            }

            const filterType = button.getAttribute('data-filter');

            if (filterType) {
                event.preventDefault();
                setActiveButton(button);
                filterItems(filterType);

                return;
            }

            if (button.querySelector('.icon-refresh')) {
                event.preventDefault();

                const defaultButton = filterBar.querySelector('[data-filter="all"]') || filterButtons[0];

                if (defaultButton) {
                    setActiveButton(defaultButton);
                }

                filterItems('all');
            }
        });

        const initialButton = filterBar.querySelector('[data-filter].active') || filterBar.querySelector('[data-filter="all"]') || filterButtons[0];

        if (initialButton) {
            setActiveButton(initialButton);
            filterItems(initialButton.getAttribute('data-filter'));
        } else {
            filterItems('all');
        }
    }

    filterBars.forEach(initFilterBar);
});
