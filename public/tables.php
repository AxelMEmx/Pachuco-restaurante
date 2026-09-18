<?php

declare(strict_types=1);


require_once __DIR__ . '/../app/auth.php';
require_admin();

$tables = db()->query('SELECT * FROM restaurant_tables ORDER BY table_number')->fetchAll();
$statusLabels = [
    'available' => 'Disponible',
    'reserved' => 'Reservada',
    'occupied' => 'Ocupada',
];

require_once __DIR__ . '/../app/layout/header.php';
?>

<div class="section-title">
    <div>
        <span class="eyebrow">Operacion</span>
        <h1>Mapa de mesas</h1>
        <p class="muted">Vista interna de capacidad, ubicacion y estado del comedor.</p>
    </div>
    <a class="button" href="<?= e(app_url('admin.php')) ?>">Panel operativo</a>
</div>

<section class="grid">
    <?php foreach ($tables as $table): ?>
        <article class="card">
            <p class="status <?= e($table['status']) ?>"><?= e($statusLabels[$table['status']] ?? $table['status']) ?></p>
            <h3>Mesa <?= e((string) $table['table_number']) ?></h3>
            <p>Capacidad: <?= e((string) $table['capacity']) ?> personas</p>
            <p class="muted">Area: <?= e($table['location']) ?></p>
        </article>
    <?php endforeach; ?>
</section>

<?php require_once __DIR__ . '/../app/layout/footer.php'; ?>
