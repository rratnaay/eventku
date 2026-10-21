// Animasi muncul saat scroll + interaksi UI ringan (tanpa framework).
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.addEventListener('DOMContentLoaded', () => {
    const items = document.querySelectorAll('.reveal');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-visible'));
    } else {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        items.forEach((el, i) => {
            el.style.transitionDelay = `${(i % 4) * 70}ms`;
            io.observe(el);
        });
    }

    // Toggle menu mobile
    document.querySelectorAll('[data-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.toggle);
            if (!target) return;
            const hidden = target.classList.toggle('hidden');
            btn.setAttribute('aria-expanded', String(!hidden));
        });
    });

    // Konfirmasi sebelum aksi berbahaya (hapus, batal, dll.)
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    // Cegah klik ganda pada tombol submit
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            if (form.dataset.confirm && form.defaultPrevented) return;
            form.querySelectorAll('button[type=submit][data-once]').forEach((b) => {
                setTimeout(() => { b.disabled = true; b.classList.add('opacity-60'); }, 0);
            });
        });
    });

    // Pratinjau poster saat memilih file
    const poster = document.getElementById('poster-input');
    const preview = document.getElementById('poster-preview');
    if (poster && preview) {
        poster.addEventListener('change', () => {
            const file = poster.files?.[0];
            if (file) preview.src = URL.createObjectURL(file);
        });
    }

    // Tutup flash message otomatis
    document.querySelectorAll('[data-flash]').forEach((el) => {
        setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 6000);
    });
});
