(() => {
    const panel = document.getElementById('galeria-animal');
    if (!panel) return;
    const form = panel.closest('form');
    const submit = form.querySelector('button[type="submit"]');
    if (submit) {
        const group = submit.parentElement;
        form.insertBefore(panel, group.parentElement === form ? group : submit);
    }
    const picker = document.getElementById('fotos-galeria-arquivos');
    const dialog = document.getElementById('galeria-recorte');
    const image = document.getElementById('galeria-imagem');
    const status = document.getElementById('galeria-aviso');
    const previews = document.getElementById('galeria-previas');
    const inputs = document.getElementById('galeria-inputs');
    const saved = Number(panel.dataset.total || 0);
    let queue = [], photos = Array.from(inputs.querySelectorAll('input')).map(input => input.value), cropper = null, objectUrl = null;
    function render() {
        inputs.replaceChildren();
        previews.replaceChildren();
        photos.forEach((src, index) => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'fotos_galeria[]'; input.value = src;
            inputs.append(input);
            const item = document.createElement('div');
            const img = document.createElement('img');
            img.src = src; img.alt = `Nova foto ${index + 1}`;
            img.className = 'w-full rounded-xl object-cover aspect-[4/5]';
            const remove = document.createElement('button');
            remove.type = 'button'; remove.textContent = 'Remover foto';
            remove.className = 'text-sm underline p-2';
            remove.addEventListener('click', () => { photos.splice(index, 1); render(); });
            item.append(img, remove); previews.append(item);
        });
        status.textContent = `${photos.length} foto(s) adicional(is) pronta(s) para salvar.`;
    }
    render();
    function clean() {
        if (cropper) cropper.destroy();
        cropper = null;
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
    }
    function next() {
        clean();
        const file = queue.shift();
        if (!file) { if (dialog.open) dialog.close(); picker.value = ''; return; }
        objectUrl = URL.createObjectURL(file);
        image.onload = () => {
            if (!dialog.open) dialog.showModal();
            cropper = new Cropper(image, { aspectRatio: 4 / 5, viewMode: 1, autoCropArea: 1, dragMode: 'move' });
        };
        image.onerror = () => { status.textContent = 'Uma imagem não pôde ser aberta.'; next(); };
        image.src = objectUrl;
    }
    picker.addEventListener('change', () => {
        const files = Array.from(picker.files || []);
        const principal = document.getElementById('foto_cortada_base64').value ? 1 : 0;
        if (saved + photos.length + files.length + principal > 10 || photos.length + files.length > 9) {
            status.textContent = 'Limite de 10 fotos no total, incluindo a principal.'; picker.value = ''; return;
        }
        if (files.some(f => !['image/jpeg','image/png','image/webp'].includes(f.type) || f.size > 5 * 1024 * 1024)) {
            status.textContent = 'Use imagens JPG, PNG ou WEBP de até 5 MB cada.'; picker.value = ''; return;
        }
        if (typeof Cropper === 'undefined') { status.textContent = 'O recorte não carregou. Recarregue a página antes de selecionar fotos.'; return; }
        queue = files; next();
    });
    document.getElementById('galeria-confirmar').addEventListener('click', () => {
        if (!cropper) return;
        const canvas = cropper.getCroppedCanvas({width: 600, height: 750, fillColor: '#fff'});
        const src = canvas.toDataURL('image/jpeg', 0.82);
        photos.push(src); render(); next();
    });
    document.getElementById('galeria-pular').addEventListener('click', next);
    dialog.addEventListener('cancel', () => { queue = []; clean(); picker.value = ''; });
    form.addEventListener('submit', event => {
        if (dialog.open || queue.length) { event.preventDefault(); status.textContent = 'Conclua os recortes antes de salvar.'; return; }
        if (saved + photos.length + (document.getElementById('foto_cortada_base64').value ? 1 : 0) > 10) { event.preventDefault(); status.textContent = 'Limite de 10 fotos no total.'; return; }
        const bytes = photos.reduce((sum, src) => sum + src.length, 0) + document.getElementById('foto_cortada_base64').value.length;
        if (bytes > 6 * 1024 * 1024) { event.preventDefault(); status.textContent = 'As fotos ultrapassam 6 MB. Remova algumas e salve as restantes depois.'; }
    });
})();
