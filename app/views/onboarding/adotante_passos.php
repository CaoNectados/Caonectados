<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Onboarding - Adotante</title>
</head>

<body>
    <main>
        <form action="/onboarding/adotante/salvar" method="POST">

            <fieldset>

                <legend>Selecione a sua localização</legend>

                <label for="regiao_id">Pesquise seu Bairro *</label>

                <select
                    name="regiao_id"
                    id="regiao_id"
                    required>

                    <option value="">
                        Selecione o bairro
                    </option>

                    <?php foreach ($regioes as $regiao): ?>

                        <option value="<?= $regiao->getRegiaoId(); ?>">

                            <?= htmlspecialchars($regiao->getNomeRegiao()); ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </fieldset>

            <fieldset>
                <legend>Sobre você</legend>
                <label for="tipo_moradia">Tipo de moradia</label>
                <select name="tipo_moradia" id="tipo_moradia" required>
                    <option value="">Escolha</option>
                    <option value="CASA">Casa</option>
                    <option value="APARTAMENTO">Apartamento</option>
                </select>

                <label for="tamanho_interno">Espaço interior</label>
                <select name="tamanho_interno" id="tamanho_interno" required>
                    <option value="">Escolha</option>
                    <option value="PEQUENO">Pequeno</option>
                    <option value="MEDIO">Médio</option>
                    <option value="GRANDE">Grande</option>
                </select>

                <label for="tamanho_externo">Espaço externo</label>
                <select name="tamanho_externo" id="tamanho_externo" required>
                    <option value="">Escolha</option>
                    <option value="PEQUENO">Pequeno</option>
                    <option value="MEDIO">Médio</option>
                    <option value="GRANDE">Grande</option>
                </select>
            </fieldset>

            <fieldset>
                <legend>Como podemos te chamar?</legend>
                <input type="text" name="nome" placeholder="Digite seu nome aqui" required>

                <p>Selecione suas preferências para montarmos o seu feed perfeito.</p>

                <label>Espécie:</label>
                <input type="checkbox" name="especie[]" value="Gato"> Gato
                <input type="checkbox" name="especie[]" value="Cachorro"> Cachorro
                <input type="checkbox" name="especie[]" value="Outros"> Outros

                <label>Porte:</label>
                <input type="checkbox" name="porte[]" value="Pequeno"> Pequeno
                <input type="checkbox" name="porte[]" value="Medio"> Médio
                <input type="checkbox" name="porte[]" value="Grande"> Grande

                <label>Sexo:</label>
                <input type="checkbox" name="sexo[]" value="Femea"> Fêmea
                <input type="checkbox" name="sexo[]" value="Macho"> Macho
            </fieldset>

            <button type="submit">Ir para o Feed</button>
        </form>
    </main>
</body>

</html>