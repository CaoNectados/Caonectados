<?php
require_once __DIR__ . '/../templates/header.php';

/** @var array $animais */
$animais = $animais ?? [];
$urlBase = defined('URL_BASE') ? rtrim(URL_BASE, '/') : '';

$porteLabels = ['pequeno' => 'Pequeno', 'medio' => 'Médio', 'grande' => 'Grande'];
$sexoLabels  = ['macho' => 'Macho', 'femea' => 'Fêmea', 'indefinido' => 'Indefinido'];
?>

<div class="min-h-screen bg-background pb-20">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <h1 class="font-shantell text-2xl sm:text-3xl font-bold text-text-dark dark:text-white">Feed de Adoção</h1>
            <p class="text-sm text-text-muted mt-1">Conheça os animais disponíveis para adoção nas ONGs e protetores parceiros.</p>
        </div>

        <?php if (empty($animais)): ?>
            <div class="bg-surface dark:bg-preto1 rounded-3xl border border-rosa-2 dark:border-preto3 p-10 text-center shadow-sm">
                <img src="<?= $urlBase ?>/assets/img/patinha-baixo.png" alt="" class="w-12 h-12 mx-auto mb-4 opacity-60">
                <p class="font-poppins text-text-muted">Nenhum animal disponível para adoção no momento. Volte em breve!</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                <?php foreach ($animais as $animal): ?>
                    <?php
                    $fotoPrincipal = $animal['foto_principal'] ?? null;
                    if (!empty($fotoPrincipal)) {
                        $fotoLimpa = ltrim(trim($fotoPrincipal), '/');
                        $fotoLimpa = preg_replace('#^(assets/)?(uploads/)+#', '', $fotoLimpa);
                        $srcFoto = $urlBase . '/assets/uploads/' . htmlspecialchars($fotoLimpa);
                    } else {
                        $srcFoto = $urlBase . '/assets/img/perfil-placeholder.png';
                    }
                    $porte = $porteLabels[$animal['porte']] ?? ucfirst((string) ($animal['porte'] ?? ''));
                    $sexo  = $sexoLabels[$animal['sexo']] ?? ucfirst((string) ($animal['sexo'] ?? ''));
                    ?>
                    <a href="<?= $urlBase ?>/animal/mostrar?id=<?= (int) $animal['animal_id'] ?>"
                       class="group bg-surface dark:bg-preto1 rounded-2xl overflow-hidden border border-rosa-2 dark:border-preto3 shadow-sm hover:shadow-md transition">
                        <div class="aspect-square w-full overflow-hidden bg-branco dark:bg-preto2">
                            <img src="<?= $srcFoto ?>"
                                 alt="Foto de <?= htmlspecialchars($animal['nome']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                 onerror="this.onerror=null; this.src='<?= $urlBase ?>/assets/img/perfil-placeholder.png';">
                        </div>
                        <div class="p-3">
                            <h3 class="font-shantell font-bold text-text-dark dark:text-white truncate"><?= htmlspecialchars($animal['nome']) ?></h3>
                            <p class="text-xs text-text-muted truncate"><?= htmlspecialchars($animal['raca_nome'] ?? 'Raça não informada') ?></p>
                            <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white"><?= htmlspecialchars($porte) ?></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rosa-1 dark:bg-preto2 text-text-dark dark:text-white"><?= htmlspecialchars($sexo) ?></span>
                            </div>
                            <p class="text-[11px] text-text-muted mt-2 truncate">🏠 <?= htmlspecialchars($animal['nome_fantasia'] ?? 'Protetor independente') ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
