<?php

declare(strict_types=1);

session_start();
session_destroy();
require_once __DIR__ . '/../app/helpers.php';
header('Location: ' . app_url('index.php'));
exit;


