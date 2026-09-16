<?php

declare(strict_types=1);


require_once __DIR__ . '/../app/auth.php';
require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tableId = (int) ($_POST['table_id'] ?? 0);
    $reservationDate = $_POST['reservation_date'] ?? '';
    $reservationTime = $_POST['reservation_time'] ?? '';
    $guests = (int) ($_POST['guests'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($tableId <= 0 || $reservationDate === '' || $reservationTime === '' || $guests <= 0) {
        flash('error', 'Completa fecha, hora, personas y preferencia de mesa.');
        redirect('reservations.php');
    }

    if ($reservationDate < date('Y-m-d')) {
    flash('error', 'La fecha de reservación no puede ser en el pasado.');
    redirect('reservations.php');
    }

    $tableCheck = db()->prepare('SELECT id FROM restaurant_tables WHERE id = ?');
    $tableCheck->execute([$tableId]);
    if (!$tableCheck->fetch()) {
        flash('error', 'La mesa seleccionada no existe. Intenta de nuevo.');
        redirect('reservations.php');
    }

    $stmt = db()->prepare(
        'INSERT INTO reservations (user_id, table_id, reservation_date, reservation_time, guests, status, notes)
         VALUES (?, ?, ?, ?, ?, "pendiente", ?)'
    );
    $stmt->execute([$user['id'], $tableId, $reservationDate, $reservationTime, $guests, $notes]);

    flash('success', 'Recibimos tu solicitud. Te confirmaremos la disponibilidad de la mesa.');
    redirect('reservations.php');
}

$tables = db()->query('SELECT * FROM restaurant_tables ORDER BY capacity, table_number')->fetchAll();
$stmt = db()->prepare(
    'SELECT reservations.*, restaurant_tables.table_number, restaurant_tables.location
     FROM reservations
     INNER JOIN restaurant_tables ON restaurant_tables.id = reservations.table_id
     WHERE reservations.user_id = ?
     ORDER BY reservation_date DESC, reservation_time DESC'
);
$stmt->execute([$user['id']]);
$reservations = $stmt->fetchAll();

require_once __DIR__ . '/../app/layout/header.php';
?>

<div class="section-title">
    <div>
        <span class="eyebrow">Reservaciones</span>
        <h1>Reserva tu mesa</h1>
        <p class="muted">Cuéntanos cuándo vienes y prepararemos el espacio ideal para tu visita.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('menu.php')) ?>">Ver menú</a>
</div>

<form class="form" method="post">
    <div class="form-row">
        <label for="table_id">Preferencia de mesa</label>
        <select id="table_id" name="table_id" required>
            <option value="">Elige una opcion</option>
            <?php foreach ($tables as $table): ?>
                <option value="<?= e((string) $table['id']) ?>">
                    <?= e($table['location']) ?> - hasta <?= e((string) $table['capacity']) ?> personas
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-row">
        <label for="reservation_date">Fecha</label>
        <input id="reservation_date" name="reservation_date" type="date" required>
    </div>
    <div class="form-row">
        <label for="reservation_time">Hora</label>
        <input id="reservation_time" name="reservation_time" type="time" required>
    </div>
    <div class="form-row">
        <label for="guests">Personas</label>
        <input id="guests" name="guests" type="number" min="1" required>
    </div>
    <div class="form-row">
        <label for="notes">Detalles de la visita</label>
        <textarea id="notes" name="notes" placeholder="Celebracion, alergias, silla para bebe o preferencias especiales"></textarea>
    </div>
    <button type="submit">Solicitar reservacion</button>
</form>

<h2>Mis reservaciones</h2>
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Ambiente</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Personas</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservations as $reservation): ?>
                <tr>
                    <td><?= e($reservation['location']) ?></td>
                    <td><?= e($reservation['reservation_date']) ?></td>
                    <td><?= e($reservation['reservation_time']) ?></td>
                    <td><?= e((string) $reservation['guests']) ?></td>
                    <td><span class="status <?= e($reservation['status']) ?>"><?= e($reservation['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../app/layout/footer.php'; ?>
