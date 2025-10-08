const DEFAULT_STORAGE_KEYS = {
    orientation: 'wsdkDashboardNavOrientation',
    tip: 'wsdkDashboardTipDismissed',
};

const DEFAULT_CLASSES = {
    verticalNav: 'is-vertical',
    verticalRoot: 'wsdk-dashboard--nav-vertical',
    ready: 'wsdk-dashboard--ready',
};

function getFocusableElements(container) {
    if (!container) {
        return [];
    }

    const selectors = [
        'a[href]',
        'button:not([disabled])',
        'textarea:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
    ];

    return Array.from(container.querySelectorAll(selectors.join(','))).filter((element) => {
        if (element.getAttribute('aria-hidden') === 'true') {
            return false;
        }

        if (element.hasAttribute('disabled')) {
            return false;
        }

        if (element.tabIndex < 0) {
            return false;
        }

        return true;
    });
}

function resolveContainer(target) {
    if (!target) {
        return null;
    }

    if (typeof target === 'string') {
        return document.querySelector(target);
    }

    if (target instanceof HTMLElement) {
        return target;
    }

    return null;
}

function loadPreference(key, fallback) {
    try {
        const value = window.localStorage.getItem(key);
        return value === null ? fallback : value;
    } catch (error) {
        return fallback;
    }
}

function savePreference(key, value) {
    try {
        window.localStorage.setItem(key, value);
    } catch (error) {
        // Ignore storage failures silently
    }
}

function applyOrientation(root, nav, toggle, orientation, labels, classes = {}) {
    if (!nav) {
        return;
    }

    const navClass = classes.verticalNav || DEFAULT_CLASSES.verticalNav;
    const rootClass = classes.verticalRoot || DEFAULT_CLASSES.verticalRoot;

    const vertical = orientation === 'vertical';
    nav.classList.toggle(navClass, vertical);
    if (root) {
        root.classList.toggle(rootClass, vertical);
    }

    if (toggle) {
        toggle.setAttribute('aria-pressed', vertical ? 'true' : 'false');
        toggle.dataset.orientation = vertical ? 'vertical' : 'horizontal';
        const icon = toggle.querySelector('[data-orientation-icon]');
        if (icon) {
            icon.textContent = vertical ? 'view_stream' : 'view_week';
        }
        const nextOrientation = vertical ? 'horizontal' : 'vertical';
        const titleLabel = nextOrientation === 'vertical' ? labels.vertical : labels.horizontal;
        if (titleLabel) {
            toggle.setAttribute('title', titleLabel);
        }
    }
}

