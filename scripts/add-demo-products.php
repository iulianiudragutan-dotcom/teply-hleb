<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require dirname(__DIR__) . '/server/config.php';

$products = [
    ['Багет', 'Хлеб', 1, 'Хрустящий пшеничный багет.', 'Пшеничная мука, вода, дрожжи, соль', 'глютен', 220, 300, 150, '/assets/baguette.jpg'],
    ['Чиабатта', 'Хлеб', 1, 'Пшеничный хлеб с пористым мякишем.', 'Пшеничная мука, вода, оливковое масло, дрожжи, соль', 'глютен', 260, 350, 180, '/assets/ciabatta.jpg'],
    ['Бриошь', 'Сладкое', 2, 'Мягкая сдобная булочка.', 'Пшеничная мука, молоко, сливочное масло, яйцо, сахар, дрожжи', 'глютен, молоко, яйцо', 180, 100, 120, '/assets/brioche.jpg'],
    ['Улитка с маком', 'Сладкое', 2, 'Сдобная выпечка с маковой начинкой.', 'Пшеничная мука, молоко, мак, сахар, сливочное масло, дрожжи', 'глютен, молоко', 200, 120, 90, '/assets/poppy-swirl.jpg'],
    ['Пирожок с картофелем', 'Сытное', 3, 'Печёный пирожок с картофельной начинкой.', 'Пшеничная мука, картофель, лук, подсолнечное масло, дрожжи, соль', 'глютен', 170, 150, 80, '/assets/potato-pie.jpg'],
    ['Слойка с яблоком', 'Сладкое', 2, 'Слоёная выпечка с яблочной начинкой.', 'Пшеничная мука, яблоко, сливочное масло, сахар, корица', 'глютен, молоко', 210, 120, 60, '/assets/apple-puff.jpg'],
];

$pdo = db();
$lookup = $pdo->prepare('SELECT id FROM products WHERE name = ? LIMIT 1');
$insert = $pdo->prepare('INSERT INTO products (name, category, category_id, description, ingredients, allergens, price, available, weight_g, prep_minutes, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)');
$added = 0;
$skipped = 0;

$pdo->beginTransaction();
try {
    foreach ($products as $product) {
        [$name, $category, $categoryId, $description, $ingredients, $allergens, $price, $weight, $minutes, $imagePath] = $product;
        if (!is_file(dirname(__DIR__) . $imagePath)) {
            throw new RuntimeException('Отсутствует фотография: ' . $imagePath);
        }
        $lookup->execute([$name]);
        if ($lookup->fetch()) {
            $skipped++;
            continue;
        }
        $insert->execute([$name, $category, $categoryId, $description, $ingredients, $allergens, $price, $weight, $minutes, $imagePath]);
        $added++;
    }
    $pdo->commit();
} catch (Throwable $error) {
    $pdo->rollBack();
    throw $error;
}

echo "Добавлено: {$added}; уже существовали: {$skipped}\n";
