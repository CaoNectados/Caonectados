<?php

namespace app\services;

use Exception;
use DateTime;
use DateTimeZone;

class ValidationService
{
    public const VERSAO_TERMOS = '2026-10-14';

    public static function validarFormatoFormulario(array $dados): void
    {
        $listas = ['preferencias_especie', 'preferencias_raca', 'preferencias_porte', 'preferencias_sexo', 'ids', 'racas_aceitas'];
        foreach ($dados as $campo => $valor) {
            if ($campo === 'fotos_galeria') {
                if (!is_array($valor) || !array_is_list($valor) || count($valor) > 9) {
                    throw new \InvalidArgumentException('Envie até 9 fotos adicionais.');
                }
                foreach ($valor as $foto) {
                    if (!is_string($foto) || strlen($foto) > 1400000 || !preg_match('#^data:image/(jpeg|png|webp);base64,#', $foto)) {
                        throw new \InvalidArgumentException('Foto adicional inválida. Recorte a imagem novamente.');
                    }
                }
                continue;
            }

            if (in_array($campo, $listas, true)) {
                if (!is_array($valor) || array_filter($valor, static fn($item) => !is_scalar($item))) {
                    throw new \InvalidArgumentException('Selecione uma lista válida para ' . $campo . '.');
                }
            } elseif (!is_scalar($valor) && $valor !== null) {
                throw new \InvalidArgumentException('Formato inválido para ' . $campo . '.');
            }
        }
    }

    public static function validarTexto($valor, string $campo, int $minimo, int $maximo): string
    {
        if (!is_string($valor) || mb_strlen(trim($valor)) < $minimo || mb_strlen(trim($valor)) > $maximo) {
            throw new Exception("{$campo}: informe entre {$minimo} e {$maximo} caracteres.");
        }
        return trim($valor);
    }

    public static function validarLocalizacao($regiaoId, $logradouro, $numero): void
    {
        if (!is_scalar($regiaoId) || !preg_match('/^[1-9][0-9]*$/', (string)$regiaoId)
            || !(new \app\repositories\RegiaoRepository())->buscarPorId((int)$regiaoId)) {
            throw new Exception('Selecione um bairro/região cadastrado.');
        }
        self::validarTexto($logradouro, 'Logradouro', 1, 1000);
        self::validarTexto($numero, 'Número da residência', 1, 20);
    }

    public static function validarDadosAdotante(array $dados, bool $cadastro = false): void
    {
        $opcoes = [
            'tipo_moradia' => ['casa', 'apartamento', 'sitio', 'chacara', 'outro'],
            'tipo_morada' => ['casa', 'apartamento', 'sitio', 'outro'],
            'espaco_interior' => ['pequeno', 'medio', 'grande'],
            'tamanho_interno_morada' => ['pequeno', 'medio', 'grande'],
            'espaco_externo' => ['nenhum', 'pequeno', 'medio', 'grande'],
            'possui_criancas' => ['sim', 'nao'],
            'possui_outros_pets' => ['sim', 'nao'],
        ];
        if ($cadastro) self::validarCamposObrigatorios($dados, ['tipo_moradia', 'espaco_interior', 'espaco_externo', 'possui_criancas', 'possui_outros_pets']);
        foreach ($opcoes as $campo => $permitidos) {
            if (array_key_exists($campo, $dados) && !in_array($dados[$campo], $permitidos, true)) {
                throw new Exception("Selecione uma opção válida para {$campo}.");
            }
        }
        foreach (['preferencias_porte' => ['pequeno', 'medio', 'grande'], 'preferencias_sexo' => ['macho', 'femea']] as $campo => $permitidos) {
            if (!isset($dados[$campo])) continue;
            if (!is_array($dados[$campo]) || array_filter($dados[$campo], static fn($valor) => !is_string($valor) || !in_array($valor, $permitidos, true))) {
                throw new Exception("Preferências inválidas para {$campo}.");
            }
        }
        foreach (['preferencias_especie' => \app\repositories\EspecieRepository::class, 'preferencias_raca' => \app\repositories\RacaRepository::class] as $campo => $repositorio) {
            if (!isset($dados[$campo])) continue;
            if (!is_array($dados[$campo])) throw new Exception("Selecione uma lista válida de {$campo}.");
            foreach ($dados[$campo] as $id) {
                if (!is_scalar($id) || !preg_match('/^[1-9][0-9]*$/', (string)$id)
                    || !($item = (new $repositorio())->buscarPorId((int)$id)) || !$item->isAtivo()) {
                    throw new Exception("Selecione opções ativas de {$campo}.");
                }
            }
        }
    }

