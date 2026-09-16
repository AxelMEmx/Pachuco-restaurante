<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../app/auth.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_item') {
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

    if ($action === 'update_reservation') {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $allowed = ['pendiente', 'confirmada', 'cancelada'];

        if ($id <= 0 || !in_array($status, $allowed, true)) {
            flash('error', 'Datos de reservacion invalidos.');
            redirect('admin.php');
        }

        $stmt = db()->prepare('UPDATE reservations SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        flash('success', 'Reservacion actualizada.');
        redirect('admin.php');
    }

    if ($action === 'update_order') {
        $id = (int) ($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $allowed = ['pendiente', 'preparando', 'entregado', 'cancelado'];

        if ($id <= 0 || !in_array($status, $allowed, true)) {
            flash('error', 'Datos de pedido invalidos.');
            redirect('admin.php');
        }

        $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        flash('success', 'Pedido actualizado.');
        redirect('admin.php');
    }
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$counts = [
    'clientes' => db()->query('SELECT COUNT(*) FROM users WHERE role = "customer"')->fetchColumn(),
    'mesas'    => db()->query('SELECT COUNT(*) FROM restaurant_tables')->fetchColumn(),
    'reservas' => db()->query('SELECT COUNT(*) FROM reservations')->fetchColumn(),
    'pedidos'  => db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
];

$totalReservations = (int) db()->query('SELECT COUNT(*) FROM reservations')->fetchColumn();
$totalOrders       = (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalPagesRes     = (int) ceil($totalReservations / $perPage);
$totalPagesOrd     = (int) ceil($totalOrders / $perPage);

$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$stmtRes = db()->prepare(
    'SELECT reservations.*, users.name AS user_name, restaurant_tables.table_number, restaurant_tables.location
     FROM reservations
     INNER JOIN users ON users.id = reservations.user_id
     INNER JOIN restaurant_tables ON restaurant_tables.id = reservations.table_id
     ORDER BY reservations.created_at DESC
     LIMIT ? OFFSET ?'
);
$stmtRes->bindValue(1, $perPage, PDO::PARAM_INT);
$stmtRes->bindValue(2, $offset, PDO::PARAM_INT);
$stmtRes->execute();
$reservations = $stmtRes->fetchAll();

$stmtOrd = db()->prepare(
    'SELECT orders.*, users.name AS user_name
     FROM orders
     INNER JOIN users ON users.id = orders.user_id
     ORDER BY orders.created_at DESC
     LIMIT ? OFFSET ?'
);
$stmtOrd->bindValue(1, $perPage, PDO::PARAM_INT);
$stmtOrd->bindValue(2, $offset, PDO::PARAM_INT);
$stmtOrd->execute();
$orders = $stmtOrd->fetchAll();

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
    <input type="hidden" name="action" value="add_item">
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
                <th>Accion</th>
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
                    <td>
                        <form method="post" style="display:flex;gap:.5rem;align-items:center;">
                            <input type="hidden" name="action" value="update_reservation">
                            <input type="hidden" name="id" value="<?= e((string) $reservation['id']) ?>">
                            <select name="status">
                                <option value="pendiente"  <?= $reservation['status'] === 'pendiente'  ? 'selected' : '' ?>>Pendiente</option>
                                <option value="confirmada" <?= $reservation['status'] === 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                                <option value="cancelada"  <?= $reservation['status'] === 'cancelada'  ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                            <button type="submit" class="button secondary">Guardar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($totalPagesRes > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a class="button secondary" href="?page=<?= $page - 1 ?>">← Anterior</a>
            <?php endif; ?>
            <span class="muted">Página <?= $page ?> de <?= $totalPagesRes ?></span>
            <?php if ($page < $totalPagesRes): ?>
                <a class="button secondary" href="?page=<?= $page + 1 ?>">Siguiente →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
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
                <th>Accion</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= e($order['user_name']) ?></td>
                    <td><?= e($order['order_type']) ?></td>
                    <td><span class="status <?= e($order['status']) ?>"><?= e($order['status']) ?></span></td>
                    <td><?= money($order['total']) ?></td>
                    <td>
                        <form method="post" style="display:flex;gap:.5rem;align-items:center;">
                            <input type="hidden" name="action" value="update_order">
                            <input type="hidden" name="id" value="<?= e((string) $order['id']) ?>">
                            <select name="status">
                                <option value="pendiente"  <?= $order['status'] === 'pendiente'  ? 'selected' : '' ?>>Pendiente</option>
                                <option value="preparando" <?= $order['status'] === 'preparando' ? 'selected' : '' ?>>Preparando</option>
                                <option value="entregado"  <?= $order['status'] === 'entregado'  ? 'selected' : '' ?>>Entregado</option>
                                <option value="cancelado"  <?= $order['status'] === 'cancelado'  ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                            <button type="submit" class="button secondary">Guardar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($totalPagesOrd > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a class="button secondary" href="?page=<?= $page - 1 ?>">← Anterior</a>
            <?php endif; ?>
            <span class="muted">Página <?= $page ?> de <?= $totalPagesOrd ?></span>
            <?php if ($page < $totalPagesOrd): ?>
                <a class="button secondary" href="?page=<?= $page + 1 ?>">Siguiente →</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../app/layout/footer.php'; ?>