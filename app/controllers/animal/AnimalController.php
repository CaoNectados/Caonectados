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

    /**
     * Lista os animais do protetor logado (ou todos, se admin), com filtro por status.
     * Usado pelas rotas GET /animal e GET /gerenciar-animais.
     */
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

    /**
     * Perfil público de um animal — qualquer usuário autenticado pode ver (inclusive
     * adotantes navegando pelo Feed); só as ações de gestão exigem posse. Usado pela rota
     * GET /animal/mostrar.
     */
    public function show(): void
    {
        $this->autenticacaoRequired();
        try {
            $id = $this->getIdFromRequest();
            $animal = $this->service->buscarPorId($id);

            if ($animal === null) {
                $this->redirecionarComMensagem('aviso', 'Animal não encontrado.', '/animal');
                return;
            }

            $protetorRepo = new ProtetorRepository($this->db);
            $protetor = $protetorRepo->buscarBasicoPorId($animal->getProtetorId());

            $this->view('animal/detalhes', [
                'titulo'   => 'Detalhes do Animal',
                'animal'   => $animal,
                'protetor' => $protetor,
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    /** Exibe o formulário de cadastro de animal. Usado pela rota GET /animal/cadastrar. */
    public function create(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        $especieRepo = new EspecieRepository($this->db);
        $especies = $especieRepo->buscarAtivas();

        $this->view('animal/cadastrar', ['titulo' => 'Cadastrar Animal', 'especies' => $especies]);
    }

    /**
     * Cadastra um animal novo (nunca já como 'adotado' — RN 08/13) e salva foto principal e
     * fotos adicionais, se enviadas. Usado pela rota POST /animal.
     */
    public function store(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $this->exigirContaNaoBloqueada();

            $data = $_POST;
            $protetorId = $this->obterProtetorIdAutenticado();

            if ($protetorId <= 0 && ($_SESSION['tipo_perfil'] ?? '') !== 'administrador') {
                throw new Exception('Perfil de protetor não encontrado para este usuário.');
            }

            if ((string) ($data['status'] ?? '') === 'adotado') {
                throw new Exception('Um animal não pode ser cadastrado já como adotado.');
            }

            $data['protetor_id'] = $protetorId;

            $animal = $this->buildAnimalFromArray($data);

            $this->service->cadastrarAnimal($animal);

            $fotoEnviada = $_FILES['foto'] ?? ($_POST['foto_cortada'] ?? null);
            if (!empty($fotoEnviada)) {
                $this->service->salvarFoto($fotoEnviada, (int) $animal->getAnimalId());
            }

            $fotosAdicionais = $this->normalizarFotosAdicionais();
            if (!empty($fotosAdicionais)) {
                $this->service->salvarFotosAdicionais($fotosAdicionais, (int) $animal->getAnimalId());
            }

            $this->redirecionarComMensagem('sucesso', 'Animal cadastrado com sucesso!', '/animal');
        } catch (Exception $e) {
            $_SESSION['old'] = $_POST;
            $_SESSION['erros'] = [$e->getMessage()];
            $this->redirect('/animal/cadastrar');
        }
    }

    /** Exibe o formulário de edição de um animal do próprio protetor. Usado pela rota GET /animal/editar. */
    public function edit(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);

        try {
            $id = $this->getIdFromRequest();
            $animal = $this->carregarEValidarPropriedade($id);

            $_SESSION['animal'] = $animal;

            $this->view('animal/editar', [
                'titulo'         => 'Editar Animal',
                'animal'         => $animal,
                'fotosAdicionais' => array_filter(
                    $this->service->listarFotos($id),
                    fn(array $foto) => (int) $foto['foto_principal'] === 0
                ),
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    /**
     * Salva as alterações de um animal. O campo status não pode ser usado pra entrar ou sair
     * de 'adotado' por aqui (RN 08/13 — ver aprovar()/registrarDevolucao() em
     * SolicitacaoAdocaoService). Usado pela rota POST /animal/editar.
     */
    public function update(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $this->exigirContaNaoBloqueada();

            $id = (int)($_POST['id'] ?? 0);
            $animalExistente = $this->carregarEValidarPropriedade($id);

            $data = $_POST;
            $data['protetor_id'] = $animalExistente->getProtetorId();

            $statusSolicitado = (string) ($data['status'] ?? $animalExistente->getStatus());
            $statusAtualEraAdotado = $animalExistente->getStatus() === 'adotado';
            if ($statusSolicitado === 'adotado' && !$statusAtualEraAdotado) {
                throw new Exception('O status "Adotado" só pode ser definido pela aprovação de uma solicitação de adoção.');
            }
            if ($statusAtualEraAdotado && $statusSolicitado !== 'adotado') {
                throw new Exception('Para marcar este animal como devolvido, use a ação "Registrar Devolução" na solicitação aprovada.');
            }

            $animal = $this->buildAnimalFromArray($data);
            $animal->setAnimalId($id);
            $this->service->editarAnimal($animal);

            $fotoEnviada = $_FILES['foto'] ?? ($_POST['foto_cortada'] ?? null);
            if (!empty($fotoEnviada)) {
                $this->service->salvarFoto($fotoEnviada, $id);
            }

            $fotosAdicionais = $this->normalizarFotosAdicionais();
            if (!empty($fotosAdicionais)) {
                $this->service->salvarFotosAdicionais($fotosAdicionais, $id);
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

    /** Exibe a confirmação de desativação de um animal. Usado pela rota GET /animal/excluir. */
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

    /** Desativa (soft delete) um animal do próprio protetor. Usado pela rota POST /animal/excluir. */
    public function destroy(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $this->exigirContaNaoBloqueada();

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

    /**
     * Altera o status de um animal do próprio protetor, exceto pra/de 'adotado' (RN 08/13 —
     * ver update()). Usado pela rota POST /animal/status.
     */
    public function status(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $this->exigirContaNaoBloqueada();

            $id = $this->getIdFromRequest();
            $animalExistente = $this->carregarEValidarPropriedade($id);

            $status = $_POST['status'] ?? '';

            $statusAtualEraAdotado = $animalExistente->getStatus() === 'adotado';
            if ($status === 'adotado' && !$statusAtualEraAdotado) {
                throw new Exception('O status "Adotado" só pode ser definido pela aprovação de uma solicitação de adoção.');
            }
            if ($statusAtualEraAdotado && $status !== 'adotado') {
                throw new Exception('Para marcar este animal como devolvido, use a ação "Registrar Devolução" na solicitação aprovada.');
            }

            $animal = new Animal();
            $animal->setAnimalId($id);
            $animal->setStatus($status);
            $this->service->atualizarStatus($animal);

            $this->redirecionarComMensagem('sucesso', 'Status atualizado com sucesso!', '/animal');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    /** Remove uma foto (principal ou adicional) de um animal. Usado pela galeria em animal/editar (AJAX). */
    public function excluirFoto(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $animalId = (int) ($_POST['animal_id'] ?? 0);
            $fotoId = (int) ($_POST['foto_id'] ?? 0);

            $this->carregarEValidarPropriedade($animalId);
            $this->service->removerFoto($fotoId, $animalId);

            $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Foto removida com sucesso!']);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    /** Promove uma foto adicional a foto principal. Usado pela galeria em animal/editar (AJAX). */
    public function definirFotoPrincipal(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $animalId = (int) ($_POST['animal_id'] ?? 0);
            $fotoId = (int) ($_POST['foto_id'] ?? 0);

            $this->carregarEValidarPropriedade($animalId);
            $this->service->definirFotoPrincipal($fotoId, $animalId);

            $this->json(200, ['status' => 'sucesso', 'mensagem' => 'Foto principal atualizada!']);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    /** Reativa um animal previamente desativado. Usado pela rota POST /animal/reativar. */
    public function reativar(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong', 'administrador']);
        try {
            $this->exigirContaNaoBloqueada();

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

    /**
     * Carrega o animal e valida posse (RN 04) — admin passa livre, protetor só acessa o
     * próprio. Usado por deleteView(), edit(), update(), status(), reativar() e destroy().
     */
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

    /**
     * Resolve o protetor_id do usuário logado, cacheando na sessão. Usado por index(),
     * store() e carregarEValidarPropriedade().
     */
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

    /** Monta um Animal a partir do $_POST bruto. Usado por store() e update(). */
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

    /**
     * Reorganiza $_FILES['fotos_adicionais'] do formato "invertido" do PHP (um array por
     * propriedade) pra uma lista de arquivos individuais. Usado por store() e update().
     */
    private function normalizarFotosAdicionais(): array
    {
        if (empty($_FILES['fotos_adicionais']) || !is_array($_FILES['fotos_adicionais']['name'] ?? null)) {
            return [];
        }

        $bruto = $_FILES['fotos_adicionais'];
        $arquivos = [];

        foreach ($bruto['name'] as $indice => $nome) {
            if (($bruto['error'][$indice] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $arquivos[] = [
                'name'     => $nome,
                'type'     => $bruto['type'][$indice] ?? '',
                'tmp_name' => $bruto['tmp_name'][$indice] ?? '',
                'error'    => $bruto['error'][$indice] ?? UPLOAD_ERR_NO_FILE,
                'size'     => $bruto['size'][$indice] ?? 0,
            ];
        }

        return $arquivos;
    }

    /** Lê e valida o parâmetro id da querystring/POST. Usado por deleteView(), show(), edit(), status() e reativar(). */
    private function getIdFromRequest(): int
    {
        $id = $_GET['id'] ?? $_POST['id'] ?? null;
        if (!is_numeric($id) || (int) $id <= 0) {
            throw new Exception('ID inválido.');
        }
        return (int) $id;
    }
}
