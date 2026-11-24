<?php
header('Content-Type: application/json; charset=utf-8');

// Convert PHP errors/warnings to JSON responses to avoid HTML error pages
ini_set('display_errors', '0');
set_error_handler(function($severity, $message, $file, $line) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => "PHP Error: {$message} in {$file}:{$line}"]);
    exit;
});
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err !== null) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Fatal error: ' . $err['message']]);
        exit;
    }
});

$dbHost = '127.0.0.1';
$dbUser = 'root';
$dbPass = '';
$dbName = 'project';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

function send_response($data, $code = 200)
{
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function send_error($message, $code = 400)
{
    send_response(['success' => false, 'error' => $message], $code);
}

function db_connect($host, $user, $pass, $name)
{
    $mysqli = new mysqli($host, $user, $pass, $name);
    if ($mysqli->connect_errno) {
        send_error('Gagal terkoneksi ke database: ' . $mysqli->connect_error, 500);
    }
    $mysqli->set_charset('utf8mb4');
    return $mysqli;
}

function ensure_status_table($mysqli)
{
    $mysqli->query("CREATE TABLE IF NOT EXISTS restaurant_status (
        id TINYINT PRIMARY KEY,
        status VARCHAR(20) NOT NULL,
        updated_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    $result = $mysqli->query('SELECT COUNT(*) AS total FROM restaurant_status');
    if ($result) {
        $row = $result->fetch_assoc();
        if (!$row || intval($row['total']) === 0) {
            $mysqli->query("INSERT INTO restaurant_status (id, status, updated_at) VALUES (1, 'open', NOW())");
        }
        $result->free();
    }
}

function ensure_users_table($mysqli)
{
    $mysqli->query("CREATE TABLE IF NOT EXISTS users (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        no_hp VARCHAR(30) NOT NULL UNIQUE,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function column_exists($mysqli, $table, $column)
{
    $tableEscaped = $mysqli->real_escape_string($table);
    $columnEscaped = $mysqli->real_escape_string($column);
    $result = $mysqli->query("SHOW COLUMNS FROM `{$tableEscaped}` LIKE '{$columnEscaped}'");
    if (!$result) {
        return false;
    }
    $exists = $result->num_rows > 0;
    $result->free();
    return $exists;
}

function index_exists($mysqli, $table, $index)
{
    $tableEscaped = $mysqli->real_escape_string($table);
    $indexEscaped = $mysqli->real_escape_string($index);
    $result = $mysqli->query("SHOW INDEX FROM `{$tableEscaped}` WHERE Key_name = '{$indexEscaped}'");
    if (!$result) {
        return false;
    }
    $exists = $result->num_rows > 0;
    $result->free();
    return $exists;
}

function ensure_orders_table($mysqli)
{
    $mysqli->query("CREATE TABLE IF NOT EXISTS orders (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if (!column_exists($mysqli, 'orders', 'user_id')) {
        $mysqli->query('ALTER TABLE orders ADD COLUMN user_id BIGINT NULL AFTER id');
    }
    if (!index_exists($mysqli, 'orders', 'idx_no_hp')) {
        $mysqli->query('ALTER TABLE orders ADD INDEX idx_no_hp (no_hp)');
    }
    if (!index_exists($mysqli, 'orders', 'idx_user_id')) {
        $mysqli->query('ALTER TABLE orders ADD INDEX idx_user_id (user_id)');
    }
}

function normalize_phone($phone)
{
    return preg_replace('/[^0-9]/', '', (string) $phone);
}

function generate_reservation_code()
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    for ($i = 0; $i < 8; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

function ensure_menu_items_table($mysqli)
{
    $mysqli->query("CREATE TABLE IF NOT EXISTS menu_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(255) NOT NULL,
        harga DECIMAL(12,2) NOT NULL,
        kategori VARCHAR(50) NOT NULL DEFAULT 'makanan',
        gambar VARCHAR(500) NOT NULL DEFAULT '',
        stock_status VARCHAR(20) NOT NULL DEFAULT 'tersedia',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        INDEX idx_stock_status (stock_status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if (!column_exists($mysqli, 'menu_items', 'kategori')) {
        $mysqli->query("ALTER TABLE menu_items ADD COLUMN kategori VARCHAR(50) NOT NULL DEFAULT 'makanan' AFTER harga");
    }
    if (!column_exists($mysqli, 'menu_items', 'gambar')) {
        $mysqli->query("ALTER TABLE menu_items ADD COLUMN gambar VARCHAR(500) NOT NULL DEFAULT '' AFTER kategori");
    }

    ensure_menu_images_table($mysqli);

    $result = $mysqli->query('SELECT COUNT(*) AS total FROM menu_items');
    if ($result) {
        $row = $result->fetch_assoc();
        $result->free();
        if (!$row || intval($row['total']) === 0) {
            $now = date('Y-m-d H:i:s');
            $items = [
                ['Mie Gacoan Level 1', 13000, 'makanan'],
                ['Mie Gacoan Level 3', 13500, 'makanan'],
                ['Mie Gacoan Level 5', 14000, 'makanan'],
                ['Mie Gacoan Level 8', 14500, 'makanan'],
                ['Mie Suit Level 1', 12000, 'makanan'],
                ['Mie Suit Level 3', 12500, 'makanan'],
                ['Dimsum Ayam', 10000, 'makanan'],
                ['Lumpia Udang', 12000, 'makanan'],
                ['Es Teh Manis', 5000, 'minuman'],
                ['Es Jeruk', 7000, 'minuman'],
                ['Es Milo', 10000, 'minuman'],
                ['Es Kopi Susu', 12000, 'minuman'],
                ['Pangsit Goreng', 10000, 'lainnya'],
                ['Udang Keju', 15000, 'lainnya'],
                ['Tahu Crispy', 8000, 'lainnya'],
                ['Kerupuk', 3000, 'lainnya']
            ];

            $stmt = $mysqli->prepare('INSERT INTO menu_items (nama, harga, kategori, stock_status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
            if ($stmt) {
                foreach ($items as $item) {
                    $nama = $item[0];
                    $harga = $item[1];
                    $kategori = $item[2];
                    $stock_status = 'tersedia';
                    $stmt->bind_param('sdssss', $nama, $harga, $kategori, $stock_status, $now, $now);
                    $stmt->execute();
                }
                $stmt->close();
            }
        }
    }
}

function ensure_menu_images_table($mysqli)
{
    $mysqli->query("CREATE TABLE IF NOT EXISTS menu_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        menu_item_id INT NOT NULL,
        image VARCHAR(500) NOT NULL,
        is_primary TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        INDEX idx_menu_item_id (menu_item_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function ensure_table_images_table($mysqli)
{
    $mysqli->query("CREATE TABLE IF NOT EXISTS table_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        table_id INT NOT NULL,
        image VARCHAR(500) NOT NULL,
        is_primary TINYINT(1) NOT NULL DEFAULT 0,
        created_at DATETIME NOT NULL,
        INDEX idx_table_id (table_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function ensure_uploads_dir()
{
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    return $uploadDir;
}

function save_uploaded_image($file)
{
    if (!isset($file) || !is_array($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];

    $finfoType = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$finfoType])) {
        send_error('Tipe file gambar tidak diperbolehkan. Gunakan JPG, PNG, GIF, atau WEBP.');
    }

    $ext = $allowed[$finfoType];
    $filename = 'menu_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destination = ensure_uploads_dir() . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        send_error('Gagal menyimpan file gambar.', 500);
    }

    return 'uploads/' . $filename;
}

function ensure_tables_table($mysqli)
{
    $mysqli->query("CREATE TABLE IF NOT EXISTS tables (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL UNIQUE,
        kapasitas INT NOT NULL,
        status VARCHAR(20) NOT NULL DEFAULT 'tersedia',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    ensure_table_images_table($mysqli);
}

function ensure_reservations_table($mysqli)
{
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
}

function get_restaurant_status($mysqli)
{
    ensure_status_table($mysqli);
    $result = $mysqli->query('SELECT status FROM restaurant_status WHERE id = 1 LIMIT 1');
    if (!$result) {
        return 'open';
    }
    $row = $result->fetch_assoc();
    $result->free();
    return $row['status'] ?? 'open';
}

function set_restaurant_status($mysqli, $status)
{
    ensure_status_table($mysqli);
    $stmt = $mysqli->prepare('INSERT INTO restaurant_status (id, status, updated_at) VALUES (1, ?, NOW()) ON DUPLICATE KEY UPDATE status = VALUES(status), updated_at = VALUES(updated_at)');
    if (!$stmt) {
        send_error('Gagal menyiapkan status query: ' . $mysqli->error, 500);
    }
    $stmt->bind_param('s', $status);
    if (!$stmt->execute()) {
        send_error('Gagal menyimpan status: ' . $stmt->error, 500);
    }
    $stmt->close();
}

function fetch_orders($mysqli, $phone = '')
{
    if ($phone !== '') {
        $stmt = $mysqli->prepare('SELECT id, nama, no_hp, jenis_pesanan, metode_pembayaran, items, total_harga, status, created_at FROM orders WHERE no_hp = ? ORDER BY created_at DESC');
        $stmt->bind_param('s', $phone);
    } else {
        $stmt = $mysqli->prepare('SELECT id, nama, no_hp, jenis_pesanan, metode_pembayaran, items, total_harga, status, created_at FROM orders ORDER BY created_at DESC');
    }

    if (!$stmt->execute()) {
        send_error('Gagal mengambil data pesanan: ' . $mysqli->error, 500);
    }

    $result = $stmt->get_result();
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    $stmt->close();
    return $orders;
}

if ($method === 'POST' && $action === 'save_order') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        send_error('Input tidak valid');
    }

    $required = ['nama', 'no_hp', 'jenis_pesanan', 'metode_pembayaran', 'items', 'total_harga'];
    foreach ($required as $field) {
        if (!isset($body[$field]) || trim((string) $body[$field]) === '') {
            send_error("Field {$field} diperlukan");
        }
    }

    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    $status = get_restaurant_status($mysqli);
    if ($status !== 'open') {
        $mysqli->close();
        send_error('Restoran tutup. Customer tidak bisa memesan', 403);
    }

    ensure_users_table($mysqli);
    ensure_orders_table($mysqli);

    $nama = trim((string) $body['nama']);
    $no_hp = normalize_phone($body['no_hp']);
    $jenis = trim((string) $body['jenis_pesanan']);
    $metode = trim((string) $body['metode_pembayaran']);
    $items = trim((string) $body['items']);
    $total = floatval($body['total_harga']);
    $status = 'menunggu';
    $created_at = date('Y-m-d H:i:s');
    $id = (string) round(microtime(true) * 1000);

    if ($no_hp === '') {
        send_error('Nomor HP tidak valid');
    }

    $stmtUser = $mysqli->prepare('SELECT id, nama FROM users WHERE no_hp = ? LIMIT 1');
    if (!$stmtUser) {
        send_error('Gagal menyiapkan query user: ' . $mysqli->error, 500);
    }
    $stmtUser->bind_param('s', $no_hp);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    $userId = null;
    $existingName = null;
    if ($row = $resultUser->fetch_assoc()) {
        $userId = (string)$row['id'];
        $existingName = trim((string)$row['nama']);
    }
    $stmtUser->close();

    if ($userId) {
        if ($existingName !== $nama) {
            $stmtUpdateUser = $mysqli->prepare('UPDATE users SET nama = ?, updated_at = ? WHERE id = ?');
            if ($stmtUpdateUser) {
                $now = date('Y-m-d H:i:s');
                $stmtUpdateUser->bind_param('sss', $nama, $now, $userId);
                $stmtUpdateUser->execute();
                $stmtUpdateUser->close();
            }
        }
    } else {
        $stmtInsertUser = $mysqli->prepare('INSERT INTO users (nama, no_hp, created_at, updated_at) VALUES (?, ?, ?, ?)');
        if (!$stmtInsertUser) {
            send_error('Gagal menyiapkan query insert user: ' . $mysqli->error, 500);
        }
        $now = date('Y-m-d H:i:s');
        $stmtInsertUser->bind_param('ssss', $nama, $no_hp, $now, $now);
        if (!$stmtInsertUser->execute()) {
            $stmtInsertUser->close();
            send_error('Gagal menyimpan data user: ' . $stmtInsertUser->error, 500);
        }
        $userId = (string)$mysqli->insert_id;
        $stmtInsertUser->close();
    }

    $stmt = $mysqli->prepare('INSERT INTO orders (id, user_id, nama, no_hp, jenis_pesanan, metode_pembayaran, items, total_harga, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        send_error('Gagal menyiapkan query: ' . $mysqli->error, 500);
    }

    $createdAt = $created_at;
    $stmt->bind_param('sssssssdss', $id, $userId, $nama, $no_hp, $jenis, $metode, $items, $total, $status, $createdAt);
    if (!$stmt->execute()) {
        send_error('Gagal menyimpan pesanan: ' . $stmt->error, 500);
    }

    $stmt->close();
    $mysqli->close();

    send_response([
        'success' => true,
        'order' => [
            'id' => $id,
            'nama' => $nama,
            'no_hp' => $no_hp,
            'jenis_pesanan' => $jenis,
            'metode_pembayaran' => $metode,
            'items' => $items,
            'total_harga' => $total,
            'status' => $status,
            'created_at' => $created_at,
        ]
    ]);
}

if ($method === 'GET' && $action === 'get_status') {
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    $status = get_restaurant_status($mysqli);
    $mysqli->close();
    send_response(['success' => true, 'status' => $status]);
}

if ($method === 'POST' && $action === 'set_status') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body) || !isset($body['status'])) {
        send_error('Input tidak valid untuk status restoran');
    }
    $status = trim((string)$body['status']);
    if (!in_array($status, ['open', 'closed'], true)) {
        send_error('Status restoran harus open atau closed');
    }
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    set_restaurant_status($mysqli, $status);
    $mysqli->close();
    send_response(['success' => true, 'status' => $status]);
}

if ($method === 'GET' && $action === 'get_orders') {
    $phone = normalize_phone($_GET['phone'] ?? '');
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    $orders = fetch_orders($mysqli, $phone);
    $mysqli->close();
    send_response(['success' => true, 'orders' => $orders]);
}

if ($method === 'POST' && $action === 'update_order_status') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body) || !isset($body['id']) || !isset($body['status'])) {
        send_error('Input tidak valid untuk update status pesanan');
    }

    $allowed = ['menunggu', 'diproses', 'sedang_dibuat', 'selesai'];
    $status = trim((string)$body['status']);
    if (!in_array($status, $allowed, true)) {
        send_error('Status pesanan tidak valid');
    }

    $id = $body['id'];
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    $stmt = $mysqli->prepare('UPDATE orders SET status = ? WHERE id = ?');
    if (!$stmt) {
        send_error('Gagal menyiapkan query update: ' . $mysqli->error, 500);
    }
    $stmt->bind_param('ss', $status, $id);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        send_error('Gagal mengupdate status pesanan: ' . $stmt->error, 500);
    }
    $stmt->close();

    // Return updated order row
    $stmt2 = $mysqli->prepare('SELECT id, nama, no_hp, jenis_pesanan, metode_pembayaran, items, total_harga, status, created_at FROM orders WHERE id = ? LIMIT 1');
    if ($stmt2) {
        $stmt2->bind_param('s', $id);
        $stmt2->execute();
        $res = $stmt2->get_result();
        $order = $res->fetch_assoc();
        $stmt2->close();
    } else {
        $order = null;
    }

    $mysqli->close();
    send_response(['success' => true, 'order' => $order]);
}

if ($method === 'GET' && $action === 'get_menu_items') {
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_menu_items_table($mysqli);
    $result = $mysqli->query('SELECT mi.id, mi.nama, mi.harga, mi.kategori, COALESCE(mg.image, mi.gambar) AS gambar, mi.stock_status FROM menu_items mi LEFT JOIN menu_images mg ON mg.menu_item_id = mi.id AND mg.is_primary = 1 ORDER BY mi.id');
    $items = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        $result->free();
    }
    $mysqli->close();
    send_response(['success' => true, 'items' => $items]);
}

if ($method === 'POST' && $action === 'add_menu_item') {
    $gambar = '';
    if (!empty($_FILES['gambar_file']) && is_uploaded_file($_FILES['gambar_file']['tmp_name'])) {
        $gambar = save_uploaded_image($_FILES['gambar_file']);
    }

    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        $body = $_POST;
    }

    $required = ['nama', 'harga', 'kategori'];
    foreach ($required as $field) {
        if (!isset($body[$field]) || trim((string) $body[$field]) === '') {
            send_error("Field {$field} diperlukan");
        }
    }

    $nama = trim((string)$body['nama']);
    $harga = floatval($body['harga']);
    $kategori = trim((string)$body['kategori']);
    $gambarUrl = trim((string)($body['gambar'] ?? ''));
    if ($gambar === '' && $gambarUrl !== '') {
        $gambar = $gambarUrl;
    }

    if ($harga <= 0) {
        send_error('Harga menu harus lebih besar dari 0');
    }

    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_menu_items_table($mysqli);

    $stmt = $mysqli->prepare('INSERT INTO menu_items (nama, harga, kategori, gambar, stock_status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())');
    if (!$stmt) {
        send_error('Gagal menyiapkan query insert menu: ' . $mysqli->error, 500);
    }

    $stockStatus = 'tersedia';
    $stmt->bind_param('sdsss', $nama, $harga, $kategori, $gambar, $stockStatus);
    if (!$stmt->execute()) {
        $stmt->close();
        send_error('Gagal menyimpan menu: ' . $stmt->error, 500);
    }

    $newId = $mysqli->insert_id;
    $stmt->close();

    if ($gambar !== '') {
        $stmtImg = $mysqli->prepare('INSERT INTO menu_images (menu_item_id, image, is_primary, created_at) VALUES (?, ?, 1, NOW())');
        if ($stmtImg) {
            $stmtImg->bind_param('is', $newId, $gambar);
            $stmtImg->execute();
            $stmtImg->close();
        }
    }

    $stmt2 = $mysqli->prepare('SELECT mi.id, mi.nama, mi.harga, mi.kategori, COALESCE(mg.image, mi.gambar) AS gambar, mi.stock_status FROM menu_items mi LEFT JOIN menu_images mg ON mg.menu_item_id = mi.id AND mg.is_primary = 1 WHERE mi.id = ? LIMIT 1');
    if ($stmt2) {
        $stmt2->bind_param('i', $newId);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $insertedItem = $result->fetch_assoc();
        $stmt2->close();
    } else {
        $insertedItem = null;
    }

    $mysqli->close();
    send_response(['success' => true, 'item' => $insertedItem]);
}

if ($method === 'POST' && $action === 'update_menu_stock') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body) || !isset($body['id']) || !isset($body['stock_status'])) {
        send_error('Input tidak valid untuk update stock menu');
    }

    $allowed = ['tersedia', 'habis'];
    $stock = trim((string)$body['stock_status']);
    if (!in_array($stock, $allowed, true)) {
        send_error('Status stock harus tersedia atau habis');
    }

    $id = intval($body['id']);
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_menu_items_table($mysqli);
    
    $stmt = $mysqli->prepare('UPDATE menu_items SET stock_status = ?, updated_at = NOW() WHERE id = ?');
    if (!$stmt) {
        send_error('Gagal menyiapkan query: ' . $mysqli->error, 500);
    }
    $stmt->bind_param('si', $stock, $id);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        send_error('Gagal mengupdate stock menu: ' . $stmt->error, 500);
    }
    $stmt->close();

    $stmt2 = $mysqli->prepare('SELECT id, nama, harga, stock_status FROM menu_items WHERE id = ?');
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $res = $stmt2->get_result();
    $item = $res->fetch_assoc();
    $stmt2->close();
    
    $mysqli->close();
    send_response(['success' => true, 'item' => $item]);
}

