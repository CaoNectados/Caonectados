<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\database\ConnectionFactory;
use app\repositories\UsuarioRepository;
use app\repositories\RegiaoRepository;
use app\repositories\AdotanteRepository;
use app\repositories\ProtetorRepository;
use app\repositories\PaginaRepository;
use app\repositories\RedeRepository;
use app\services\PerfilService;
use app\repositories\EspecieRepository;
use app\services\MailService;
use Exception;

class PerfilController extends Controller
{
    private PerfilService $perfilService;
    private UsuarioRepository $usuarioRepo;
    private RegiaoRepository $regiaoRepo;
    private EspecieRepository $especieRepo;
    private ProtetorRepository $protetorRepo;

    public function __construct()
    {
        $this->autenticacaoRequired();
        $this->perfilService = new PerfilService();
        $this->usuarioRepo = new UsuarioRepository();
        $this->regiaoRepo = new RegiaoRepository();
        $this->especieRepo = new EspecieRepository();
        $this->protetorRepo = new ProtetorRepository();
    }

    public function index(): void
    {
        $usuarioId = (int)$_SESSION['usuario_id'];
        $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';
        $fotoPerfil = null;

        if ($tipoPerfil === 'adotante' || $tipoPerfil === 'usuario') {
            $adotanteRepo = new AdotanteRepository();
            $adotante = $adotanteRepo->buscarPorUsuarioId($usuarioId);
            $fotoPerfil = $adotante['foto_perfil'] ?? null;
        } elseif (in_array($tipoPerfil, ['ong', 'protetor'], true)) {
            $paginaRepo = new PaginaRepository();
            $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
            if ($protetor) {
                $pagina = $paginaRepo->buscarPorProtetorId((int)$protetor['protetor_id']);
                $fotoPerfil = $pagina['foto_perfil'] ?? null;
            }
        }

        $this->view('perfil/perfil', [
            'titulo'     => 'Perfil',
            'fotoPerfil' => $fotoPerfil
        ]);
    }

    public function perfil(): void
    {
        $this->index();
    }

    public function editar(): void
    {
        $usuarioId = (int)$_SESSION['usuario_id'];
        $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';

        $usuario = $this->usuarioRepo->buscarPorId($usuarioId);
        $regioes = $this->regiaoRepo->buscarTodas();

        $regiaoAtual = null;
        if (!empty($usuario['regiao_id'])) {
            $regiaoAtual = $this->regiaoRepo->buscarPorId((int)$usuario['regiao_id']);
        }

        $dadosEspecificos = [];
        $redes = [];
        $especiesAtivas = $this->especieRepo->listarAtivas();

      if ($tipoPerfil === 'adotante' || $tipoPerfil === 'usuario') {
            $adotanteRepo = new AdotanteRepository();
            $dadosEspecificos = $adotanteRepo->buscarPorUsuarioId($usuarioId) ?? [];

            $detalhes = json_decode($dadosEspecificos['detalhes'] ?? '{}', true) ?: [];
            
            $rawEspecies = $detalhes['preferencias_especie'] ?? $detalhes['preferencias']['especie'] ?? [];
            $rawPorte    = $detalhes['preferencias_porte'] ?? $detalhes['preferencias']['porte'] ?? [];
            $rawSexo     = $detalhes['preferencias_sexo'] ?? $detalhes['preferencias']['sexo'] ?? [];
            $rawRacas    = $detalhes['preferencias_raca'] ?? $detalhes['preferencias']['raca'] ?? [];

            $dadosEspecificos['possui_criancas']      = $detalhes['possui_criancas'] ?? 'nao';
            $dadosEspecificos['possui_outros_pets']   = $detalhes['possui_outros_pets'] ?? 'nao';
            $dadosEspecificos['espaco_externo']       = $detalhes['espaco_externo'] ?? '';
            $dadosEspecificos['preferencias_especie'] = array_map('strval', is_array($rawEspecies) ? $rawEspecies : []);
            $dadosEspecificos['preferencias_porte']   = is_array($rawPorte) ? $rawPorte : [];
            $dadosEspecificos['preferencias_sexo']    = is_array($rawSexo) ? $rawSexo : [];
            $dadosEspecificos['preferencias_raca']    = array_map('strval', is_array($rawRacas) ? $rawRacas : []);
        } elseif (in_array($tipoPerfil, ['ong', 'protetor'], true)) {
            $paginaRepo = new PaginaRepository();
            $redeRepo = new RedeRepository();

            $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
            if ($protetor) {
                $protetorId = (int)$protetor['protetor_id'];
                $dadosEspecificos = $protetor;
                $pagina = $paginaRepo->buscarPorProtetorId($protetorId) ?? [];

                $dadosEspecificos['descricao'] = $pagina['descricao'] ?? '';
                $dadosEspecificos['chave_pix'] = $pagina['chave_pix'] ?? '';
                $dadosEspecificos['foto_perfil'] = $pagina['foto_perfil'] ?? '';

                $redesBanco = $redeRepo->buscarPorProtetorId($protetorId) ?? [];
                foreach ($redesBanco as $r) {
                    $redes[$r['tipo_rede']] = $r['link_rede'];
                }
            }
        }

        $emailCompleto = $usuario['email'] ?? '';
        $partes = explode('@', $emailCompleto);
        $emailMascarado = strlen($partes[0]) > 2 ? substr($partes[0], 0, 2) . '***@' . $partes[1] : $emailCompleto;

        $this->view('perfil/editar', [
            'titulo'         => 'Editar Perfil',
            'usuario'        => $usuario,
            'regioes'        => $regioes,
            'regiaoAtual'    => $regiaoAtual,
            'especifico'     => $dadosEspecificos,
            'redes'          => $redes,
            'tipoPerfil'     => $tipoPerfil,
            'emailMascarado' => $emailMascarado,
            'especies'       => $especiesAtivas
        ]);
    }

