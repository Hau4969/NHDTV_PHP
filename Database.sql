-- =========================================
-- INIT DATABASE
-- =========================================
CREATE DATABASE ql_cinema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ql_cinema;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =========================================
-- 1) BASE / ROOT TABLES (NO FK)
-- =========================================

-- roles
CREATE TABLE roles (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- membership_levels
CREATE TABLE membership_levels (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  min_points INT(11) NOT NULL DEFAULT 0,
  discount_percent INT(11) NOT NULL DEFAULT 0,
  perks LONGTEXT NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_membership_levels_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- seat_types
CREATE TABLE seat_types (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  description VARCHAR(255) NULL,
  base_price INT(10) NOT NULL DEFAULT 80000,
  PRIMARY KEY (id),
  UNIQUE KEY uq_seat_types_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- movies
CREATE TABLE movies (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  title VARCHAR(200) NOT NULL,
  genre VARCHAR(100) NULL,
  duration INT(11) NULL,
  release_date DATE NULL,
  director VARCHAR(100) NULL,
  cast TEXT NULL,
  summary TEXT NULL,
  poster_url VARCHAR(255) NULL,
  poster_thumb VARCHAR(255) NULL,
  trailer_url VARCHAR(255) NULL,
  status ENUM('draft', 'published') NULL DEFAULT 'published',
  is_now_showing TINYINT(1) NULL DEFAULT 1,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_movies_nowshow (is_now_showing),
  KEY idx_movies_release (release_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 2) TABLES DEPENDING ON BASE
-- =========================================

-- users (FK -> roles)
CREATE TABLE users (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  phone VARCHAR(15) NULL DEFAULT NULL,
  birthday DATE NULL,
  address VARCHAR(255) NULL DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  role_id INT(11) NOT NULL DEFAULT 2,
  remember_token VARCHAR(100) NULL DEFAULT NULL,
  email_verified_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_phone (phone),
  KEY fk_users_role (role_id),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- rooms
CREATE TABLE rooms (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  total_seats INT(11) NOT NULL DEFAULT 50,
  capacity INT(11) NOT NULL DEFAULT 0,
  seat_layout LONGTEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_rooms_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- audit_logs (FK -> users)
CREATE TABLE audit_logs (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  user_id BIGINT(20) NULL,
  action VARCHAR(50) NOT NULL,
  entity_type VARCHAR(100) NULL,
  entity_id BIGINT(20) NULL,
  meta LONGTEXT NULL,
  ip VARCHAR(45) NULL,
  user_agent TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_user (user_id),
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 3) SEATS, SHOWTIMES, PRICING
-- =========================================

-- seats (FK -> rooms, seat_types)
CREATE TABLE seats (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  room_id BIGINT(20) NOT NULL,
  row_label VARCHAR(5) NULL,
  row_letter VARCHAR(5) NULL,
  seat_number INT(11) NOT NULL,
  seat_code VARCHAR(10) NULL,
  code VARCHAR(32) NULL,
  label VARCHAR(50) NULL,
  seat_type_id BIGINT(20) NOT NULL,
  status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_room_seat (room_id, row_letter, seat_number),
  KEY idx_seats_room (room_id),
  KEY idx_seats_type (seat_type_id),
  CONSTRAINT fk_seats_room FOREIGN KEY (room_id) REFERENCES rooms(id),
  CONSTRAINT fk_seats_type FOREIGN KEY (seat_type_id) REFERENCES seat_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- showtimes (FK -> movies, rooms)
CREATE TABLE showtimes (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  movie_id BIGINT(20) NOT NULL,
  room_id BIGINT(20) NOT NULL,
  start_time DATETIME NOT NULL,
  end_time DATETIME NULL,
  language VARCHAR(50) NULL DEFAULT 'VN',
  status ENUM('scheduled', 'ongoing', 'completed', 'cancelled') NULL DEFAULT 'scheduled',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_showtimes_movie (movie_id),
  KEY idx_showtimes_room (room_id),
  KEY idx_showtimes_start_time (start_time),
  CONSTRAINT fk_showtimes_movie FOREIGN KEY (movie_id) REFERENCES movies(id),
  CONSTRAINT fk_showtimes_room FOREIGN KEY (room_id) REFERENCES rooms(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- showtime_prices (FK -> showtimes, seat_types)
CREATE TABLE showtime_prices (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  showtime_id BIGINT(20) NOT NULL,
  seat_type_id BIGINT(20) NOT NULL,
  price_modifier INT(11) NULL DEFAULT 0,
  base_price INT(10) NULL DEFAULT 0,
  price INT(10) NULL DEFAULT 0,
  final_price INT(10) NULL DEFAULT 0,
  note VARCHAR(255) NULL,
  updated_by BIGINT(20) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_showtime_type (showtime_id, seat_type_id),
  KEY idx_stprice_showtime (showtime_id),
  KEY idx_stprice_seattype (seat_type_id),
  CONSTRAINT fk_stprice_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(id),
  CONSTRAINT fk_stprice_seattype FOREIGN KEY (seat_type_id) REFERENCES seat_types(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 4) VOUCHERS & TICKETS & PAYMENTS
-- =========================================

-- vouchers
CREATE TABLE vouchers (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  code VARCHAR(40) NOT NULL,
  type ENUM('percent', 'fixed') NOT NULL,
  value DECIMAL(10,2) NOT NULL,
  min_order INT(11) NULL,
  start_at DATETIME NULL,
  end_at DATETIME NULL,
  usage_limit INT(11) NULL,
  used_count INT(11) NOT NULL DEFAULT 0,
  status ENUM('active', 'inactive', 'expired') NOT NULL DEFAULT 'active',
  meta LONGTEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_vouchers_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- tickets (FK -> users, showtimes, seats, vouchers)
CREATE TABLE tickets (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  user_id BIGINT(20) NOT NULL,
  showtime_id BIGINT(20) NOT NULL,
  seat_id BIGINT(20) NOT NULL,
  ticket_type VARCHAR(20) NULL DEFAULT 'adult',
  price DECIMAL(10,2) NOT NULL,
  final_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  voucher_id BIGINT(20) NULL DEFAULT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'reserved',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ticket_unique (showtime_id, seat_id),
  KEY idx_tickets_user (user_id),
  KEY idx_tickets_voucher (voucher_id),
  KEY idx_tickets_showtime_status (showtime_id, status),
  CONSTRAINT fk_tickets_user     FOREIGN KEY (user_id)     REFERENCES users(id),
  CONSTRAINT fk_tickets_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(id),
  CONSTRAINT fk_tickets_seat_fk  FOREIGN KEY (seat_id)     REFERENCES seats(id),
  CONSTRAINT fk_tickets_voucher  FOREIGN KEY (voucher_id)  REFERENCES vouchers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- payments (FK -> tickets + optional to user/showtime/voucher)
CREATE TABLE payments (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  user_id BIGINT(20) NOT NULL,
  showtime_id BIGINT(20) NULL,
  voucher_id BIGINT(20) NULL,
  reference VARCHAR(50) NULL,
  ticket_id BIGINT(20) NULL,
  amount DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(50) NULL DEFAULT 'cash',
  status ENUM('pending', 'completed', 'failed') NULL DEFAULT 'pending',
  expires_at DATETIME NULL,
  txn_id VARCHAR(100) NULL,
  paid_at DATETIME NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_payment_ticket (ticket_id),
  KEY idx_pay_user (user_id),
  KEY idx_pay_showtime (showtime_id),
  KEY idx_pay_voucher (voucher_id),
  CONSTRAINT fk_payments_ticket   FOREIGN KEY (ticket_id)   REFERENCES tickets(id),
  CONSTRAINT fk_payments_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_payments_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_payments_voucher  FOREIGN KEY (voucher_id)  REFERENCES vouchers(id)  ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================
-- 5) EXTENSIONS
-- =========================================

-- comments (FK -> users, movies)
CREATE TABLE comments (
  id BIGINT(20) NOT NULL AUTO_INCREMENT,
  movie_id BIGINT(20) NOT NULL,
  user_id BIGINT(20) NOT NULL,
  content TEXT NOT NULL,
  rating INT(11) NULL DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_comments_movie (movie_id),
  KEY fk_comments_user (user_id),
  CONSTRAINT fk_comments_movie FOREIGN KEY (movie_id) REFERENCES movies(id),
  CONSTRAINT fk_comments_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- voucher_usages (FK -> vouchers, showtimes ; user_id optional)
CREATE TABLE voucher_usages (
  id INT(11) NOT NULL AUTO_INCREMENT,
  voucher_id BIGINT(20) NOT NULL,
  user_id BIGINT(20) NULL,
  showtime_id BIGINT(20) NOT NULL,
  seats_count INT(11) NOT NULL DEFAULT 1,
  subtotal INT(11) NOT NULL,
  discount INT(11) NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_voucher_usages_voucher (voucher_id),
  KEY idx_voucher_usages_showtime (showtime_id),
  KEY idx_voucher_usages_user (user_id),
  CONSTRAINT fk_voucher_usages_voucher  FOREIGN KEY (voucher_id)  REFERENCES vouchers(id),
  CONSTRAINT fk_voucher_usages_showtime FOREIGN KEY (showtime_id) REFERENCES showtimes(id),
  CONSTRAINT fk_voucher_usages_user     FOREIGN KEY (user_id)     REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- sessions
CREATE TABLE sessions (
  id VARCHAR(255) NOT NULL,
  user_id BIGINT(20) NULL,
  ip_address VARCHAR(45) NULL,
  user_agent TEXT NULL,
  payload LONGTEXT NOT NULL,
  last_activity INT(11) NOT NULL,
  PRIMARY KEY (id),
  KEY sessions_user_id_index (user_id),
  KEY sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- cache
CREATE TABLE cache (
  `key` VARCHAR(191) NOT NULL,
  `value` MEDIUMTEXT NOT NULL,
  `expiration` INT(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================
-- SEED CƠ BẢN
-- =========================================
INSERT INTO roles (id, name) VALUES (1,'Admin'),(2,'Customer')
  ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO seat_types (id, name, description, base_price) VALUES
  (1,'Thường','Ghế ngồi tiêu chuẩn',80000),
  (2,'VIP','Ghế VIP cao cấp',110000),
  (3,'Đôi','Ghế đôi 2 người',150000)
ON DUPLICATE KEY UPDATE description=VALUES(description), base_price=VALUES(base_price);

ALTER TABLE users
ADD COLUMN avatar VARCHAR(255) NULL AFTER address;

ALTER TABLE tickets
ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0,
ADD COLUMN membership_discount_rate DECIMAL(5,2) DEFAULT 0,
ADD COLUMN qr_code VARCHAR(255) NULL;
