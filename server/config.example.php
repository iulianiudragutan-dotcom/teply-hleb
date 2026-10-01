<?php
declare(strict_types=1);

function db(): PDO {
    static $connection = null;
    if ($connection instanceof PDO) return $connection;
    $dsn = getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;dbname=teply_hleb;charset=utf8mb4';
    $connection = new PDO($dsn, getenv('DB_USER') ?: 'root', getenv('DB_PASSWORD') ?: '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $connection;
}

function respond(array $body, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

function input(): array {
    $raw = file_get_contents('php://input');
    if (strlen($raw) > 1048576) respond(['error' => 'Слишком большой запрос'], 413);
    try { $data = json_decode($raw, true, 64, JSON_THROW_ON_ERROR); }
    catch (JsonException $e) { respond(['error' => 'Неверный JSON'], 400); }
    if (!is_array($data) || array_is_list($data)) respond(['error' => 'Неверный формат данных'], 400);
    return $data;
}

function requireAdmin(): void {
    if (empty($_SESSION['admin_id'])) respond(['error' => 'Требуется вход'], 401);
}

function requireCsrf(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        respond(['error' => 'Обновите страницу и попробуйте снова'], 403);
    }
}

function validPhone(string $phone): bool {
    return (bool) preg_match('/^\+?[0-9 ()-]{10,20}$/D', $phone) && strlen(preg_replace('/\D/', '', $phone)) >= 10;
}

