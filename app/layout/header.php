<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../auth.php';

$user = current_user();
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="El Pachuco Restaurante: cocina mexicana contemporanea, reservas y pedidos en linea.">
    <title><?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(app_url('assets/css/styles.css')) ?>">
</head>
<body>
<header class="site-header">
    <a class="brand" href="<?= e(app_url('index.php')) ?>"> El Pachuco</a>
    <nav class="main-nav" aria-label="Navegacion principal">
        <?php if ($user && $user['role'] === 'admin'): ?>
            <a href="<?= e(app_url('admin.php')) ?>">Operacion</a>
            <a href="<?= e(app_url('tables.php')) ?>">Mesas</a>
            <a href="<?= e(app_url('logout.php')) ?>">Salir</a>
        <?php else: ?>
            <a href="<?= e(app_url('index.php')) ?>">Inicio</a>
            <a href="<?= e(app_url('menu.php')) ?>">Menú</a>
            <a href="<?= e(app_url('reservations.php')) ?>">Reservar</a>
            <a href="<?= e(app_url('orders.php')) ?>">Ordenar</a>
            <?php if ($user): ?>
                <a href="<?= e(app_url('logout.php')) ?>">Salir</a>
            <?php else: ?>
                <a href="<?= e(app_url('login.php')) ?>">Mi cuenta</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

</header>

<main class="container">
    <?php if ($message = flash('success')): ?>
        <div class="alert success"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if ($message = flash('error')): ?>
        <div class="alert error"><?= e($message) ?></div>
    <?php endif; ?>
