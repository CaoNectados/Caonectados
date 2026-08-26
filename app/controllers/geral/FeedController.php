<?php

namespace app\controllers\geral;

use app\core\Controller;
use app\repositories\FeedRepository;
use app\repositories\AdotanteRepository;
use app\repositories\EspecieRepository;
use app\repositories\RegiaoRepository;
use app\repositories\ProtetorRepository;
use Exception;

/**
 * RF 10 (UC 14 / UC 14.1) — feed/catálogo de animais disponíveis para o Adotante, com
 * embaralhamento estável por sessão e scroll infinito.
 */
class FeedController extends Controller
{
    private FeedRepository $feedRepo;
    private AdotanteRepository $adotanteRepo;

    private const TAMANHO_PAGINA = 6;

    public function __construct()
    {
        // RF 10 tem foco no perfil Adotante (RN 17 e o score de preferências dependem de
        // adotante_id). Protetor/ONG e admin navegam pelo catálogo através de outras telas.
        $this->autenticacaoRequired(['adotante']);
        $this->feedRepo = new FeedRepository();
        $this->adotanteRepo = new AdotanteRepository();
    }

    /** Renderiza a primeira página do feed, com um novo seed de embaralhamento. Usado pela rota GET /feed (UC 14). */
    public function index(): void
    {
        try {
            // Novo embaralhamento a cada carregamento completo da página — as chamadas de
            // "carregar mais" durante o scroll reaproveitam esse mesmo seed (ver
            // carregarMais()), então a paginação não repete/pula animais no meio do scroll.
            $_SESSION['feed_seed'] = random_int(1, 999999);

            $adotanteId = $this->obterAdotanteIdAutenticado();
            $filtros = $this->filtrosDaRequisicao();
            $preferencias = $this->obterPreferenciasAdotante($adotanteId);

            $animais = $this->feedRepo->buscarFeed($adotanteId, $preferencias, $filtros, $_SESSION['feed_seed'], 0, self::TAMANHO_PAGINA);
            $animais = $this->anexarFotos($animais);

            $this->view('feed/feed', [
                'titulo'          => 'Feed de Adoção',
                'animais'         => $animais,
                'temMais'         => count($animais) === self::TAMANHO_PAGINA,
                'proximoOffset'   => self::TAMANHO_PAGINA,
                'filtrosAtuais'   => $filtros,
                'especies'        => (new EspecieRepository())->buscarAtivas(),
                'regioes'         => (new RegiaoRepository())->buscarTodas(),
                'protetores'      => (new ProtetorRepository())->listarValidados(),
            ]);
        } catch (Exception $e) {
            $this->redirecionarComMensagem('erro', 'Erro ao carregar o feed de adoção.', '/perfil', $e->getMessage());
        }
    }

    /** Endpoint AJAX de scroll infinito, reaproveitando o seed da carga inicial. Usado pela rota GET /feed/carregar-mais. */
    public function carregarMais(): void
    {
        try {
            $adotanteId = $this->obterAdotanteIdAutenticado();
            $filtros = $this->filtrosDaRequisicao();
            $preferencias = $this->obterPreferenciasAdotante($adotanteId);

            $seed = (int) ($_SESSION['feed_seed'] ?? 0);
            if ($seed <= 0) {
                // Chegou aqui sem nunca ter carregado /feed nesta sessão (ex: aba antiga) —
                // gera um seed novo em vez de quebrar a requisição.
                $seed = random_int(1, 999999);
                $_SESSION['feed_seed'] = $seed;
            }

            $offset = max(0, (int) ($_GET['offset'] ?? 0));

            $animais = $this->feedRepo->buscarFeed($adotanteId, $preferencias, $filtros, $seed, $offset, self::TAMANHO_PAGINA);
            $animais = $this->anexarFotos($animais);

            $this->json(200, [
                'status'        => 'sucesso',
                'animais'       => array_map(fn(array $a) => $this->formatarAnimalParaJson($a), $animais),
                'temMais'       => count($animais) === self::TAMANHO_PAGINA,
                'proximoOffset' => $offset + self::TAMANHO_PAGINA,
            ]);
        } catch (Exception $e) {
            $this->json(400, ['status' => 'erro', 'mensagem' => $e->getMessage()]);
        }
    }

    /** Resolve o adotante_id do usuário logado, cacheando na sessão. Usado por index() e carregarMais(). */
    private function obterAdotanteIdAutenticado(): int
    {
        if (isset($_SESSION['adotante_id']) && (int) $_SESSION['adotante_id'] > 0) {
            return (int) $_SESSION['adotante_id'];
        }

        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        $adotante = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);