function renderBadges(container, attributeName = 'data-wsdk-badges') {
    if (!container) {
        return;
    }

    const dataAttr = container.getAttribute(attributeName);
    if (!dataAttr) {
        return;
    }

    let payload;
    try {
        payload = JSON.parse(dataAttr);
    } catch (error) {
        console.error('[WSDK Dashboard] Failed to parse badge payload', error);
        return;
    }

    container.innerHTML = '';

    const items = Array.isArray(payload.items) ? payload.items : [];
    if (!items.length) {
        if (payload.emptyLabel) {
            const badge = document.createElement('span');
            badge.className = 'wsdk-badge wsdk-badge--muted';
            badge.textContent = payload.emptyLabel;
            container.appendChild(badge);
        }
        return;
    }

    items.forEach((item) => {
        const action = item.action || null;
        const hasLink = action === 'link' && item.href;
        let elementTag = 'span';

        if (action === 'version') {
            elementTag = 'button';
        } else if (hasLink) {
            elementTag = 'a';
        }

        const badge = document.createElement(elementTag);
        const type = item.type || 'info';
        badge.classList.add('wsdk-badge', `wsdk-badge--${type}`);

        if (action === 'version') {
            const modalTarget = item.modalTarget || payload.modalTarget || '';
            const title = item.title || payload.triggerTitle || '';
            const assistive = item.assistive || payload.triggerAssistive || title || '';

            badge.type = 'button';
            badge.setAttribute('data-wsdk-version-open', '');
            if (modalTarget) {
                badge.setAttribute('data-wsdk-version-target', modalTarget);
            }
            badge.setAttribute('aria-haspopup', 'dialog');
            if (title) {
                badge.setAttribute('title', title);
            }
            if (assistive) {
                badge.setAttribute('aria-label', assistive);
            }
            badge.classList.add('wsdk-badge--interactive');
        } else if (hasLink) {
            const title = item.title || '';
            const assistive = item.assistive || title || '';

            badge.setAttribute('href', item.href);
            if (item.target) {
                badge.setAttribute('target', item.target);
            }
            if (item.rel) {
                badge.setAttribute('rel', item.rel);
            }
            if (title) {
                badge.setAttribute('title', title);
            }
            if (assistive) {
                badge.setAttribute('aria-label', assistive);
            }
            badge.classList.add('wsdk-badge--interactive');
        } else {
            const title = item.title || '';
            const assistive = item.assistive || '';

            if (title) {
                badge.setAttribute('title', title);
            }
            if (assistive) {
                badge.setAttribute('aria-label', assistive);
            }
        }

        const hasIcon = !!item.icon;

        if (hasIcon) {
            const icon = document.createElement('span');
            const iconClass = item.iconClass || 'material-icons';
            icon.className = iconClass;
            icon.setAttribute('aria-hidden', 'true');
            icon.textContent = item.icon;
            badge.appendChild(icon);

            if (item.iconOnly) {
                badge.classList.add('wsdk-badge--icon');
            }

            const labelText = item.label || '';
            if (labelText) {
                const labelElement = document.createElement('span');
                labelElement.textContent = labelText;
                if (item.labelHidden) {
                    labelElement.classList.add('wsdk-sr-only');
                }
                badge.appendChild(labelElement);
            }
        } else {
            badge.textContent = item.label || '';
        }
        if (item.state) {
            badge.dataset.state = item.state;
        }
        container.appendChild(badge);
    });
}

function updateProgress(container, attributeName = 'data-wsdk-progress') {
    if (!container) {
        return;
    }

    const rawValue = Number(container.getAttribute(attributeName));
    const value = Number.isFinite(rawValue) ? Math.min(Math.max(rawValue, 0), 100) : 0;
    const bar = container.querySelector('[data-wsdk-progress-bar]');
    const valueNode = container.querySelector('[data-wsdk-progress-value]');

    if (bar) {
        bar.style.width = `${value}%`;
        bar.setAttribute('aria-valuenow', String(value));
    }

    if (valueNode) {
        valueNode.textContent = `${value}%`;
    }
}

function bindQuickActions(root, options) {
    const actionAttribute = (options && options.actionAttribute) || 'data-wsdk-action';
    const triggers = root.querySelectorAll(`[${actionAttribute}]`);
    if (!triggers.length) {
        return;
    }

    triggers.forEach((element) => {
        element.addEventListener('click', (event) => {
            const action = element.getAttribute(actionAttribute) || 'unknown';
            const payload = {
                action,
                module: options.module || null,
                context: {
                    rtl: !!options.rtl,
                    mocks: !!options.mocks,
                },
            };
            // eslint-disable-next-line no-console
            console.log('[WSDK Dashboard] Quick action triggered', payload);

            const tagName = element.tagName ? element.tagName.toUpperCase() : '';
            const href = element.getAttribute('href');
            const navigates = tagName === 'A' && href && href !== '#';

            if (!navigates) {
                event.preventDefault();
            }

            const shouldToggle = !navigates || tagName === 'BUTTON';

            if (shouldToggle) {
                element.classList.add('is-active');
            }

            if (tagName === 'BUTTON') {
                element.disabled = true;
            }

            if (shouldToggle) {
                window.setTimeout(() => {
                    element.classList.remove('is-active');
                    if (tagName === 'BUTTON') {
                        element.disabled = false;
                    }
                }, 600);
            }
        });
    });
}

