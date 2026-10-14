import { showToast, playBeep } from './toast';

/**
 * Polling notifikasi pesanan baru untuk panel kasir.
 *
 * Cara pakai (di blade):
 *   <div id="kasir-polling" data-url="{{ route('kasir.polling') }}"></div>
 */
export function initKasirPolling() {
    const el = document.getElementById('kasir-polling');
    if (!el) return;

    const url = el.dataset.url;
    const interval = parseInt(el.dataset.interval || '8000', 10);
    const soundEnabled = el.dataset.sound !== 'off';

    let lastId = parseInt(el.dataset.lastId || '0', 10);
    let stopped = false;
    let failures = 0;

    const badgeEls = document.querySelectorAll('[data-count]');

    function updateBadges(counts) {
        badgeEls.forEach((badge) => {
            const key = badge.dataset.count;
            const val = counts[key];
            if (val === undefined) return;
            badge.textContent = val;
            badge.classList.toggle('hidden', !val);
        });
    }

    async function poll() {
        if (stopped) return;
        try {
            const res = await fetch(`${url}?since_id=${lastId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                credentials: 'same-origin',
            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            failures = 0;

            updateBadges(data.counts);

            if (data.baru && data.baru.length) {
                const first = data.baru[0];
                const jumlah = data.baru.length;
                const pemesan = first.nim ? `${first.customer} (${first.nim})` : first.customer;

                showToast(
                    jumlah === 1
                        ? `<b>${first.nomor_pesanan}</b> dari ${pemesan} — Rp ${formatRupiah(first.total)}`
                        : `<b>${jumlah} pesanan baru</b> masuk. Yang terbaru: ${first.nomor_pesanan} oleh ${pemesan}.`,
                    {
                        type: 'warning',
                        title: '🔔 Pesanan Baru Masuk!',
                        duration: 9000,
                        onClick: () => { window.location.reload(); },
                    }
                );

                if (soundEnabled) playBeep({ frequency: 920 });

                lastId = Math.max(...data.baru.map((p) => p.id));
                el.dataset.lastId = String(lastId);

                // Segarkan halaman pesanan otomatis agar daftar ikut update.
                // Scroll position dipulihkan oleh initScrollRestoration(),
                // jadi reload tidak melompat ke atas.
                const autoRefresh = el.dataset.autoRefresh === 'on';
                if (autoRefresh && !document.hidden) {
                    setTimeout(() => {
                        if (!document.hidden) window.location.reload();
                    }, 2500);
                }
            }

            if (data.latest_id && !data.baru.length) {
                lastId = data.latest_id;
                el.dataset.lastId = String(lastId);
            }
        } catch (err) {
            failures++;
            if (failures === 3) {
                console.warn('[polling] gagal beberapa kali:', err.message);
            }
        }
    }

    function formatRupiah(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n || 0));
    }

    // Hentikan polling saat tab tidak aktif, lanjutkan saat kembali aktif.
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) poll();
    });

    // Mulai polling.
    lastId = lastId || 0;
    setTimeout(poll, 2000);
    setInterval(() => {
        if (!document.hidden) poll();
    }, interval);
}
