<?php
declare(strict_types=1);
require __DIR__ . '/config.php';
require_once __DIR__ . '/customer_helpers.php';

session_name('teply_hleb_session');
session_set_cookie_params(['httponly' => true, 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'samesite' => 'Strict', 'path' => '/']);
session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));

$route = $_GET['route'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET' && $method !== 'HEAD') requireCsrf();

try {
    if ($route === 'session' && $method === 'GET') {
        respond(['csrf' => $_SESSION['csrf'], 'admin' => !empty($_SESSION['admin_id']), 'customer' => !empty($_SESSION['customer_id'])]);
    }
    if ($route === 'products' && $method === 'GET') {
        $rows = db()->query('SELECT p.id, p.name, COALESCE(c.name,p.category) AS category, p.description, p.ingredients, p.allergens, p.price, p.available, p.image_path, p.weight_g, p.prep_minutes FROM products p LEFT JOIN categories c ON c.id=p.category_id ORDER BY p.id')->fetchAll();
        respond(['products' => $rows]);
    }
    if ($route === 'categories' && $method === 'GET') {
        respond(['categories' => db()->query('SELECT id, name FROM categories ORDER BY id')->fetchAll()]);
    }
    if ($route === 'register' && $method === 'POST') {
        $data = input(); $phone = trim((string) ($data['phone'] ?? '')); $password = (string) ($data['password'] ?? '');
        if (!validPhone($phone) || strlen($password) < 10 || strlen($password) > 200 || $password !== (string) ($data['repeat'] ?? '')) respond(['error' => 'Проверьте телефон и пароль: минимум 10 символов'], 422);
        $phone = normalizedPhone($phone);
        $check = db()->prepare('SELECT id FROM users WHERE phone=?'); $check->execute([$phone]);
        if ($check->fetch()) respond(['error' => 'Этот номер уже зарегистрирован'], 409);
        db()->prepare('INSERT INTO users (phone,password_hash) VALUES (?,?)')->execute([$phone,password_hash($password,PASSWORD_DEFAULT)]);
        session_regenerate_id(true); $_SESSION['customer_id'] = (int) db()->lastInsertId(); $_SESSION['csrf'] = bin2hex(random_bytes(24));
        respond(['ok' => true, 'customer' => true, 'csrf' => $_SESSION['csrf']], 201);
    }
    if ($route === 'customer-login' && $method === 'POST') {
        $data = input(); $phone = trim((string) ($data['phone'] ?? '')); $password = (string) ($data['password'] ?? '');
        if (!validPhone($phone)) respond(['error' => 'Неверный телефон или пароль'], 401);
        $stmt = db()->prepare('SELECT id,password_hash FROM users WHERE phone=?'); $stmt->execute([normalizedPhone($phone)]); $user = $stmt->fetch();
        if (!$user || !password_verify($password,$user['password_hash'])) respond(['error' => 'Неверный телефон или пароль'], 401);
        session_regenerate_id(true); $_SESSION['customer_id'] = (int) $user['id']; $_SESSION['csrf'] = bin2hex(random_bytes(24));
        respond(['ok' => true, 'customer' => true, 'csrf' => $_SESSION['csrf']]);
    }
    if ($route === 'customer-logout' && $method === 'POST') {
        unset($_SESSION['customer_id']); session_regenerate_id(true); $_SESSION['csrf'] = bin2hex(random_bytes(24));
        respond(['ok' => true, 'csrf' => $_SESSION['csrf']]);
    }
    if ($route === 'customer-cart' && $method === 'GET') {
        $id = requireCustomer(); $stmt = db()->prepare('SELECT product_id AS id, quantity FROM shopping_cart WHERE user_id=? ORDER BY product_id'); $stmt->execute([$id]);
        respond(['items' => $stmt->fetchAll()]);
    }
    if ($route === 'customer-cart' && $method === 'POST') {
        $id = requireCustomer(); $data = input(); $items = $data['items'] ?? null;
        if (!is_array($items) || count($items) > 30) respond(['error' => 'Неверная корзина'], 422);
        $checked = [];
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['id'],$item['quantity']) || !is_int($item['id']) || !is_int($item['quantity']) || $item['quantity'] < 1 || $item['quantity'] > 20 || isset($checked[$item['id']])) respond(['error' => 'Количество товара должно быть от 1 до 20'], 422);
            $checked[$item['id']] = $item['quantity'];
        }
        $pdo = db(); $pdo->beginTransaction();
        try {
            $pdo->prepare('DELETE FROM shopping_cart WHERE user_id=?')->execute([$id]);
            $stmt = $pdo->prepare('INSERT INTO shopping_cart (user_id,product_id,quantity) VALUES (?,?,?)');
            foreach ($checked as $productId => $quantity) $stmt->execute([$id,$productId,$quantity]);
            $pdo->commit();
        } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
        respond(['ok' => true]);
    }
    if ($route === 'feedback' && $method === 'POST') {
        $data = input();
        $name = trim((string) ($data['name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));
        if (($data['consent'] ?? false) !== true || mb_strlen($name) < 2 || mb_strlen($name) > 80 || !validPhone($phone) || mb_strlen($message) < 10 || mb_strlen($message) > 2000) {
            respond(['error' => 'Проверьте имя, телефон и сообщение'], 422);
        }
        if (time() - (int) ($_SESSION['last_feedback_at'] ?? 0) < 60) respond(['error' => 'Подождите минуту перед следующим сообщением'], 429);
        db()->prepare('INSERT INTO feedback (name, phone, message) VALUES (?, ?, ?)')->execute([$name, $phone, $message]);
        $_SESSION['last_feedback_at'] = time();
        respond(['ok' => true], 201);
    }
    if ($route === 'order' && $method === 'POST') {
        $data = input();
        $name = trim((string) ($data['name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $pickup = (string) ($data['pickup'] ?? '');
        $fulfillment = (string) ($data['fulfillment'] ?? 'pickup');
        $address = trim((string) ($data['address'] ?? ''));
        $items = $data['items'] ?? null;
        if (mb_strlen($name) < 2 || mb_strlen($name) > 80 || !validPhone($phone) || !is_array($items) || count($items) < 1 || count($items) > 30) respond(['error' => 'Проверьте имя, телефон и корзину'], 422);
        if (!in_array($fulfillment, ['pickup','delivery'], true) || ($fulfillment === 'delivery' && (mb_strlen($address) < 10 || mb_strlen($address) > 255))) respond(['error' => 'Укажите адрес доставки'], 422);
        $dt = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i:sP', $pickup);
        if (!$dt || $dt->getTimestamp() < time() + 30 * 60 || $dt->getTimestamp() > time() + 14 * 86400) respond(['error' => 'Выберите время самовывоза от 30 минут до 14 дней'], 422);
        $quantities = [];
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['id'], $item['quantity']) || !is_int($item['id']) || !is_int($item['quantity']) || $item['quantity'] < 1 || $item['quantity'] > 20 || isset($quantities[$item['id']])) respond(['error' => 'Количество товара должно быть от 1 до 20'], 422);
            $quantities[$item['id']] = $item['quantity'];
        }
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $select = $pdo->prepare('SELECT id, price, available FROM products WHERE id = ?');
            $products = []; $total = 0;
            foreach ($quantities as $id => $quantity) {
                $select->execute([$id]); $product = $select->fetch();
                if (!$product || !$product['available']) { $pdo->rollBack(); respond(['error' => 'Один из товаров недоступен. Обновите каталог'], 409); }
                $products[] = [$product, $quantity]; $total += (int) $product['price'] * $quantity;
            }
            $code = strtoupper(bin2hex(random_bytes(6)));
            $pdo->prepare('INSERT INTO orders (code, customer_name, phone, pickup_at, status, total, user_id, fulfillment, delivery_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')->execute([$code, $name, normalizedPhone($phone), $dt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'), 'Новый', $total, $_SESSION['customer_id'] ?? null, $fulfillment, $fulfillment === 'delivery' ? $address : null]);
            $orderId = (int) $pdo->lastInsertId();
            $insert = $pdo->prepare('INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)');
            foreach ($products as [$product, $quantity]) $insert->execute([$orderId, $product['id'], $quantity, $product['price']]);
            if (!empty($_SESSION['customer_id'])) $pdo->prepare('DELETE FROM shopping_cart WHERE user_id=?')->execute([$_SESSION['customer_id']]);
            $pdo->commit(); respond(['code' => $code, 'status' => 'Новый', 'total' => $total], 201);
        } catch (Throwable $e) { if ($pdo->inTransaction()) $pdo->rollBack(); throw $e; }
    }
    if ($route === 'status' && $method === 'POST') {
        $data = input(); $code = strtoupper(trim((string) ($data['code'] ?? ''))); $phone = trim((string) ($data['phone'] ?? ''));
        if (!preg_match('/^[A-F0-9]{12}$/D', $code) || !validPhone($phone)) respond(['error' => 'Проверьте номер и телефон'], 422);
        $stmt = db()->prepare('SELECT code, status, total, pickup_at, phone FROM orders WHERE code = ?');
        $stmt->execute([$code]);
        $order = $stmt->fetch();
        // Пробелы, скобки и дефисы не должны мешать найти свой заказ.
        if (!$order || preg_replace('/\D/', '', $order['phone']) !== preg_replace('/\D/', '', $phone)) {
            respond(['error' => 'Заказ не найден'], 404);
        }
        unset($order['phone']);
        respond(['order' => $order]);
    }
    if ($route === 'my-orders' && $method === 'GET') {
        $id = requireCustomer(); $stmt = db()->prepare('SELECT code,status,total,pickup_at,fulfillment,delivery_address,created_at FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 30'); $stmt->execute([$id]);
        respond(['orders' => $stmt->fetchAll()]);
    }
    if ($route === 'reset-password' && $method === 'POST') {
        $data = input(); $phone = trim((string) ($data['phone'] ?? '')); $token = trim((string) ($data['code'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        if (!validPhone($phone) || !preg_match('/^[A-F0-9]{12}$/D',$token) || strlen($password) < 10 || strlen($password) > 200 || $password !== (string) ($data['repeat'] ?? '')) respond(['error' => 'Проверьте телефон, код и пароль'], 422);
        $stmt = db()->prepare('SELECT u.id,r.token_hash,r.expires_at FROM users u JOIN password_resets r ON r.user_id=u.id WHERE u.phone=?'); $stmt->execute([normalizedPhone($phone)]); $reset = $stmt->fetch();
        if (!$reset || strtotime($reset['expires_at']) < time() || !hash_equals($reset['token_hash'],hash('sha256',$token))) respond(['error' => 'Код недействителен или истёк'], 400);
        $pdo = db(); $pdo->beginTransaction();
        try {
            $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($password,PASSWORD_DEFAULT),$reset['id']]);
            $pdo->prepare('DELETE FROM password_resets WHERE user_id=?')->execute([$reset['id']]); $pdo->commit();
        } catch (Throwable $e) { $pdo->rollBack(); throw $e; }
        respond(['ok' => true]);
    }
    if ($route === 'login' && $method === 'POST') {
        $data = input(); $login = (string) ($data['login'] ?? ''); $password = (string) ($data['password'] ?? '');
        $stmt = db()->prepare('SELECT id, password_hash FROM admins WHERE login = ?'); $stmt->execute([$login]); $admin = $stmt->fetch();
        if (!$admin || !password_verify($password, $admin['password_hash'])) respond(['error' => 'Неверные данные для входа'], 401);
        session_regenerate_id(true); $_SESSION['admin_id'] = (int) $admin['id']; $_SESSION['csrf'] = bin2hex(random_bytes(24));
        respond(['csrf' => $_SESSION['csrf'], 'admin' => true]);
    }
    if ($route === 'logout' && $method === 'POST') { $_SESSION = []; session_destroy(); respond(['ok' => true]); }
    if ($route === 'admin-orders' && $method === 'GET') {
        requireAdmin();
        $orders = db()->query('SELECT id, code, customer_name, phone, pickup_at, status, total, created_at, fulfillment, delivery_address, staff_id FROM orders ORDER BY id DESC LIMIT 100')->fetchAll();
        $stmt = db()->prepare('SELECT p.name, oi.quantity, oi.unit_price FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id = ?');
        foreach ($orders as &$order) { $stmt->execute([$order['id']]); $order['items'] = $stmt->fetchAll(); } unset($order);
        respond(['orders' => $orders]);
    }
    if ($route === 'admin-staff' && $method === 'GET') {
        requireAdmin(); respond(['staff' => db()->query('SELECT s.id,s.full_name,s.phone,s.address,s.birth_date,p.name AS position FROM staff s JOIN positions p ON p.id=s.position_id ORDER BY s.id')->fetchAll(),
            'positions' => db()->query('SELECT id,name FROM positions ORDER BY id')->fetchAll()]);
    }
    if ($route === 'admin-staff' && $method === 'POST') {
        requireAdmin(); $data = input(); $name = trim((string) ($data['full_name'] ?? '')); $position = filter_var($data['position_id'] ?? null,FILTER_VALIDATE_INT);
        $phone = trim((string) ($data['phone'] ?? '')); $address = trim((string) ($data['address'] ?? '')); $birth = trim((string) ($data['birth_date'] ?? ''));
        if (mb_strlen($name) < 3 || mb_strlen($name) > 120 || !$position || ($phone !== '' && !validPhone($phone)) || mb_strlen($address) > 255 || ($birth !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$birth))) respond(['error' => 'Проверьте данные сотрудника'],422);
        db()->prepare('INSERT INTO staff (position_id,full_name,address,phone,birth_date) VALUES (?,?,?,?,?)')->execute([$position,$name,$address ?: null,$phone ?: null,$birth ?: null]);
        respond(['ok' => true, 'id' => (int) db()->lastInsertId()],201);
    }
    if ($route === 'admin-assign' && $method === 'POST') {
        requireAdmin(); $data = input(); $order = filter_var($data['order_id'] ?? null,FILTER_VALIDATE_INT); $staff = filter_var($data['staff_id'] ?? null,FILTER_VALIDATE_INT);
        if (!$order || !$staff) respond(['error' => 'Выберите заказ и сотрудника'],422);
        $stmt = db()->prepare('UPDATE orders SET staff_id=? WHERE id=?'); $stmt->execute([$staff,$order]); respond(['ok' => $stmt->rowCount() > 0]);
    }
    if ($route === 'admin-report' && $method === 'GET') {
        requireAdmin(); respond(['rows' => db()->query('SELECT sale_date,order_id,code,status,total,line_count,item_count,responsible_staff FROM sales_report ORDER BY sale_date DESC,order_id DESC LIMIT 100')->fetchAll()]);
    }
    if ($route === 'admin-reset-code' && $method === 'POST') {
        requireAdmin(); $data = input(); $phone = trim((string) ($data['phone'] ?? ''));
        if (!validPhone($phone)) respond(['error' => 'Проверьте телефон'],422);
        $stmt = db()->prepare('SELECT id FROM users WHERE phone=?'); $stmt->execute([normalizedPhone($phone)]); $user = $stmt->fetch();
        if (!$user) respond(['error' => 'Покупатель не найден'],404);
        $token = strtoupper(bin2hex(random_bytes(6)));
        db()->prepare('REPLACE INTO password_resets (user_id,token_hash,expires_at) VALUES (?,?,DATE_ADD(UTC_TIMESTAMP(),INTERVAL 15 MINUTE))')->execute([$user['id'],hash('sha256',$token)]);
        respond(['code' => $token, 'expires_minutes' => 15]);
    }
    if ($route === 'admin-feedback' && $method === 'GET') {
        requireAdmin();
        $rows = db()->query('SELECT id, name, phone, message, created_at FROM feedback ORDER BY id DESC LIMIT 100')->fetchAll();
        respond(['feedback' => $rows]);
    }
    if ($route === 'admin-status' && $method === 'POST') {
        requireAdmin(); $data = input(); $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT); $status = (string) ($data['status'] ?? '');
        if (!$id || !in_array($status, ['Новый', 'Подтверждён', 'Готов', 'В пути', 'Выдан', 'Отменён'], true)) respond(['error' => 'Неверный статус'], 422);
        $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?'); $stmt->execute([$status, $id]); respond(['ok' => $stmt->rowCount() > 0]);
    }
    if ($route === 'admin-product' && $method === 'POST') {
        requireAdmin(); $data = input(); $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT);
        $name = trim((string) ($data['name'] ?? '')); $category = (string) ($data['category'] ?? '');
        $description = trim((string) ($data['description'] ?? '')); $ingredients = trim((string) ($data['ingredients'] ?? '')); $allergens = trim((string) ($data['allergens'] ?? ''));
        $price = $data['price'] ?? null; $available = !empty($data['available']) ? 1 : 0;
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100 || !in_array($category, ['Хлеб', 'Сладкое', 'Сытное'], true) || !is_int($price) || $price < 1 || $price > 100000 || mb_strlen($description) > 500 || mb_strlen($ingredients) > 500 || mb_strlen($allergens) > 300) respond(['error' => 'Проверьте данные товара'], 422);
        $weight = filter_var($data['weight_g'] ?? null,FILTER_VALIDATE_INT); $minutes = filter_var($data['prep_minutes'] ?? null,FILTER_VALIDATE_INT);
        $categoryIds = ['Хлеб'=>1,'Сладкое'=>2,'Сытное'=>3];
        if (!$weight || $weight > 10000 || !$minutes || $minutes > 1440) respond(['error' => 'Укажите вес и время приготовления'],422);
        $fields = [$name, $category, $categoryIds[$category], $description, $ingredients, $allergens, $price, $available, $weight, $minutes];
        if ($id) { $fields[] = $id; $stmt = db()->prepare('UPDATE products SET name=?, category=?, category_id=?, description=?, ingredients=?, allergens=?, price=?, available=?, weight_g=?, prep_minutes=? WHERE id=?'); $stmt->execute($fields); }
        else { $stmt = db()->prepare('INSERT INTO products (name, category, category_id, description, ingredients, allergens, price, available, weight_g, prep_minutes) VALUES (?,?,?,?,?,?,?,?,?,?)'); $stmt->execute($fields); $id = (int) db()->lastInsertId(); }
        respond(['ok' => true, 'id' => $id]);
    }
    if ($route === 'admin-image' && $method === 'POST') {
        requireAdmin(); $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT); $file = $_FILES['image'] ?? null;
        if (!$id || !is_array($file) || $file['error'] !== UPLOAD_ERR_OK || $file['size'] > 2 * 1024 * 1024) respond(['error' => 'Загрузите изображение до 2 МБ'], 422);
        $type = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$type] ?? null;
        if (!$ext) respond(['error' => 'Допустимы JPG, PNG и WebP'], 422);
        $stmt = db()->prepare('SELECT id FROM products WHERE id = ?'); $stmt->execute([$id]); if (!$stmt->fetch()) respond(['error' => 'Товар не найден'], 404);
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $dir = dirname(__DIR__) . '/uploads'; if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) respond(['error' => 'Не удалось сохранить файл'], 500);
        $path = '/uploads/' . $name; db()->prepare('UPDATE products SET image_path = ? WHERE id = ?')->execute([$path, $id]); respond(['path' => $path]);
    }
    respond(['error' => 'Маршрут не найден'], 404);
} catch (Throwable $e) {
    error_log((string) $e);
    respond(['error' => 'Ошибка сервера'], 500);
}
