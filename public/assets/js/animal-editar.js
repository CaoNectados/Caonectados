(() => {
    const form = document.getElementById('form-editar-animal');
    if (!form) return;
    const especie = document.getElementById('especie-edicao');
    const raca = document.getElementById('raca-edicao');
    const racas = JSON.parse(form.dataset.racas);
    especie.addEventListener('change', () => {
        raca.replaceChildren(new Option('Selecione', ''));
        racas.filter(item => item.ativo && String(item.especie_id) === especie.value)
            .forEach(item => raca.add(new Option(item.nome, item.id)));
    });
})();