if ($method === 'GET' && $action === 'get_tables') {
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_tables_table($mysqli);
    ensure_table_images_table($mysqli);
    $result = $mysqli->query('SELECT t.id, t.nama, t.kapasitas, t.status, COALESCE(ti.image, "") AS image FROM tables t LEFT JOIN table_images ti ON ti.table_id = t.id AND ti.is_primary = 1 ORDER BY t.nama');
    $tables = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            if (empty($row['image'])) {
                $defaultPath = __DIR__ . '/uploads/MEJA/MEJA' . intval($row['id']) . '.jpg';
                if (file_exists($defaultPath)) {
                    $row['image'] = 'uploads/MEJA/MEJA' . intval($row['id']) . '.jpg';
                }
            }
            $tables[] = $row;
        }
        $result->free();
    }
    $mysqli->close();
    send_response(['success' => true, 'tables' => $tables]);
}

if ($method === 'POST' && $action === 'upload_table_image') {
    $tableId = isset($_POST['table_id']) ? intval($_POST['table_id']) : 0;
    $imageUrl = trim((string)($_POST['image_url'] ?? ''));
    $imagePath = '';

    if ($tableId <= 0) {
        send_error('ID meja diperlukan');
    }

    if (!empty($_FILES['gambar_file']) && is_uploaded_file($_FILES['gambar_file']['tmp_name'])) {
        $imagePath = save_uploaded_image($_FILES['gambar_file']);
    }
    if ($imagePath === '' && $imageUrl !== '') {
        $imagePath = $imageUrl;
    }

    if ($imagePath === '') {
        send_error('File atau URL gambar diperlukan');
    }

    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_tables_table($mysqli);
    ensure_table_images_table($mysqli);

    $stmtReset = $mysqli->prepare('UPDATE table_images SET is_primary = 0 WHERE table_id = ? AND is_primary = 1');
    if ($stmtReset) {
        $stmtReset->bind_param('i', $tableId);
        $stmtReset->execute();
        $stmtReset->close();
    }

    $stmt = $mysqli->prepare('INSERT INTO table_images (table_id, image, is_primary, created_at) VALUES (?, ?, 1, NOW())');
    if (!$stmt) {
        send_error('Gagal menyiapkan query upload gambar meja: ' . $mysqli->error, 500);
    }
    $stmt->bind_param('is', $tableId, $imagePath);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        send_error('Gagal menyimpan gambar meja: ' . $stmt->error, 500);
    }
    $stmt->close();

    $stmt2 = $mysqli->prepare('SELECT t.id, t.nama, t.kapasitas, t.status, COALESCE(ti.image, "") AS image FROM tables t LEFT JOIN table_images ti ON ti.table_id = t.id AND ti.is_primary = 1 WHERE t.id = ? LIMIT 1');
    if ($stmt2) {
        $stmt2->bind_param('i', $tableId);
        $stmt2->execute();
        $result = $stmt2->get_result();
        $table = $result->fetch_assoc();
        $stmt2->close();
    } else {
        $table = null;
    }

    $mysqli->close();
    send_response(['success' => true, 'table' => $table]);
}

