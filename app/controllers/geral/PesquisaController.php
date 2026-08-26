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

    /** Exibe a tela de pesquisa com um feed ocioso inicial (estilo TikTok), por perfil. Usado pela rota GET /pesquisar. */
    public function index(): void
    {
        $tipoPerfil = $_SESSION['tipo_perfil'] ?? 'usuario';

        try {
            $feedInicial = match ($tipoPerfil) {
                'adotante'          => $this->feedComoAdotante(),
                'protetor', 'ong'   => $this->feedComoProtetor(),
                'administrador'     => $this->feedComoAdmin(),
                default             => [],
            };
        } catch (Exception $e) {
            // Feed ocioso é só um "TikTok de sugestões" pra tela não abrir vazia — se falhar,
            // a busca digitada continua funcionando normalmente, então degrada pra lista vazia.
            $feedInicial = [];
        }

        $this->view('pesquisa/pesquisa', [
            'titulo'      => 'Pesquisa',
            'tipoPerfil'  => $tipoPerfil,
            'feedInicial' => $feedInicial,
        ]);
    }

    /** Endpoint AJAX de busca ao digitar, roteado por tipo de perfil. Usado pela rota GET /pesquisar/buscar. */
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

    /** Busca animais disponíveis (RN 17 aplicada) e ONGs por termo, para o Adotante. Usado por buscar(). */
    private function buscarComoAdotante(string $termo): array
    {
        $adotanteId = $this->obterAdotanteIdAutenticado();

        return $this->montarResultadoAdotante(
            $this->pesquisaRepo->buscarOngsPorTermo($termo),
            $this->pesquisaRepo->buscarAnimaisDisponiveisPorTermo($termo, $adotanteId)
        );
    }

    /** Monta o feed ocioso do Adotante (mesmo formato da busca, sem termo digitado). Usado por index(). */
    private function feedComoAdotante(): array
    {
        $adotanteId = $this->obterAdotanteIdAutenticado();

        return $this->montarResultadoAdotante(
            $this->pesquisaRepo->buscarOngsFeed(),
            $this->pesquisaRepo->buscarAnimaisDisponiveisFeed($adotanteId)
        );
    }

    /** Formata o resultado (ongs + animais) no formato comum usado por buscarComoAdotante() e feedComoAdotante(). */
    private function montarResultadoAdotante(array $ongs, array $animais): array
    {
        $urlBase = rtrim(URL_BASE, '/');

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

    /** Busca os próprios animais cadastrados por termo, para o Protetor/ONG. Usado por buscar(). */
    private function buscarComoProtetor(string $termo): array
    {
        $protetorId = $this->obterProtetorIdAutenticado();

        return $this->montarResultadoProtetor(
            $this->pesquisaRepo->buscarAnimaisDoProtetorPorTermo($termo, $protetorId)
        );
    }

    /** Monta o feed ocioso do Protetor/ONG: seus próprios animais mais recentes. Usado por index(). */
    private function feedComoProtetor(): array
    {
        $protetorId = $this->obterProtetorIdAutenticado();

        return $this->montarResultadoProtetor(
            $this->pesquisaRepo->buscarAnimaisDoProtetorFeed($protetorId)
        );
    }

    /** Formata o resultado (meus_animais) no formato comum usado por buscarComoProtetor() e feedComoProtetor(). */
    private function montarResultadoProtetor(array $animais): array
    {
        $urlBase = rtrim(URL_BASE, '/');
        $statusLabels = ['disponivel' => 'Disponível', 'em_analise' => 'Em Análise', 'adotado' => 'Adotado', 'desativado' => 'Desativado'];

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

    /** Busca usuários e ONGs/Protetores da plataforma inteira por termo, para o Admin. Usado por buscar(). */
    private function buscarComoAdmin(string $termo): array
    {
        return $this->montarResultadoAdmin(
            $this->pesquisaRepo->buscarUsuariosPorTermoAdmin($termo),
            $this->pesquisaRepo->buscarProtetoresPorTermoAdmin($termo)
        );
    }

    /** Monta o feed ocioso do Admin: usuários e ONGs/Protetores mais recentes. Usado por index(). */
    private function feedComoAdmin(): array
    {
        return $this->montarResultadoAdmin(
            $this->pesquisaRepo->buscarUsuariosFeedAdmin(),
            $this->pesquisaRepo->buscarProtetoresFeedAdmin()
        );
    }

    /** Formata o resultado (usuarios + protetores) no formato comum usado por buscarComoAdmin() e feedComoAdmin(). */
    private function montarResultadoAdmin(array $usuarios, array $protetores): array
    {
        $urlBase = rtrim(URL_BASE, '/');

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

    /** Monta a URL pública de um arquivo enviado via upload, ou null se vazio. */
    private function montarUrlUpload(?string $caminho, string $urlBase): ?string
    {
        if (empty($caminho)) {
            return null;
        }
        return $urlBase . '/' . ltrim($caminho, '/');
    }

    /** Resolve o adotante_id do usuário logado. Mesmo padrão de FeedController::obterAdotanteIdAutenticado(). */
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

    /** Resolve o protetor_id do usuário logado. Mesmo padrão de AnimalController::obterProtetorIdAutenticado(). */
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
