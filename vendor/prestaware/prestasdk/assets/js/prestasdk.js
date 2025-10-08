$(document).ready(function(){
    $(".grower").click(function(){
        var element = $(this);
        if(element.hasClass('open')){
            element.addClass('close').removeClass('open');
            element.parent().find('ul:first').slideUp();
        }
        else{
            $('.grower').not(element).addClass('close').removeClass('open');
            var elementUL = element.parent().find('ul:first');
            $('.list-group-item ul').not(elementUL).slideUp();

            element.addClass('open').removeClass('close');
            element.parent().find('ul:first').slideDown();
        }
    });
});

function toggleMenu(arrow) {
    var $arrow = $(arrow);
    var $menu = $arrow.closest('.wsdk-menu');
    var $info = $('#wsdk-info');

    $menu.toggleClass('wsdk-menu-closed');
    $info.toggleClass('wsdk-info-closed');
}

(function ($) {
    'use strict';

    var STORAGE_KEY = 'wapm_sidebar_orientation';

    function normalizeOrientation(value, fallback) {
        var orientation = (value || '').toString().toLowerCase();

        if (orientation === 'horizontal' || orientation === 'vertical') {
            return orientation;
        }

        if (fallback === null) {
            return null;
        }

        if (fallback === 'horizontal' || fallback === 'vertical') {
            return fallback;
        }

        return 'horizontal';
    }

    function getStoredOrientation() {
        try {
            var stored = window.localStorage.getItem(STORAGE_KEY);
            return normalizeOrientation(stored, null);
        } catch (error) {
            return null;
        }
    }

    function storeOrientation(orientation) {
        try {
            var normalized = normalizeOrientation(orientation, null);

            if (!normalized) {
                return;
            }

            window.localStorage.setItem(STORAGE_KEY, normalized);
        } catch (error) {
            // Storage can be disabled; ignore errors.
        }
    }

    function applyOrientation(options, orientation) {
        var normalized = normalizeOrientation(orientation, 'horizontal');
        var isHorizontal = normalized === 'horizontal';

        options.panel.toggleClass('wsdk-panel--horizontal', isHorizontal);
        options.content.toggleClass('wsdk-panel-content--horizontal', isHorizontal);
        options.sidebar.toggleClass('wsdk-panel-sidebar--horizontal', isHorizontal);
        options.menus.toggleClass('wsdk-menu--horizontal', isHorizontal);
        options.panelMenus.toggleClass('wsdk-panel-menu--horizontal', isHorizontal);

        options.toggle.attr('data-current-orientation', normalized);

        var nextOrientation = isHorizontal ? 'vertical' : 'horizontal';
        var nextLabel = options.labels[nextOrientation];
        var nextIcon = options.icons[nextOrientation];

        options.button.attr('data-next-orientation', nextOrientation);

        if (nextLabel) {
            options.button.attr('aria-label', nextLabel)
                .attr('title', nextLabel);

            if (options.buttonText && options.buttonText.length) {
                options.buttonText.text(nextLabel);
            }
        }

        if (nextIcon) {
            options.buttonIcon.text(nextIcon);
        }
    }

    $(function () {
        var $panel = $('#wsdk-panel');
        var $toggle = $('.wsdk-sidebar-toggle');

        if (!$panel.length || !$toggle.length) {
            return;
        }

        var $button = $toggle.find('.wsdk-sidebar-toggle__button').first();
        var $buttonIcon = $button.find('.material-icons').first();

        if (!$button.length || !$buttonIcon.length) {
            return;
        }

        var $buttonText = $button.find('.wsdk-sidebar-toggle__text').first();
        var groupLabel = String($toggle.attr('aria-label') || '').trim();
        var fallbackLabel = groupLabel || 'Switch menu layout';

        var labels = {
            horizontal: String($toggle.data('label-horizontal') || '').trim(),
            vertical: String($toggle.data('label-vertical') || '').trim()
        };

        if (!labels.horizontal) {
            labels.horizontal = fallbackLabel;
        }

        if (!labels.vertical) {
            labels.vertical = fallbackLabel;
        }

        var options = {
            panel: $panel,
            content: $panel.find('.wsdk-panel-content').first(),
            sidebar: $panel.find('.wsdk-panel-sidebar').first(),
            menus: $panel.find('.wsdk-panel-sidebar .wsdk-menu'),
            panelMenus: $panel.find('.wsdk-panel-sidebar .wsdk-panel-menu'),
            toggle: $toggle,
            button: $button,
            buttonIcon: $buttonIcon,
            buttonText: $buttonText,
            labels: labels,
            icons: {
                horizontal: String($toggle.data('icon-horizontal') || 'view_week').trim() || 'view_week',
                vertical: String($toggle.data('icon-vertical') || 'view_stream').trim() || 'view_stream'
            }
        };

        if (!options.content.length || !options.sidebar.length) {
            return;
        }

        var storedOrientation = getStoredOrientation();
        var fallbackOrientation = normalizeOrientation(
            $toggle.attr('data-initial-orientation'),
            $panel.hasClass('wsdk-panel--horizontal') ? 'horizontal' : 'vertical'
        );

        if (!fallbackOrientation) {
            fallbackOrientation = 'horizontal';
        }

        var currentOrientation = storedOrientation || fallbackOrientation;

        applyOrientation(options, currentOrientation);

        $button.on('click', function (event) {
            event.preventDefault();

            var nextOrientation = normalizeOrientation($(this).attr('data-next-orientation'), null);
            if (!nextOrientation || nextOrientation === options.toggle.attr('data-current-orientation')) {
                return;
            }

            applyOrientation(options, nextOrientation);
            storeOrientation(nextOrientation);
        });
    });
})(window.jQuery);