if ($method === 'POST' && $action === 'update_table_status') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body) || !isset($body['id']) || !isset($body['status'])) {
        send_error('Input tidak valid untuk update status meja');
    }

    $allowed = ['tersedia', 'terisi'];
    $status = trim((string)$body['status']);
    if (!in_array($status, $allowed, true)) {
        send_error('Status meja harus tersedia atau terisi');
    }

    $id = intval($body['id']);
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_tables_table($mysqli);
    
    $stmt = $mysqli->prepare('UPDATE tables SET status = ?, updated_at = NOW() WHERE id = ?');
    if (!$stmt) {
        send_error('Gagal menyiapkan query: ' . $mysqli->error, 500);
    }
    $stmt->bind_param('si', $status, $id);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        send_error('Gagal mengupdate status meja: ' . $stmt->error, 500);
    }
    $stmt->close();

    $stmt2 = $mysqli->prepare('SELECT id, nama, kapasitas, status FROM tables WHERE id = ?');
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $res = $stmt2->get_result();
    $table = $res->fetch_assoc();
    $stmt2->close();
    
    $mysqli->close();
    send_response(['success' => true, 'table' => $table]);
}

if ($method === 'POST' && $action === 'save_reservation') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body)) {
        send_error('Input tidak valid');
    }

    $required = ['table_id', 'nama', 'no_hp', 'tanggal', 'waktu', 'jumlah_orang'];
    foreach ($required as $field) {
        if (!isset($body[$field]) || trim((string) $body[$field]) === '') {
            send_error("Field {$field} diperlukan");
        }
    }

    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_users_table($mysqli);
    ensure_reservations_table($mysqli);
    ensure_tables_table($mysqli);

    $table_id = intval($body['table_id']);
    $nama = trim((string) $body['nama']);
    $no_hp = normalize_phone($body['no_hp']);
    $tanggal = trim((string) $body['tanggal']);
    $waktu = trim((string) $body['waktu']);
    $jumlah_orang = intval($body['jumlah_orang']);
    $catatan = trim((string) ($body['catatan'] ?? ''));
    // use Indonesian status keys to match admin UI
    $status = 'menunggu';
    $created_at = date('Y-m-d H:i:s');
    $id = (string) round(microtime(true) * 1000);

    if ($no_hp === '') {
        send_error('Nomor HP tidak valid');
    }

    // Generate unique reservation code
    $reservasi_code = generate_reservation_code();
    $codeAttempts = 0;
    while ($codeAttempts < 10) {
        $checkStmt = $mysqli->prepare('SELECT COUNT(*) as cnt FROM reservations WHERE reservasi_code = ?');
        $checkStmt->bind_param('s', $reservasi_code);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $codeRow = $checkResult->fetch_assoc();
        $checkStmt->close();
        if (intval($codeRow['cnt']) === 0) {
            break;
        }
        $reservasi_code = generate_reservation_code();
        $codeAttempts++;
    }

    // Get or create user
    $stmtUser = $mysqli->prepare('SELECT id, nama FROM users WHERE no_hp = ? LIMIT 1');
    if (!$stmtUser) {
        send_error('Gagal menyiapkan query user: ' . $mysqli->error, 500);
    }
    $stmtUser->bind_param('s', $no_hp);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    $userId = null;
    $existingName = null;
    if ($row = $resultUser->fetch_assoc()) {
        $userId = (string)$row['id'];
        $existingName = trim((string)$row['nama']);
    }
    $stmtUser->close();

    if ($userId) {
        if ($existingName !== $nama) {
            $stmtUpdateUser = $mysqli->prepare('UPDATE users SET nama = ?, updated_at = ? WHERE id = ?');
            if ($stmtUpdateUser) {
                $now = date('Y-m-d H:i:s');
                $stmtUpdateUser->bind_param('sss', $nama, $now, $userId);
                $stmtUpdateUser->execute();
                $stmtUpdateUser->close();
            }
        }
    } else {
        $stmtInsertUser = $mysqli->prepare('INSERT INTO users (nama, no_hp, created_at, updated_at) VALUES (?, ?, ?, ?)');
        if (!$stmtInsertUser) {
            send_error('Gagal menyiapkan query insert user: ' . $mysqli->error, 500);
        }
        $now = date('Y-m-d H:i:s');
        $stmtInsertUser->bind_param('ssss', $nama, $no_hp, $now, $now);
        if (!$stmtInsertUser->execute()) {
            $stmtInsertUser->close();
            send_error('Gagal menyimpan data user: ' . $stmtInsertUser->error, 500);
        }
        $userId = (string)$mysqli->insert_id;
        $stmtInsertUser->close();
    }

    // Save reservation
    $stmt = $mysqli->prepare('INSERT INTO reservations (id, user_id, table_id, nama, no_hp, reservasi_code, tanggal, waktu, jumlah_orang, catatan, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if (!$stmt) {
        send_error('Gagal menyiapkan query: ' . $mysqli->error, 500);
    }

    $stmt->bind_param('ssisssssissss', $id, $userId, $table_id, $nama, $no_hp, $reservasi_code, $tanggal, $waktu, $jumlah_orang, $catatan, $status, $created_at, $created_at);
    if (!$stmt->execute()) {
        send_error('Gagal menyimpan reservasi: ' . $stmt->error, 500);
    }
    $stmt->close();

    $mysqli->close();

    send_response([
        'success' => true,
        'reservation' => [
            'id' => $id,
            'table_id' => $table_id,
            'nama' => $nama,
            'no_hp' => $no_hp,
            'reservasi_code' => $reservasi_code,
            'tanggal' => $tanggal,
            'waktu' => $waktu,
            'jumlah_orang' => $jumlah_orang,
            'catatan' => $catatan,
            'status' => $status,
            'created_at' => $created_at,
        ]
    ]);
}

