<?php
/**
 * =============================================================
 * LAB 01 — Bypass Login Klasik
 * KERENTANAN: Penggabungan string langsung ke dalam query SQL
 * =============================================================
 */
require_once '../../db.php';

$username = '';
$password = '';
$result = null;
$query_display = "SELECT * FROM users WHERE username='\$username' AND password='\$password'";
$error_msg = '';
$success = false;
$flagFeedback = null;

// Penanganan pengiriman flag atau konfirmasi manual
if (isset($_POST['action_progress'])) {
    if ($_POST['action_progress'] === 'submit_flag') {
        $flagFeedback = submitLabFlag('01', $_POST['flag_input'] ?? '');
    } elseif ($_POST['action_progress'] === 'toggle_manual') {
        $flagFeedback = toggleManualLab('01');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action_progress'])) {
    // ⚠️ SENGAJA DIBUAT RENTAN - Penggabungan string tanpa sanitasi
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // QUERY RENTAN - JANGAN DIGUNAKAN DI APLIKASI PRODUKSI
    $raw_query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $query_display = $raw_query;

    try {
        $db = getDB();
        $stmt = $db->query($raw_query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($rows)) {
            $result = $rows;
            $success = true;
        } else {
            $error_msg = 'Nama pengguna atau kata sandi tidak valid.';
        }
    } catch (PDOException $e) {
        $error_msg = '[SQL ERROR] ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 01 — Bypass Login Klasik | Lab SQLi</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<canvas id="matrix-canvas"></canvas>

<div class="lab-page wrapper">
    <a href="../../index.php" class="nav-back">← KEMBALI KE DASHBOARD</a>

    <div class="lab-header">
        <div class="lab-header-info">
            <div style="color:var(--text-dim); font-size:0.78rem; font-family:'JetBrains Mono',monospace; letter-spacing:2px; margin-bottom:8px;">MODUL 01 / 08</div>
            <h1 style="font-size:1.85rem; margin-bottom:8px;">🔓 Bypass Login Klasik</h1>
            <div class="meta" style="display:flex; align-items:center; gap:10px;">
                <span class="badge badge-easy">Mudah</span>
                <span class="vuln-label">⚠ RENTAN</span>
                <span style="color:var(--text-dim); font-size:0.82rem;">Teknik: Bypass Autentikasi via Injeksi String</span>
                <?php if (isLabCompleted('01')): ?>
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
            Form login mengirimkan input pengguna <strong style="color:#f43f5e;">langsung ke dalam query SQL</strong> melalui penggabungan string (concatenation).
            Dengan menyuntikkan sintaks SQL ke kolom username atau password, penyerang dapat memanipulasi logika query untuk melewati pemeriksaan login sepenuhnya — tanpa mengetahui kata sandi asli akun tersebut.
        </p>
        <div class="code-block">
<span style="color:#64748b;">// KODE RENTAN (Server-Side)</span>
<span style="color:#06b6d4;">$query</span> = <span style="color:#fbbf24;">"SELECT * FROM users WHERE username='</span><span style="color:#f43f5e;">$username</span><span style="color:#fbbf24;">' AND password='</span><span style="color:#f43f5e;">$password</span><span style="color:#fbbf24;">'"</span>;
$db->query($query); <span style="color:#64748b;">// ← Sangat berbahaya!</span>
        </div>
    </div>

    <!-- Kotak Submit Flag / Progres Modul 01 -->
    <?php renderFlagSubmissionBox('01'); ?>

    <div class="grid-2">
        <!-- Form Login -->
        <div>
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">🎯 TARGET: FORM LOGIN</div>
                </div>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Nama Pengguna (Username)</label>
                        <input type="text" name="username" id="username" 
                               placeholder="Masukkan username atau payload..."
                               value="<?= htmlspecialchars($username) ?>">
                    </div>
                    <div class="form-group">
                        <label>Kata Sandi (Password)</label>
                        <input type="text" name="password" id="password"
                               placeholder="Masukkan password atau payload..."
                               value="<?= htmlspecialchars($password) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-full" style="margin-top:10px;">
                        ▶ &nbsp; KIRIM LOGIN
                    </button>
                </form>
            </div>

            <!-- Hasil -->
            <div class="result-panel <?= $success ? 'result-success' : ($error_msg ? 'result-error' : '') ?>">
                <?php if ($success): ?>
                    <div style="margin-bottom:15px;">
                        <span style="color:#34d399; font-size:1.05rem; font-weight:700;">✓ LOGIN BERHASIL DITEMBUS</span>
                        <span style="color:var(--text-dim); font-size:0.8rem; margin-left:10px;">— <?= count($result) ?> baris data dikembalikan</span>
                    </div>
                    <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th><th>Username</th><th>Peran</th><th>Email</th><th>Flag Rahasia</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($result as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td style="font-weight:600; color:#34d399;"><?= htmlspecialchars($row['username']) ?></td>
                                <td><span style="color:<?= $row['role']==='admin'?'#f43f5e':'#06b6d4' ?>; font-weight:600;"><?= htmlspecialchars($row['role']) ?></span></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td class="flag" style="cursor:pointer;" title="Klik untuk salin" onclick="copyText('<?= htmlspecialchars($row['secret']) ?>')"><?= htmlspecialchars($row['secret']) ?> 📋</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php elseif ($error_msg): ?>
                    <div style="color:#f43f5e; font-weight:600;">✗ <?= htmlspecialchars($error_msg) ?></div>
                <?php else: ?>
                    <div style="color:var(--text-dim); font-size:0.85rem;">Menunggu pengiriman data form...</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Kolom Kanan: Live Query & Petunjuk -->
        <div>
            <!-- Live Query -->
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">MONITOR QUERY SQL REAL-TIME</div>
                </div>
                <div class="code-block" style="color:<?= $success ? '#34d399' : ($error_msg ? '#f43f5e' : '#06b6d4') ?>;">
<?= htmlspecialchars($query_display) ?>
                </div>
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action_progress'])): ?>
                <div style="margin-top:10px; font-size:0.78rem; color:var(--text-dim);">
                    Status: <?= $success ? '<span style="color:#34d399; font-weight:600;">✓ Mengembalikan ' . count($result) . ' baris data</span>' : '<span style="color:#f43f5e; font-weight:600;">✗ Data kosong / Terjadi Error</span>' ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Target Tujuan -->
            <div class="panel" style="border-color:rgba(6,182,212,0.3);">
                <div class="panel-header">
                    <div class="panel-title" style="color:#06b6d4;">🎯 TUJUAN LAB</div>
                </div>
                <div class="steps">
                    <div class="step-item">① Bypass login (pengguna mana pun)</div>
                    <div class="step-item">② Login langsung sebagai admin</div>
                    <div class="step-item">③ Temukan flag rahasia admin</div>
                    <div class="step-item">④ Masukkan flag ke kotak di atas</div>
                </div>
            </div>

            <!-- Bantuan & Payload -->
            <div class="hint-box">
                <button class="hint-toggle" onclick="toggleHint('hint1')">
                    💡 LIHAT PETUNJUK & PAYLOAD <span class="arrow">▼</span>
                </button>
                <div class="hint-content" id="hint1">
                    <p><strong style="color:#fbbf24;">Cara kerjanya:</strong> Saat Anda memasukkan <code>' OR '1'='1' --</code> pada kolom username, query SQL akan berubah menjadi:</p>
                    <div class="code-block" style="margin-bottom:15px; font-size:0.8rem;">SELECT * FROM users WHERE username='<span style="color:#f43f5e;">' OR '1'='1' --</span>' AND password='...'
<span style="color:#64748b;">-- Query dibaca: WHERE username='' ATAU '1'='1' (selalu bernilai Benar/True!)</span></div>
                    <p><strong style="color:#fbbf24;">Klik payload berikut untuk langsung mengisi kolom username:</strong></p>
                    <ul class="payload-list">
                        <li data-target="username" data-payload="' OR '1'='1' --">' OR '1'='1' -- (Bypass umum)</li>
                        <li data-target="username" data-payload="admin'--">admin'-- (Login akun admin langsung, password bebas)</li>
                        <li data-target="username" data-payload="' OR 1=1 --">' OR 1=1 -- (Tampilkan semua pengguna)</li>
                    </ul>
                    <p style="margin-top:15px;"><strong style="color:#fbbf24;">Cara Pencegahan:</strong> Gunakan Prepared Statements dengan Parameterized Queries!</p>
                    <div class="code-block" style="font-size:0.8rem; color:#34d399;">
<span style="color:#64748b;">// KODE AMAN:</span>
$stmt = $db->prepare("SELECT * FROM users WHERE username=? AND password=?");
$stmt->execute([$username, $password]);
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">
    <div>MODUL 01 — BYPASS LOGIN KLASIK · LAB PRAKTIK SQL INJECTION</div>
</footer>

<script src="../../assets/js/main.js"></script>
</body>
</html>
