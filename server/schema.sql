CREATE DATABASE IF NOT EXISTS teply_hleb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE teply_hleb;

CREATE TABLE IF NOT EXISTS products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  category VARCHAR(30) NOT NULL,
  description VARCHAR(500) NOT NULL DEFAULT '',
  ingredients VARCHAR(500) NOT NULL DEFAULT '',
  allergens VARCHAR(300) NOT NULL DEFAULT '',
  price INT UNSIGNED NOT NULL,
  available TINYINT(1) NOT NULL DEFAULT 1,
  image_path VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS access_rights (
  id TINYINT UNSIGNED PRIMARY KEY,
  name VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB;
INSERT IGNORE INTO access_rights (id, name) VALUES (1, 'USER'), (2, 'ADMIN');

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  access_right_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
  phone VARCHAR(20) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (access_right_id) REFERENCES access_rights(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
  id TINYINT UNSIGNED PRIMARY KEY,
  name VARCHAR(30) NOT NULL UNIQUE
) ENGINE=InnoDB;
INSERT IGNORE INTO categories (id, name) VALUES (1, 'Хлеб'), (2, 'Сладкое'), (3, 'Сытное');

ALTER TABLE products
  ADD COLUMN category_id TINYINT UNSIGNED NULL,
  ADD COLUMN weight_g SMALLINT UNSIGNED NOT NULL DEFAULT 300,
  ADD COLUMN prep_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 20,
  ADD CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id);

CREATE TABLE IF NOT EXISTS admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  login VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS positions (
  id TINYINT UNSIGNED PRIMARY KEY,
  name VARCHAR(40) NOT NULL UNIQUE
) ENGINE=InnoDB;
INSERT IGNORE INTO positions (id, name) VALUES (1, 'Пекарь'), (2, 'Курьер'), (3, 'Менеджер');

CREATE TABLE IF NOT EXISTS staff (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  position_id TINYINT UNSIGNED NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  address VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  birth_date DATE DEFAULT NULL,
  FOREIGN KEY (position_id) REFERENCES positions(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code CHAR(12) NOT NULL UNIQUE,
  customer_name VARCHAR(80) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  pickup_at DATETIME NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Новый',
  total INT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  user_id INT UNSIGNED DEFAULT NULL,
  staff_id INT UNSIGNED DEFAULT NULL,
  fulfillment VARCHAR(20) NOT NULL DEFAULT 'pickup',
  delivery_address VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (staff_id) REFERENCES staff(id),
  INDEX (phone), INDEX (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS shopping_cart (
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, product_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS password_resets (
  user_id INT UNSIGNED NOT NULL PRIMARY KEY,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity TINYINT UNSIGNED NOT NULL,
  unit_price INT UNSIGNED NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS feedback (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO products (name, category, description, ingredients, allergens, price) VALUES
('Хлеб на закваске', 'Хлеб', 'Пшеничный хлеб с плотной корочкой и мягким мякишем.', 'Пшеница, вода, закваска, соль', 'глютен', 240),
('Круассан', 'Сладкое', 'Слоёное тесто со сливочным маслом.', 'Пшеница, масло, молоко, яйцо', 'глютен, молоко, яйцо', 190),
('Булочка с корицей', 'Сладкое', 'Булочка с корицей и лёгкой глазурью.', 'Пшеница, молоко, корица, сахар', 'глютен, молоко', 210),
('Ржаной хлеб', 'Хлеб', 'Хлеб из ржаной и пшеничной муки.', 'Рожь, пшеница, вода, соль', 'глютен', 270),
('Пирог с капустой', 'Сытное', 'Домашний пирог с капустой.', 'Пшеница, капуста, яйцо, масло', 'глютен, яйцо, молоко', 320),
('Слойка с сыром', 'Сытное', 'Слоёное тесто и сырная начинка.', 'Пшеница, сыр, масло', 'глютен, молоко', 230);

UPDATE products p JOIN categories c ON c.name = p.category SET p.category_id = c.id WHERE p.category_id IS NULL;
UPDATE products SET weight_g = CASE id WHEN 1 THEN 500 WHEN 2 THEN 90 WHEN 3 THEN 120 WHEN 4 THEN 550 WHEN 5 THEN 320 ELSE 110 END,
  prep_minutes = CASE id WHEN 1 THEN 180 WHEN 2 THEN 35 WHEN 3 THEN 40 WHEN 4 THEN 200 WHEN 5 THEN 50 ELSE 35 END;

CREATE OR REPLACE VIEW catalog_view AS
SELECT p.id, p.name, c.name AS category, p.description, p.price, p.weight_g, p.prep_minutes, p.available
FROM products p JOIN categories c ON c.id = p.category_id;

CREATE OR REPLACE VIEW sales_report AS
SELECT DATE(o.created_at) AS sale_date, o.id AS order_id, o.code, o.status, o.total,
       COUNT(oi.id) AS line_count, SUM(oi.quantity) AS item_count,
       s.full_name AS responsible_staff
FROM orders o JOIN order_items oi ON oi.order_id = o.id
LEFT JOIN staff s ON s.id = o.staff_id
GROUP BY DATE(o.created_at), o.id, o.code, o.status, o.total, s.full_name;
