/**
 * Menjaga posisi scroll saat halaman di-reload (mis. oleh auto-refresh
 * polling kasir). Tanpa ini, reload membuat halaman melompat ke atas.
 *
 * Posisi disimpan per-URL sehingga pindah halaman tetap mulai dari atas,
 * tetapi reload halaman yang sama memulihkan posisi.
 */
const POS_KEY = 'nongkrongin-scroll-pos';
const URL_KEY = 'nongkrongin-scroll-url';

function currentUrl() {
    return window.location.pathname + window.location.search;
}

export function initScrollRestoration() {
    // Matikan pemulihan bawaan browser agar tidak bentrok.
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }

    // Pulihkan HANYA bila reload halaman yang sama.
    if (sessionStorage.getItem(URL_KEY) === currentUrl()) {
        const y = parseInt(sessionStorage.getItem(POS_KEY) || '0', 10);
        if (y > 0) {
            requestAnimationFrame(() => {
                requestAnimationFrame(() => window.scrollTo(0, y));
            });
        }
    }

    // Simpan posisi scroll terkini (throttle via requestAnimationFrame).
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(() => {
            sessionStorage.setItem(POS_KEY, String(window.scrollY));
            sessionStorage.setItem(URL_KEY, currentUrl());
            ticking = false;
        });
    }, { passive: true });
}

/** Hapus memori scroll (mulai dari atas). */
export function resetScrollMemory() {
    sessionStorage.removeItem(POS_KEY);
    sessionStorage.removeItem(URL_KEY);
}
