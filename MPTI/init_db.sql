CREATE DATABASE IF NOT EXISTS project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE project;

CREATE TABLE IF NOT EXISTS orders (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NULL,
    nama VARCHAR(255) NOT NULL,
    no_hp VARCHAR(30) NOT NULL,
    jenis_pesanan VARCHAR(50) NOT NULL,
    metode_pembayaran VARCHAR(50) NOT NULL,
    items TEXT NOT NULL,
    total_harga DECIMAL(12,2) NOT NULL,
    status VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_no_hp (no_hp),
    INDEX idx_user_id (user_id)
);

CREATE TABLE IF NOT EXISTS users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    no_hp VARCHAR(30) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
);

CREATE TABLE IF NOT EXISTS restaurant_status (
    id TINYINT PRIMARY KEY,
    status VARCHAR(20) NOT NULL,
    updated_at DATETIME NOT NULL
);

INSERT INTO restaurant_status (id, status, updated_at)
SELECT 1, 'open', NOW()
WHERE NOT EXISTS (SELECT 1 FROM restaurant_status WHERE id = 1);

CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    harga DECIMAL(12,2) NOT NULL,
    stock_status VARCHAR(20) NOT NULL DEFAULT 'tersedia',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_stock_status (stock_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL UNIQUE,
    kapasitas INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'tersedia',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reservations (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NULL,
    table_id INT NOT NULL,
    nama VARCHAR(255) NOT NULL,
    no_hp VARCHAR(30) NOT NULL,
    reservasi_code VARCHAR(8) NOT NULL UNIQUE,
    tanggal DATE NOT NULL,
    waktu TIME NOT NULL,
    jumlah_orang INT NOT NULL,
    catatan TEXT,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_table_id (table_id),
    INDEX idx_no_hp (no_hp),
    INDEX idx_reservasi_code (reservasi_code),
    INDEX idx_status (status),
    FOREIGN KEY (table_id) REFERENCES tables(id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
