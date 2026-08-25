<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\repositories\PesquisaRepository;
use app\repositories\AdotanteRepository;
use app\repositories\ProtetorRepository;
use Exception;

/**
 * Pesquisa é diferente por tipo de perfil: Adotante busca animais disponíveis + ONGs;
 * Protetor/ONG busca só os próprios animais cadastrados; Admin busca usuários e
 * ONGs/Protetores da plataforma inteira. Sem UC/RF numerado específico — item de
 * navegação que já existia no menu (header.php) apontando pra uma rota inexistente.
 */
class PesquisaController extends Controller
{
    private PesquisaRepository $pesquisaRepo;

    public function __construct()
    {
        $this->autenticacaoRequired();
        $this->pesquisaRepo = new PesquisaRepository();
    }

    // Usado por: rota GET /pesquisar
    public function index(): void
    {
        $this->view('pesquisa/pesquisa', [
            'titulo'     => 'Pesquisa',
            'tipoPerfil' => $_SESSION['tipo_perfil'] ?? 'usuario',
        ]);
    }

    // Usado por: rota GET /pesquisar/buscar (AJAX, busca ao digitar)
    public function buscar(): void
    {
        try {
            $termo = trim($_GET['q'] ?? '');
            if (mb_strlen($termo) < 2) {
                $this->json(200, ['status' => 'sucesso', 'resultados' => []]);
                return;
            }

            $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';

            $resultados = match ($tipoPerfil) {
                'adotante'              => $this->buscarComoAdotante($termo),
                'protetor', 'ong'       => $this->buscarComoProtetor($termo),
                'administrador'         => $this->buscarComoAdmin($termo),
                default                 => [],
            };

            $this->json(200, ['status' => 'sucesso', 'resultados' => $resultados]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    // Usado por: buscar() — Adotante: animais disponíveis (RN 17 aplicada) + ONGs
    private function buscarComoAdotante(string $termo): array
    {
        $adotanteId = $this->obterAdotanteIdAutenticado();
        $urlBase = rtrim(URL_BASE, '/');

        $animais = $this->pesquisaRepo->buscarAnimaisDisponiveisPorTermo($termo, $adotanteId);
        $ongs = $this->pesquisaRepo->buscarOngsPorTermo($termo);

        return [
            'ongs' => array_map(function (array $p) use ($urlBase) {
                return [
                    'protetor_id'   => (int) $p['protetor_id'],
                    'nome_fantasia' => $p['nome_fantasia'],
                    'foto_perfil'   => $this->montarUrlUpload($p['foto_perfil'] ?? null, $urlBase),
                    'url'           => $urlBase . '/pagina?id=' . (int) $p['protetor_id'],
                ];
            }, $ongs),
            'animais' => array_map(function (array $a) use ($urlBase) {
                return [
                    'animal_id'     => (int) $a['animal_id'],
                    'nome'          => $a['nome'],
                    'raca_nome'     => $a['raca_nome'],
                    'foto'          => $this->montarUrlUpload($a['foto_principal'] ?? null, $urlBase),
                    'url'           => $urlBase . '/animal/mostrar?id=' . (int) $a['animal_id'],
                ];
            }, $animais),
        ];
    }

    // Usado por: buscar() — Protetor/ONG: só os próprios animais cadastrados
    private function buscarComoProtetor(string $termo): array
    {
        $protetorId = $this->obterProtetorIdAutenticado();
        $urlBase = rtrim(URL_BASE, '/');

        $statusLabels = ['disponivel' => 'Disponível', 'em_analise' => 'Em Análise', 'adotado' => 'Adotado', 'desativado' => 'Desativado'];
        $animais = $this->pesquisaRepo->buscarAnimaisDoProtetorPorTermo($termo, $protetorId);

        return [
            'meus_animais' => array_map(function (array $a) use ($urlBase, $statusLabels) {
                return [
                    'animal_id'  => (int) $a['animal_id'],
                    'nome'       => $a['nome'],
                    'raca_nome'  => $a['raca_nome'],
                    'status'     => $statusLabels[$a['status']] ?? $a['status'],
                    'foto'       => $this->montarUrlUpload($a['foto_principal'] ?? null, $urlBase),
                    'url'        => $urlBase . '/animal/editar?id=' . (int) $a['animal_id'],
                ];
            }, $animais),
        ];
    }

    // Usado por: buscar() — Admin: usuários e ONGs/Protetores da plataforma inteira
    private function buscarComoAdmin(string $termo): array
    {
        $urlBase = rtrim(URL_BASE, '/');

        $usuarios = $this->pesquisaRepo->buscarUsuariosPorTermoAdmin($termo);
        $protetores = $this->pesquisaRepo->buscarProtetoresPorTermoAdmin($termo);

        return [
            'usuarios' => array_map(function (array $u) use ($urlBase) {
                return [
                    'usuario_id' => (int) $u['usuario_id'],
                    'nome'       => $u['nome'] ?? '(sem nome)',
                    'email'      => $u['email'],
                    'tipo_atual' => $u['tipo_atual'],
                    'url'        => $urlBase . '/admin/usuarios/detalhes?id=' . (int) $u['usuario_id'],
                ];
            }, $usuarios),
            'protetores' => array_map(function (array $p) use ($urlBase) {
                return [
                    'protetor_id'   => (int) $p['protetor_id'],
                    'nome_fantasia' => $p['nome_fantasia'],
                    'email'         => $p['email'],
                    'validado'      => (bool) $p['validado'],
                    'url'           => $urlBase . '/admin/solicitacoes/detalhes?id=' . (int) $p['protetor_id'],
                ];
            }, $protetores),
        ];
    }

    // Usado por: buscarComoAdotante(), buscarComoProtetor(), buscarComoAdmin() (uso interno)
    private function montarUrlUpload(?string $caminho, string $urlBase): ?string
    {
        if (empty($caminho)) {
            return null;
        }
        return $urlBase . '/' . ltrim($caminho, '/');
    }

    // Usado por: buscarComoAdotante() — mesmo padrão de FeedController::obterAdotanteIdAutenticado()
    private function obterAdotanteIdAutenticado(): int
    {
        if (isset($_SESSION['adotante_id']) && (int) $_SESSION['adotante_id'] > 0) {
            return (int) $_SESSION['adotante_id'];
        }

        $adotante = (new AdotanteRepository())->buscarPorUsuarioId((int) ($_SESSION['usuario_id'] ?? 0));
        if (!$adotante) {
            throw new Exception('Perfil de adotante não encontrado para este usuário.');
        }

        $_SESSION['adotante_id'] = (int) $adotante['adotante_id'];
        return (int) $adotante['adotante_id'];
    }

    // Usado por: buscarComoProtetor() — mesmo padrão de AnimalController::obterProtetorIdAutenticado()
    private function obterProtetorIdAutenticado(): int
    {
        if (isset($_SESSION['protetor_id']) && (int) $_SESSION['protetor_id'] > 0) {
            return (int) $_SESSION['protetor_id'];
        }

        $protetor = (new ProtetorRepository())->buscarPorUsuarioId((int) ($_SESSION['usuario_id'] ?? 0));
        if (!$protetor) {
            throw new Exception('Perfil de protetor não encontrado para este usuário.');
        }

        $_SESSION['protetor_id'] = (int) $protetor['protetor_id'];
        return (int) $protetor['protetor_id'];
    }
}
