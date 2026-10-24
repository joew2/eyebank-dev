/* Paint a single address panel without moving fields or replacing Gravity Forms logic. */
(function () {
    let observer, resize;
    function bind() {
        const fields = document.getElementById('gform_fields_85');
        if (!fields || fields.dataset.edMailPanel) return;
        fields.dataset.edMailPanel = '1';
        const measure = function () {
            const first = document.getElementById('field_85_5');
            const address = [7, 8, 9, 10, 11].map(id => document.getElementById('field_85_' + id)).filter(Boolean);
            const visible = first && first.getClientRects().length && getComputedStyle(first).display !== 'none';
            fields.classList.toggle('ed-mail-visible', !!visible);
            if (!visible) return;
            const top = fields.getBoundingClientRect().top;
            const start = first.getBoundingClientRect().top - top;
            const end = Math.max(...address.filter(el => el.getClientRects().length).map(el => el.getBoundingClientRect().bottom - top));
            fields.style.setProperty('--ed-mail-top', start + 'px');
            fields.style.setProperty('--ed-mail-height', Math.max(0, end - start) + 'px');
        };
        if (observer) observer.disconnect();
        observer = new MutationObserver(() => requestAnimationFrame(measure));
        for (const field of fields.children) observer.observe(field, {attributes: true, attributeFilter: ['style', 'hidden']});
        if (resize) resize.disconnect();
        resize = new ResizeObserver(measure); resize.observe(fields);
        fields.addEventListener('change', () => requestAnimationFrame(measure));
        measure();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind); else bind();
    document.addEventListener('gform/post_render', bind);
    if (window.jQuery) window.jQuery(document).on('gform_post_render', bind);
}());