if ($method === 'GET' && $action === 'get_reservations') {
    $phone = normalize_phone($_GET['phone'] ?? '');
    $code = trim($_GET['code'] ?? '');
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    // Ensure both reservations and tables exist. Use LEFT JOIN so reservations
    // are returned even if the `tables` record is missing.
    ensure_reservations_table($mysqli);
    ensure_tables_table($mysqli);

    if ($code !== '') {
        $stmt = $mysqli->prepare('SELECT r.id, r.table_id, r.nama, r.no_hp, r.reservasi_code, r.tanggal, r.waktu, r.jumlah_orang, r.catatan, r.status, r.created_at, COALESCE(t.nama, "") as table_name FROM reservations r LEFT JOIN tables t ON r.table_id = t.id WHERE r.reservasi_code = ? ORDER BY r.created_at DESC');
        $stmt->bind_param('s', $code);
    } elseif ($phone !== '') {
        $stmt = $mysqli->prepare('SELECT r.id, r.table_id, r.nama, r.no_hp, r.reservasi_code, r.tanggal, r.waktu, r.jumlah_orang, r.catatan, r.status, r.created_at, COALESCE(t.nama, "") as table_name FROM reservations r LEFT JOIN tables t ON r.table_id = t.id WHERE r.no_hp = ? ORDER BY r.created_at DESC');
        $stmt->bind_param('s', $phone);
    } else {
        $stmt = $mysqli->prepare('SELECT r.id, r.table_id, r.nama, r.no_hp, r.reservasi_code, r.tanggal, r.waktu, r.jumlah_orang, r.catatan, r.status, r.created_at, COALESCE(t.nama, "") as table_name FROM reservations r LEFT JOIN tables t ON r.table_id = t.id ORDER BY r.created_at DESC');
    }

    if (!$stmt->execute()) {
        send_error('Gagal mengambil data reservasi: ' . $mysqli->error, 500);
    }

    $result = $stmt->get_result();
    $reservations = [];
    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }
    $stmt->close();
    $mysqli->close();
    send_response(['success' => true, 'reservations' => $reservations]);
}

