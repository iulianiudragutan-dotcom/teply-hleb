<?php
declare(strict_types=1);

if (!function_exists('normalizedPhone')) {
function normalizedPhone(string $phone): string {
    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) === 11 && $digits[0] === '8') $digits = '7' . substr($digits, 1);
    return '+' . $digits;
}
}

if (!function_exists('requireCustomer')) {
function requireCustomer(): int {
    $id = (int) ($_SESSION['customer_id'] ?? 0);
    if ($id < 1) respond(['error' => 'Войдите в аккаунт покупателя'], 401);
    return $id;
}
}
