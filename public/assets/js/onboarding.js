/**
 * Gerenciador de Onboarding Compartilhado
 */
const OnboardingManager = {
    etapaAtual: 1,
    totalEtapas: 5,
    urlSelecionarPerfil: '',

    init: function(config) {
        this.totalEtapas = config.totalEtapas || 5;
        this.urlSelecionarPerfil = config.urlSelecionarPerfil || '';
        this.atualizarVisualEtapas();
        this.registrarEventos(config.validacoesPorEtapa, config.validarEnvioFinal);
    },

    atualizarVisualEtapas: function() {
        for (let i = 1; i <= this.totalEtapas; i++) {
            const elEtapa = document.getElementById(`etapa-${i}`);
            const elProgresso = document.getElementById(`progresso-${i}`);

            if (elEtapa) {
                if (i === this.etapaAtual) {
                    elEtapa.classList.remove('hidden');
                } else {
                    elEtapa.classList.add('hidden');
                }
            }

            if (elProgresso) {
                if (i <= this.etapaAtual) {
                    elProgresso.classList.remove('bg-gray-300');
                    elProgresso.classList.add('bg-green-500');
                } else {
                    elProgresso.classList.remove('bg-green-500');
                    elProgresso.classList.add('bg-gray-300');
                }
            }
        }
    },

    sincronizarRegiaoId: function() {
        const inputTexto = document.getElementById('input-busca-bairro');
        const inputHidden = document.getElementById('regiao_id_hidden');
        const msgErro = document.getElementById('erro-bairro-invalido');
        const datalistOptions = document.querySelectorAll('#lista-regioes option');

        if (!inputTexto || !inputHidden) return;

        let encontradoId = '';

        datalistOptions.forEach(option => {
            if (option.value.trim().toLowerCase() === inputTexto.value.trim().toLowerCase()) {
                encontradoId = option.getAttribute('data-id');
            }
        });

        inputHidden.value = encontradoId;

        if (msgErro) {
            if (inputTexto.value.trim() !== '' && !encontradoId) {
                msgErro.classList.remove('hidden');
            } else {
                msgErro.classList.add('hidden');
            }
        }
    },

    avancarEtapa: function(validacaoCallback) {
        if (typeof validacaoCallback === 'function') {
            const ehValido = validacaoCallback(this.etapaAtual);
            if (!ehValido) return;
        }

        if (this.etapaAtual < this.totalEtapas) {
            this.etapaAtual++;
            this.atualizarVisualEtapas();
        }
    },

    voltarEtapa: function() {
        if (this.etapaAtual > 1) {
            this.etapaAtual--;
            this.atualizarVisualEtapas();
        } else if (this.urlSelecionarPerfil) {
            window.location.href = this.urlSelecionarPerfil;
        }
    },

    registrarEventos: function(validacoesPorEtapa, validarEnvioFinal) {
        const self = this;

        // AJAX submit handler
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', async function(event) {
                event.preventDefault();

                if (typeof validarEnvioFinal === 'function' && !validarEnvioFinal()) {
                    return;
                }

                const formData = new FormData(form);
                const btnSubmit = form.querySelector('button[type="submit"]');
                const btnTextoOriginal = btnSubmit ? btnSubmit.innerHTML : 'Enviar';

                if (btnSubmit) {
                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = 'Enviando...';
                }

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'erro') {
                        if (btnSubmit) {
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = btnTextoOriginal;
                        }
                        if (typeof mostrarModalFeedback === 'function') {
                            mostrarModalFeedback('erro', result.mensagem);
                        }
                    } else if (result.status === 'sucesso') {
                        if (typeof limparAutoSave === 'function') limparAutoSave();
                        window.location.href = result.redirect_url;
                    }
                } catch (error) {
                    if (btnSubmit) {
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = btnTextoOriginal;
                    }
                    if (typeof mostrarModalFeedback === 'function') {
                        mostrarModalFeedback('erro', 'Erro de conexão com o servidor.');
                    }
                }
            });
        }
    }
};

// Funções globais auxiliares para reuso de preview de imagens
function exibirPreviewFoto(input, idPreview, idPlaceholder) {
    const imgPreview = document.getElementById(idPreview);
    const placeholder = document.getElementById(idPlaceholder);

    if (input.files && input.files[0]) {
        const leitor = new FileReader();
        leitor.onload = function(e) {
            if (imgPreview) {
                imgPreview.src = e.target.result;
                imgPreview.classList.remove('hidden');
            }
            if (placeholder) {
                placeholder.classList.add('hidden');
            }
        };
        leitor.readAsDataURL(input.files[0]);
    }
}

function exibirNomeArquivo(input, idLabel) {
    const pNome = document.getElementById(idLabel);
    if (pNome) {
        if (input.files && input.files[0]) {
            pNome.innerText = "Arquivo anexado: " + input.files[0].name;
        } else {
            pNome.innerText = "";
        }
    }
}