if ($method === 'POST' && $action === 'reject_reservation') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body) || !isset($body['id'])) {
        send_error('Input tidak valid untuk menolak reservasi');
    }

    $id = $body['id'];
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_reservations_table($mysqli);
    ensure_tables_table($mysqli);

    $stmt = $mysqli->prepare('SELECT status, table_id FROM reservations WHERE id = ? LIMIT 1');
    if (!$stmt) {
        send_error('Gagal menyiapkan query reservasi: ' . $mysqli->error, 500);
    }
    $stmt->bind_param('s', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $reservation = $result->fetch_assoc();
    $stmt->close();

    if (!$reservation) {
        $mysqli->close();
        send_error('Reservasi tidak ditemukan', 404);
    }

    if (in_array($reservation['status'], ['dipesan', 'confirmed'], true) && intval($reservation['table_id']) > 0) {
        $stmt2 = $mysqli->prepare('UPDATE tables SET status = ?, updated_at = NOW() WHERE id = ?');
        if ($stmt2) {
            $sval = 'tersedia';
            $tableId = intval($reservation['table_id']);
            $stmt2->bind_param('si', $sval, $tableId);
            $stmt2->execute();
            $stmt2->close();
        }
    }

    $stmt3 = $mysqli->prepare('DELETE FROM reservations WHERE id = ?');
    if (!$stmt3) {
        send_error('Gagal menyiapkan query hapus reservasi: ' . $mysqli->error, 500);
    }
    $stmt3->bind_param('s', $id);
    if (!$stmt3->execute()) {
        send_error('Gagal menghapus reservasi: ' . $stmt3->error, 500);
    }
    $stmt3->close();
    $mysqli->close();

    send_response(['success' => true]);
}

