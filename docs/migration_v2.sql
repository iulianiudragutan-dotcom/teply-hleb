USE teply_hleb;

CREATE TABLE access_rights (id TINYINT UNSIGNED PRIMARY KEY, name VARCHAR(20) NOT NULL UNIQUE) ENGINE=InnoDB;
INSERT INTO access_rights VALUES (1, 'USER'), (2, 'ADMIN');
CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  access_right_id TINYINT UNSIGNED NOT NULL DEFAULT 1,
  phone VARCHAR(20) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (access_right_id) REFERENCES access_rights(id)
) ENGINE=InnoDB;
CREATE TABLE categories (id TINYINT UNSIGNED PRIMARY KEY, name VARCHAR(30) NOT NULL UNIQUE) ENGINE=InnoDB;
INSERT INTO categories VALUES (1, 'Хлеб'), (2, 'Сладкое'), (3, 'Сытное');
ALTER TABLE products
  ADD COLUMN category_id TINYINT UNSIGNED NULL,
  ADD COLUMN weight_g SMALLINT UNSIGNED NOT NULL DEFAULT 300,
  ADD COLUMN prep_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 20,
  ADD CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id);
UPDATE products p JOIN categories c ON c.name = p.category SET p.category_id = c.id WHERE p.category_id IS NULL;
UPDATE products SET weight_g = CASE id WHEN 1 THEN 500 WHEN 2 THEN 90 WHEN 3 THEN 120 WHEN 4 THEN 550 WHEN 5 THEN 320 ELSE 110 END,
  prep_minutes = CASE id WHEN 1 THEN 180 WHEN 2 THEN 35 WHEN 3 THEN 40 WHEN 4 THEN 200 WHEN 5 THEN 50 ELSE 35 END WHERE id BETWEEN 1 AND 6;

CREATE TABLE positions (id TINYINT UNSIGNED PRIMARY KEY, name VARCHAR(40) NOT NULL UNIQUE) ENGINE=InnoDB;
INSERT INTO positions VALUES (1, 'Пекарь'), (2, 'Курьер'), (3, 'Менеджер');
CREATE TABLE staff (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  position_id TINYINT UNSIGNED NOT NULL,
  full_name VARCHAR(120) NOT NULL,
  address VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(20) DEFAULT NULL,
  birth_date DATE DEFAULT NULL,
  FOREIGN KEY (position_id) REFERENCES positions(id)
) ENGINE=InnoDB;
ALTER TABLE orders
  ADD COLUMN user_id INT UNSIGNED DEFAULT NULL,
  ADD COLUMN staff_id INT UNSIGNED DEFAULT NULL,
  ADD COLUMN fulfillment VARCHAR(20) NOT NULL DEFAULT 'pickup',
  ADD COLUMN delivery_address VARCHAR(255) DEFAULT NULL,
  ADD CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id),
  ADD CONSTRAINT fk_order_staff FOREIGN KEY (staff_id) REFERENCES staff(id);
CREATE TABLE shopping_cart (
  user_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  quantity TINYINT UNSIGNED NOT NULL,
  PRIMARY KEY (user_id, product_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;
CREATE TABLE password_resets (
  user_id INT UNSIGNED NOT NULL PRIMARY KEY,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

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
