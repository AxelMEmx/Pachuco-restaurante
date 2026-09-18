<?php

declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

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

<h1>Pachuco Restaurante</h1>

<p>La conexión con PHP y MySQL funciona correctamente.</p>

<h2>Platillos</h2>

<?php foreach ($featured as $item): ?>
    <div>
        <h3><?= e($item['name']) ?></h3>
        <p><?= e($item['description']) ?></p>
        <p><?= money($item['price']) ?></p>
    </div>
<?php endforeach; ?>

</main>

</body>
</html>