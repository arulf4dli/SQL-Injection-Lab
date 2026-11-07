<?php
/**
 * =============================================================
 * LAB 03 — Injeksi SQL Berbasis Error
 * KERENTANAN: Error database ditampilkan ke user + input tanpa tanda kutip
 * =============================================================
 */
require_once '../../db.php';

$product_id = '';
$result = null;
$query_display = "SELECT * FROM products WHERE id=\$id";
$error_msg = '';
$flagFeedback = null;

// Penanganan pengiriman flag atau konfirmasi manual
if (isset($_POST['action_progress'])) {
    if ($_POST['action_progress'] === 'submit_flag') {
        $flagFeedback = submitLabFlag('03', $_POST['flag_input'] ?? '');
    } elseif ($_POST['action_progress'] === 'toggle_manual') {
        $flagFeedback = toggleManualLab('03');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action_progress'])) {
    $product_id = $_POST['product_id'] ?? '';

    $raw_query = "SELECT * FROM products WHERE id=$product_id";
    $query_display = $raw_query;

    try {
        $db = getDB();
        $stmt = $db->query($raw_query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_msg = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 03 — Injeksi Berbasis Error | Lab SQLi</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<canvas id="matrix-canvas"></canvas>

<div class="lab-page wrapper">
    <a href="../../index.php" class="nav-back">← KEMBALI KE DASHBOARD</a>

    <div class="lab-header">
        <div class="lab-header-info">
            <div style="color:var(--text-dim); font-size:0.78rem; font-family:'JetBrains Mono',monospace; letter-spacing:2px; margin-bottom:8px;">MODUL 03 / 08</div>
            <h1 style="font-size:1.85rem; margin-bottom:8px;">💥 Injeksi Berbasis Error</h1>
            <div class="meta" style="display:flex; align-items:center; gap:10px;">
                <span class="badge badge-medium">Menengah</span>
                <span class="vuln-label">⚠ RENTAN</span>
                <span style="color:var(--text-dim); font-size:0.82rem;">Teknik: Kebocoran Data via Pesan Error Database</span>
                <?php if (isLabCompleted('03')): ?>
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

    <div class="panel">
        <div class="panel-header">
            <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
            <div class="panel-title">DESKRIPSI KERENTANAN</div>
        </div>
        <p style="font-size:0.9rem; color:var(--text-muted); line-height:1.6; margin-bottom:14px;">
            Aplikasi menampilkan pesan mentah <strong style="color:#f43f5e;">error database</strong> kepada pengguna. Ditambah lagi dengan input numerik tanpa tanda kutip (string quotes), penyerang dapat menyuntikkan SQL yang memicu error secara sengaja — membongkar struktur tabel, skema, hingga isi data rahasia langsung dari teks kesalahan tersebut.
        </p>
        <div class="code-block">
<span style="color:#64748b;">// KODE RENTAN: Input numerik digabung langsung tanpa tanda kutip</span>
$id = $_POST['product_id']; <span style="color:#64748b;">// Tanpa validasi integer!</span>
$query = <span style="color:#fbbf24;">"SELECT * FROM products WHERE id=<span style="color:#f43f5e;">$id</span>"</span>;
        </div>
    </div>

    <!-- Kotak Submit Flag / Progres Modul 03 -->
    <?php renderFlagSubmissionBox('03'); ?>

    <div class="grid-2">
        <div>
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">🎯 TARGET: CARI PRODUK (BY ID)</div>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label>ID Produk (Angka)</label>
                        <input type="text" name="product_id" id="product_id"
                               placeholder="1-8 atau masukkan payload..."
                               value="<?= htmlspecialchars($product_id) ?>">
                        <div style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;">
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                            <button type="button" onclick="document.getElementById('product_id').value='<?= $i ?>'" 
                                    class="btn btn-cyan btn-sm" style="padding:4px 10px;"><?= $i ?></button>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">▶ &nbsp; CARI PRODUK</button>
                </form>
            </div>

            <div class="hint-box">
                <button class="hint-toggle" onclick="toggleHint('hint3')">
                    💡 CONTOH PAYLOAD <span class="arrow">▼</span>
                </button>
                <div class="hint-content" id="hint3">
                    <p><strong style="color:#fbbf24;">Payload injeksi numerik:</strong></p>
                    <ul class="payload-list">
                        <li data-target="product_id" data-payload="0 UNION SELECT 1,name,value,description,5 FROM flags">0 UNION SELECT 1,name,value,description,5 FROM flags (Cari Flag)</li>
                        <li data-target="product_id" data-payload="1 AND 1=2">1 AND 1=2 (Uji boolean)</li>
                        <li data-target="product_id" data-payload="1 UNION SELECT 1,username,password,role,secret FROM users">1 UNION SELECT 1,username,password,role,secret FROM users</li>
                    </ul>
                </div>
            </div>
        </div>

        <div>
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">MONITOR QUERY SQL REAL-TIME</div>
                </div>
                <div class="code-block" style="color:#06b6d4; word-break:break-all;">
<?= htmlspecialchars($query_display) ?>
                </div>
            </div>

            <div class="result-panel <?= !empty($result) ? 'result-success' : ($error_msg ? 'result-error' : '') ?>">
                <?php if ($error_msg): ?>
                    <div style="color:#fbbf24; font-size:0.75rem; letter-spacing:1px; font-weight:700; margin-bottom:8px;">⚠ PESAN ERROR DATABASE (DIEKSPOS):</div>
                    <div style="color:#f43f5e; font-size:0.85rem; word-break:break-all; background:rgba(244,63,94,0.06); padding:12px; border-radius:6px; border:1px solid rgba(244,63,94,0.25);">
                        <?= htmlspecialchars($error_msg) ?>
                    </div>
                <?php elseif (!empty($result)): ?>
                    <div style="margin-bottom:10px; color:#34d399; font-size:0.85rem; font-weight:600;">✓ <?= count($result) ?> data ditemukan</div>
                    <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead><tr>
                            <?php foreach (array_keys($result[0]) as $col): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                            <?php endforeach; ?>
                        </tr></thead>
                        <tbody>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <?php foreach ($row as $val): ?>
                                <td class="<?= (strpos((string)$val, 'FLAG{') !== false) ? 'flag' : '' ?>"
                                    <?= (strpos((string)$val, 'FLAG{') !== false) ? 'style="cursor:pointer;" title="Klik untuk salin" onclick="copyText(\''.htmlspecialchars($val).'\')"' : '' ?>>
                                    <?= htmlspecialchars($val ?? 'NULL') ?> <?= (strpos((string)$val, 'FLAG{') !== false) ? '📋' : '' ?>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action_progress'])): ?>
                    <div style="color:var(--text-dim);">Tidak ada data ditemukan</div>
                <?php else: ?>
                    <div style="color:var(--text-dim); font-size:0.85rem;">Hasil pencarian akan tampil di sini...</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">MODUL 03 — INJEKSI BERBASIS ERROR · LAB PRAKTIK SQL INJECTION</footer>
<script src="../../assets/js/main.js"></script>
</body>
</html>