function handleTipCard(root, storageKey, selectors = {}) {
    const tipSelector = selectors.tip || '[data-wsdk-tip]';
    const dismissSelector = selectors.dismiss || '[data-wsdk-dismiss]';
    const tip = root.querySelector(tipSelector);
    if (!tip) {
        return;
    }

    const dataKey = tip.getAttribute('data-storage-key');
    const key = storageKey || dataKey || DEFAULT_STORAGE_KEYS.tip;
    let dismissed = false;
    try {
        dismissed = window.localStorage.getItem(key) === '1';
    } catch (error) {
        dismissed = false;
    }

    if (dismissed) {
        tip.classList.add('is-hidden');
    }

    const dismissButton = tip.querySelector(dismissSelector);
    if (dismissButton) {
        dismissButton.addEventListener('click', (event) => {
            event.preventDefault();
            tip.classList.add('is-hidden');
            try {
                window.localStorage.setItem(key, '1');
            } catch (error) {
                // Ignore storage issues silently
            }
        });
    }
}

function bindVersionDialog(root) {
    const triggers = Array.from(root.querySelectorAll('[data-wsdk-version-open]'));
    if (!triggers.length) {
        return;
    }

    let modal = null;
    let modalId = null;

    triggers.some((candidate) => {
        const candidateId = candidate.getAttribute('data-wsdk-version-target');
        if (candidateId) {
            modalId = candidateId;
            const candidateModal = document.getElementById(candidateId);
            if (candidateModal) {
                modal = candidateModal;
                return true;
            }
        }

        return false;
    });

    if (!modal) {
        modal = root.querySelector('[data-wsdk-version-modal]');
        if (modal && modal.id) {
            modalId = modal.id;
        }
    }

    if (!modal) {
        return;
    }

    const dialog = modal.querySelector('[data-wsdk-version-dialog]') || modal.querySelector('.wsdk-modal__dialog');
    const closeElements = modal.querySelectorAll('[data-wsdk-version-close]');
    const initialElement = modal.querySelector('[data-wsdk-version-initial]');
    let previouslyFocused = null;
    let activeTrigger = null;

    const enhanceTrigger = (trigger) => {
        const target = trigger.getAttribute('data-wsdk-version-target') || modalId;
        if (target) {
            trigger.setAttribute('data-wsdk-version-target', target);
            if (!trigger.getAttribute('aria-controls')) {
                trigger.setAttribute('aria-controls', target);
            }
        }
        trigger.setAttribute('aria-haspopup', 'dialog');
        trigger.setAttribute('aria-expanded', 'false');
    };

    triggers.forEach(enhanceTrigger);
    modal.setAttribute('aria-hidden', modal.classList.contains('is-visible') ? 'false' : 'true');

    const focusInitial = () => {
        if (initialElement && typeof initialElement.focus === 'function') {
            initialElement.focus();
            return;
        }

        const focusable = getFocusableElements(dialog);
        if (focusable.length && typeof focusable[0].focus === 'function') {
            focusable[0].focus();
        }
    };

    const handleKeydown = (event) => {
        if (event.key === 'Escape' || event.key === 'Esc') {
            event.preventDefault();
            closeModal();
            return;
        }

        if (event.key === 'Tab' && dialog) {
            const focusable = getFocusableElements(dialog);
            if (!focusable.length) {
                event.preventDefault();
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    };

    const openModal = (trigger) => {
        if (modal.classList.contains('is-visible')) {
            return;
        }

        activeTrigger = trigger || null;
        previouslyFocused = document.activeElement;
        modal.classList.add('is-visible');
        modal.setAttribute('aria-hidden', 'false');
        if (activeTrigger) {
            activeTrigger.setAttribute('aria-expanded', 'true');
        }
        focusInitial();
        document.addEventListener('keydown', handleKeydown);
    };

    const closeModal = () => {
        if (!modal.classList.contains('is-visible')) {
            return;
        }

        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
        if (activeTrigger) {
            activeTrigger.setAttribute('aria-expanded', 'false');
        }
        document.removeEventListener('keydown', handleKeydown);

        const focusTarget = activeTrigger || previouslyFocused;
        if (focusTarget && typeof focusTarget.focus === 'function') {
            focusTarget.focus();
        }

        activeTrigger = null;
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            if (modal.classList.contains('is-visible')) {
                closeModal();
                return;
            }

            openModal(trigger);
        });
    });

    closeElements.forEach((element) => {
        element.addEventListener('click', (event) => {
            event.preventDefault();
            closeModal();
        });
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal || event.target.hasAttribute('data-wsdk-version-close')) {
            event.preventDefault();
            closeModal();
        }
    });
}

