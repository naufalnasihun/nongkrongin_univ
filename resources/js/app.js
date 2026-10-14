import { initDarkMode, terapkanTemaCepat } from './theme';
import { initKasirPolling } from './polling';
import { initScrollRestoration } from './scroll';
import { showToast } from './toast';

// Terapkan tema sedini mungkin (sebelum DOMContentLoaded) agar tidak kedip.
terapkanTemaCepat();

// Ekspos util global bila diperlukan.
window.Nongkrongin = { showToast };

document.addEventListener('DOMContentLoaded', () => {
    initDarkMode();
    initScrollRestoration();
    initKasirPolling();
});
