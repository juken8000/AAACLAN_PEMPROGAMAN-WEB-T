CREATE DATABASE IF NOT EXISTS kostrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kostrack;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS bills;
DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS tenants;
DROP TABLE IF EXISTS rooms;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_depan VARCHAR(100) DEFAULT NULL,
  nama_belakang VARCHAR(100) DEFAULT NULL,
  no_hp VARCHAR(20) DEFAULT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'penghuni',
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_number VARCHAR(20) NOT NULL UNIQUE,
  type ENUM('AC','NON-AC') NOT NULL,
  price DECIMAL(12,2) NOT NULL,
  status ENUM('kosong','terisi') NOT NULL DEFAULT 'kosong',
  image VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tenants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  room_id INT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE DEFAULT NULL,
  status ENUM('aktif','keluar') NOT NULL DEFAULT 'aktif',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tenant_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_tenant_room FOREIGN KEY (room_id) REFERENCES rooms(id)
) ENGINE=InnoDB;

CREATE TABLE bills (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  period_month TINYINT NOT NULL,
  period_year SMALLINT NOT NULL,
  description VARCHAR(150) NOT NULL DEFAULT 'Tagihan sewa kost',
  amount DECIMAL(12,2) NOT NULL,
  status ENUM('belum_lunas','lunas') NOT NULL DEFAULT 'belum_lunas',
  due_date DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_bill_period (tenant_id, period_month, period_year),
  CONSTRAINT fk_bill_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  bill_id INT NOT NULL,
  tenant_id INT NOT NULL,
  paid_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  amount DECIMAL(12,2) NOT NULL,
  status ENUM('lunas','belum_lunas') NOT NULL DEFAULT 'lunas',
  CONSTRAINT fk_payment_bill FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
  CONSTRAINT fk_payment_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE complaints (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  message TEXT NOT NULL,
  response TEXT DEFAULT NULL,
  status ENUM('pending','selesai') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  responded_at DATETIME DEFAULT NULL,
  CONSTRAINT fk_complaint_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE expenses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  description VARCHAR(150) NOT NULL,
  expense_date DATE NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO users (nama_depan, nama_belakang, no_hp, role, email, password) VALUES
('althaf', 'ismail', '081519204077', 'owner', 'althafzabran2505@gmail.com', '$2y$10$EqCXs2IUVUDAvE8GhD5KPedRBRthGWaA6sZAGlrcMtziR7/asgPRq'),
('alfin', 'lambok', '081519204088', 'owner', 'alfin@gmail.com', '$2y$10$/9irwzm9nSy36oMhZLhmhetT2W7c4oiFUDm4hrCM7F3hDSgcLB.sa'),
('Owner', 'KOSTRACK', '081234567890', 'owner', 'owner@kostrack.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Andi', 'Saputra', '081111111111', 'penghuni', 'andi@kostrack.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Sari', 'Dewi', '082222222222', 'penghuni', 'sari@kostrack.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO rooms (room_number, type, price, status, image) VALUES
('A01', 'AC', 1200000, 'terisi', NULL),
('A02', 'AC', 1200000, 'kosong', NULL),
('B01', 'NON-AC', 850000, 'terisi', NULL),
('B02', 'NON-AC', 850000, 'kosong', NULL);

INSERT INTO tenants (user_id, room_id, start_date, status) VALUES
(4, 1, '2026-01-10', 'aktif'),
(5, 3, '2026-02-01', 'aktif');

INSERT INTO bills (tenant_id, period_month, period_year, description, amount, status, due_date) VALUES
(1, 5, 2026, 'Tagihan sewa Mei 2026', 1200000, 'lunas', '2026-05-10'),
(1, 4, 2026, 'Tagihan sewa April 2026', 1200000, 'lunas', '2026-04-10'),
(2, 5, 2026, 'Tagihan sewa Mei 2026', 850000, 'belum_lunas', '2026-05-10');

INSERT INTO payments (bill_id, tenant_id, paid_at, amount, status) VALUES
(1, 1, '2026-05-05 09:00:00', 1200000, 'lunas'),
(2, 1, '2026-04-06 11:30:00', 1200000, 'lunas');

INSERT INTO complaints (tenant_id, message, response, status, created_at, responded_at) VALUES
(1, 'Lampu kamar mandi redup dan perlu diganti.', 'Baik, teknisi akan datang sore ini.', 'selesai', '2026-05-15 13:00:00', '2026-05-15 15:00:00'),
(2, 'Air keran kamar B01 kecil sejak pagi.', NULL, 'pending', '2026-05-20 08:15:00', NULL);

INSERT INTO expenses (description, expense_date, amount) VALUES
('Tagihan listrik', '2026-05-12', 450000),
('Perbaikan pompa air', '2026-05-18', 250000);
