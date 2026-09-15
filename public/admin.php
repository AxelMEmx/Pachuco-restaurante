<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../app/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $categoryId = (int) ($_POST['category_id'] ?? 0);

    if ($name === '' || $price <= 0 || $categoryId <= 0) {
        flash('error', 'Completa nombre, categoria y precio del platillo.');
        redirect('admin.php');
    }

    $stmt = db()->prepare(
        'INSERT INTO menu_items (category_id, name, description, price, is_available) VALUES (?, ?, ?, ?, 1)'
    );
    $stmt->execute([$categoryId, $name, $description, $price]);
    flash('success', 'El platillo ya esta disponible en la carta.');
    redirect('admin.php');
}

$counts = [
    'clientes' => db()->query('SELECT COUNT(*) FROM users WHERE role = "customer"')->fetchColumn(),
    'mesas' => db()->query('SELECT COUNT(*) FROM restaurant_tables')->fetchColumn(),
    'reservas' => db()->query('SELECT COUNT(*) FROM reservations')->fetchColumn(),
    'pedidos' => db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
];

$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$reservations = db()->query(
    'SELECT reservations.*, users.name AS user_name, restaurant_tables.table_number, restaurant_tables.location
     FROM reservations
     INNER JOIN users ON users.id = reservations.user_id
     INNER JOIN restaurant_tables ON restaurant_tables.id = reservations.table_id
     ORDER BY reservations.created_at DESC
     LIMIT 10'
)->fetchAll();
$orders = db()->query(
    'SELECT orders.*, users.name AS user_name
     FROM orders
     INNER JOIN users ON users.id = orders.user_id
     ORDER BY orders.created_at DESC
     LIMIT 10'
)->fetchAll();

require_once __DIR__ . '/../app/layout/header.php';
?>

<div class="section-title">
    <div>
        <span class="eyebrow">Operacion</span>
        <h1>Panel operativo</h1>
        <p class="muted">Resumen diario de clientes, reservas, pedidos y carta.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('tables.php')) ?>">Ver mesas</a>
</div>

<section class="grid stat-grid">
    <?php foreach ($counts as $label => $count): ?>
        <article class="card stat-card">
            <p class="muted"><?= e(ucfirst($label)) ?></p>
            <p class="price"><?= e((string) $count) ?></p>
        </article>
    <?php endforeach; ?>
</section>

<h2>Agregar a la carta</h2>
<form class="form" method="post">
    <div class="form-row">
        <label for="category_id">Categoria</label>
        <select id="category_id" name="category_id" required>
            <option value="">Selecciona una categoria</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e((string) $category['id']) ?>"><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-row">
        <label for="name">Nombre del platillo</label>
        <input id="name" name="name" required>
    </div>
    <div class="form-row">
        <label for="description">Descripcion para la carta</label>
        <textarea id="description" name="description"></textarea>
    </div>
    <div class="form-row">
        <label for="price">Precio</label>
        <input id="price" name="price" type="number" min="1" step="0.01" required>
    </div>
    <button type="submit">Publicar platillo</button>
</form>

<h2>Reservas recientes</h2>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Area</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?= e($reservation['user_name']) ?></td>
                    <td><?= e($reservation['location']) ?></td>
                    <td><?= e($reservation['reservation_date']) ?></td>
                    <td><?= e($reservation['reservation_time']) ?></td>
                    <td><span class="status <?= e($reservation['status']) ?>"><?= e($reservation['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h2>Pedidos recientes</h2>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Modalidad</th>
                <th>Estado</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= e($order['user_name']) ?></td>
                    <td><?= e($order['order_type']) ?></td>
                    <td><span class="status <?= e($order['status']) ?>"><?= e($order['status']) ?></span></td>
                    <td><?= money($order['total']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/layout/footer.php'; ?>
