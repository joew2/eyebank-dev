(() => {
    document.querySelectorAll('.ed-gt-slider').forEach(slider => {
        const slides = [...slider.querySelectorAll('.ed-gt-slide')];
        const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let current = 0, timer;
        const stop = () => clearInterval(timer);
        const show = index => {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide, i) => {
                slide.classList.toggle('is-active', i === current);
                slide.setAttribute('aria-hidden', String(i !== current));
            });
        };
        show(0);
        slides.forEach(slide => { slide.hidden = false; });
        slider.classList.add('is-ready');
        const start = () => {
            stop();
            if (!motion.matches && !document.hidden) timer = setInterval(() => show(current + 1), 3000);
        };
        slider.querySelector('.ed-gt-prev').addEventListener('click', () => { show(current - 1); start(); });
        slider.querySelector('.ed-gt-next').addEventListener('click', () => { show(current + 1); start(); });
        document.addEventListener('visibilitychange', start);
        motion.addEventListener('change', start);
        let touchX;
        slider.addEventListener('touchstart', event => { touchX = event.changedTouches[0].clientX; }, {passive:true});
        slider.addEventListener('touchend', event => { const delta = event.changedTouches[0].clientX - touchX; if (Math.abs(delta) > 50) { show(current + (delta < 0 ? 1 : -1)); start(); } }, {passive:true});
        slider.querySelector('.ed-gt-controls').hidden = false;
        start();
    });
})();
