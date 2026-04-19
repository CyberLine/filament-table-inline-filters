(function () {
    function decodeSvg(b64) {
        if (!b64) {
            return '';
        }
        try {
            return atob(b64);
        } catch {
            return '';
        }
    }

    function findLivewireComponent(td) {
        const root = td.closest('[wire\\:id]');
        if (!root) {
            return null;
        }
        const id = root.getAttribute('wire:id');
        if (!id || typeof Livewire === 'undefined') {
            return null;
        }
        return Livewire.find(id);
    }

    function mountCell(td) {
        if (!td.getAttribute('data-ilf-col')) {
            return;
        }

        const hasOurRow = td.querySelector(':scope > .fi-ilf-cell-row');
        if (td.getAttribute('data-ilf-mounted') === '1' && hasOurRow) {
            return;
        }

        td.removeAttribute('data-ilf-mounted');

        const orphanRow = td.querySelector(':scope > .fi-ilf-cell-row');
        if (orphanRow) {
            orphanRow.remove();
        }

        td.setAttribute('data-ilf-mounted', '1');
        td.classList.add('fi-ilf-cell');

        const align = td.getAttribute('data-ilf-align') || 'left';
        const col = td.getAttribute('data-ilf-col');
        const label = td.getAttribute('data-ilf-label') || col;
        const rawValue = td.getAttribute('data-ilf-value') || 'null';

        let parsedValue;
        try {
            parsedValue = JSON.parse(rawValue);
        } catch {
            parsedValue = rawValue;
        }

        const row = document.createElement('div');
        row.className = 'fi-ilf-cell-row';

        const valueWrap = document.createElement('span');
        valueWrap.className = 'fi-ilf-cell-value';
        while (td.firstChild) {
            valueWrap.appendChild(td.firstChild);
        }

        const actions = document.createElement('span');
        actions.className = 'fi-ilf-actions';

        const mkBtn = (operator, b64) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className =
                'fi-ilf-btn fi-icon-btn fi-size-xs fi-text-gray-500 hover:fi-text-primary-600 dark:fi-text-gray-400 dark:hover:fi-text-primary-400';
            btn.innerHTML = decodeSvg(b64);
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const comp = findLivewireComponent(td);
                if (!comp) {
                    return;
                }
                comp.call('addInlineFilter', col, operator, parsedValue, label);
            });
            return btn;
        };

        actions.appendChild(mkBtn('=', td.getAttribute('data-ilf-plus-svg') || ''));
        actions.appendChild(mkBtn('!=', td.getAttribute('data-ilf-minus-svg') || ''));

        if (align === 'right') {
            row.appendChild(valueWrap);
            row.appendChild(actions);
        } else {
            row.appendChild(actions);
            row.appendChild(valueWrap);
        }

        td.appendChild(row);
    }

    function scan(root) {
        const scope = root && root.nodeType === 1 ? root : document;
        scope.querySelectorAll('td[data-ilf-col]').forEach(mountCell);
    }

    const observer = new MutationObserver((mutations) => {
        for (const m of mutations) {
            if (m.addedNodes && m.addedNodes.length) {
                m.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        scan(node);
                    }
                });
            }
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        scan(document);
        observer.observe(document.body, { childList: true, subtree: true });
    });

    document.addEventListener('livewire:navigated', () => {
        setTimeout(() => scan(document), 0);
    });

    document.addEventListener('livewire:init', () => {
        Livewire.hook('morphed', ({ el }) => {
            if (el && el.nodeType === 1) {
                requestAnimationFrame(() => scan(el));
            }
        });
    });
})();
