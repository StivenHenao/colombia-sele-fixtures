<?php
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}
require_once 'logic.php'; 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selección Colombia - Fixtures</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">

    <?php if ($proximo): ?>
    <!-- Hero: próximo partido -->
    <div class="hero">
        <p class="hero-label">Selección Colombia · Fixtures</p>
        <h1 class="hero-title">Faltan <span><?= $dias ?></span> días</h1>
        <div class="countdown-box">
            <span class="countdown-num"><?= $dias ?></span>
            <span class="countdown-lbl">días para el próximo partido</span>
        </div>
        <div>
            <div class="match-chip">
                <span class="match-chip-dot"></span>
                <?= htmlspecialchars($proximo["partido"]) ?>
            </div>
            <p class="match-date-small">
                <?= ($dias_es[$proximo["fecha"]->format('l')] ?? $proximo["fecha"]->format('l')) ?>, 
                <?= $proximo["fecha"]->format('d') ?>
                de <?= $meses_es[(int)$proximo["fecha"]->format('n')] ?>
                de <?= $proximo["fecha"]->format('Y') ?>
            </p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lista de partidos -->
    <p class="section-label">Calendario de partidos</p>
    <div class="fixtures">
        <?php foreach ($partidos as $i => $p):
            $d   = $p["fecha"]->format('j');
            $mon = $meses_es[(int)$p["fecha"]->format('n')];
            $yr  = $p["fecha"]->format('Y');
            $isNext = ($i === 0);
        ?>
        <div class="fixture-item <?= $isNext ? 'is-next' : '' ?>">
            <div class="fixture-date">
                <div class="fixture-day"><?= $d ?></div>
                <div class="fixture-mon"><?= $mon ?> <?= $yr ?></div>
            </div>

            <div class="fixture-body">
                <div class="fixture-name"><?= htmlspecialchars($p["partido"]) ?></div>
                <div class="fixture-meta">
                    <?php if ($p["competicion"]): ?>
                        <span class="meta-comp"><?= htmlspecialchars($p["competicion"]) ?></span>
                    <?php endif; ?>
                    <?php if ($p["hora"]): ?>
                        <span class="meta-hora"><?= htmlspecialchars($p["hora"]) ?></span>
                    <?php endif; ?>
                    <?php if ($p["tv"]): ?>
                        <span class="meta-tv">📺 <?= htmlspecialchars($p["tv"]) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <span class="fixture-badge <?= $isNext ? 'badge-next' : 'badge-sched' ?>">
                <?= $isNext ? 'Próximo' : 'Programado' ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>

</div>
</body>
</html>