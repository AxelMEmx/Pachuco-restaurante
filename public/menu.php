<?php

declare(strict_types=1);


require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/layout/header.php';

$items = db()->query(
    'SELECT menu_items.*, categories.name AS category_name
     FROM menu_items
     INNER JOIN categories ON categories.id = menu_items.category_id
     WHERE menu_items.is_available = 1
     ORDER BY categories.name, menu_items.name'
)->fetchAll();

$categories = [];
foreach ($items as $item) {
    $categories[$item['category_name']] = true;
}

$loggedUser = current_user();
?>

<div class="section-title">
    <div>
        <span class="eyebrow">Carta</span>
        <h1>Menú</h1>
        <p class="muted">Agrega tus favoritos, ajusta cantidades y confirma tu pedido en la misma pantalla.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('reservations.php')) ?>">Reservar mesa</a>
</div>

<div class="menu-tools" aria-label="Filtros de menú">
    <button class="filter-button is-active" type="button" data-filter="todos">Todos</button>
    <?php foreach (array_keys($categories) as $category): ?>
        <button class="filter-button" type="button" data-filter="<?= e($category) ?>"><?= e($category) ?></button>
    <?php endforeach; ?>
</div>

<section class="order-layout" data-cart-root data-authenticated="<?= $loggedUser ? '1' : '0' ?>" data-login-url="<?= e(app_url('login.php')) ?>">
    <div class="grid menu-grid">
        <?php foreach ($items as $item): ?>
            <article class="card menu-card" data-category="<?= e($item['category_name']) ?>">
                <div class="menu-card-body">
                    <p class="status"><?= e($item['category_name']) ?></p>
                    <h3><?= e($item['name']) ?></h3>
                    <p class="muted"><?= e($item['description']) ?></p>
                    <p class="price"><?= money($item['price']) ?></p>
                </div>
                <div class="menu-card-actions">
                    <div class="quantity-control" aria-label="Cantidad de <?= e($item['name']) ?>">
                        <button type="button" class="qty-button" data-menu-step="-1" data-item-id="<?= e((string) $item['id']) ?>">-</button>
                        <span data-menu-qty="<?= e((string) $item['id']) ?>">0</span>
                        <button type="button" class="qty-button" data-menu-step="1" data-item-id="<?= e((string) $item['id']) ?>">+</button>
                    </div>
                    <button
                        type="button"
                        class="button add-button"
                        data-cart-add
                        data-id="<?= e((string) $item['id']) ?>"
                        data-name="<?= e($item['name']) ?>"
                        data-price="<?= e((string) $item['price']) ?>"
                    >Agregar</button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <aside class="cart-panel">
        <div class="cart-panel-header">
            <div>
                <span class="eyebrow">Tu orden</span>
                <h2>Resumen</h2>
            </div>
            <button type="button" class="clear-cart" data-cart-clear>Vaciar</button>
        </div>

        <div class="cart-empty" data-cart-empty>
            <strong>Aun no has agregado platillos.</strong>
            <p class="muted">Usa los botones de la carta para armar tu pedido.</p>
        </div>

        <div class="cart-items" data-cart-items></div>

        <form class="cart-form" method="post" action="<?= e(app_url('orders.php')) ?>" data-cart-form>
            <input type="hidden" name="cart_json" data-cart-json>

            <div class="form-row">
                <label for="cart_order_type">Modalidad</label>
                <select id="cart_order_type" name="order_type" data-order-type>
                    <option value="mesa">Consumir en mesa</option>
                    <option value="recoger">Recoger en restaurante</option>
                    <option value="entrega">Entrega a domicilio</option>
                </select>
            </div>

            <div class="form-row">
                <label for="cart_notes">Indicaciones</label>
                <textarea id="cart_notes" name="notes" data-order-notes placeholder="Ej. sin cebolla, salsa aparte, direccion de entrega o numero de mesa"></textarea>
            </div>

            <dl class="cart-totals">
                <div>
                    <dt>Productos</dt>
                    <dd data-cart-count>0</dd>
                </div>
                <div>
                    <dt>Subtotal</dt>
                    <dd data-cart-subtotal>$0.00</dd>
                </div>
                <div data-delivery-row hidden>
                    <dt>Entrega</dt>
                    <dd data-cart-delivery>$35.00</dd>
                </div>
                <div class="cart-total-row">
                    <dt>Total</dt>
                    <dd data-cart-total>$0.00</dd>
                </div>
            </dl>

            <p class="cart-note" data-cart-note>Tiempo estimado: 20 a 35 minutos. El total puede ajustarse si el restaurante confirma cambios especiales.</p>

            <button type="submit" data-cart-submit disabled><?= $loggedUser ? 'Confirmar pedido' : 'Inicia sesion para confirmar' ?></button>
        </form>
    </aside>
</section>

<?php require_once __DIR__ . '/../app/layout/footer.php'; ?>
