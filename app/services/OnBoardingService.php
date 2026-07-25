<?php

namespace app\services;

use app\database\ConnectionFactory;
use app\models\Usuario;
use app\models\Tutor;
use app\models\Protetor;
use app\repositories\UsuarioRepository;
use app\repositories\TutorRepository;
use app\repositories\ProtetorRepository;
use app\repositories\RegiaoRepository;

use Exception;

class OnboardingService
{
    private UsuarioRepository $usuarioRepo;
    private TutorRepository $tutorRepo;
    private ProtetorRepository $protetorRepo;
    private RegiaoRepository $regiaoRepository;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
        $this->tutorRepo = new TutorRepository();
        $this->protetorRepo = new ProtetorRepository();
        $this->regiaoRepository = new RegiaoRepository();
    }

    public function processarAdotante(array $dados, int $usuarioId): void
    {
        $pdo = ConnectionFactory::getConnection();
        
        try {
            $pdo->beginTransaction();

            $usuario = new Usuario();
            $usuario->setUsuarioId($usuarioId);
            $usuario->setNome($dados['nome']);
            $usuario->setRegiaoId($dados['regiao_id']);
            $usuario->setTipoAtual('ADOTANTE');

            $this->usuarioRepo->atualizarOnboarding($usuario, $pdo);

            $tutor = new Tutor();
            $tutor->setUsuarioId($usuarioId);
            $tutor->setTipoMoradia($dados['tipo_moradia']);
            $tutor->setTamanhoInternoMoradia($dados['tamanho_interno']);
            $tutor->setTamanhoExternoMoradia($dados['tamanho_externo']);
            
            $detalhes = json_encode([
                'especie' => $dados['especie'] ?? [],
                'porte' => $dados['porte'] ?? [],
                'sexo' => $dados['sexo'] ?? []
            ]);
            $tutor->setDetalhes($detalhes);

            $this->tutorRepo->salvar($tutor, $pdo);

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function processarOng(array $dados, int $usuarioId): void
    {
        $pdo = ConnectionFactory::getConnection();
        
        try {
            $pdo->beginTransaction();

            $usuario = new Usuario();
            $usuario->setUsuarioId($usuarioId);
            $usuario->setNome($dados['nome_fantasia']);
            $usuario->setRegiaoId($dados['regiao_id']);
            $usuario->setTipoAtual($dados['tipo_perfil']); // ONG ou PROTETOR

            $this->usuarioRepo->atualizarOnboarding($usuario, $pdo);

            $protetor = new Protetor();
            $protetor->setUsuarioId($usuarioId);
            $protetor->setCodigoDocumento($dados['cnpj_cpf']);
            $protetor->setTipoDocumento(strlen($dados['cnpj_cpf']) > 14 ? 'CNPJ' : 'CPF');
            $protetor->setNomeFantasia($dados['nome_fantasia']);
            $protetor->setValidado(false);

            $protetorId = $this->protetorRepo->salvar($protetor, $pdo);

            if (!empty($dados['chave_pix'])) {
                $this->protetorRepo->salvarPagina($protetorId, $dados['chave_pix'], $pdo);
            }

            if (!empty($dados['instagram'])) {
                $this->protetorRepo->salvarRedeSocial($protetorId, $dados['instagram'], 'INSTAGRAM', $pdo);
            }
            if (!empty($dados['facebook'])) {
                $this->protetorRepo->salvarRedeSocial($protetorId, $dados['facebook'], 'FACEBOOK', $pdo);
            }

            // Lógica de upload de arquivos (foto e comprovante) entraria aqui usando move_uploaded_file

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }


    public function listarRegioes(): array
    {
        return $this->regiaoRepository->listar();
    }
    
}