(function ($) {
    'use strict';

    var COOKIE_NAME = 'wapm_hide_admin_header';
    var COOKIE_MAX_AGE = 60 * 60 * 24 * 365; // one year

    /**
     * Read a cookie by name.
     * @param {string} name
     * @returns {string|null}
     */
    function getCookie(name) {
        var pattern = new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)');
        var matches = document.cookie.match(pattern);
        return matches ? decodeURIComponent(matches[1]) : null;
    }

    /**
     * Store a cookie value with module defaults.
     * @param {string} name
     * @param {string} value
     */
    function setCookie(name, value) {
        document.cookie = name + '=' + encodeURIComponent(value) + '; path=/; max-age=' + COOKIE_MAX_AGE + '; SameSite=Lax';
    }

    $(function () {
        var $body = $('body');
        var $pageHead = $('.page-head').first();

        if (!$body.length || !$pageHead.length) {
            return;
        }

        if ($body.hasClass('wsdk-header-toggle-initialized')) {
            return;
        }

        $body.addClass('wsdk-header-toggle-initialized wsdk-admin-header-toggle');

        var $wrapper = $('<div>', {
            'class': 'wsdk-header-toggle-wrapper'
        });

        var $button = $('<button>', {
            type: 'button',
            'class': 'wsdk-header-toggle-button',
            'aria-expanded': 'true',
            'aria-label': 'Hide admin header',
            title: 'Hide admin header'
        });

        var $icon = $('<span>', {
            'class': 'wsdk-header-toggle-icon'
        }).text('×');

        $button.append($icon);
        $wrapper.append($button);

        var $helpButton = $pageHead.find('.breadcrumb, .page-breadcrumb').first();
        if ($helpButton.length && $helpButton.parent().length) {
            $wrapper.insertBefore($helpButton);
        } else {
            $pageHead.append($wrapper);
        }

        /**
         * Apply current toggle state to the UI.
         * @param {boolean} hidden
         */
        function applyState(hidden) {
            if (hidden) {
                $body.addClass('wsdk-hide-page-head');
                $button.attr('aria-expanded', 'false')
                    .attr('aria-label', 'Show admin header')
                    .attr('title', 'Show admin header');
                $icon.text('×');
            } else {
                $body.removeClass('wsdk-hide-page-head');
                $button.attr('aria-expanded', 'true')
                    .attr('aria-label', 'Hide admin header')
                    .attr('title', 'Hide admin header');
                $icon.text('×');
            }
        }

        var storedState = getCookie(COOKIE_NAME);
        applyState(storedState === '1');

        $button.on('click', function (event) {
            event.preventDefault();
            var shouldHide = !$body.hasClass('wsdk-hide-page-head');
            applyState(shouldHide);
            setCookie(COOKIE_NAME, shouldHide ? '1' : '0');
        });
    });
})(window.jQuery);

