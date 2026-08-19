document.addEventListener("DOMContentLoaded", function() {
    const form = document.querySelector('form');
    
    if (!form || form.hasAttribute('data-no-autosave')) return;

    const storageKey = 'caonectados_backup_' + window.location.pathname + window.location.search;
    const stepKey = storageKey + '_etapa';

    // Função auxiliar para restaurar valores salvos
    function restaurarValoresFormulario() {
        const savedData = sessionStorage.getItem(storageKey);
        if (!savedData) return;

        try {
            const parsedData = JSON.parse(savedData);
            
            Object.keys(parsedData).forEach(key => {
                const elements = form.querySelectorAll(`[name="${key}"]`);
                
                elements.forEach(element => {
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        const savedValues = Array.isArray(parsedData[key]) ? parsedData[key] : [parsedData[key]];
                        if (savedValues.includes(element.value)) {
                            element.checked = true;
                        }
                    } else if (element.tagName === 'SELECT') {
                        // Define o valor do select
                        element.value = parsedData[key];
                        // Dispara o evento change para acionar dependências (ex: carregar raças ao restaurar espécie)
                        element.dispatchEvent(new Event('change'));
                    } else if (element.type !== 'password' && element.type !== 'file') {
                        element.value = parsedData[key];
                    }
                });
            });

            // Se restaurou o texto do bairro, sincroniza o ID oculto automaticamente
            if (typeof OnboardingManager !== 'undefined' && OnboardingManager.sincronizarRegiaoId) {
                OnboardingManager.sincronizarRegiaoId();
            }

            // Se for tela de adotante e marcou espécies extras, reabre a caixinha "Outros"
            if (typeof toggleOutrasEspecies === 'function') {
                const checkOutros = document.getElementById('checkbox-outras-especies');
                if (checkOutros && checkOutros.checked) {
                    toggleOutrasEspecies();
                }
            }
        } catch (e) {
            console.error("Erro ao restaurar dados salvos:", e);
        }
    }

    // 1. Executa a restauração inicial
    restaurarValoresFormulario();

    // Especial para o cadastro de animais: Se a espécie já foi salva no autosave, aguarda um instante e força a seleção da raça salva
    setTimeout(() => {
        const savedData = sessionStorage.getItem(storageKey);
        if (savedData) {
            try {
                const parsedData = JSON.parse(savedData);
                if (parsedData['raca_id']) {
                    const racaSelect = document.getElementById('raca_id');
                    if (racaSelect) {
                        racaSelect.value = parsedData['raca_id'];
                    }
                }
            } catch (err) {}
        }
    }, 500);

    // 2. RECUPERAÇÃO DA ETAPA ATUAL
    const savedStep = sessionStorage.getItem(stepKey);
    if (savedStep && typeof OnboardingManager !== 'undefined' && OnboardingManager.mostrarEtapa) {
        const etapa = parseInt(savedStep, 10);
        if (etapa > 1) {
            OnboardingManager.mostrarEtapa(etapa);
        }
    }

    // 3. SALVAMENTO AUTOMÁTICO
    function salvarDados() {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            // Ignora senhas e arquivos
            if (key.includes('senha') || key.includes('foto') || key.includes('comprovante')) continue;

            if (key.endsWith('[]')) {
                if (!data[key]) data[key] = [];
                data[key].push(value);
            } else {
                data[key] = value;
            }
        }
        
        sessionStorage.setItem(storageKey, JSON.stringify(data));
    }

    // Escuta a digitação, mudanças em selects e desfoque
    form.addEventListener('input', salvarDados);
    form.addEventListener('change', salvarDados);
    form.addEventListener('focusout', salvarDados); 
});

// Chame esta função após o envio com sucesso
function limparAutoSave() {
    const storageKey = 'caonectados_backup_' + window.location.pathname + window.location.search;
    sessionStorage.removeItem(storageKey);
    sessionStorage.removeItem(storageKey + '_etapa');
}