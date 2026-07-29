<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\database\ConnectionFactory;
use app\repositories\UsuarioRepository;
use app\repositories\RegiaoRepository;
use app\services\ValidationService;
use Exception;
use PDO;

class PerfilController extends Controller
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/login');
            exit;
        }
    }

    private function responderJson(string $status, string $mensagem, ?string $redirectUrl = null)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status'       => $status,
            'mensagem'     => $mensagem,
            'redirect_url' => $redirectUrl
        ]);
        exit;
    }

    public function index()
    {
        $pdo = ConnectionFactory::getConnection();
        $usuarioId = $_SESSION['usuario_id'];
        $tipoPerfil = $_SESSION['tipo_perfil'] ?? '';

        $fotoPerfil = null;

        if ($tipoPerfil === 'adotante' || $tipoPerfil === 'usuario') {
            $stmt = $pdo->prepare("SELECT foto_perfil FROM TUTOR WHERE usuario_id = ?");
            $stmt->execute([$usuarioId]);
            $fotoPerfil = $stmt->fetchColumn();
        } elseif ($tipoPerfil === 'ong' || $tipoPerfil === 'protetor') {
            $stmt = $pdo->prepare("
                SELECT p.foto_perfil 
                FROM PAGINA p 
                INNER JOIN PROTETOR pr ON p.protetor_id = pr.protetor_id 
                WHERE pr.usuario_id = ?
            ");
            $stmt->execute([$usuarioId]);
            $fotoPerfil = $stmt->fetchColumn();
        }

        $this->view('perfil/perfil', [
            'titulo' => 'Perfil',
            'fotoPerfil' => $fotoPerfil
        ]);
    }

    public function perfil()
    {
        $this->index();
    }

    public function editar()
    {
        $pdo = ConnectionFactory::getConnection();
        $usuarioRepo = new UsuarioRepository();
        $regiaoRepo = new RegiaoRepository();

        $usuarioId = $_SESSION['usuario_id'];
        $tipoPerfil = $_SESSION['tipo_perfil'];

        $usuario = $usuarioRepo->buscarPorId($usuarioId);
        $regioes = $regiaoRepo->buscarTodas();

        $dadosEspecificos = [];

        if ($tipoPerfil === 'adotante' || $tipoPerfil === 'usuario') {
            $stmt = $pdo->prepare("SELECT * FROM TUTOR WHERE usuario_id = ?");
            $stmt->execute([$usuarioId]);
            $dadosEspecificos = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            $detalhes = json_decode($dadosEspecificos['detalhes'] ?? '{}', true);
            $dadosEspecificos['possui_criancas'] = $detalhes['possui_criancas'] ?? '';
            $dadosEspecificos['possui_outros_pets'] = $detalhes['possui_outros_pets'] ?? '';

        } elseif ($tipoPerfil === 'ong' || $tipoPerfil === 'protetor') {
            $stmt = $pdo->prepare("SELECT * FROM PROTETOR WHERE usuario_id = ?");
            $stmt->execute([$usuarioId]);
            $protetor = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            
            if ($protetor) {
                $dadosEspecificos = $protetor;
                
                $stmtPag = $pdo->prepare("SELECT * FROM PAGINA WHERE protetor_id = ?");
                $stmtPag->execute([$protetor['protetor_id']]);
                $pagina = $stmtPag->fetch(PDO::FETCH_ASSOC);
                $dadosEspecificos['descricao'] = $pagina['descricao'] ?? '';
                $dadosEspecificos['chave_pix'] = $pagina['chave_pix'] ?? '';
                $dadosEspecificos['foto_perfil'] = $pagina['foto_perfil'] ?? '';

                $stmtRedes = $pdo->prepare("SELECT tipo_rede, link_rede FROM REDE WHERE protetor_id = ?");
                $stmtRedes->execute([$protetor['protetor_id']]);
                while ($rede = $stmtRedes->fetch(PDO::FETCH_ASSOC)) {
                    $dadosEspecificos[$rede['tipo_rede']] = $rede['link_rede'];
                }
            }
        }

        $this->view('perfil/editar', [
            'titulo'     => 'Editar Perfil',
            'usuario'    => $usuario,
            'regioes'    => $regioes,
            'especifico' => $dadosEspecificos,
            'tipoPerfil' => $tipoPerfil
        ]);
    }

    public function atualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $pdo = ConnectionFactory::getConnection();
        $usuarioId = $_SESSION['usuario_id'];
        $tipoPerfil = $_SESSION['tipo_perfil'];

        // Captura os dados globais de usuário
        $nome = trim($_POST['nome'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $novaSenha = $_POST['senha'] ?? '';
        $regiao_id = !empty($_POST['regiao_id']) ? $_POST['regiao_id'] : null;
        
        // Dados de endereço que pertencem à tabela USUARIO (Valem para todos)
        $numMorada = trim($_POST['num_morada'] ?? 'S/N');
        $obsCasa = trim($_POST['obs_casa'] ?? null);

        if (mb_strlen($nome) < 3) {
            $this->responderJson('erro', 'O nome deve ter pelo menos 3 caracteres.');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->responderJson('erro', 'Informe um e-mail válido.');
        }

        try {
            $pdo->beginTransaction();

            // 1. ATUALIZA TABELA USUÁRIO (Completando com num_morada e obs_casa para TODOS)
            $sqlUsuario = "UPDATE USUARIO SET nome = ?, telefone = ?, email = ?, regiao_id = ?, num_morada = ?, obs_casa = ?";
            $params = [$nome, $telefone, $email, $regiao_id, $numMorada, $obsCasa];

            if (!empty($novaSenha)) {
                if (strlen($novaSenha) < 8) {
                    throw new Exception("A nova senha deve ter pelo menos 8 caracteres.");
                }
                $sqlUsuario .= ", senha = ?";
                $params[] = password_hash($novaSenha, PASSWORD_DEFAULT);
            }
            $sqlUsuario .= " WHERE usuario_id = ?";
            $params[] = $usuarioId;

            $stmt = $pdo->prepare($sqlUsuario);
            $stmt->execute($params);

            // Tratamento da foto via Base64 do Cropper.js
            $caminhoFoto = $_POST['foto_atual'] ?? null;
            if (!empty($_POST['foto_cortada'])) {
                $base64Data = $_POST['foto_cortada'];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                    $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                    $binario = base64_decode($base64Data);
                    if ($binario !== false) {
                        $nomeArquivo = 'perfil_' . $usuarioId . '_' . time() . '.png';
                        $pastaDestino = __DIR__ . '/../../../public/assets/uploads/perfil/';
                        if (!is_dir($pastaDestino)) { mkdir($pastaDestino, 0755, true); }
                        file_put_contents($pastaDestino . $nomeArquivo, $binario);
                        $caminhoFoto = 'assets/uploads/perfil/' . $nomeArquivo;
                    }
                }
            }

            if ($tipoPerfil === 'tutor') {
               $tipoMorada = $_POST['tipo_morada'] ?? 'casa';
                $tamanhoInterno = $_POST['tamanho_interno_morada'] ?? 'medio';
                $numMorada = trim($_POST['num_morada'] ?? 'S/N');
                $obsCasa = trim($_POST['obs_casa'] ?? null);
                
                $detalhes = json_encode([
                    'possui_criancas' => $_POST['possui_criancas'] ?? 'nao',
                    'possui_outros_pets' => $_POST['possui_outros_pets'] ?? 'nao'
                ]);

                $stmtTutor = $pdo->prepare("UPDATE TUTOR SET tipo_morada = ?, tamanho_interno_morada = ?, num_morada = ?, obs_casa = ?, detalhes = ?, foto_perfil = ? WHERE usuario_id = ?");
                $stmtTutor->execute([$tipoMorada, $tamanhoInterno, $numMorada, $obsCasa, $detalhes, $caminhoFoto, $usuarioId]);

            } elseif ($tipoPerfil === 'ong' || $tipoPerfil === 'protetor') {
                
                $nomeFantasia = trim($_POST['nome_fantasia'] ?? '');
                if (mb_strlen($nomeFantasia) < 3) {
                    throw new Exception("O nome da instituição/fantasia deve ter pelo menos 3 caracteres.");
                }

                $cnpjCpfNovo = preg_replace('/[^0-9]/', '', $_POST['codigo_documento'] ?? '');
                $cnpjCpfAntigo = preg_replace('/[^0-9]/', '', $_POST['codigo_documento_atual'] ?? '');
                
                // Reutilização rigorosa das validações do ValidationService do Onboarding
                if ($tipoPerfil === 'ong') {
                    if (strlen($cnpjCpfNovo) !== 14 || !ValidationService::validarCnpj($cnpjCpfNovo)) {
                        throw new Exception("O CNPJ informado é inválido.");
                    }
                } else {
                    if (strlen($cnpjCpfNovo) !== 11 || !ValidationService::validarCpf($cnpjCpfNovo)) {
                        throw new Exception("O CPF informado é inválido.");
                    }
                }

                if (!empty($_POST['instagram']) && !ValidationService::validarLinkRedeSocial($_POST['instagram'], 'instagram')) {
                    throw new Exception("O link do Instagram informado é inválido.");
                }
                if (!empty($_POST['facebook']) && !ValidationService::validarLinkRedeSocial($_POST['facebook'], 'facebook')) {
                    throw new Exception("O link do Facebook informado é inválido.");
                }
                if (!empty($_POST['chave_pix']) && !ValidationService::validarChavePix($_POST['chave_pix'])) {
                    throw new Exception("A Chave PIX informada não é válida.");
                }

                $comprovanteAtual = $_POST['comprovante_atual'] ?? null;
                $documentoAlterado = ($cnpjCpfNovo !== $cnpjCpfAntigo);

                if (isset($_FILES['comprovante_documento']) && $_FILES['comprovante_documento']['error'] === UPLOAD_ERR_OK) {
                    if (!ValidationService::validarTamanhoArquivo($_FILES['comprovante_documento'], 5)) {
                        throw new Exception("O comprovante excede o tamanho máximo de 5MB.");
                    }
                    $ext = pathinfo($_FILES['comprovante_documento']['name'], PATHINFO_EXTENSION);
                    $nomeDoc = 'doc_' . $usuarioId . '_' . time() . '.' . $ext;
                    $pastaDoc = __DIR__ . '/../../../public/assets/uploads/documentos/';
                    if (!is_dir($pastaDoc)) { mkdir($pastaDoc, 0755, true); }
                    move_uploaded_file($_FILES['comprovante_documento']['tmp_name'], $pastaDoc . $nomeDoc);
                    $comprovanteAtual = 'assets/uploads/documentos/' . $nomeDoc;
                    $documentoAlterado = true;
                }

                // Se alterou o documento, inativa a conta temporariamente para revalidação do Admin
                if ($documentoAlterado) {
                    $pdo->prepare("UPDATE USUARIO SET status_conta = 'pendente' WHERE usuario_id = ?")->execute([$usuarioId]);
                }

                $stmtProt = $pdo->prepare("UPDATE PROTETOR SET nome_fantasia = ?, codigo_documento = ?, comprovante_documento = ? WHERE usuario_id = ?");
                $stmtProt->execute([$nomeFantasia, $cnpjCpfNovo, $comprovanteAtual, $usuarioId]);

                $protetorId = $pdo->prepare("SELECT protetor_id FROM PROTETOR WHERE usuario_id = ?");
                $protetorId->execute([$usuarioId]);
                $id = $protetorId->fetchColumn();

                if ($id) {
                    $descricao = $_POST['descricao'] ?? '';
                    if (mb_strlen($descricao) < 15) {
                        throw new Exception("A descrição da causa deve ter no mínimo 15 caracteres.");
                    }

                    $chavePix = $_POST['chave_pix'] ?? '';
                    $stmtPag = $pdo->prepare("UPDATE PAGINA SET descricao = ?, chave_pix = ?, foto_perfil = ? WHERE protetor_id = ?");
                    $stmtPag->execute([$descricao, $chavePix, $caminhoFoto, $id]);

                    $pdo->prepare("DELETE FROM REDE WHERE protetor_id = ?")->execute([$id]);
                    if (!empty($_POST['instagram'])) {
                        $pdo->prepare("INSERT INTO REDE (protetor_id, link_rede, tipo_rede) VALUES (?, ?, 'instagram')")->execute([$id, $_POST['instagram']]);
                    }
                    if (!empty($_POST['facebook'])) {
                        $pdo->prepare("INSERT INTO REDE (protetor_id, link_rede, tipo_rede) VALUES (?, ?, 'facebook')")->execute([$id, $_POST['facebook']]);
                    }
                }
            }

            $pdo->commit();
            $_SESSION['usuario_nome'] = $nome;

            $mensagemFinal = isset($documentoAlterado) && $documentoAlterado 
                ? 'Perfil salvo! Como você alterou documentos, sua conta passará por uma nova aprovação.' 
                : 'Perfil atualizado com sucesso!';

            $this->responderJson('sucesso', $mensagemFinal, URL_BASE . '/perfil');

        } catch (Exception $e) {
            $pdo->rollBack();
            $this->responderJson('erro', $e->getMessage());
        }
    }

    public function editarFoto()
    {
        $pdo = ConnectionFactory::getConnection();
        $usuarioId = $_SESSION['usuario_id'];
        $tipoPerfil = $_SESSION['tipo_perfil'] ?? '';
        $fotoAtual = null;

        if ($tipoPerfil === 'tutor' ) {
            $stmt = $pdo->prepare("SELECT foto_perfil FROM TUTOR WHERE usuario_id = ?");
            $stmt->execute([$usuarioId]);
            $fotoAtual = $stmt->fetchColumn();
        } elseif ($tipoPerfil === 'ong' || $tipoPerfil === 'protetor') {
            $stmt = $pdo->prepare("
                SELECT p.foto_perfil 
                FROM PAGINA p 
                INNER JOIN PROTETOR pr ON p.protetor_id = pr.protetor_id 
                WHERE pr.usuario_id = ?
            ");
            $stmt->execute([$usuarioId]);
            $fotoAtual = $stmt->fetchColumn();
        }

        $this->view('perfil/editar_foto', [
            'titulo'    => 'Alterar Foto de Perfil',
            'fotoAtual' => $fotoAtual
        ]);
    }

    public function atualizarFoto()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $pdo = ConnectionFactory::getConnection();
        $usuarioId = $_SESSION['usuario_id'];
        $tipoPerfil = $_SESSION['tipo_perfil'];

        $base64Data = $_POST['foto_cortada'] ?? '';
        if (empty($base64Data)) {
            $this->responderJson('erro', 'Nenhuma imagem foi recortada.');
        }

        try {
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $binario = base64_decode($base64Data);
                
                if ($binario !== false) {
                    $nomeArquivo = 'perfil_' . $usuarioId . '_' . time() . '.png';
                    $pastaDestino = __DIR__ . '/../../../public/assets/uploads/perfil/';
                    if (!is_dir($pastaDestino)) { mkdir($pastaDestino, 0755, true); }
                    
                    file_put_contents($pastaDestino . $nomeArquivo, $binario);
                    $caminhoFoto = 'assets/uploads/perfil/' . $nomeArquivo;

                    if ($tipoPerfil === 'tutor') {
                        $stmt = $pdo->prepare("UPDATE TUTOR SET foto_perfil = ? WHERE usuario_id = ?");
                        $stmt->execute([$caminhoFoto, $usuarioId]);
                    } elseif ($tipoPerfil === 'ong' || $tipoPerfil === 'protetor') {
                        $stmt = $pdo->prepare("SELECT protetor_id FROM PROTETOR WHERE usuario_id = ?");
                        $stmt->execute([$usuarioId]);
                        $protetorId = $stmt->fetchColumn();

                        if ($protetorId) {
                            $stmtPag = $pdo->prepare("UPDATE PAGINA SET foto_perfil = ? WHERE protetor_id = ?");
                            $stmtPag->execute([$caminhoFoto, $protetorId]);
                        }
                    }

                    $this->responderJson('sucesso', 'Foto de perfil atualizada com sucesso!', URL_BASE . '/perfil');
                }
            }
            throw new Exception("Formato de imagem inválido.");
        } catch (Exception $e) {
            $this->responderJson('erro', 'Erro ao salvar a foto: ' . $e->getMessage());
        }
    }
}