/**
 * Toast notification ringan tanpa dependensi.
 * Dipakai untuk notifikasi pesanan baru (polling) di panel kasir.
 */
export function showToast(message, { type = 'info', title = null, duration = 6000, onClick = null } = {}) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 right-4 z-[60] flex flex-col gap-3 w-[calc(100vw-2rem)] max-w-sm pointer-events-none';
        document.body.appendChild(container);
    }

    const palette = {
        info: { bar: 'bg-blue-500', icon: 'ℹ️', ring: 'ring-blue-100' },
        success: { bar: 'bg-emerald-500', icon: '✅', ring: 'ring-emerald-100' },
        warning: { bar: 'bg-amber-500', icon: '🔔', ring: 'ring-amber-100' },
        error: { bar: 'bg-red-500', icon: '⚠️', ring: 'ring-red-100' },
    };
    const p = palette[type] ?? palette.info;

    const toast = document.createElement('div');
    toast.className = `pointer-events-auto bg-white rounded-2xl shadow-lg shadow-gray-300/40 border border-gray-100 ring-4 ${p.ring} overflow-hidden animate-slide-in-right cursor-pointer`;
    toast.innerHTML = `
        <div class="flex items-start gap-3 p-4">
            <div class="w-10 h-10 rounded-xl ${p.bar} flex items-center justify-center text-lg flex-shrink-0">${p.icon}</div>
            <div class="min-w-0 flex-1">
                ${title ? `<p class="text-sm font-bold text-gray-900">${title}</p>` : ''}
                <p class="text-sm text-gray-600 leading-snug">${message}</p>
            </div>
            <button type="button" class="toast-close p-1 -m-1 rounded-lg hover:bg-gray-100 text-gray-400 flex-shrink-0" aria-label="Tutup">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    `;

    let dismissed = false;
    const dismiss = () => {
        if (dismissed) return;
        dismissed = true;
        toast.style.transition = 'opacity .2s ease, transform .2s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(24px)';
        setTimeout(() => toast.remove(), 200);
    };

    toast.querySelector('.toast-close').addEventListener('click', (e) => {
        e.stopPropagation();
        dismiss();
    });

    if (onClick) {
        toast.addEventListener('click', () => {
            onClick();
            dismiss();
        });
    } else {
        toast.addEventListener('click', dismiss);
    }

    container.appendChild(toast);

    if (duration > 0) {
        setTimeout(dismiss, duration);
    }

    return dismiss;
}

/** Beep singkat via Web Audio API (tanpa file audio). */
export function playBeep({ frequency = 880, duration = 0.15 } = {}) {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.value = frequency;
        gain.gain.setValueAtTime(0.0001, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.15, ctx.currentTime + 0.01);
        gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + duration);
        osc.start();
        osc.stop(ctx.currentTime + duration);
        setTimeout(() => ctx.close(), (duration + 0.05) * 1000);
    } catch (e) {
        /* audio opsional, abaikan bila gagal */
    }
}
