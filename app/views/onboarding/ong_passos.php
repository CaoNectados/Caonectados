<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Onboarding - ONG/Protetor</title>
</head>
<body>
    <main>
        <form action="/onboarding/ong/salvar" method="POST" enctype="multipart/form-data">
            
            <!-- Campo oculto para definir se é ONG ou PROTETOR baseado na tela anterior -->
            <input type="hidden" name="tipo_perfil" value="<?= htmlspecialchars($_GET['tipo'] ?? 'ONG') ?>">

            <fieldset>
                <legend>Vamos validar sua ONG/Perfil</legend>
                <label for="cnpj_cpf">Digite o CNPJ ou CPF da sua instituição:</label>
                <input type="text" name="cnpj_cpf" id="cnpj_cpf" placeholder="00.000.000/0000-00" required>
            </fieldset>

            <fieldset>
                <legend>Selecione a sua localização</legend>
                <label for="regiao_id">Pesquise seu Bairro *</label>
                <select name="regiao_id" id="regiao_id" required>
                    <option value="">Selecione o bairro</option>
                    <option value="1">Porto Meira</option>
                </select>
            </fieldset>

            <fieldset>
                <legend>Página da ONG</legend>
                <label for="nome_fantasia">Nome da ONG/Protetor</label>
                <input type="text" name="nome_fantasia" id="nome_fantasia" required>

                <label for="instagram">Instagram</label>
                <input type="text" name="instagram" id="instagram" placeholder="Ex: @suaong">

                <label for="facebook">Facebook</label>
                <input type="text" name="facebook" id="facebook" placeholder="Ex: facebook.com/suaong">

                <label for="chave_pix">Chave PIX para doações</label>
                <input type="text" name="chave_pix" id="chave_pix" placeholder="Ex: CNPJ ou CPF">
            </fieldset>

            <fieldset>
                <legend>Anexos</legend>
                <label for="foto_perfil">Adicione uma foto de perfil</label>
                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*">

                <label for="comprovante">Comprove a sua atividade</label>
                <input type="file" name="comprovante" id="comprovante" accept=".pdf, image/*">
            </fieldset>

            <button type="submit">Concluir Cadastro</button>
        </form>
    </main>
</body>
</html>