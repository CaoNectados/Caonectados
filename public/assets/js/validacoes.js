// public/assets/js/validacoes.js

const CaonectadosValidator = {
    
    // 1. Valida se o bairro selecionado existe no datalist
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
        return encontradoId !== ''; // Retorna true se achou, false se não achou
    },

    // 2. Valida CPF matematicamente
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

    // 3. Valida CNPJ matematicamente
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

    // 4. Determina se é CPF ou CNPJ pelo tamanho e valida
    validarDocumento: function(documento) {
        const docLimpo = documento.replace(/[^\d]+/g, '');
        if (docLimpo.length <= 11) {
            return this.isCpfValido(docLimpo);
        } else {
            return this.isCnpjValido(docLimpo);
        }
    },

    // 5. Valida o tamanho máximo de um arquivo (em Megabytes)
    validarTamanhoArquivo: function(inputElement, tamanhoMaximoMB = 2) {
        if (inputElement.files && inputElement.files[0]) {
            const tamanhoEmMB = inputElement.files[0].size / (1024 * 1024);
            return tamanhoEmMB <= tamanhoMaximoMB;
        }
        return true; // Se não tem arquivo, passa (a validação de 'required' pega isso depois se precisar)
    },

    // 6. Verifica todos os campos obrigatórios de um container (div ou form)
    validarCamposObrigatorios: function(containerElement) {
        const campos = containerElement.querySelectorAll('[required]');
        let valido = true;

        campos.forEach(campo => {
            if (!campo.checkValidity()) {
                campo.reportValidity();
                valido = false;
            }
        });
        return valido;
    }
};