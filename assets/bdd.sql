-- ----------------------------------------------------------
-- Script MYSQL pour mcd 
-- ----------------------------------------------------------


-- ----------------------------
-- Table: users
-- ----------------------------
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL,
  passwordHash VARCHAR(255) NOT NULL,
  UNIQUE KEY (email)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ----------------------------
-- Table: products
-- ----------------------------
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  category VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  location VARCHAR(255) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  user_id INT NOT NULL DEFAULT 1,
  FOREIGN KEY (user_id) REFERENCES users(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ----------------------------
-- Table: categories
-- ----------------------------
CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Catégories par défaut
INSERT INTO categories (name) VALUES 
('Électronique'),
('Outils & Bricolage'),
('Sport'),
('Mobilité'),
('Musique');


-- ----------------------------
-- Table: rents (Locations)
-- ----------------------------
CREATE TABLE rents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  user_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  total_price DECIMAL(10,2) NOT NULL,
  status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