if ($method === 'POST' && $action === 'update_reservation_code') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body) || !isset($body['id'])) {
        send_error('Input tidak valid untuk update kode reservasi');
    }

    $id = $body['id'];
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_reservations_table($mysqli);

    // Generate new reservation code
    $reservasi_code = generate_reservation_code();
    $codeAttempts = 0;
    while ($codeAttempts < 10) {
        $checkStmt = $mysqli->prepare('SELECT COUNT(*) as cnt FROM reservations WHERE reservasi_code = ?');
        $checkStmt->bind_param('s', $reservasi_code);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $codeRow = $checkResult->fetch_assoc();
        $checkStmt->close();
        if (intval($codeRow['cnt']) === 0) {
            break;
        }
        $reservasi_code = generate_reservation_code();
        $codeAttempts++;
    }

    $stmt = $mysqli->prepare('UPDATE reservations SET reservasi_code = ?, updated_at = NOW() WHERE id = ?');
    if (!$stmt) {
        send_error('Gagal menyiapkan query: ' . $mysqli->error, 500);
    }
    $stmt->bind_param('ss', $reservasi_code, $id);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        send_error('Gagal mengupdate kode reservasi: ' . $stmt->error, 500);
    }
    $stmt->close();

    $stmt2 = $mysqli->prepare('SELECT id, reservasi_code FROM reservations WHERE id = ? LIMIT 1');
    $stmt2->bind_param('s', $id);
    $stmt2->execute();
    $res = $stmt2->get_result();
    $reservation = $res->fetch_assoc();
    $stmt2->close();

    $mysqli->close();
    send_response(['success' => true, 'reservation' => $reservation]);
}