    public static function validarDadosPagina(array $dados, bool $cadastro = false): void
    {
        self::validarTexto($dados['descricao'] ?? '', 'Descrição da página', $cadastro ? 15 : 0, 1000);
        if (!self::validarChavePix($dados['chave_pix'] ?? '', $dados['tipo_chave_pix'] ?? 'auto')) {
            throw new Exception('Informe uma chave PIX válida para o tipo selecionado.');
        }
        foreach (['instagram', 'facebook'] as $rede) {
            if (isset($dados[$rede]) && (!is_string($dados[$rede]) || !self::validarLinkRedeSocial($dados[$rede], $rede))) {
                throw new Exception('Informe um link válido de ' . ucfirst($rede) . '.');
            }
        }
    }

    public static function validarAceiteTermos($aceite): void
    {
        if (!is_scalar($aceite) || !in_array($aceite, ['on', '1', 1, true], true)) {
            throw new Exception('Leia e aceite os Termos de Responsabilidade para continuar.');
        }
    }
    /**
     * Verifica se os campos obrigatórios foram preenchidos
     */
    // Usado por: OnBoardingService (cadastro de adotante e de protetor/ONG)
    public static function validarCamposObrigatorios(array $dados, array $camposRequeridos): void
    {
        foreach ($camposRequeridos as $campo) {
            if (!isset($dados[$campo]) || !is_scalar($dados[$campo]) || trim((string)$dados[$campo]) === '') {
                throw new Exception("O campo obrigatório '{$campo}' não foi preenchido.");
            }
        }
    }

    /**
     * Valida o Nome (Mínimo de 2 caracteres)
     */
    // Usado por: OnBoardingService (cadastro de adotante e de protetor/ONG), PerfilService::atualizarPerfil
    public static function validarNome($nome): void
    {
        if (!is_string($nome) || mb_strlen(trim($nome)) < 2 || mb_strlen(trim($nome)) > 150) {
            throw new Exception("O nome deve conter entre 2 e 150 caracteres.");
        }
    }

    /**
     * Valida formato de e-mail
     */
    // Usado por: AuthController (cadastro/recuperação de senha), PerfilController (troca de e-mail)
    public static function validarEmail($email): void
    {
        if (!is_string($email) || empty($email) || strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Informe um e-mail válido.");
        }
    }

    /**
     * Valida e limpa o telefone (Deve conter 10 ou 11 dígitos)
     */
    // Usado por: OnBoardingService (cadastro de adotante e de protetor/ONG), PerfilService::atualizarPerfil
    public static function validarTelefone($telefone): ?string
    {
        if ($telefone === null || $telefone === '') {
            return null;
        }

        if (!is_string($telefone) || !preg_match('/^[0-9() .+\-]+$/', $telefone)) throw new Exception('Informe um telefone válido com DDD.');
        $telefoneLimpo = preg_replace('/[^0-9]/', '', $telefone);
        if (strlen($telefoneLimpo) < 10 || strlen($telefoneLimpo) > 11) {
            throw new Exception("O número de telefone informado é inválido.");
        }

        return $telefoneLimpo;
    }

    /**
     * Exige mínimo de 8 caracteres com maiúscula, minúscula, número e caractere especial
     */
    // Usado por: AuthController (cadastro/redefinição de senha), PerfilController (troca de senha)
    public static function validarForcaSenha(string $senha): void
    {
        if (
            strlen($senha) < 8 ||
            !preg_match('/[A-Z]/', $senha) ||
            !preg_match('/[a-z]/', $senha) ||
            !preg_match('/[0-9]/', $senha) ||
            !preg_match('/[\W_]/', $senha)
        ) {
            throw new Exception("A senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e um caractere especial.");
        }
    }

    /**
     * Valida Maioridade (Mínimo de 18 anos)
     */
    // Usado por: OnBoardingService (cadastro de adotante e de protetor pessoa física)
    public static function validarMaioridade($dataNascimento): void
    {
        if (!is_string($dataNascimento) || empty($dataNascimento)) {
            throw new Exception("A data de nascimento é obrigatória.");
        }

        $dtNascimento = DateTime::createFromFormat('!Y-m-d', $dataNascimento);
        if (!$dtNascimento || $dtNascimento->format('Y-m-d') !== $dataNascimento) {
            throw new Exception("Data de nascimento inválida.");
        }

        $hoje = new DateTime();
        if ($dtNascimento > $hoje) {
            throw new Exception('A data de nascimento não pode ser futura.');
        }
        $idade = $hoje->diff($dtNascimento)->y;
        if ($idade < 18) {
            throw new Exception("É necessário ter pelo menos 18 anos para se cadastrar.");
        }
    }

