<?php
/**
 * index.php — Dashboard Laboratorium SQL Injection
 *
 * ⚠️ RANCANGAN KHUSUS UNTUK PRAKTIK KEAMANAN SIBER — HANYA UNTUK TUJUAN PENDIDIKAN ⚠️
 */
require_once __DIR__ . '/db.php';

$dbReady = dbExists();
$alertMsg = '';
$alertType = 'info';

// Penanganan pengiriman flag atau konfirmasi progres dari dashboard
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $dbReady) {
    if (isset($_POST['action_progress']) && $_POST['action_progress'] === 'submit_flag') {
        $labId = $_POST['target_lab'] ?? '';
        $flag = $_POST['flag_input'] ?? '';
        $res = submitLabFlag($labId, $flag);
        $alertMsg = $res['message'];
        $alertType = $res['success'] ? 'success' : 'error';
    } elseif (isset($_POST['action_progress']) && $_POST['action_progress'] === 'reset_all') {
        $res = resetAllLabProgress();
        $alertMsg = $res['message'];
        $alertType = 'info';
    }
}

$completedLabs = getCompletedLabs();
$completedCount = count($completedLabs);
$totalLabs = 8;
$progressPct = round(($completedCount / $totalLabs) * 100);

$labs = [
    [
        'id' => '01',
        'slug' => '01_classic_login',
        'name' => 'Bypass Login Klasik',
        'diff' => 'Mudah',
        'badge' => 'badge-easy',
        'desc' => 'Bentuk serangan paling mendasar — memotong autentikasi form login menggunakan logika boolean dan trik komentar SQL.',
        'tags' => ['Autentikasi', 'Komentar SQL']
    ],
    [
        'id' => '02',
        'slug' => '02_union_based',
        'name' => 'Injeksi Berbasis UNION',
        'diff' => 'Menengah',
        'badge' => 'badge-medium',
        'desc' => 'Memanfaatkan operator UNION SELECT untuk menggabungkan query tambahan dan mengekstrak data dari tabel lain.',
        'tags' => ['Eksfiltrasi Data', 'UNION SELECT']
    ],
    [
        'id' => '03',
        'slug' => '03_error_based',
        'name' => 'Injeksi Berbasis Error',
        'diff' => 'Menengah',
        'badge' => 'badge-medium',
        'desc' => 'Memaksa database membocorkan data dan struktur internal melalui pesan kesalahan teknis (error leakage).',
        'tags' => ['Kebocoran Error', 'SQLite']
    ],
    [
        'id' => '04',
        'slug' => '04_blind_boolean',
        'name' => 'Blind SQLi: Berbasis Boolean',
        'diff' => 'Sulit',
        'badge' => 'badge-hard',
        'desc' => 'Tanpa tampilan output data — hanya respon Benar/Salah. Mengonstruksi data rahasia karakter per karakter.',
        'tags' => ['Blind SQLi', 'Logika Boolean']
    ],
    [
        'id' => '05',
        'slug' => '05_blind_time',
        'name' => 'Blind SQLi: Berbasis Waktu',
        'diff' => 'Sulit',
        'badge' => 'badge-hard',
        'desc' => 'Menyimpulkan data dari jeda delay waktu respon server. Tidak ada output visual — hanya jeda waktu yang mengungkap kebenaran.',
        'tags' => ['Blind SQLi', 'Jeda Waktu']
    ],
    [
        'id' => '06',
        'slug' => '06_second_order',
        'name' => 'Injeksi Second-Order',
        'diff' => 'Sulit',
        'badge' => 'badge-hard',
        'desc' => 'Payload tersimpan aman pada input tahap pertama, namun meledak saat dieksekusi query tahap kedua.',
        'tags' => ['Injeksi Tersimpan', 'Tingkat Lanjut']
    ],
    [
        'id' => '07',
        'slug' => '07_stacked_queries',
        'name' => 'Stacked Queries (Multi-Statement)',
        'diff' => 'Menengah',
        'badge' => 'badge-medium',
        'desc' => 'Menumpuk banyak perintah SQL dalam satu input menggunakan pemisah titik-koma (INSERT, UPDATE, DELETE).',
        'tags' => ['Multi-Statement', 'Manipulasi Data']
    ],
    [
        'id' => '08',
        'slug' => '08_out_of_band',
        'name' => 'Teknik Lanjutan & Bypass WAF',
        'diff' => 'Sulit',
        'badge' => 'badge-hard',
        'desc' => 'Eksfiltrasi DNS/HTTP ke server luar, teknik melewati firewall WAF, dan metode obfusikasi query.',
        'tags' => ['Out-Of-Band', 'Bypass WAF']
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratorium SQL Injection — Dashboard Utama</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .lab-card.completed {
            border-color: rgba(16, 185, 129, 0.5);
            background: rgba(16, 185, 129, 0.03);
            box-shadow: 0 10px 25px -10px rgba(16, 185, 129, 0.2);
        }
    </style>
</head>
<body>
<canvas id="matrix-canvas"></canvas>

<header class="site-header">
    <div class="container">
        <div class="header-inner">
            <div class="logo">[ LAB_SQL_INJECTION ]</div>
            <nav>
                <a href="index.php" style="color:var(--emerald-primary); font-weight:600;">Dashboard</a>
                <a href="solutions/index.php">Kunci Solusi</a>
                <a href="setup.php">Reset Database</a>
            </nav>
        </div>
    </div>
</header>

<div class="container" style="margin-top:20px;">
    <div class="disclaimer-banner">
        ⚠️ &nbsp;SENGAJA DIRANCANG RENTAN — KHUSUS UNTUK TUJUAN PEMBELAJARAN KEAMANAN SIBER — JANGAN DITERAPKAN DI SERVER PUBLIK&nbsp; ⚠️
    </div>

    <?php if ($alertMsg): ?>
    <div style="padding:14px 18px; border-radius:10px; margin-bottom:24px; font-weight:600; font-size:0.9rem;
                background: <?= $alertType === 'success' ? 'rgba(16,185,129,0.1)' : ($alertType === 'error' ? 'rgba(244,63,94,0.1)' : 'rgba(6,182,212,0.1)') ?>;
                border: 1px solid <?= $alertType === 'success' ? '#10b981' : ($alertType === 'error' ? '#f43f5e' : '#06b6d4') ?>;
                color: <?= $alertType === 'success' ? '#34d399' : ($alertType === 'error' ? '#fb7185' : '#67e8f9') ?>;">
        <?= htmlspecialchars($alertMsg) ?>
    </div>
    <?php endif; ?>
</div>

<main>
<div class="container">

    <div class="hero">
        <h1 class="hero-title">LAB PRAKTIK SQL INJECTION</h1>
        <p class="hero-sub">// Pelajari. Eksploitasi. Pahami. Amankan. //</p>
        <?php if (!$dbReady): ?>
        <div style="background:rgba(245,158,11,0.1); border:1px solid #f59e0b; border-radius:10px; padding:16px; max-width:520px; margin:0 auto 24px;">
            <div style="color:#fbbf24; font-weight:700; margin-bottom:6px;">⚠ Basis Data Belum Diinisialisasi</div>
            <div style="color:var(--text-muted); font-size:0.85rem; margin-bottom:12px;">Klik tombol di bawah untuk menyiapkan database SQLite secara otomatis:</div>
            <a href="setup.php" class="btn btn-primary btn-sm">Jalankan setup.php →</a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Statistik Progres Belajar -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-value">8</div>
            <div class="stat-label">Total Modul Lab</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" id="progress-count" style="color: <?= $completedCount > 0 ? '#10b981' : 'var(--text-dim)' ?>;">
                <?= $completedCount ?> / 8
            </div>
            <div class="stat-label">Modul Selesai</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color: <?= $progressPct > 0 ? '#06b6d4' : 'var(--text-dim)' ?>;">
                <?= $progressPct ?>%
            </div>
            <div class="stat-label">Tingkat Penyelesaian</div>
        </div>
        <div class="stat-card">
            <div class="stat-value" style="color:#f59e0b;">
                <?= 8 - $completedCount ?>
            </div>
            <div class="stat-label">Tersisa</div>
        </div>
    </div>

    <!-- Progress Bar Section -->
    <div class="progress-section">
        <div class="progress-header">
            <span class="progress-label">Progres Keseluruhan Laboratorium</span>
            <span class="progress-value"><span id="progress-pct"><?= $progressPct ?></span>% Selesai</span>
        </div>
        <div class="progress-wrap">
            <div class="progress-bar" id="progress-bar" style="width: <?= $progressPct ?>%;"></div>
        </div>
        <?php if ($completedCount > 0): ?>
        <div style="display:flex; justify-content:flex-end; margin-top:12px;">
            <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset seluruh progres belajar ke 0%?');" style="margin:0;">
                <input type="hidden" name="action_progress" value="reset_all">
                <button type="submit" style="background:none; border:none; color:var(--text-dim); font-size:0.75rem; cursor:pointer; text-decoration:underline;">
                    ↺ Reset Seluruh Progres ke 0%
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Kotak Submit Flag Cepat di Dashboard -->
    <div class="panel" style="border-color: rgba(6, 182, 212, 0.3); background: rgba(15, 23, 42, 0.95); margin-bottom:32px;">
        <div class="panel-header">
            <div class="panel-title" style="color:#06b6d4;">
                🚩 SUBMIT FLAG CEPAT DARI DASHBOARD
            </div>
            <span style="font-size:0.75rem; color:var(--text-dim);">Format: FLAG{...}</span>
        </div>
        <form method="POST" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
            <input type="hidden" name="action_progress" value="submit_flag">
            <select name="target_lab" style="min-width:180px; background:#030712; border:1px solid var(--border-subtle); border-radius:6px; padding:10px 14px; color:var(--text-main); font-family:var(--font-sans); font-size:0.85rem;" required>
                <option value="">Pilih Target Modul...</option>
                <?php foreach ($labs as $l): ?>
                <option value="<?= $l['id'] ?>" <?= isset($completedLabs[$l['id']]) ? 'style="color:#34d399;"' : '' ?>>
                    Modul <?= $l['id'] ?>: <?= $l['name'] ?> <?= isset($completedLabs[$l['id']]) ? '✓ (Selesai)' : '' ?>
                </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="flag_input" placeholder="Masukkan string flag (contoh: FLAG{...})" 
                   style="flex:1; min-width:260px; background:#030712; border:1px solid var(--border-subtle); border-radius:6px; padding:10px 14px; color:#34d399; font-family:'JetBrains Mono',monospace; font-size:0.85rem;" required>
            <button type="submit" class="btn btn-primary" style="padding:10px 22px;">
                🚀 Verifikasi Flag
            </button>
        </form>
    </div>

    <!-- Daftar Modul Lab -->
    <div class="section-title"><span>Daftar Modul Lab SQL Injection</span></div>
    <div class="labs-grid">
        <?php foreach ($labs as $lab): 
            $isDone = isset($completedLabs[$lab['id']]);
        ?>
        <div class="lab-card <?= $isDone ? 'completed' : '' ?>" data-lab-id="<?= $lab['id'] ?>">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div class="lab-number">Modul <?= $lab['id'] ?></div>
                <?php if ($isDone): ?>
                    <span class="badge" style="background:rgba(16,185,129,0.15); color:#34d399; border:1px solid #10b981;">✓ Selesai</span>
                <?php else: ?>
                    <span class="badge <?= $lab['badge'] ?>"><?= $lab['diff'] ?></span>
                <?php endif; ?>
            </div>
            
            <div class="lab-title" style="margin-top:6px;"><?= htmlspecialchars($lab['name']) ?></div>
            <div class="lab-desc"><?= htmlspecialchars($lab['desc']) ?></div>
            
            <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:16px;">
                <?php foreach ($lab['tags'] as $tag): ?>
                <span class="tag tag-cyan"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </div>

            <div class="lab-footer">
                <span style="font-size:0.8rem; color:<?= $isDone ? '#34d399' : 'var(--text-dim)' ?>;">
                    <?= $isDone ? '✓ Terverifikasi' : 'Belum selesai' ?>
                </span>
                <?php if ($dbReady): ?>
                <a href="labs/<?= $lab['slug'] ?>/index.php" class="btn <?= $isDone ? 'btn-cyan' : 'btn-primary' ?> btn-sm">
                    <?= $isDone ? 'Tinjau Lab →' : 'Mulai Lab →' ?>
                </a>
                <?php else: ?>
                <span class="btn btn-primary btn-sm" style="opacity:0.4; cursor:not-allowed;">Perlu Database</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Referensi Cepat -->
    <div class="section-title mt-4"><span>Referensi Singkat Payload</span></div>
    <div class="card mt-2">
        <h3 style="color:#06b6d4; margin-bottom:16px;">Contoh Payload Umum SQL Injection</h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:16px;">
            <div>
                <div style="color:var(--text-dim); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px;">Bypass Autentikasi Form Login</div>
                <ul class="payload-list">
                    <li><code>' OR '1'='1' --</code></li>
                    <li><code>admin' --</code></li>
                    <li><code>' OR 1=1 --</code></li>
                </ul>
            </div>
            <div>
                <div style="color:var(--text-dim); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px;">Injeksi Berbasis UNION</div>
                <ul class="payload-list">
                    <li><code>' UNION SELECT 1,2,3 --</code></li>
                    <li><code>' UNION SELECT 1,username,password FROM users --</code></li>
                </ul>
            </div>
            <div>
                <div style="color:var(--text-dim); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px;">Blind Berbasis Boolean</div>
                <ul class="payload-list">
                    <li><code>admin' AND '1'='1</code></li>
                    <li><code>admin' AND SUBSTR(password,1,1)='s</code></li>
                </ul>
            </div>
            <div>
                <div style="color:var(--text-dim); font-size:0.75rem; text-transform:uppercase; letter-spacing:0.08em; margin-bottom:8px;">Gaya Karakter Komentar</div>
                <ul class="payload-list">
                    <li><code>--</code> &nbsp;(Standar SQL)</li>
                    <li><code>/* ... */</code> &nbsp;(Komentar Blok)</li>
                </ul>
            </div>
        </div>
    </div>

</div>
</main>

<footer class="site-footer">
    <div class="container">
        <p>Laboratorium Praktik SQL Injection — Khusus untuk tujuan edukasi dan uji keamanan berizin.</p>
        <p style="margin-top:4px;">Dilarang menggunakan metode ini pada sistem yang bukan milik Anda tanpa izin tertulis resmi.</p>
    </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