export function initDashboard(container, options = {}) {
    const root = resolveContainer(container);
    if (!root) {
        // eslint-disable-next-line no-console
        console.warn('[WSDK Dashboard] Container not found', container);
        return;
    }

    const classes = Object.assign({}, DEFAULT_CLASSES, typeof options.classes === 'object' && options.classes ? options.classes : {});
    const selectors = Object.assign({
        nav: '[data-wsdk-nav]',
        orientationToggle: '[data-wsdk-orientation-toggle]',
        badges: '[data-wsdk-badges]',
        progress: '[data-wsdk-progress]',
        tip: '[data-wsdk-tip]',
        tipDismiss: '[data-wsdk-dismiss]',
    }, typeof options.selectors === 'object' && options.selectors ? options.selectors : {});
    const attributes = Object.assign({
        badges: 'data-wsdk-badges',
        progress: 'data-wsdk-progress',
    }, typeof options.attributes === 'object' && options.attributes ? options.attributes : {});
    const actionAttribute = typeof options.actionAttribute === 'string' && options.actionAttribute !== ''
        ? options.actionAttribute
        : 'data-wsdk-action';

    const mergedOptions = {
        rtl: !!options.rtl,
        mocks: !!options.mocks,
        module: options.module || null,
        orientationKey: options.orientationKey || DEFAULT_STORAGE_KEYS.orientation,
        tipKey: options.tipKey || DEFAULT_STORAGE_KEYS.tip,
        classes,
        selectors,
        attributes,
        actionAttribute,
    };

    const nav = root.querySelector(selectors.nav);
    const toggle = root.querySelector(selectors.orientationToggle);
    const labels = {
        horizontal: toggle ? toggle.getAttribute('data-horizontal-label') || '' : '',
        vertical: toggle ? toggle.getAttribute('data-vertical-label') || '' : '',
    };

    const storedOrientation = loadPreference(mergedOptions.orientationKey, 'horizontal');
    let orientation = storedOrientation === 'vertical' ? 'vertical' : 'horizontal';
    applyOrientation(root, nav, toggle, orientation, labels, classes);

    if (toggle) {
        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            orientation = orientation === 'vertical' ? 'horizontal' : 'vertical';
            applyOrientation(root, nav, toggle, orientation, labels, classes);
            savePreference(mergedOptions.orientationKey, orientation);
        });
    }

    root.querySelectorAll(selectors.badges).forEach((element) => renderBadges(element, attributes.badges));
    root.querySelectorAll(selectors.progress).forEach((element) => updateProgress(element, attributes.progress));
    bindQuickActions(root, mergedOptions);
    handleTipCard(root, mergedOptions.tipKey, { tip: selectors.tip, dismiss: selectors.tipDismiss });
    bindVersionDialog(root);

    const readyClass = classes.ready || DEFAULT_CLASSES.ready;
    if (readyClass) {
        root.classList.add(readyClass);
    }
    root.dataset.wsdkDashboardInitialized = 'true';

    root.dispatchEvent(new CustomEvent('wsdk-dashboard:ready', {
        detail: {
            options: mergedOptions,
        },
    }));
}

if (typeof window !== 'undefined') {
    window.WSDK = window.WSDK || {};
    window.WSDK.initDashboard = initDashboard;
    window.WSDK.dashboard = Object.assign({}, window.WSDK.dashboard || {}, {
        init: initDashboard,
        applyOrientation,
        renderBadges,
        updateProgress,
        bindQuickActions,
        handleTipCard,
    });
}

export { applyOrientation, renderBadges, updateProgress, bindQuickActions, handleTipCard };
export default initDashboard;
