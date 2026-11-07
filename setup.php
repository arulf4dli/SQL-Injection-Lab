<?php
/**
 * =============================================================
 * LAB SQL INJECTION - INISIALISASI BASIS DATA
 * Jalankan file ini untuk menyiapkan atau mereset tabel database
 * =============================================================
 */

$dbPath = __DIR__ . '/database.db';

// Hapus DB lama jika ada
if (file_exists($dbPath)) {
    unlink($dbPath);
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ---- TABEL PENGGUNA (USERS) ----
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        password TEXT NOT NULL,
        email TEXT,
        role TEXT DEFAULT 'user',
        secret TEXT
    )");

    $db->exec("INSERT INTO users (username, password, email, role, secret) VALUES
        ('admin', 'sup3r_s3cr3t_p4ss', 'admin@sqlilab.local', 'admin', 'FLAG{admin_account_compromised_via_sqli}'),
        ('alice', 'alice_pass_2024', 'alice@sqlilab.local', 'user', 'FLAG{alice_data_exposed}'),
        ('bob', 'b0bL0v3sSQL', 'bob@sqlilab.local', 'user', 'FLAG{bob_secret_leaked}'),
        ('charlie', 'ch4rl13_secure', 'charlie@sqlilab.local', 'moderator', 'FLAG{charlie_mod_access}'),
        ('dave', 'dave123password', 'dave@sqlilab.local', 'user', 'FLAG{dave_flag_here}')
    ");

    // ---- TABEL PRODUK (PRODUCTS) ----
    $db->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        price REAL,
        category TEXT,
        description TEXT,
        stock INTEGER DEFAULT 10
    )");

    $db->exec("INSERT INTO products (name, price, category, description, stock) VALUES
        ('Laptop Pro X1', 1299.99, 'Elektronik', 'Laptop performa tinggi dengan RAM 16GB', 15),
        ('Mouse Wireless M200', 29.99, 'Aksesoris', 'Mouse nirkabel ergonomis, 2.4GHz', 50),
        ('USB-C Hub 7-in-1', 49.99, 'Aksesoris', 'Multi-port hub dengan HDMI, USB3.0, SD card', 30),
        ('Monitor 4K 32inch', 599.99, 'Elektronik', 'Layar 32 inci IPS 4K HDR', 8),
        ('Keyboard Mekanikal K80', 89.99, 'Aksesoris', 'Keyboard mekanikal TKL, Switch Merah', 25),
        ('SSD 1TB NVMe', 119.99, 'Penyimpanan', 'SSD NVMe kecepatan tinggi, baca 7000MB/s', 40),
        ('Webcam HD 1080p', 69.99, 'Aksesoris', 'Webcam Full HD dilengkapi mikrofon bawaan', 20),
        ('Router WiFi 6 AX3000', 149.99, 'Jaringan', 'Router WiFi 6 tri-band, 3000Mbps', 12)
    ");

    // ---- TABEL PESANAN (ORDERS) ----
    $db->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        item TEXT NOT NULL,
        quantity INTEGER DEFAULT 1,
        status TEXT DEFAULT 'pending',
        created_at TEXT DEFAULT (datetime('now'))
    )");

    // ---- TABEL BENDERA RAHASIA (FLAGS) ----
    $db->exec("CREATE TABLE IF NOT EXISTS flags (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE,
        value TEXT NOT NULL,
        description TEXT
    )");

    $db->exec("INSERT INTO flags (name, value, description) VALUES
        ('master_flag', 'FLAG{sql_injection_master_pwned_all_labs_2024}', 'Selesaikan semua lab untuk membuka ini'),
        ('union_flag', 'FLAG{union_based_exfiltration_success}', 'Ditemukan lewat injeksi UNION-based'),
        ('blind_flag', 'FLAG{blind_boolean_no_output_needed}', 'Ditemukan lewat blind boolean'),
        ('time_flag', 'FLAG{time_delays_reveal_all_secrets}', 'Ditemukan lewat blind time-based'),
        ('error_flag', 'FLAG{errors_are_information_disclosure}', 'Ditemukan lewat error-based'),
        ('second_order_flag', 'FLAG{stored_payload_detonated_second_order}', 'Ditemukan lewat second-order')
    ");

    // ---- TABEL PENGGUNA TERDAFTAR (REGISTERED_USERS) ----
    $db->exec("CREATE TABLE IF NOT EXISTS registered_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        display_name TEXT NOT NULL,
        bio TEXT,
        registered_at TEXT DEFAULT (datetime('now'))
    )");

    // ---- TABEL LOG PENCARIAN (SEARCH_LOG) ----
    $db->exec("CREATE TABLE IF NOT EXISTS search_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        query TEXT,
        searched_at TEXT DEFAULT (datetime('now'))
    )");

    // ---- TABEL PROGRES LAB (LAB_PROGRESS) ----
    $db->exec("CREATE TABLE IF NOT EXISTS lab_progress (
        lab_id TEXT PRIMARY KEY,
        flag_submitted TEXT,
        completed_at DATETIME DEFAULT (datetime('now', 'localtime'))
    )");

} catch (PDOException $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inisialisasi Database — Lab SQL Injection</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .setup-box { max-width: 600px; width: 100%; text-align: center; padding: 20px; }
    </style>
</head>
<body>
<canvas id="matrix-canvas"></canvas>
<div class="setup-box">
    <?php if (isset($error)): ?>
        <div style="color:#f43f5e; font-size:2rem; margin-bottom:20px;">✗</div>
        <h1 style="color:#f43f5e; margin-bottom:15px;">INISIALISASI GAGAL</h1>
        <div class="code-block" style="text-align:left; color:#f43f5e;"><?= htmlspecialchars($error) ?></div>
    <?php else: ?>
        <div style="font-size:3rem; margin-bottom:16px;">⚡</div>
        <h1 class="hero-title" style="font-size:1.8rem; margin-bottom:10px;">DATABASE BERHASIL DIINISIALISASI</h1>
        <p style="color:var(--text-muted); font-size:0.88rem; margin-bottom:28px;">Semua tabel dan sistem pelacakan progres telah siap digunakan</p>
        <div style="background:#0f172a; border:1px solid var(--border-subtle); border-radius:12px; padding:20px; text-align:left; margin-bottom:28px;">
            <div style="color:#06b6d4; font-size:0.75rem; letter-spacing:1px; font-weight:700; margin-bottom:15px;">▸ DAFTAR TABEL YANG DIBUAT:</div>
            <?php foreach([
                'users' => '5 data akun (admin, alice, bob, charlie, dave)',
                'products' => '8 produk (elektronik, aksesoris, dll.)',
                'orders' => 'kosong (untuk modul second-order)',
                'flags' => '6 flag rahasia untuk ditemukan',
                'registered_users' => 'kosong (untuk modul second-order)',
                'search_log' => 'kosong (untuk logging modul stacked)',
                'lab_progress' => 'tabel pelacak progres belajar & flag'
            ] as $table => $desc): ?>
            <div style="color:#34d399; font-size:0.82rem; padding:5px 0;">
                <span style="color:#f59e0b;">▸</span> <span style="color:#06b6d4; font-family:'JetBrains Mono',monospace;"><?= $table ?></span>
                <span style="color:var(--text-dim);"> — <?= $desc ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <a href="index.php" class="btn btn-primary" style="font-size:0.95rem; padding:12px 36px; display:inline-block;">
            ▶ &nbsp; MASUK KE LABORATORIUM
        </a>
    <?php endif; ?>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
