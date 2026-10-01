-- 1. Сотрудник, ответственный за заказ.
SELECT o.code, o.status, s.full_name, p.name AS position
FROM orders o LEFT JOIN staff s ON s.id=o.staff_id
LEFT JOIN positions p ON p.id=s.position_id
WHERE o.code='ПОДСТАВЬТЕ_КОД';

-- 2. Все категории и количество доступных изделий.
SELECT c.name, COUNT(p.id) AS available_products
FROM categories c LEFT JOIN products p ON p.category_id=c.id AND p.available=1
GROUP BY c.id,c.name ORDER BY c.id;

-- 3. Заказы, сумма, дата и ответственный сотрудник.
SELECT sale_date,code,status,total,item_count,responsible_staff
FROM sales_report ORDER BY sale_date DESC,order_id DESC;

-- 4. Выпечка, приготовление которой длится более 20 минут.
SELECT name,prep_minutes FROM products WHERE prep_minutes > 20 ORDER BY prep_minutes;

-- 5. Изделия весом более 300 г и дешевле 500 рублей.
SELECT name,weight_g,price FROM products WHERE weight_g > 300 AND price < 500 ORDER BY price;
