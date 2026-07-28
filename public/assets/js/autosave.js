
document.addEventListener("DOMContentLoaded", function() {
    // Procura o formulário principal da página
    const form = document.querySelector('form');
    
    // Se não tiver formulário nesta página, o script para aqui 
    if (!form) return;

    // Cria uma chave única baseada na URL atual (ex: /cadastro, /onboarding)
    const storageKey = 'caonectados_backup_' + window.location.pathname;

    // 1. RECUPERAÇÃO MÁGICA: Preenche os dados se a página for recarregada
    const savedData = sessionStorage.getItem(storageKey);
    if (savedData) {
        const parsedData = JSON.parse(savedData);
        
        Object.keys(parsedData).forEach(key => {
            const elements = form.querySelectorAll(`[name="${key}"]`);
            
            elements.forEach(element => {
                if (element.type === 'checkbox' || element.type === 'radio') {
                    const savedValues = Array.isArray(parsedData[key]) ? parsedData[key] : [parsedData[key]];
                    if (savedValues.includes(element.value)) {
                        element.checked = true;
                    }
                } else if (element.type !== 'password' && element.type !== 'file') {
                    element.value = parsedData[key];
                }
            });
        });
    }

    //Salva cada letra que o usuário digita
    form.addEventListener('input', function() {
        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            // Regra de Segurança: Nunca salva senhas ou arquivos localmente
            if (key.includes('senha') || key.includes('foto') || key.includes('comprovante')) continue;

            if (key.endsWith('[]')) {
                if (!data[key]) data[key] = [];
                data[key].push(value);
            } else {
                data[key] = value;
            }
        }
        
        sessionStorage.setItem(storageKey, JSON.stringify(data));
    });
});

// Função para limpar o rascunho (chamar essa função quando o AJAX der sucesso)
function limparAutoSave() {
    const storageKey = 'caonectados_backup_' + window.location.pathname;
    sessionStorage.removeItem(storageKey);
}