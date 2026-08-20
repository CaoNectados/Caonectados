<?php
require_once __DIR__ . '/../templates/header.php';
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$perfis = [
    [
        'id'       => 'adotante',
        'titulo'   => 'Quero Adotar',
        'cor'      => 'bg-rosa-4',
        'descricao'=> 'Encontre seu novo melhor amigo. Navegue por animais disponíveis e converse com protetores e ONGs.',
        'url'      => URL_BASE . '/onboarding/adotante',
    ],
    [
        'id'       => 'protetor',
        'titulo'   => 'Protetor Independente',
        'cor'      => 'bg-laranja-1',
        'descricao'=> 'Cadastre os animais que você resgata, gerencie solicitações de adoção e compartilhe sua causa com a comunidade.',
        'url'      => URL_BASE . '/onboarding/protetor',
    ],
    [
        'id'       => 'ong',
        'titulo'   => 'Sou uma ONG',
        'cor'      => 'bg-roxo2',
        'descricao'=> 'Gerencie o feed de pets da sua instituição, com acesso a todas as funcionalidades de adoção do app.',
        'url'      => URL_BASE . '/onboarding/ong',
    ],
];
?>

<main class="flex flex-col items-center justify-center min-h-[75vh] px-4 py-10">
    <div class="max-w-md w-full">

        <div class="text-center mb-8">
            <h1 class="font-shantell text-2xl md:text-3xl font-bold text-text-dark dark:text-white">
                Como você vai usar o CãoNectados?
            </h1>
            <p class="text-sm text-text-muted mt-2">
                Escolha o perfil que melhor descreve você para personalizarmos a sua experiência.
            </p>
        </div>

        <!-- Clicar no rótulo navega direto; clicar no ícone "i" só mostra/esconde a explicação -->
        <div class="space-y-3" id="lista-perfis">
            <?php foreach ($perfis as $p): ?>
                <div class="perfil-opcao group rounded-xl overflow-hidden shadow-sm hover:-translate-y-1 hover:shadow-lg transition-all duration-300" data-perfil="<?= $p['id'] ?>">
                    <div class="flex items-stretch <?= $p['cor'] ?>">
                        <a href="<?= $p['url'] ?>" class="flex-1 px-5 py-4 font-shantell font-bold text-text-dark hover:brightness-95 transition">
                            <?= htmlspecialchars($p['titulo']) ?>
                        </a>
                        <button type="button"
                                onclick="OnboardingSelecao.toggleInfo('<?= $p['id'] ?>')"
                                class="px-4 flex items-center justify-center hover:brightness-95 transition cursor-pointer"
                                aria-label="Saiba mais sobre o perfil <?= htmlspecialchars($p['titulo']) ?>">
                            <img src="<?= $urlBase ?>/assets/icons/info.svg" alt="" class="w-5 h-5">
                        </button>
                    </div>
                    <div class="perfil-descricao hidden group-hover:block bg-branco dark:bg-preto2 border-t border-black/5 dark:border-white/5 px-5 py-3 transition-all duration-300">
                        <p class="text-xs text-text-dark dark:text-white/90 leading-relaxed"><?= htmlspecialchars($p['descricao']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <p class="text-xs text-text-muted text-center mt-6">
            Todos os perfis passam por validação para garantir a segurança da nossa comunidade.
        </p>
    </div>
</main>

<script>
const OnboardingSelecao = {
    toggleInfo(id) {
        document.querySelectorAll('.perfil-opcao').forEach(function (card) {
            const descricao = card.querySelector('.perfil-descricao');
            if (card.dataset.perfil === id) {
                descricao.classList.toggle('hidden');
            } else {
                descricao.classList.add('hidden');
            }
        });
    }
};
</script>

<?php
require_once __DIR__ . '/../templates/footer.php';
?>
