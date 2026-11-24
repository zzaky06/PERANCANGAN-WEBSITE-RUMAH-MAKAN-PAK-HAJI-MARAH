<?php
// Seed sample tables and a sample reservation for local dev/testing
header('Content-Type: application/json; charset=utf-8');

$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'project';

function respond($data) {
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    respond(['ok' => false, 'error' => $mysqli->connect_error]);
}
$mysqli->set_charset('utf8mb4');

// Ensure tables exist
$mysqli->query("CREATE TABLE IF NOT EXISTS tables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL UNIQUE,
    kapasitas INT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'tersedia',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$now = date('Y-m-d H:i:s');
$res = $mysqli->query("SELECT COUNT(*) AS c FROM tables");
$row = $res->fetch_assoc();
$cnt = intval($row['c']);

$inserted = [];
if ($cnt === 0) {
    $names = ['Meja 1','Meja 2','Meja 3','Meja 4','Meja 5','Meja 6','Meja 7','Meja 8'];
    $stmt = $mysqli->prepare('INSERT INTO tables (nama, kapasitas, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');
    foreach ($names as $i => $n) {
        $kap = 4;
        // mark some as terisi for demo
        $status = in_array($i+1, [4,8]) ? 'terisi' : 'tersedia';
        $stmt->bind_param('sisss', $n, $kap, $status, $now, $now);
        $stmt->execute();
        $inserted[] = $n;
    }
    $stmt->close();
}

// Ensure reservations table exists
$mysqli->query("CREATE TABLE IF NOT EXISTS reservations (
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
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Insert a sample user + reservation if none exist
$res2 = $mysqli->query("SELECT COUNT(*) AS c FROM reservations");
$row2 = $res2->fetch_assoc();
$cnt2 = intval($row2['c']);
$sample = [];
if ($cnt2 === 0) {
    // Create user
    $no_hp = '081234567890';
    $nama = 'Budi Santoso';
    $mysqli->query("CREATE TABLE IF NOT EXISTS users (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        no_hp VARCHAR(30) NOT NULL UNIQUE,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $stmtu = $mysqli->prepare('INSERT INTO users (nama, no_hp, created_at, updated_at) VALUES (?, ?, ?, ?)');
    $stmtu->bind_param('ssss', $nama, $no_hp, $now, $now);
    $stmtu->execute();
    $userId = $mysqli->insert_id;
    $stmtu->close();

    // find a table id (first available)
    $res3 = $mysqli->query("SELECT id FROM tables ORDER BY id LIMIT 1");
    $trow = $res3->fetch_assoc();
    $table_id = intval($trow['id'] ?? 1);

    // generate code
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i=0;$i<8;$i++) $code .= $chars[random_int(0, strlen($chars)-1)];

    $id = (string) round(microtime(true) * 1000);
    $tanggal = date('Y-m-d');
    $waktu = date('H:i', strtotime('+2 hours'));
    $jumlah_orang = 2;
    $catatan = 'Contoh reservasi';
    $status = 'confirmed';

    $stmtr = $mysqli->prepare('INSERT INTO reservations (id, user_id, table_id, nama, no_hp, reservasi_code, tanggal, waktu, jumlah_orang, catatan, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmtr->bind_param('ssisssssissss', $id, $userId, $table_id, $nama, $no_hp, $code, $tanggal, $waktu, $jumlah_orang, $catatan, $status, $now, $now);
    $stmtr->execute();
    $stmtr->close();

    // mark table as terisi
    $stmtu2 = $mysqli->prepare('UPDATE tables SET status = ?, updated_at = ? WHERE id = ?');
    $st = 'terisi';
    $stmtu2->bind_param('ssi', $st, $now, $table_id);
    $stmtu2->execute();
    $stmtu2->close();

    $sample[] = ['no_hp' => $no_hp, 'nama' => $nama, 'reservasi_code' => $code];
}

$mysqli->close();
respond(['ok' => true, 'inserted_tables' => $inserted, 'sample_reservations' => $sample, 'note' => 'Jalankan http://localhost/bud/reservasi.html lalu buka tab Riwayat. Jika tidak melihat Riwayat, bersihkan localStorage atau lakukan satu reservasi melalui UI.']);
