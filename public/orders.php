<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../app/auth.php';
require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderType = $_POST['order_type'] ?? 'mesa';
    $allowedTypes = ['mesa', 'recoger', 'entrega'];
    if (!in_array($orderType, $allowedTypes, true)) {
        $orderType = 'mesa';
    }

    $notes = trim($_POST['notes'] ?? '');
    $cartJson = $_POST['cart_json'] ?? '';
    $cart = json_decode($cartJson, true);

    if (!is_array($cart) || count($cart) === 0) {
        flash('error', 'Agrega al menos un platillo antes de confirmar tu pedido.');
        redirect('menu.php');
    }

    $quantities = [];
    foreach ($cart as $entry) {
        $id = (int) ($entry['id'] ?? 0);
        $quantity = (int) ($entry['quantity'] ?? 0);

        if ($id > 0 && $quantity > 0) {
            $quantities[$id] = ($quantities[$id] ?? 0) + min($quantity, 20);
        }
    }

    if (!$quantities) {
        flash('error', 'Revisa las cantidades de tu pedido.');
        redirect('menu.php');
    }

    $placeholders = implode(',', array_fill(0, count($quantities), '?'));
    $stmt = db()->prepare("SELECT id, name, price FROM menu_items WHERE is_available = 1 AND id IN ($placeholders)");
    $stmt->execute(array_keys($quantities));
    $items = $stmt->fetchAll();

    if (count($items) !== count($quantities)) {
        flash('error', 'Uno de los productos ya no esta disponible. Actualiza tu pedido.');
        redirect('menu.php');
    }

    $subtotal = 0.0;
    $summaryLines = [];
    foreach ($items as $item) {
        $quantity = $quantities[(int) $item['id']];
        $subtotal += (float) $item['price'] * $quantity;
        $summaryLines[] = $quantity . ' x ' . $item['name'];
    }

    $deliveryFee = $orderType === 'entrega' ? 35.00 : 0.00;
    $total = $subtotal + $deliveryFee;
    $orderNotes = trim($notes . "\n\nDetalle: " . implode(', ', $summaryLines));

    $pdo = db();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'INSERT INTO orders (user_id, order_type, status, total, notes) VALUES (?, ?, "pendiente", ?, ?)'
    );
    $stmt->execute([$user['id'], $orderType, $total, $orderNotes]);
    $orderId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare(
        'INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price) VALUES (?, ?, ?, ?)'
    );

    foreach ($items as $item) {
        $stmt->execute([$orderId, $item['id'], $quantities[(int) $item['id']], $item['price']]);
    }

    $pdo->commit();

    flash('success', 'Tu orden fue recibida. En breve comenzaremos a prepararla.');
    redirect('orders.php');
}

$stmt = db()->prepare(
    'SELECT orders.*, GROUP_CONCAT(CONCAT(order_items.quantity, " x ", menu_items.name) ORDER BY menu_items.name SEPARATOR ", ") AS detail
     FROM orders
     LEFT JOIN order_items ON order_items.order_id = orders.id
     LEFT JOIN menu_items ON menu_items.id = order_items.menu_item_id
     WHERE orders.user_id = ?
     GROUP BY orders.id
     ORDER BY orders.created_at DESC'
);
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

require_once __DIR__ . '/../app/layout/header.php';
?>

<div class="section-title">
    <div>
        <span class="eyebrow">Orden en linea</span>
        <h1>Mis pedidos</h1>
        <p class="muted">Consulta el estado y detalle de tus ordenes recientes.</p>
    </div>
    <a class="button" href="<?= e(app_url('menu.php')) ?>">Ordenar de nuevo</a>
</div>

<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Detalle</th>
                <th>Modalidad</th>
                <th>Estado</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= e($order['created_at']) ?></td>
                    <td><?= e($order['detail'] ?: 'Pedido registrado') ?></td>
                    <td><?= e($order['order_type']) ?></td>
                    <td><span class="status <?= e($order['status']) ?>"><?= e($order['status']) ?></span></td>
                    <td><?= money($order['total']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/layout/footer.php'; ?>
