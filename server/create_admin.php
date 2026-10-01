<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
if (PHP_SAPI !== 'cli') exit("Запускайте только из командной строки.\n");
$login = $argv[1] ?? ''; $password = $argv[2] ?? '';
if ($login === '' || strlen($password) < 12) exit("Использование: php create_admin.php ЛОГИН ПАРОЛЬ_ОТ_12_СИМВОЛОВ\n");
$stmt = db()->prepare('INSERT INTO admins (login, password_hash) VALUES (?, ?)');
$stmt->execute([$login, password_hash($password, PASSWORD_DEFAULT)]);
echo "Администратор создан.\n";
