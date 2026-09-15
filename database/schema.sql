CREATE DATABASE IF NOT EXISTS pachuco_restaurante
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE pachuco_restaurante;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS reservations;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS restaurant_tables;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(25),
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  description VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE restaurant_tables (
  id INT AUTO_INCREMENT PRIMARY KEY,
  table_number INT NOT NULL UNIQUE,
  capacity INT NOT NULL,
  location VARCHAR(80) NOT NULL,
  status ENUM('available', 'reserved', 'occupied') NOT NULL DEFAULT 'available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_menu_category FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  table_id INT NOT NULL,
  reservation_date DATE NOT NULL,
  reservation_time TIME NOT NULL,
  guests INT NOT NULL,
  status ENUM('pendiente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendiente',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_reservation_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_reservation_table FOREIGN KEY (table_id) REFERENCES restaurant_tables(id)
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  order_type ENUM('mesa', 'recoger', 'entrega') NOT NULL DEFAULT 'mesa',
  status ENUM('pendiente', 'preparando', 'entregado', 'cancelado') NOT NULL DEFAULT 'pendiente',
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  menu_item_id INT NOT NULL,
  quantity INT NOT NULL,
  unit_price DECIMAL(10,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_item_order FOREIGN KEY (order_id) REFERENCES orders(id),
  CONSTRAINT fk_item_menu FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

INSERT INTO users (name, email, phone, password_hash, role) VALUES
('Administrador Pachuco', 'admin@pachuco.test', '555-0101', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'admin'),
('Ana Torres', 'ana@pachuco.test', '555-0102', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Luis Ramos', 'luis@pachuco.test', '555-0103', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('María León', 'maria@pachuco.test', '555-0104', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Carlos Vega', 'carlos@pachuco.test', '555-0105', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Sofía Cruz', 'sofia@pachuco.test', '555-0106', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Jorge Silva', 'jorge@pachuco.test', '555-0107', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Paola Núñez', 'paola@pachuco.test', '555-0108', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Diego Mora', 'diego@pachuco.test', '555-0109', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Laura Peña', 'laura@pachuco.test', '555-0110', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Iván Santos', 'ivan@pachuco.test', '555-0111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Clara Ríos', 'clara@pachuco.test', '555-0112', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Mateo Díaz', 'mateo@pachuco.test', '555-0113', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Elena Solís', 'elena@pachuco.test', '555-0114', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer'),
('Raúl Méndez', 'raul@pachuco.test', '555-0115', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi', 'customer');

INSERT INTO categories (name, description) VALUES
('Entradas', 'Botanas y primeros tiempos'),
('Tacos', 'Tacos de la casa'),
('Especialidades', 'Platillos fuertes mexicanos'),
('Sopas', 'Caldos y sopas'),
('Ensaladas', 'Opciones frescas'),
('Postres', 'Dulces de cierre'),
('Bebidas', 'Aguas y refrescos'),
('Cafetería', 'Café y bebidas calientes'),
('Cocteles', 'Bebidas preparadas'),
('Desayunos', 'Platillos matutinos'),
('Mariscos', 'Sabores del mar'),
('Infantil', 'Porciones para niños'),
('Vegano', 'Opciones sin productos animales'),
('Guarniciones', 'Complementos'),
('Promociones', 'Combos y paquetes');

INSERT INTO restaurant_tables (table_number, capacity, location, status) VALUES
(1, 2, 'Ventana', 'available'),
(2, 4, 'Centro', 'reserved'),
(3, 4, 'Centro', 'available'),
(4, 6, 'Terraza', 'occupied'),
(5, 2, 'Barra', 'available'),
(6, 8, 'Salón principal', 'reserved'),
(7, 4, 'Ventana', 'available'),
(8, 6, 'Terraza', 'available'),
(9, 2, 'Barra', 'occupied'),
(10, 10, 'Salón privado', 'available'),
(11, 4, 'Salón principal', 'reserved'),
(12, 6, 'Salón principal', 'available'),
(13, 2, 'Terraza', 'available'),
(14, 4, 'Patio', 'occupied'),
(15, 8, 'Patio', 'available');

INSERT INTO menu_items (category_id, name, description, price, is_available) VALUES
(1, 'Guacamole de la casa', 'Aguacate, pico de gallo y totopos.', 95.00, 1),
(1, 'Queso fundido', 'Queso gratinado con chorizo.', 120.00, 1),
(2, 'Tacos al pastor', 'Orden de tres tacos con piña.', 105.00, 1),
(2, 'Tacos de arrachera', 'Orden de tres tacos con salsa tatemada.', 145.00, 1),
(3, 'Mole poblano', 'Pollo con mole, arroz y ajonjolí.', 185.00, 1),
(3, 'Enchiladas verdes', 'Rellenas de pollo con crema y queso.', 155.00, 1),
(4, 'Sopa de tortilla', 'Con aguacate, queso y chile pasilla.', 110.00, 1),
(5, 'Ensalada nopales', 'Nopal, jitomate, queso fresco y cilantro.', 98.00, 1),
(6, 'Flan napolitano', 'Flan casero con caramelo.', 78.00, 1),
(7, 'Agua de jamaica', 'Agua fresca natural.', 38.00, 1),
(8, 'Café de olla', 'Café con canela y piloncillo.', 45.00, 1),
(9, 'Margarita clásica', 'Tequila, limón y sal.', 135.00, 1),
(10, 'Chilaquiles rojos', 'Totopos, salsa roja, crema y queso.', 125.00, 1),
(11, 'Tostada de camarón', 'Camarón, aguacate y salsa fresca.', 150.00, 1),
(13, 'Tacos de setas', 'Setas al ajillo con salsa verde.', 118.00, 1);

INSERT INTO reservations (user_id, table_id, reservation_date, reservation_time, guests, status, notes) VALUES
(2, 1, '2026-09-16', '14:00:00', 2, 'confirmada', 'Cumpleaños'),
(3, 2, '2026-09-16', '15:30:00', 4, 'pendiente', 'Silla para bebé'),
(4, 3, '2026-09-17', '20:00:00', 4, 'confirmada', ''),
(5, 4, '2026-09-17', '21:00:00', 6, 'pendiente', 'Terraza'),
(6, 5, '2026-09-18', '13:00:00', 2, 'confirmada', ''),
(7, 6, '2026-09-18', '19:30:00', 8, 'cancelada', 'Cambio de planes'),
(8, 7, '2026-09-19', '18:00:00', 4, 'confirmada', ''),
(9, 8, '2026-09-19', '20:30:00', 6, 'pendiente', ''),
(10, 9, '2026-09-20', '14:30:00', 2, 'confirmada', ''),
(11, 10, '2026-09-20', '21:30:00', 10, 'pendiente', 'Cena de equipo'),
(12, 11, '2026-09-21', '16:00:00', 4, 'confirmada', ''),
(13, 12, '2026-09-21', '19:00:00', 6, 'pendiente', ''),
(14, 13, '2026-09-22', '13:30:00', 2, 'confirmada', ''),
(15, 14, '2026-09-22', '18:30:00', 4, 'cancelada', ''),
(2, 15, '2026-09-23', '20:00:00', 8, 'pendiente', 'Cerca de música');

INSERT INTO orders (user_id, order_type, status, total, notes) VALUES
(2, 'mesa', 'entregado', 200.00, 'Sin cebolla'),
(3, 'recoger', 'preparando', 145.00, ''),
(4, 'entrega', 'pendiente', 185.00, 'Tocar timbre'),
(5, 'mesa', 'entregado', 155.00, ''),
(6, 'recoger', 'pendiente', 110.00, ''),
(7, 'mesa', 'cancelado', 98.00, 'Cliente canceló'),
(8, 'entrega', 'preparando', 78.00, ''),
(9, 'mesa', 'entregado', 76.00, ''),
(10, 'recoger', 'pendiente', 45.00, ''),
(11, 'mesa', 'preparando', 135.00, ''),
(12, 'entrega', 'pendiente', 125.00, 'Extra salsa'),
(13, 'mesa', 'entregado', 150.00, ''),
(14, 'recoger', 'preparando', 118.00, ''),
(15, 'mesa', 'pendiente', 240.00, 'Dos bebidas'),
(2, 'entrega', 'entregado', 290.00, 'Pago con tarjeta');

INSERT INTO order_items (order_id, menu_item_id, quantity, unit_price) VALUES
(1, 1, 1, 95.00),
(1, 3, 1, 105.00),
(2, 4, 1, 145.00),
(3, 5, 1, 185.00),
(4, 6, 1, 155.00),
(5, 7, 1, 110.00),
(6, 8, 1, 98.00),
(7, 9, 1, 78.00),
(8, 10, 2, 38.00),
(9, 11, 1, 45.00),
(10, 12, 1, 135.00),
(11, 13, 1, 125.00),
(12, 14, 1, 150.00),
(13, 15, 1, 118.00),
(14, 2, 2, 120.00),
(15, 4, 2, 145.00);
