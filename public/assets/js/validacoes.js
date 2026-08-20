// public/assets/js/validacoes.js
// Ponto único de validações de formulário do front-end. Todo formulário que precisa
// validar CPF/CNPJ, e-mail, telefone, links sociais, chave PIX, datas ou arquivos
// deve reutilizar as funções abaixo em vez de reimplementar regex/checagens na view.

const CaonectadosValidator = {

    // Usado por: atributo oninput do campo de bairro em perfil/editar.php
    validarRegiao: function(inputId, hiddenId, datalistId) {
        const inputTexto = document.getElementById(inputId);
        const inputHidden = document.getElementById(hiddenId);
        const datalistOptions = document.querySelectorAll(`#${datalistId} option`);

        let encontradoId = '';

        datalistOptions.forEach(option => {
            if (option.value.trim().toLowerCase() === inputTexto.value.trim().toLowerCase()) {
                encontradoId = option.getAttribute('data-id');
            }
        });

        inputHidden.value = encontradoId;
        return encontradoId !== '';
    },

    // Usado por: perfil/editar.php, protetor_onboarding.php e internamente por validarDocumento()/validarChavePix()
    isCpfValido: function(cpf) {
        cpf = cpf.replace(/[^\d]+/g, '');
        if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;

        let soma = 0, resto;
        for (let i = 1; i <= 9; i++) soma = soma + parseInt(cpf.substring(i-1, i)) * (11 - i);
        resto = (soma * 10) % 11;
        if ((resto === 10) || (resto === 11)) resto = 0;
        if (resto !== parseInt(cpf.substring(9, 10))) return false;

        soma = 0;
        for (let i = 1; i <= 10; i++) soma = soma + parseInt(cpf.substring(i-1, i)) * (12 - i);
        resto = (soma * 10) % 11;
        if ((resto === 10) || (resto === 11)) resto = 0;
        if (resto !== parseInt(cpf.substring(10, 11))) return false;

        return true;
    },

    // Usado por: perfil/editar.php, protetor_onboarding.php e internamente por validarDocumento()/validarChavePix()
    isCnpjValido: function(cnpj) {
        cnpj = cnpj.replace(/[^\d]+/g, '');
        if (cnpj.length !== 14 || /^(\d)\1+$/.test(cnpj)) return false;

        let tamanho = cnpj.length - 2;
        let numeros = cnpj.substring(0, tamanho);
        let digitos = cnpj.substring(tamanho);
        let soma = 0, pos = tamanho - 7;

        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        let resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(0)) return false;

        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;
        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }
        resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;
        if (resultado != digitos.charAt(1)) return false;

        return true;
    },

    // Usado por: (não referenciado atualmente) — determina CPF/CNPJ pelo tamanho do documento e delega para isCpfValido()/isCnpjValido()
    validarDocumento: function(documento) {
        const docLimpo = documento.replace(/[^\d]+/g, '');
        if (docLimpo.length <= 11) {
            return this.isCpfValido(docLimpo);
        } else {
            return this.isCnpjValido(docLimpo);
        }
    },

    // Usado por: perfil/editar.php, adotante_onboarding.php e protetor_onboarding.php (uploads de foto/comprovante)
    validarTamanhoArquivo: function(inputElement, tamanhoMaximoMB = 2) {
        if (inputElement.files && inputElement.files[0]) {
            const tamanhoEmMB = inputElement.files[0].size / (1024 * 1024);
            return tamanhoEmMB <= tamanhoMaximoMB;
        }
        return true;
    },

    // Usado por: perfil/editar.php e protetor_onboarding.php (campos de Instagram/Facebook)
    validarLinkSocial: function(url, rede) {
        if (!url || url.trim() === '') return true;

        let link = url.trim().toLowerCase();
        if (!link.startsWith('http://') && !link.startsWith('https://')) {
            link = 'https://' + link;
        }

        try {
            const parsedUrl = new URL(link);
            if (rede === 'instagram') {
                return parsedUrl.hostname.includes('instagram.com');
            }
            if (rede === 'facebook') {
                return parsedUrl.hostname.includes('facebook.com');
            }
            return true;
        } catch (_) {
            return false;
        }
    },

    // Usado por: perfil/editar.php e protetor_onboarding.php (campo de chave PIX)
    validarChavePix: function(chave) {
        if (!chave || chave.trim() === '') return true;
        const c = chave.trim();

        if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(c)) return true;

        const numeros = c.replace(/[^\d]+/g, '');
        if (numeros.length === 11) return this.isCpfValido(numeros);
        if (numeros.length === 14) return this.isCnpjValido(numeros);
        if (numeros.length === 10 || numeros.length === 11) return true;

        // Chave aleatória EVP (padrão UUID)
        if (/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(c)) return true;

        return false;
    },

    // Usado por: adotante_onboarding.php e protetor_onboarding.php (etapa de dados pessoais)
    validarMaioridade: function(dataNascimento) {
        if (!dataNascimento) return false;

        const hoje = new Date();
        const nascimento = new Date(dataNascimento);
        let idade = hoje.getFullYear() - nascimento.getFullYear();
        const mes = hoje.getMonth() - nascimento.getMonth();

        if (mes < 0 || (mes === 0 && hoje.getDate() < nascimento.getDate())) {
            idade--;
        }

        return idade >= 18;
    },

    // Usado por: perfil/editar.php, adotante_onboarding.php e protetor_onboarding.php
    validarNome: function(nome) {
        return nome && nome.trim().length >= 2;
    },

    // Usado por: perfil/editar.php, adotante_onboarding.php e protetor_onboarding.php
    validarTelefone: function(telefone) {
        if (!telefone || telefone.trim() === '') return true; // Campo opcional

        const numeros = telefone.replace(/[^\d]+/g, '');
        return numeros.length === 10 || numeros.length === 11;
    },

    // Usado por: (não referenciado atualmente) — equivalente client-side de ValidationService::validarDataNaoFutura() no back-end
    validarDataNaoFutura: function(dataStr) {
        if (!dataStr || typeof dataStr !== 'string') return false;

        const regex = /^\d{4}-\d{2}-\d{2}$/;
        if (!regex.test(dataStr)) return false;

        const dataEntrada = new Date(dataStr + 'T00:00:00');
        if (isNaN(dataEntrada.getTime())) return false;

        const hoje = new Date();
        hoje.setHours(0, 0, 0, 0);

        return dataEntrada <= hoje;
    }

};

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { CaonectadosValidator };
}
