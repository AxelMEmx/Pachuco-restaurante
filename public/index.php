<?php

declare(strict_types=1);


require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/layout/header.php';

$featured = db()->query(
    'SELECT menu_items.*, categories.name AS category_name
     FROM menu_items
     INNER JOIN categories ON categories.id = menu_items.category_id
     WHERE menu_items.is_available = 1
     ORDER BY menu_items.id
     LIMIT 3'
)->fetchAll();
?>

<section class="hero restaurant-hero">
    <div>
        <span class="hero-kicker">Cocina mexicana contemporanea</span>
        <h1>Sabores de barrio, mesa bien servida.</h1>
        <p>El Pachuco reune antojitos, brasas, salsas hechas en casa y cocteleria fresca en un ambiente relajado para comer bien cualquier dia de la semana.</p>
        <div class="actions">
            <a class="button" href="<?= e(app_url('reservations.php')) ?>">Reservar mesa</a>
            <a class="button secondary" href="<?= e(app_url('menu.php')) ?>">Ver menú</a>
        </div>
    </div>
    <aside class="hero-panel">
        <h2>Hoy en El Pachuco</h2>
        <p class="muted">Servicio de comedor, pedidos para recoger y reservaciones para grupos.</p>
        <ul>
            <li>Comedor abierto de 13:00 a 23:00</li>
            <li>Especialidades de maiz, mole y parrilla</li>
            <li>Opciones vegetarianas y para compartir</li>
            <li>Reservas recomendadas para cenas y fines de semana</li>
        </ul>
    </aside>
</section>

<section class="split-section">
    <div>
        <span class="eyebrow">Nuestra cocina</span>
        <h2>Recetas mexicanas con servicio actual</h2>
        <p class="muted">Trabajamos con ingredientes frescos, preparaciones de temporada y una carta pensada para compartir: entradas al centro, tacos, platos fuertes, postres y bebidas de la casa.</p>
    </div>
    <div class="highlight-box">
        <strong>Reservas para grupos</strong>
        <p>Para reuniones familiares, comidas de trabajo o celebraciones, puedes solicitar mesa desde la pagina de reservaciones.</p>
    </div>
</section>

<div class="section-title">
    <div>
        <h2>Favoritos de la casa</h2>
        <p class="muted">Una probadita de lo que puedes pedir hoy.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('orders.php')) ?>">Ordenar ahora</a>
</div>

<section class="grid">
    <?php foreach ($featured as $item): ?>
        <article class="card">
            <p class="status"><?= e($item['category_name']) ?></p>
            <h3><?= e($item['name']) ?></h3>
            <p class="muted"><?= e($item['description']) ?></p>
            <p class="price"><?= money($item['price']) ?></p>
        </article>
    <?php endforeach; ?>
</section>

<?php require_once __DIR__ . '/../app/layout/footer.php'; ?>
