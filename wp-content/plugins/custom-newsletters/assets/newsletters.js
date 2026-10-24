(function () {
    const dialog = document.getElementById('cwsnl-dialog');
    if (!dialog || typeof dialog.showModal !== 'function') return;
    let trigger, timer;
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
    function open(source) {
        clearTimeout(timer);
        trigger = source;
        dialog.classList.remove('cwsnl-closing');
        if (!dialog.open) dialog.showModal();
        document.documentElement.classList.add('cwsnl-open');
        dialog.querySelector('[data-cwsnl-close]').focus();
    }
    function close() {
        if (dialog.classList.contains('cwsnl-closing')) return;
        dialog.classList.add('cwsnl-closing');
        timer = setTimeout(function () { dialog.close(); }, reduced.matches ? 0 : 180);
    }
    document.addEventListener('click', function (event) {
        const opener = event.target.closest('[data-cwsnl-open]');
        if (!opener || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        event.preventDefault(); open(opener);
    });
    dialog.querySelector('[data-cwsnl-close]').addEventListener('click', function (event) { event.preventDefault(); close(); });
    dialog.addEventListener('cancel', function (event) { event.preventDefault(); close(); });
    dialog.addEventListener('click', function (event) {
        if (event.target !== dialog) return;
        const box = dialog.getBoundingClientRect();
        if (event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom) close();
    });
    dialog.addEventListener('close', function () {
        clearTimeout(timer); dialog.classList.remove('cwsnl-closing');
        document.documentElement.classList.remove('cwsnl-open');
        if (trigger && trigger.isConnected) trigger.focus();
    });
    if (dialog.hasAttribute('data-cwsnl-initial')) {
        dialog.close(); open(document.querySelector('[data-cwsnl-open]'));
    }
}());
