<?php

namespace app\controllers\animal;

use app\core\Controller;
use app\models\Animal;
use app\repositories\AnimalRepository;
use app\repositories\EspecieRepository;
use app\repositories\ProtetorRepository;
use app\repositories\RacaRepository;
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
        $this->validarCsrf();

        $this->db = ConnectionFactory::getConnection();
        $repository = new AnimalRepository($this->db);
        $this->service = new AnimalService($repository);
    }

    // Usado por: rotas GET /animal e GET /gerenciar-animais
    public function index(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);

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

    // Usado por: rota GET /animal/mostrar
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

            $this->view('animal/detalhes', ['titulo' => 'Detalhes do Animal', 'animal' => $animal, 'fotosGaleria' => (new AnimalRepository($this->db))->listarFotos($id)]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    // Usado por: rota GET /animal/cadastrar
    public function create(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);

        $especieRepo = new EspecieRepository($this->db);
        $especies = $especieRepo->buscarAtivas();

        $this->view('animal/cadastrar', ['titulo' => 'Cadastrar Animal', 'especies' => $especies]);
    }

    // Usado por: rota POST /animal
    public function store(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);
        try {
            $data = $_POST;
            $protetorId = $this->obterProtetorIdAutenticado();

            if ($protetorId <= 0) {
                throw new Exception('Perfil de protetor não encontrado para este usuário.');
            }

            // Força a fonte de identidade vinda da sessão backend
            $data['protetor_id'] = $protetorId;

            $this->service->validarSelecaoRaca((int)($data['raca_id'] ?? 0), isset($data['especie_id']) ? (int)$data['especie_id'] : null);
            $animal = $this->buildAnimalFromArray($data);

            $fotoEnviada = !empty($_POST['foto_cortada'])
                ? $_POST['foto_cortada'] : ($_FILES['foto'] ?? null);
            $this->persistirComFotos($animal, $fotoEnviada, $_POST['fotos_galeria'] ?? [], true);

            $this->redirecionarComMensagem('sucesso', 'Animal cadastrado com sucesso!', '/animal');
        } catch (Exception $e) {
            $_SESSION['old'] = $_POST;
            $_SESSION['erros'] = [$e->getMessage()];
            $this->redirect('/animal/cadastrar');
        }
    }

    // Usado por: rota GET /animal/editar
    public function edit(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);

        try {
            $id = $this->getIdFromRequest();
            $animal = $this->carregarEValidarPropriedade($id);

            $_SESSION['animal'] = $animal;

            $racaAtual = (new RacaRepository($this->db))->buscarPorId($animal->getRacaId());
            $racas = array_filter((new RacaRepository($this->db))->listarTodas(), fn($r) => $r->isAtivo() || $r->getId() === $animal->getRacaId());
            $this->view('animal/editar', [
                'titulo' => 'Editar Animal', 'animal' => $animal,
                'fotosGaleria' => (new AnimalRepository($this->db))->listarFotos($id),
                'especieAtual' => $racaAtual?->getEspecieId(),
                'especies' => (new EspecieRepository($this->db))->listarTodas(),
                'racasEdicao' => array_values(array_map(fn($r) => ['id'=>$r->getId(),'nome'=>$r->getNome(),'especie_id'=>$r->getEspecieId(),'ativo'=>$r->isAtivo()], $racas)),
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    // Usado por: rota POST /animal/editar
    public function update(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);
        try {
            $id = (int)($_POST['id'] ?? 0);
            $animalExistente = $this->carregarEValidarPropriedade($id);

            $data = $_POST;
            // Preserva o vínculo original do protetor do registro auditado
            $data['protetor_id'] = $animalExistente->getProtetorId();
            $data += ['comportamento' => $animalExistente->getComportamento(), 'historico_saude' => $animalExistente->getHistoricoSaude()];
            $this->service->validarSelecaoRaca((int)($data['raca_id'] ?? 0), isset($data['especie_id']) ? (int)$data['especie_id'] : null, $animalExistente->getRacaId());

            $animal = $this->buildAnimalFromArray($data);
            $animal->setAnimalId($id);
            $fotoEnviada = !empty($_POST['foto_cortada'])
                ? $_POST['foto_cortada'] : ($_FILES['foto'] ?? null);
            $this->persistirComFotos($animal, $fotoEnviada, $_POST['fotos_galeria'] ?? [], false);

            unset($_SESSION['animal']);
            $this->redirecionarComMensagem('sucesso', 'Animal atualizado com sucesso!', '/animal');
        } catch (Exception $e) {
            $_SESSION['old'] = $_POST;
            $_SESSION['erros'] = [$e->getMessage()];
            $id = $_POST['id'] ?? 0;
            $this->redirect('/animal/editar?id=' . $id);
        }
    }

    private function persistirComFotos(Animal $animal, $principal, $galeria, bool $novo): void
    {
        if (!is_array($galeria) || count($galeria) > 9) throw new Exception('Selecione até 9 fotos adicionais.');
        $repo = new AnimalRepository($this->db);
        $existentes = $novo ? [] : $repo->listarFotos((int)$animal->getAnimalId());
        if (count($existentes) + count($galeria) + (!empty($principal) ? 1 : 0) > 10) {
            throw new Exception('O animal pode ter até 10 fotos no total.');
        }
        $principalId = filter_var($_POST['foto_principal_id'] ?? 0, FILTER_VALIDATE_INT);
        if ($principalId === false || $principalId < 0 || ($principalId && ($novo || !in_array($principalId, array_column($existentes, 'foto_id'))))) {
            throw new Exception('Selecione uma foto principal deste animal.');
        }
        if ($principalId && !empty($principal)) throw new Exception('Selecione apenas uma foto principal.');
        $uploads = new \app\services\UploadService();
        $caminhos = [];
        try {
            $entradas = !empty($principal) ? [$principal] : [];
            foreach ($galeria as $foto) {
                if (!is_string($foto) || strlen($foto) > 1400000 || !str_starts_with($foto, 'data:image/')) {
                    throw new Exception('Foto adicional inválida ou maior que 1 MB após recorte.');
                }
                $entradas[] = $foto;
            }
            foreach ($entradas as $foto) {
                $caminho = $uploads->salvar($foto, 'animal');
                if (!$caminho) throw new Exception('Não foi possível salvar uma das fotos.');
                $caminhos[] = $caminho;
            }
            $this->db->beginTransaction();
            if ($novo) $this->service->cadastrarAnimal($animal);
            else $this->service->editarAnimal($animal);
            foreach ($caminhos as $indice => $caminho) {
                $repo->adicionarFoto((int)$animal->getAnimalId(), $caminho,
                    $indice === 0 && (!empty($principal) || !$existentes));
            }
            if ($principalId) $repo->definirFotoPrincipal((int)$animal->getAnimalId(), $principalId);
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            foreach ($caminhos as $caminho) $uploads->remover($caminho);
            throw new Exception($e->getMessage(), 0, $e);
        }
    }

    // Usado por: rota GET /animal/excluir
    public function deleteView(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);

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

    // Usado por: rota POST /animal/excluir
    public function destroy(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);
        try {
            $id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
            $this->carregarEValidarPropriedade($id);

            foreach (['nome', 'dt_nasc', 'sexo', 'porte', 'status', 'descricao', 'comportamento', 'historico_saude', 'raca_id', 'especie_id', 'vacinado', 'castrado'] as $campo) {
            if (isset($data[$campo]) && !is_scalar($data[$campo])) throw new Exception('Formato inválido para ' . $campo . '.');
        }
        $animal = new Animal();
            $animal->setAnimalId($id);
            $this->service->desativarAnimal($animal);

            $this->redirecionarComMensagem('sucesso', 'Animal desativado com sucesso!', '/animal');
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', $e->getMessage(), '/animal');
        }
    }

    // Usado por: rota POST /animal/status
    public function status(): void
    {
        $this->autenticacaoRequired(['protetor', 'ong']);
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

    // Usado por: rota POST /animal/reativar
    public function reativar(): void
    {
        // RF11 permite ao administrador reativar animais; demais perfis ficam limitados aos próprios.
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

    // Usado por: deleteView(), edit(), update(), status(), reativar() e destroy()
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

    // Usado por: index(), store() e carregarEValidarPropriedade()
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

    // Usado por: store() e update()
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
        $animal->setComportamento(($data['comportamento'] ?? '') === '' ? null : $data['comportamento']);
        $animal->setHistoricoSaude($data['historico_saude'] ?? null);

        return $animal;
    }

    // Usado por: deleteView(), show(), edit(), status() e reativar()
    private function getIdFromRequest(): int
    {
        $id = $_GET['id'] ?? $_POST['id'] ?? null;
        if (!is_numeric($id) || (int) $id <= 0) {
            throw new Exception('ID inválido.');
        }
        return (int) $id;
    }
}
