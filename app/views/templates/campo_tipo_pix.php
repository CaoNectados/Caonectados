<label for="tipo_chave_pix" class="label-padrao">Tipo de chave PIX</label>
<select name="tipo_chave_pix" id="tipo_chave_pix" onchange="document.getElementById('chave_pix')?.setCustomValidity('')" class="input-padrao mb-3" aria-describedby="ajuda-tipo-pix">
    <option value="auto">Identificar automaticamente</option>
    <option value="cpf">CPF</option>
    <option value="cnpj">CNPJ</option>
    <option value="telefone">Telefone</option>
    <option value="email">E-mail</option>
    <option value="aleatoria">Chave aleatória</option>
</select>
<p id="ajuda-tipo-pix" class="mb-2 text-xs text-text-dark dark:text-white/85">Opcional. Selecione o tipo para conferir o formato da chave. Telefone: informe o DDD.</p>
