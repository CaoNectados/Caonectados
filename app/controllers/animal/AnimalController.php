<?php

namespace app\controllers\animal;

use app\core\Controller;
use app\models\Animal;
use app\repositories\AnimalRepository;
use app\repositories\EspecieRepository;
use app\repositories\ProtetorRepository;
use app\services\AnimalService;
use app\database\ConnectionFactory;
use PDO;
use Exception;

class AnimalController extends Controller
{
    private AnimalService $service;
    private PDO $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->db = ConnectionFactory::getConnection();
        $repository = new AnimalRepository($this->db);
        $this->service = new AnimalService($repository);
    }

    public function index(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        try {
            $tipoPerfil = $_SESSION['tipo_perfil'] ?? '';
            $statusFiltro = $_GET['status'] ?? 'todos';

            $protetorId = $this->obterProtetorIdAutenticado();

            $animais = $this->service->listarComFiltros($tipoPerfil, $protetorId, $statusFiltro);

            $this->view('animal/index', [
                'titulo'  => 'Gerenciar Animais',
                'animais' => $animais
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao carregar animais: ' . $e->getMessage(), '/admin/dashboard');
        }
    }

    public function deleteView(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        try {
            $id = $this->getIdFromRequest();
            $animal = $this->carregarEValidarPropriedade($id);

            $_SESSION['animal'] = $animal;

            $this->view('animal/excluir', [
                'titulo' => 'Desativar Animal',
                'animal' => $animal
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    public function create(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        $especieRepo = new EspecieRepository($this->db);
        $especies = $especieRepo->buscarAtivas();

        $this->view('animal/cadastrar', ['titulo' => 'Cadastrar Animal', 'especies' => $especies]);
    }

    public function store(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $data = $_POST;
            $protetorId = $this->obterProtetorIdAutenticado();

            if ($protetorId <= 0 && ($_SESSION['tipo_perfil'] ?? '') !== 'administrador') {
                throw new Exception('Perfil de protetor não encontrado para este usuário.');
            }

            // Força a fonte de identidade vinda da sessão backend
            $data['protetor_id'] = $protetorId;

            $animal = $this->buildAnimalFromArray($data);

            $this->service->cadastrarAnimal($animal);

            $fotoEnviada = $_FILES['foto'] ?? ($_POST['foto_cortada'] ?? null);
            if (!empty($fotoEnviada)) {
                $this->service->salvarFoto($fotoEnviada, (int) $animal->getAnimalId());
            }

            $this->redirecionarComMensagem('sucesso', 'Animal cadastrado com sucesso!', '/animal');
        } catch (Exception $e) {
            $_SESSION['old'] = $_POST;
            $_SESSION['erros'] = [$e->getMessage()];
            $this->redirect('/animal/cadastrar');
        }
    }

    public function show(): void
    {
        // Perfil público do animal: qualquer usuário autenticado (inclusive adotantes
        // navegando pelo Feed) pode visualizar — só as ações de gestão (editar/excluir/
        // status) permanecem restritas ao protetor dono ou ao administrador.
        $this->autenticacaoRequired();
        try {
            $id = $this->getIdFromRequest();
            $animal = $this->service->buscarPorId($id);

            if ($animal === null) {
                $this->redirecionarComMensagem('aviso', 'Animal não encontrado.', '/animal');
                return;
            }

            $this->view('animal/detalhes', ['titulo' => 'Detalhes do Animal', 'animal' => $animal]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    public function edit(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        try {
            $id = $this->getIdFromRequest();
            $animal = $this->carregarEValidarPropriedade($id);

            $_SESSION['animal'] = $animal;

            $this->view('animal/editar', ['titulo' => 'Editar Animal', 'animal' => $animal]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    public function update(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $id = (int)($_POST['id'] ?? 0);
            $animalExistente = $this->carregarEValidarPropriedade($id);

            $data = $_POST;
            // Preserva o vínculo original do protetor do registro auditado
            $data['protetor_id'] = $animalExistente->getProtetorId();

            $animal = $this->buildAnimalFromArray($data);
            $animal->setAnimalId($id);
            $this->service->editarAnimal($animal);

            $fotoEnviada = $_FILES['foto'] ?? ($_POST['foto_cortada'] ?? null);
            if (!empty($fotoEnviada)) {
                $this->service->salvarFoto($fotoEnviada, $id);
            }

            unset($_SESSION['animal']);
            $this->redirecionarComMensagem('sucesso', 'Animal atualizado com sucesso!', '/animal');
        } catch (Exception $e) {
            $_SESSION['old'] = $_POST;
            $_SESSION['erros'] = [$e->getMessage()];
            $id = $_POST['id'] ?? 0;
            $this->redirect('/animal/editar?id=' . $id);
        }
    }

    public function status(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $id = $this->getIdFromRequest();
            $this->carregarEValidarPropriedade($id);

            $status = $_POST['status'] ?? '';

            $animal = new Animal();
            $animal->setAnimalId($id);
            $animal->setStatus($status);
            $this->service->atualizarStatus($animal);

            $this->redirecionarComMensagem('sucesso', 'Status atualizado com sucesso!', '/animal');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    public function reativar(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $id = $this->getIdFromRequest();
            $this->carregarEValidarPropriedade($id);

            $animal = new Animal();
            $animal->setAnimalId($id);
            $this->service->reativarAnimal($animal);

            $this->redirecionarComMensagem('sucesso', 'Animal reativado com sucesso!', '/animal');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    public function destroy(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
            $this->carregarEValidarPropriedade($id);

            $animal = new Animal();
            $animal->setAnimalId($id);
            $this->service->desativarAnimal($animal);

            $this->redirecionarComMensagem('sucesso', 'Animal desativado com sucesso!', '/animal');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    private function carregarEValidarPropriedade(int $animalId): Animal
    {
        $animal = $this->service->buscarPorId($animalId);

        if (!$animal) {
            throw new Exception('Animal não encontrado.');
        }

        $tipoPerfil = $_SESSION['tipo_perfil'] ?? '';
        if ($tipoPerfil === 'administrador') {
            return $animal;
        }

        $protetorId = $this->obterProtetorIdAutenticado();
        if ($animal->getProtetorId() !== $protetorId) {
            throw new Exception('Acesso negado: Você não tem permissão para manipular este animal.');
        }

        return $animal;
    }

    private function obterProtetorIdAutenticado(): int
    {
        if (isset($_SESSION['protetor_id']) && (int)$_SESSION['protetor_id'] > 0) {
            return (int)$_SESSION['protetor_id'];
        }

        $usuarioId = (int)($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId > 0) {
            $protetorRepo = new ProtetorRepository($this->db);
            $protetor = $protetorRepo->buscarPorUsuarioId($usuarioId);
            if ($protetor && isset($protetor['protetor_id'])) {
                $_SESSION['protetor_id'] = (int)$protetor['protetor_id'];
                return (int)$protetor['protetor_id'];
            }
        }

        return 0;
    }

    private function buildAnimalFromArray(?array $data): Animal
    {
        if (!is_array($data)) {
            throw new Exception('Os dados enviados são inválidos.');
        }

        $animal = new Animal();
        $animal->setProtetorId((int) ($data['protetor_id'] ?? 0));
        $animal->setRacaId((int) ($data['raca_id'] ?? 0));
        $animal->setNome((string) ($data['nome'] ?? ''));
        $dtNasc = trim((string) ($data['dt_nasc'] ?? ''));
        $animal->setDtNasc($dtNasc === '' ? null : $dtNasc);
        $animal->setSexo((string) ($data['sexo'] ?? ''));
        $animal->setPorte((string) ($data['porte'] ?? ''));
        $animal->setStatus((string) ($data['status'] ?? 'disponivel'));
        $animal->setDescricao((string) ($data['descricao'] ?? ''));
        $animal->setVacinado(!empty($data['vacinado']));
        $animal->setCastrado(!empty($data['castrado']));
        $animal->setComportamento($data['comportamento'] ?? null);
        $animal->setHistoricoSaude($data['historico_saude'] ?? null);

        return $animal;
    }

    private function getIdFromRequest(): int
    {
        $id = $_GET['id'] ?? $_POST['id'] ?? null;
        if (!is_numeric($id) || (int) $id <= 0) {
            throw new Exception('ID inválido.');
        }
        return (int) $id;
    }
}