/*
 * Foldable content navigation.
 *
 * The navigation is rendered expanded, so that it stays usable without JavaScript. This script adds
 * the toggle buttons, collapses the sub levels and expands the ancestors of the anchor being linked.
 */
(function () {
    'use strict';

    var idCounter = 0;

    function subList(item) {
        for (var i = 0; i < item.children.length; i++) {
            if (item.children[i].tagName === 'UL') {
                return item.children[i];
            }
        }

        return null;
    }

    function toggleButton(item) {
        for (var i = 0; i < item.children.length; i++) {
            if (item.children[i].classList.contains('toc-toggle')) {
                return item.children[i];
            }
        }

        return null;
    }

    function itemByAnchor(root, anchor) {
        var items = root.querySelectorAll('[data-toc-id]');

        for (var i = 0; i < items.length; i++) {
            if (items[i].getAttribute('data-toc-id') === anchor) {
                return items[i];
            }
        }

        return null;
    }

    function setExpanded(button, expanded) {
        var list = document.getElementById(button.getAttribute('aria-controls'));

        button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        button.setAttribute(
            'aria-label',
            (expanded ? button.dataset.labelCollapse : button.dataset.labelExpand)
        );

        if (list) {
            list.hidden = !expanded;
        }

        button.parentNode.classList.toggle('toc-expanded', expanded);
    }

    function build(root) {
        if (root.hasAttribute('data-toc-ready')) {
            return;
        }

        root.setAttribute('data-toc-ready', '');

        var expandLabel = root.getAttribute('data-toc-expand') || 'Show sub items of %s';
        var collapseLabel = root.getAttribute('data-toc-collapse') || 'Hide sub items of %s';
        var items = root.querySelectorAll('li');

        Array.prototype.forEach.call(items, function (item) {
            var list = subList(item);

            if (!list) {
                return;
            }

            var link = item.querySelector('a');
            var title = link ? link.textContent.trim() : '';

            if (!list.id) {
                list.id = 'toc-sub-' + ++idCounter;
            }

            var button = document.createElement('button');

            button.type = 'button';
            button.className = 'toc-toggle';
            button.setAttribute('aria-controls', list.id);
            button.dataset.labelExpand = expandLabel.replace('%s', title);
            button.dataset.labelCollapse = collapseLabel.replace('%s', title);

            button.addEventListener('click', function () {
                setExpanded(button, button.getAttribute('aria-expanded') !== 'true');
            });

            item.insertBefore(button, list);
            item.classList.add('toc-has-children');

            setExpanded(button, false);
        });
    }

    function reveal(root) {
        var anchor = window.location.hash.slice(1);

        if (!anchor) {
            return;
        }

        var item = itemByAnchor(root, anchor);

        if (!item) {
            return;
        }

        var current = root.querySelector('.toc-current');

        if (current) {
            current.classList.remove('toc-current');
        }

        item.classList.add('toc-current');

        // Expand every ancestor, so that the linked item becomes visible
        var node = item.parentNode;

        while (node && node !== root.parentNode) {
            if (node.tagName === 'LI') {
                var button = toggleButton(node);

                if (button) {
                    setExpanded(button, true);
                }
            }

            node = node.parentNode;
        }
    }

    function init() {
        var roots = document.querySelectorAll('[data-toc-foldable]');

        Array.prototype.forEach.call(roots, function (root) {
            build(root);
            reveal(root);

            window.addEventListener('hashchange', function () {
                reveal(root);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
