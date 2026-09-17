export function initComparison(root) {
    const range = root.querySelector('[data-compare-range]');
    const beforeWrap = root.querySelector('[data-compare-before-wrap]');
    const handle = root.querySelector('[data-compare-handle]');
    const tabs = [...root.querySelectorAll('[data-compare-tab]')];
    const panels = [...root.querySelectorAll('[data-compare-panel]')];

    const setPosition = (value) => {
        beforeWrap.style.clipPath = `inset(0 ${100 - value}% 0 0)`;
        handle.style.left = `${value}%`;
        range.setAttribute('aria-valuetext', `${Math.round(value)}% original`);
    };

    range.addEventListener('input', () => setPosition(Number(range.value)));

    const selectTab = (tab, focus = false) => {
        tabs.forEach((item) => {
            const selected = item === tab;
            item.setAttribute('aria-selected', String(selected));
            item.tabIndex = selected ? 0 : -1;
        });
        panels.forEach((panel) => {
            panel.hidden = panel.dataset.comparePanel !== tab.dataset.compareTab;
        });

        if (focus) {
            tab.focus();
        }
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectTab(tab));
        tab.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') {
                event.preventDefault();
                const offset = event.key === 'ArrowRight' ? 1 : -1;
                selectTab(tabs[(index + offset + tabs.length) % tabs.length], true);
            }
        });
    });

    return {
        reset() {
            range.value = '50';
            setPosition(50);
            selectTab(tabs[0]);
        },
    };
}
