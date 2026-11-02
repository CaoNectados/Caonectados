(() => {
    const panel = document.getElementById('galeria-animal');
    if (!panel) return;
    const form = panel.closest('form');
    const $ = id => document.getElementById(id);
    const picker = $('fotos-galeria-arquivos'), dialog = $('galeria-recorte'), image = $('galeria-imagem');
    const status = $('galeria-aviso'), rail = $('galeria-previas'), inputs = $('galeria-inputs');
    let photos = JSON.parse($('galeria-inicial').textContent), selected = 0, queue = [], cropper = null, objectUrl = null, editIndex = null;
    if (photos.length && !photos.some(p => p.principal)) photos[0].principal = true;
    function serialize() {
        inputs.replaceChildren();
        const principal = photos.find(p => p.principal);
        $('foto_cortada_base64').value = principal && !principal.id ? principal.src : '';
        $('foto-principal-id').value = principal?.id || 0;
        photos.filter(p => !p.id && !p.principal).forEach(p => {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = 'fotos_galeria[]'; input.value = p.src;
            inputs.append(input);
        });
    }
    function render() {
        selected = Math.max(0, Math.min(selected, photos.length - 1));
        const current = photos[selected];
        $('galeria-destaque').hidden = !current;
        if (current) $('galeria-destaque').src = current.src;
        $('galeria-vazia').hidden = !!current;
        $('galeria-badge').hidden = !current?.principal;
        $('galeria-acoes').hidden = !current;
        $('galeria-principal').hidden = !current || current.principal;
        $('galeria-editar').hidden = !current || !!current.id;
        $('galeria-remover').hidden = !current || !!current.id;
        $('galeria-contador').textContent = `${photos.length} / 10`;
        $('galeria-adicionar').disabled = photos.length >= 10;
        rail.replaceChildren();
        photos.forEach((p, index) => {
            const button = document.createElement('button'); button.type = 'button';
            button.className = 'pet-gallery-thumb';
            button.setAttribute('aria-label', `Foto ${index + 1}${p.principal ? ', principal' : ''}`);
            button.setAttribute('aria-pressed', String(index === selected));
            const img = document.createElement('img'); img.src = p.src; img.alt = '';
            button.append(img);
            if (p.principal) { const badge = document.createElement('span'); badge.textContent = 'Principal'; button.append(badge); }
            button.addEventListener('click', () => { selected = index; render(); });
            rail.append(button);
        });
        serialize();
    }
    function clean() {
        if (cropper) cropper.destroy(); cropper = null;
        if (objectUrl) URL.revokeObjectURL(objectUrl); objectUrl = null;
    }
    function openCrop(src) {
        image.onload = () => {
            if (!dialog.open) dialog.showModal();
            cropper = new Cropper(image, {aspectRatio: 4/5, viewMode: 1, autoCropArea: 1, dragMode: 'move'});
            $('galeria-recorte-posicao').textContent = queue.length ? `Ajuste esta foto. Faltam mais ${queue.length} na fila.` : 'Arraste a imagem ou use o zoom.';
        };
        image.onerror = () => { status.textContent = 'Não foi possível abrir esta imagem.'; next(); };
        image.src = src;
    }
    function next() {
        clean(); editIndex = null;
        const file = queue.shift();
        if (!file) { if (dialog.open) dialog.close(); picker.value = ''; $('galeria-adicionar').focus(); return; }
        objectUrl = URL.createObjectURL(file); openCrop(objectUrl);
    }
    $('galeria-adicionar').addEventListener('click', () => picker.click());
    picker.addEventListener('change', () => {
        const files = Array.from(picker.files || []);
        if (!files.length) return;
        if (photos.length + files.length > 10) { status.textContent = 'Você pode adicionar até 10 fotos no total.'; picker.value = ''; return; }
        if (files.some(f => !['image/jpeg','image/png','image/webp'].includes(f.type) || f.size > 5*1024*1024)) {
            status.textContent = 'Escolha JPG, PNG ou WEBP de até 5 MB por imagem.'; picker.value = ''; return;
        }
        if (typeof Cropper === 'undefined') { status.textContent = 'O recorte não carregou. Recarregue a página.'; return; }
        queue = files; next();
    });
    $('galeria-confirmar').addEventListener('click', () => {
        if (!cropper) return;
        try {
            const src = cropper.getCroppedCanvas({width:600,height:750,fillColor:'#fff'}).toDataURL('image/jpeg',0.82);
            if (editIndex !== null) photos[editIndex].src = src;
            else { photos.push({id:null,src,principal:photos.length === 0}); selected = photos.length - 1; }
            render(); status.textContent = 'Recorte aplicado. As fotos serão enviadas ao salvar o animal.'; next();
        } catch { status.textContent = 'Não foi possível aplicar o recorte. Escolha outra imagem.'; }
    });
    $('galeria-editar').addEventListener('click', () => { clean(); editIndex = selected; openCrop(photos[selected].src); });
    $('galeria-principal').addEventListener('click', () => { photos.forEach((p,i) => p.principal = i === selected); render(); });
    $('galeria-remover').addEventListener('click', () => {
        const removed = photos.splice(selected,1)[0];
        if (removed?.principal && photos.length) photos[0].principal = true;
        render();
    });
    $('galeria-pular').addEventListener('click', next);
    $('galeria-zoom-menos').addEventListener('click', () => cropper?.zoom(-0.1));
    $('galeria-zoom-mais').addEventListener('click', () => cropper?.zoom(0.1));
    dialog.addEventListener('cancel', () => { queue = []; clean(); editIndex = null; picker.value = ''; });
    form.addEventListener('submit', event => {
        serialize();
        const bytes = photos.filter(p => !p.id).reduce((sum,p) => sum + p.src.length, 0);
        if (dialog.open || queue.length || bytes > 6*1024*1024) {
            event.preventDefault(); status.textContent = bytes > 6*1024*1024 ? 'As fotos ultrapassam 6 MB. Remova algumas e salve em duas etapas.' : 'Conclua os recortes antes de salvar.';
        }
    });
    render();
})();
