<?php
header('Content-Type: application/json; charset=utf-8');

$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'project';

function respond($data) {
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$mysqli = @new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($mysqli->connect_errno) {
    respond(['ok' => false, 'error' => 'Gagal koneksi DB: ' . $mysqli->connect_error]);
}
$mysqli->set_charset('utf8mb4');

$tablesToCheck = ['users', 'tables', 'reservations', 'orders', 'menu_items', 'menu_images', 'table_images', 'restaurant_status'];
$status = [];
foreach ($tablesToCheck as $t) {
    $res = $mysqli->query("SHOW TABLES LIKE '" . $mysqli->real_escape_string($t) . "'");
    $exists = ($res && $res->num_rows > 0);
    $status[$t] = ['exists' => $exists];
    if ($exists) {
        $countRes = $mysqli->query("SELECT COUNT(*) AS c FROM `" . $mysqli->real_escape_string($t) . "`");
        if ($countRes) {
            $row = $countRes->fetch_assoc();
            $status[$t]['rows'] = intval($row['c']);
            $countRes->free();
        } else {
            $status[$t]['rows'] = null;
        }
    }
    if ($res) $res->free();
}

// Fetch a few sample reservations if table exists
$samples = [];
if (!empty($status['reservations']) && $status['reservations']['exists']) {
    $q = "SELECT r.id, r.table_id, r.nama, r.no_hp, r.reservasi_code, r.tanggal, r.waktu, r.jumlah_orang, r.catatan, r.status, r.created_at, t.nama AS table_name FROM reservations r LEFT JOIN tables t ON r.table_id = t.id ORDER BY r.created_at DESC LIMIT 10";
    $res = $mysqli->query($q);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            // coerce null table_name to empty string
            if (!isset($row['table_name']) || $row['table_name'] === null) $row['table_name'] = '';
            $samples[] = $row;
        }
        $res->free();
    }
}

$mysqli->close();

respond(['ok' => true, 'db' => $dbName, 'status' => $status, 'sample_reservations' => $samples]);