        if (!$adotante) {
            throw new Exception('Perfil de adotante não encontrado para este usuário.');
        }

        $_SESSION['adotante_id'] = (int) $adotante['adotante_id'];
        return (int) $adotante['adotante_id'];
    }

    /** Lê os filtros de busca em GET, compartilhados por index() e carregarMais(). */
    private function filtrosDaRequisicao(): array
    {
        return [
            'porte'       => $_GET['porte'] ?? null,
            'sexo'        => $_GET['sexo'] ?? null,
            'castrado'    => $_GET['castrado'] ?? '',
            'vacinado'    => $_GET['vacinado'] ?? '',
            'regiao_id'   => $_GET['regiao_id'] ?? null,
            'especie_id'  => $_GET['especie_id'] ?? null,
            'raca_id'     => $_GET['raca_id'] ?? null,
            'protetor_id' => $_GET['protetor_id'] ?? null,
        ];
    }

    /**
     * Lê as preferências do adotante a partir do JSON em ADOTANTE.detalhes. O formato salvo
     * varia conforme o fluxo: no cadastro inicial (OnboardingService::processarAdotante) fica
     * aninhado em detalhes['preferencias']['especie'|'porte'|'sexo'], mas na edição de perfil
     * (PerfilService::atualizarPerfil) vira chaves soltas 'preferencias_especie' etc. — mesma
     * inconsistência que PerfilController::editar() já contorna tentando os dois formatos.
     * Usado por index() e carregarMais().
     */
    private function obterPreferenciasAdotante(int $adotanteId): array
    {
        $adotante = null;
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        if ($usuarioId > 0) {
            $adotante = $this->adotanteRepo->buscarPorUsuarioId($usuarioId);
        }

        $detalhes = json_decode($adotante['detalhes'] ?? '{}', true) ?: [];

        $especies = $detalhes['preferencias_especie'] ?? $detalhes['preferencias']['especie'] ?? [];
        $portes   = $detalhes['preferencias_porte'] ?? $detalhes['preferencias']['porte'] ?? [];
        $sexos    = $detalhes['preferencias_sexo'] ?? $detalhes['preferencias']['sexo'] ?? [];
        $racas    = $detalhes['preferencias_raca'] ?? $detalhes['preferencias']['raca'] ?? [];

        return [
            'especie_id' => is_array($especies) ? $especies : [],
            'porte'      => is_array($portes) ? $portes : [],
            'sexo'       => is_array($sexos) ? $sexos : [],
            'raca_id'    => is_array($racas) ? $racas : [],
        ];
    }

    /** Busca as fotos de todos os animais da página em uma única query e anexa como $animal['fotos'] pro carrossel do card. Usado por index() e carregarMais(). */
    private function anexarFotos(array $animais): array
    {
        if (empty($animais)) {
            return [];
        }

        $fotosPorAnimal = $this->feedRepo->buscarFotosPorAnimais(array_column($animais, 'animal_id'));

        foreach ($animais as &$animal) {
            $animal['fotos'] = $fotosPorAnimal[(int) $animal['animal_id']] ?? [];
        }
        unset($animal);

        return $animais;
    }

    /** Monta o payload JSON de um card de animal pro JS renderizar. Usado por carregarMais(). */
    private function formatarAnimalParaJson(array $animal): array
    {
        $urlBase = rtrim(URL_BASE, '/');

        $montarUrl = function (?string $caminho) use ($urlBase): ?string {
            if (empty($caminho)) {
                return null;
            }
            return $urlBase . '/' . ltrim($caminho, '/');
        };

        return [
            'animal_id'      => (int) $animal['animal_id'],
            'protetor_id'    => (int) $animal['protetor_id'],
            'nome'           => $animal['nome'],
            'dt_nasc'        => $animal['dt_nasc'],
            'sexo'           => $animal['sexo'],
            'porte'          => $animal['porte'],
            'comportamento'  => $animal['comportamento'],
            'raca_nome'      => $animal['raca_nome'],
            'especie_nome'   => $animal['especie_nome'],
            'nome_fantasia'  => $animal['nome_fantasia'],
            'nome_regiao'    => $animal['nome_regiao'],
            'vacinado'       => (bool) $animal['vacinado'],
            'castrado'       => (bool) $animal['castrado'],
            'fotos'          => array_map(fn(array $f) => $montarUrl($f['caminho_foto']), $animal['fotos']),
            'url_detalhes'   => $urlBase . '/animal/mostrar?id=' . (int) $animal['animal_id'],
        ];
    }
}