if ($method === 'POST' && $action === 'update_reservation_status') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body) || !isset($body['id']) || !isset($body['status'])) {
        send_error('Input tidak valid untuk update status reservasi');
    }

    $allowed = ['menunggu', 'dipesan', 'selesai'];
    $status = trim((string)$body['status']);
    if (!in_array($status, $allowed, true)) {
        send_error('Status reservasi tidak valid');
    }

    $id = $body['id'];
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_reservations_table($mysqli);
    ensure_tables_table($mysqli);

    // Update reservation status
    $stmt = $mysqli->prepare('UPDATE reservations SET status = ?, updated_at = NOW() WHERE id = ?');
    if (!$stmt) {
        send_error('Gagal menyiapkan query update reservasi: ' . $mysqli->error, 500);
    }
    $stmt->bind_param('ss', $status, $id);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        send_error('Gagal mengupdate status reservasi: ' . $stmt->error, 500);
    }
    $stmt->close();

    // If reservation finished, free the table; if reserved, mark table terisi
    $stmt2 = $mysqli->prepare('SELECT table_id FROM reservations WHERE id = ? LIMIT 1');
    if ($stmt2) {
        $stmt2->bind_param('s', $id);
        $stmt2->execute();
        $res = $stmt2->get_result();
        $row = $res->fetch_assoc();
        $stmt2->close();
        $tableId = isset($row['table_id']) ? intval($row['table_id']) : null;
        if ($tableId !== null) {
            if ($status === 'selesai') {
                $stmt3 = $mysqli->prepare('UPDATE tables SET status = ?, updated_at = NOW() WHERE id = ?');
                if ($stmt3) {
                    $sval = 'tersedia';
                    $stmt3->bind_param('si', $sval, $tableId);
                    $stmt3->execute();
                    $stmt3->close();
                }
            } elseif ($status === 'dipesan') {
                $stmt3 = $mysqli->prepare('UPDATE tables SET status = ?, updated_at = NOW() WHERE id = ?');
                if ($stmt3) {
                    $sval = 'terisi';
                    $stmt3->bind_param('si', $sval, $tableId);
                    $stmt3->execute();
                    $stmt3->close();
                }
            }
        }
    }

    // Return updated reservation
    $stmt4 = $mysqli->prepare('SELECT r.id, r.table_id, r.nama, r.no_hp, r.reservasi_code, r.tanggal, r.waktu, r.jumlah_orang, r.catatan, r.status, r.created_at, COALESCE(t.nama, "") as table_name FROM reservations r LEFT JOIN tables t ON r.table_id = t.id WHERE r.id = ? LIMIT 1');
    if ($stmt4) {
        $stmt4->bind_param('s', $id);
        $stmt4->execute();
        $res4 = $stmt4->get_result();
        $reservation = $res4->fetch_assoc();
        $stmt4->close();
    } else {
        $reservation = null;
    }

    $mysqli->close();
    send_response(['success' => true, 'reservation' => $reservation]);
}

