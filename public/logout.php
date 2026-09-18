<?php

declare(strict_types=1);

session_start();
require_once __DIR__ . '/../app/helpers.php';
session_destroy();
redirect('index.php');

