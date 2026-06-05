import './bootstrap';

// register component
document.addEventListener('alpine:init', () => {

    Alpine.data('sidebarComp', () => ({
        pinned: localStorage.getItem('sidebarPinned') === 'true',
        hovered: false,

        get isExpanded() {
            return this.pinned || this.hovered;
        },

        onMouseEnter() { this.hovered = true; },
        onMouseLeave() { this.hovered = false; },

        init() {
            window.addEventListener('toggle-pin-sidebar', () => {
                this.pinned = !this.pinned;
                localStorage.setItem('sidebarPinned', this.pinned);
            });
        }
    }));

    Alpine.data('appLayout', () => ({
        darkMode: localStorage.getItem('darkMode') === 'true',
        mobileOpen: false,
        sidebarPinned: localStorage.getItem('sidebarPinned') === 'true',

        init() {
            window.addEventListener('toggle-dark', () => {
                this.darkMode = !this.darkMode;
                localStorage.setItem('darkMode', this.darkMode);
            });
            window.addEventListener('toggle-mobile-sidebar', () => {
                this.mobileOpen = !this.mobileOpen;
            });
            window.addEventListener('toggle-pin-sidebar', () => {
                this.sidebarPinned = !this.sidebarPinned;
                localStorage.setItem('sidebarPinned', this.sidebarPinned);
            });

            setTimeout(() => {
                const ls = document.getElementById('loading-screen');
                if (ls) ls.classList.add('erp-loading-hide');
            }, 600);
        }
    }));

});

