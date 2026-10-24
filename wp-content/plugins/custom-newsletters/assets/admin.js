(function () {
    const choose = document.getElementById('cwsnl-select-pdf');
    if (!choose) return;
    let picker;
    const id = document.getElementById('cwsnl-pdf-id');
    const remove = document.getElementById('cwsnl-remove');
    const current = document.getElementById('cwsnl-pdf-current');
    choose.addEventListener('click', function () {
        if (!picker) {
            picker = wp.media({title: 'Choose newsletter PDF', library: {type: 'application/pdf'}, multiple: false, button: {text: 'Use PDF'}});
            picker.on('select', function () {
                const file = picker.state().get('selection').first().toJSON();
                if (file.mime !== 'application/pdf') return;
                id.value = file.id; remove.value = '0';
                const link = document.createElement('a');
                link.href = file.url; link.textContent = file.filename; link.target = '_blank'; link.rel = 'noopener';
                current.replaceChildren(link);
            });
        }
        picker.open();
    });
    document.getElementById('cwsnl-remove-pdf').addEventListener('click', function () {
        id.value = ''; remove.value = '1'; current.textContent = 'PDF will be removed when you save.';
    });
}());