if ($method === 'POST' && $action === 'delete_order') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body) || !isset($body['id'])) {
        send_error('Input tidak valid untuk menghapus pesanan');
    }

    $id = $body['id'];
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_orders_table($mysqli);

    // Optionally check order exists
    $stmtCheck = $mysqli->prepare('SELECT id FROM orders WHERE id = ? LIMIT 1');
    if ($stmtCheck) {
        $stmtCheck->bind_param('s', $id);
        $stmtCheck->execute();
        $resC = $stmtCheck->get_result();
        $exists = $resC->fetch_assoc();
        $stmtCheck->close();
        if (!$exists) {
            $mysqli->close();
            send_error('Pesanan tidak ditemukan', 404);
        }
    }

    $stmt = $mysqli->prepare('DELETE FROM orders WHERE id = ?');
    if (!$stmt) {
        send_error('Gagal menyiapkan query hapus pesanan: ' . $mysqli->error, 500);
    }
    $stmt->bind_param('s', $id);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        send_error('Gagal menghapus pesanan: ' . $stmt->error, 500);
    }
    $stmt->close();
    $mysqli->close();

    send_response(['success' => true]);
}

if ($method === 'POST' && $action === 'delete_menu_item') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!is_array($body) || !isset($body['id'])) {
        send_error('Input tidak valid untuk menghapus menu');
    }

    $id = intval($body['id']);
    $mysqli = db_connect($dbHost, $dbUser, $dbPass, $dbName);
    ensure_menu_items_table($mysqli);

    $stmtCheck = $mysqli->prepare('SELECT id FROM menu_items WHERE id = ? LIMIT 1');
    if ($stmtCheck) {
        $stmtCheck->bind_param('i', $id);
        $stmtCheck->execute();
        $resC = $stmtCheck->get_result();
        $exists = $resC->fetch_assoc();
        $stmtCheck->close();
        if (!$exists) {
            $mysqli->close();
            send_error('Menu tidak ditemukan', 404);
        }
    }

    $stmt = $mysqli->prepare('DELETE FROM menu_items WHERE id = ?');
    if (!$stmt) {
        send_error('Gagal menyiapkan query hapus menu: ' . $mysqli->error, 500);
    }
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        $stmt->close();
        $mysqli->close();
        send_error('Gagal menghapus menu: ' . $stmt->error, 500);
    }
    $stmt->close();
    $mysqli->close();

    send_response(['success' => true]);
}

send_error('Aksi tidak dikenali', 400);
