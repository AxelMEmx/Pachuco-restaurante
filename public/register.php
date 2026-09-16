<?php

declare(strict_types=1);


require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        flash('error', 'Completa tu nombre, correo y contraseña.');
        redirect('register.php');
    }

    try {
        $stmt = db()->prepare(
            'INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, "customer")'
        );
        $stmt->execute([$name, $email, $phone, password_hash($password, PASSWORD_DEFAULT)]);
        flash('success', 'Tu cuenta esta lista. Ya puedes entrar.');
        redirect('login.php');
    } catch (PDOException $exception) {
        flash('error', 'Ese correo ya esta registrado o no se pudo crear la cuenta.');
        redirect('register.php');
    }
}

require_once __DIR__ . '/../app/layout/header.php';
?>

<div class="section-title">
    <div>
        <span class="eyebrow">Clientes</span>
        <h1>Crear cuenta</h1>
        <p class="muted">Guarda tus datos para reservar y ordenar con mayor comodidad.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('login.php')) ?>">Ya tengo cuenta</a>
</div>

<form class="form" method="post">
    <div class="form-row">
        <label for="name">Nombre completo</label>
        <input id="name" name="name" autocomplete="name" required>
    </div>
    <div class="form-row">
        <label for="email">Correo electronico</label>
        <input id="email" name="email" type="email" autocomplete="email" required>
    </div>
    <div class="form-row">
        <label for="phone">Telefono</label>
        <input id="phone" name="phone" autocomplete="tel">
    </div>
    <div class="form-row">
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" minlength="6" autocomplete="new-password" required>
    </div>
    <button type="submit">Crear cuenta</button>
</form>

<?php require_once __DIR__ . '/../app/layout/footer.php'; ?>