    public function atualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        try {
            $usuarioId = (int)$_SESSION['usuario_id'];
            $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';

            $mensagem = $this->perfilService->atualizarPerfil($_POST, $_FILES, $usuarioId, $tipoPerfil);

            $_SESSION['usuario_nome'] = trim($_POST['nome'] ?? $_SESSION['usuario_nome']);

            $this->json(200, [
                'status'       => 'sucesso',
                'mensagem'     => $mensagem,
                'redirect_url' => URL_BASE . '/perfil'
            ]);

        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    public function atualizarFoto(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        try {
            $usuarioId = (int)$_SESSION['usuario_id'];
            $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';
            
            // Aceita tanto 'foto_cortada' (do modal direto) quanto 'foto_perfil'
            $base64Data = $_POST['foto_cortada'] ?? $_POST['foto_perfil'] ?? '';

            if (empty($base64Data)) {
                throw new Exception('Nenhuma imagem enviada.');
            }

            $this->perfilService->atualizarApenasFoto($base64Data, $usuarioId, $tipoPerfil);

            $this->json(200, [
                'status'   => 'sucesso',
                'mensagem' => 'Foto de perfil atualizada com sucesso!'
            ]);

        } catch (Exception $e) {
            $this->json(200, [
                'status'   => 'erro',
                'mensagem' => $e->getMessage()
            ]);
        }
    }

    // ==========================================
    // FLUXOS DE SEGURANÇA (SENHA E E-MAIL)
    // ==========================================

    public function telaRedefinirSenha(): void
    {
        $usuario = $this->usuarioRepo->buscarPorId((int)$_SESSION['usuario_id']);
        $emailCompleto = $usuario['email'] ?? '';
        $partes = explode('@', $emailCompleto);
        $emailMascarado = strlen($partes[0]) > 2 ? substr($partes[0], 0, 2) . '***@' . $partes[1] : $emailCompleto;

        $this->view('perfil/redefinir_senha', [
            'titulo'         => 'Redefinir Senha',
            'emailMascarado' => $emailMascarado
        ]);
    }

    public function enviarCodigoSenha(): void
    {
        try {
            $usuarioId = (int)$_SESSION['usuario_id'];
            $usuario = $this->usuarioRepo->buscarPorId($usuarioId);

            $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiraEm = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $this->usuarioRepo->salvarCodigoVerificacao($usuarioId, $codigo, $expiraEm);
            
            MailService::enviarCodigoVerificacao($usuario['email'], $usuario['nome'] ?? 'Usuário', $codigo, 'redefinir_senha');

            $_SESSION['redefinir_senha_usuario_id'] = $usuarioId;

            $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Código de verificação enviado para o seu e-mail.']);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    public function confirmarNovaSenha(): void
    {
        try {
            $usuarioId = (int)$_SESSION['usuario_id'];
            $codigo = trim($_POST['codigo'] ?? '');
            $novaSenha = $_POST['nova_senha'] ?? '';
            $confSenha = $_POST['confirmar_senha'] ?? '';

            if ($novaSenha !== $confSenha) {
                throw new Exception('As senhas não coincidem.');
            }

            if (strlen($novaSenha) < 8 || !preg_match('/[A-Z]/', $novaSenha) || !preg_match('/[a-z]/', $novaSenha) || !preg_match('/[0-9]/', $novaSenha) || !preg_match('/[\W_]/', $novaSenha)) {
                throw new Exception('A senha deve conter ao menos 8 caracteres, letras maiúsculas, minúsculas, números e um caractere especial.');
            }

            $registro = $this->usuarioRepo->buscarCodigoValido($usuarioId, $codigo);
            if (!$registro) {
                throw new Exception('Código de verificação inválido ou expirado.');
            }

            $this->usuarioRepo->marcarCodigoComoUsado((int)$registro['codigo_id']);
            $this->usuarioRepo->atualizarSenha($usuarioId, password_hash($novaSenha, PASSWORD_BCRYPT));

            unset($_SESSION['redefinir_senha_usuario_id']);

            $this->json(200, [
                'status'       => 'sucesso',
                'mensagem'     => 'Senha alterada com sucesso!',
                'redirect_url' => URL_BASE . '/perfil/editar'
            ]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    public function telaTrocarEmail(): void
    {
        $usuario = $this->usuarioRepo->buscarPorId((int)$_SESSION['usuario_id']);
        $this->view('perfil/trocar_email', [
            'titulo'     => 'Trocar E-mail',
            'emailAtual' => $usuario['email'] ?? ''
        ]);
    }

    public function enviarCodigoTrocaEmail(): void
    {
        try {
            $usuarioId = (int)$_SESSION['usuario_id'];
            $novoEmail = trim($_POST['novo_email'] ?? '');

            if (empty($novoEmail) || !filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Informe um e-mail válido.');
            }

            $existente = $this->usuarioRepo->buscarPorEmail($novoEmail);
            if ($existente && (int)$existente->getUsuarioId() !== $usuarioId) {
                throw new Exception('Este e-mail já está em uso por outra conta.');
            }

            $codigo = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiraEm = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $this->usuarioRepo->salvarCodigoVerificacao($usuarioId, $codigo, $expiraEm);
            
            MailService::enviarCodigoVerificacao($novoEmail, 'Usuário', $codigo, 'trocar_email');

            $_SESSION['troca_email_pendente'] = [
                'usuario_id' => $usuarioId,
                'novo_email' => $novoEmail
            ];

            $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Código de verificação enviado para o NOVO e-mail.']);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    public function confirmarTrocaEmail(): void
    {
        try {
            $dados = $_SESSION['troca_email_pendente'] ?? null;
            $codigo = trim($_POST['codigo'] ?? '');

            if (!$dados) {
                throw new Exception('Sessão expirada. Tente a solicitação novamente.');
            }

            $registro = $this->usuarioRepo->buscarCodigoValido((int)$dados['usuario_id'], $codigo);
            if (!$registro) {
                throw new Exception('Código de verificação inválido ou expirado.');
            }

            $this->usuarioRepo->marcarCodigoComoUsado((int)$registro['codigo_id']);
            
            // Atualiza o e-mail no banco e na sessão
            $this->usuarioRepo->atualizarEmail((int)$dados['usuario_id'], $dados['novo_email']);

            $_SESSION['usuario_email'] = $dados['novo_email'];
            unset($_SESSION['troca_email_pendente']);

            $this->json(200, [
                'status'       => 'sucesso',
                'mensagem'     => 'E-mail alterado com sucesso!',
                'redirect_url' => URL_BASE . '/perfil/editar'
            ]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

   public function alternar(): void
    {
        $tipo = strtolower(trim($_POST['tipo'] ?? ''));
        $perfisAtivos = $_SESSION['perfis_ativos'] ?? [];

        // Garante que o tipo informado seja estritamente um dos 4 permitidos
        $perfisPermitidos = ['adotante', 'protetor', 'ong', 'administrador', 'admin'];
        if (!in_array($tipo, $perfisPermitidos, true)) {
            $this->redirecionarComMensagem('erro', 'Tipo de perfil inválido.', '/perfil');
            return;
        }

        $usuarioId = (int)$_SESSION['usuario_id'];
        $db = ConnectionFactory::getConnection();

        // Persiste o tipo_atual no banco
        $stmt = $db->prepare("UPDATE USUARIO SET tipo_atual = :tipo WHERE usuario_id = :id");
        $stmt->bindValue(':tipo', $tipo);
        $stmt->bindValue(':id', $usuarioId, \PDO::PARAM_INT);
        $stmt->execute();

        // Normaliza para os papéis oficiais
        $tipoSession = ($tipo === 'admin') ? 'administrador' : $tipo;

        // Busca a foto correspondente ao perfil exato que está sendo ativado
        $fotoPerfilAtiva = null;
        if ($tipoSession === 'adotante') {
            $adotanteRepo = new AdotanteRepository();
            $adotante = $adotanteRepo->buscarPorUsuarioId($usuarioId);
            $fotoPerfilAtiva = $adotante['foto_perfil'] ?? null;
        } elseif (in_array($tipoSession, ['ong', 'protetor'], true)) {
            $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
            if ($protetor) {
                $paginaRepo = new PaginaRepository();
                $pagina = $paginaRepo->buscarPorProtetorId((int)$protetor['protetor_id']);
                $fotoPerfilAtiva = $pagina['foto_perfil'] ?? null;
            }
        }

        // Atualiza a sessão completamente com os dados do perfil correto
        $_SESSION['tipo_perfil'] = $tipoSession;
        $_SESSION['foto_perfil'] = $fotoPerfilAtiva;
        $_SESSION['perfil_ativo'] = [
            'id'          => $usuarioId,
            'tipo'        => $tipoSession,
            'foto_perfil' => $fotoPerfilAtiva
        ];

        // Sincroniza status do protetor/ong se aplicável
        if (in_array($tipoSession, ['ong', 'protetor'], true)) {
            $protetor = $this->protetorRepo->buscarPorUsuarioId($usuarioId);
            $_SESSION['protetor_id'] = $protetor ? (int)$protetor['protetor_id'] : 0;
            $_SESSION['validado'] = $protetor ? (bool)$protetor['validado'] : false;
        } else {
            $_SESSION['validado'] = true;
        }

        $this->redirecionarComMensagem('sucesso', 'Perfil alternado para ' . ucfirst($tipoSession) . ' com sucesso!', '/perfil');
    }
}