    /**
     * Valida formato e garante que a data não seja superior à data atual
     */
    // Usado por: OnBoardingService::processarOng (data de abertura do CNPJ)
    public static function validarDataNaoFutura($data): bool
    {
        if (!is_string($data) || empty($data)) {
            return false;
        }

        $timezone = new DateTimeZone(date_default_timezone_get() ?: 'America/Sao_Paulo');
        $d = DateTime::createFromFormat('!Y-m-d', $data, $timezone);

        // Verifica formato e integridade real da data (evita 2026-02-31)
        if (!$d || $d->format('Y-m-d') !== $data) {
            return false;
        }

        $hoje = new DateTime('today', $timezone);
        $d->setTime(0, 0, 0);

        return $d <= $hoje;
    }

    // Usado por: OnBoardingService::processarOng, validarChavePix()
    public static function validarCpf(string $cpf): bool
    {
        if (!preg_match('/^[0-9. \-]+$/', $cpf)) return false;
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    // Usado por: OnBoardingService::processarOng, validarChavePix()
    public static function validarCnpj(string $cnpj): bool
    {
        if (!preg_match('/^[0-9.\/ \-]+$/', $cnpj)) return false;
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }

        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }

    // Usado por: (não referenciado atualmente)
    public static function verificarExistenciaCnpjReal(string $cnpj): bool
    {
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpjLimpo) !== 14) return false;

        $url = "https://minhareceita.org/" . $cnpjLimpo;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $resposta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $dados = json_decode($resposta, true);
            return isset($dados['situacao_cadastral']) && (int)$dados['situacao_cadastral'] === 2;
        }

        return false;
    }

    /**
     * Valida os formatos permitidos de Chave PIX
     */
    // Usado por: (não referenciado atualmente)
    public static function validarChavePix($chave, $tipo = 'auto'): bool
    {
        if (($chave !== null && !is_string($chave)) || !is_string($tipo)) return false;
        if (!in_array($tipo, ['auto', 'cpf', 'cnpj', 'telefone', 'email', 'aleatoria'], true)) return false;
        $chave = trim($chave ?? '');
        if ($chave === '') return true;
        if (strlen($chave) > 255) return false;

        $numeros = preg_replace('/\D/', '', $chave);
        $documento = preg_match('/^[0-9.\/\-]+$/', $chave) === 1;
        $telefone = preg_match('/^\+?[0-9() .\-]+$/', $chave) === 1;
        if (in_array(strlen($numeros), [12, 13], true) && str_starts_with($numeros, '55')) {
            $numerosTelefone = substr($numeros, 2);
        } else {
            $numerosTelefone = $numeros;
        }
        $validos = [
            'email' => strlen($chave) <= 150 && filter_var($chave, FILTER_VALIDATE_EMAIL) !== false,
            'aleatoria' => preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $chave) === 1,
            'cpf' => $documento && self::validarCpf($chave),
            'cnpj' => $documento && self::validarCnpj($chave),
            'telefone' => $telefone && preg_match('/^[1-9][0-9](?:[2-5][0-9]{7}|9[0-9]{8})$/', $numerosTelefone) === 1,
        ];
        return $tipo === 'auto' ? in_array(true, $validos, true) : $validos[$tipo];
    }

    /**
     * Valida se a string é um link válido e se pertence à rede social correta
     */
    // Usado por: OnBoardingService::processarOng, PerfilService::atualizarPerfil (links de Instagram/Facebook do protetor)
    public static function validarLinkRedeSocial(?string $url, string $rede): bool
    {
        if (empty($url)) return true;

        $link = trim($url);
        if (strlen($link) > 255 || !in_array($rede, ['instagram', 'facebook', 'whatsapp', 'outro'], true)) return false;
        if (!preg_match('~^[a-z][a-z0-9+.-]*:~i', $link)) $link = 'https://' . $link;
        if (!filter_var($link, FILTER_VALIDATE_URL)) return false;
        $partes = parse_url($link);
        if (!in_array(strtolower($partes['scheme'] ?? ''), ['http', 'https'], true)
            || isset($partes['user']) || isset($partes['pass'])) return false;
        $host = strtolower($partes['host'] ?? '');
        $dominio = ['instagram' => 'instagram.com', 'facebook' => 'facebook.com'][$rede] ?? null;
        if ($dominio && $host !== $dominio && !str_ends_with($host, '.' . $dominio)) return false;

        return true;
    }

    // Usado por: sanitizarArray()
    public static function sanitizarString(?string $texto): ?string
    {
        if ($texto === null) return null;
        return htmlspecialchars(strip_tags(trim($texto)), ENT_QUOTES, 'UTF-8');
    }

    // Usado por: OnBoardingController (limpeza dos dados de formulário antes do processamento)
    public static function sanitizarArray(array $dados): array
    {
        $dadosLimpos = [];
        foreach ($dados as $key => $value) {
            if (is_array($value)) {
                $dadosLimpos[$key] = self::sanitizarArray($value);
            } else {
                $dadosLimpos[$key] = self::sanitizarString($value);
            }
        }
        return $dadosLimpos;
    }
}
