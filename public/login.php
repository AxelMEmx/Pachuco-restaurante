<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../app/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (login_user($email, $password)) {
        flash('success', 'Que gusto verte de nuevo.');
        redirect('index.php');
    }

    flash('error', 'No encontramos una cuenta con esos datos.');
    redirect('login.php');
}

require_once __DIR__ . '/../app/layout/header.php';
?>

<div class="section-title">
    <div>
        <span class="eyebrow">Clientes</span>
        <h1>Mi cuenta</h1>
        <p class="muted">Entra para consultar tus reservaciones y hacer pedidos mas rapido.</p>
    </div>
    <a class="button secondary" href="<?= e(app_url('register.php')) ?>">Crear cuenta</a>
</div>

<form class="form" method="post">
    <div class="form-row">
        <label for="email">Correo electronico</label>
        <input id="email" name="email" type="email" autocomplete="email" required>
    </div>
    <div class="form-row">
        <label for="password">Contrasena</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
    </div>
    <button type="submit">Entrar</button>
</form>

<?php require_once __DIR__ . '/../app/layout/footer.php'; ?>
