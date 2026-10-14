/**
 * Dark mode: menyimpan preferensi di localStorage dan menerapkan
 * class `.dark` pada <html>. Toggle tersedia di header tiap layout.
 * Menghormati preferensi sistem saat pertama kali dibuka.
 */
const STORAGE_KEY = 'nongkrongin-theme';

function terapkanTema(tema) {
    const html = document.documentElement;
    if (tema === 'dark') {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
    }
    // Perbarui ikon semua tombol toggle.
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.querySelector('[data-theme-icon-dark]')?.classList.toggle('hidden', tema !== 'dark');
        btn.querySelector('[data-theme-icon-light]')?.classList.toggle('hidden', tema === 'dark');
        btn.setAttribute('aria-label', tema === 'dark' ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
    });
}

function temaAwal() {
    const tersimpan = localStorage.getItem(STORAGE_KEY);
    if (tersimpan === 'dark' || tersimpan === 'light') {
        return tersimpan;
    }
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function initDarkMode() {
    terapkanTema(temaAwal());

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const gelapSekarang = document.documentElement.classList.contains('dark');
            const temaBaru = gelapSekarang ? 'light' : 'dark';
            localStorage.setItem(STORAGE_KEY, temaBaru);
            terapkanTema(temaBaru);
        });
    });
}

/** Terapkan tema paling awal sebelum paint untuk menghindari kedip. */
export function terapkanTemaCepat() {
    terapkanTema(temaAwal());
}
