<?php
/**
 * =============================================================
 * LAB 06 — Injeksi SQL Second-Order
 * KERENTANAN: Input disimpan aman, namun dieksekusi secara rentan di query tahap kedua
 * =============================================================
 */
require_once '../../db.php';

$msg = '';
$msg_type = 'info';
$second_order_result = null;
$second_query_display = '';
$flagFeedback = null;

$db = getDB();

// Penanganan pengiriman flag atau konfirmasi manual
if (isset($_POST['action_progress'])) {
    if ($_POST['action_progress'] === 'submit_flag') {
        $flagFeedback = submitLabFlag('06', $_POST['flag_input'] ?? '');
    } elseif ($_POST['action_progress'] === 'toggle_manual') {
        $flagFeedback = toggleManualLab('06');
    }
}

// Menangani Registrasi Pengguna (Tahap 1: Penyimpanan)
if (isset($_POST['action']) && $_POST['action'] === 'register' && !isset($_POST['action_progress'])) {
    $username = $_POST['reg_username'] ?? '';
    $display_name = $_POST['reg_display'] ?? '';
    $bio = $_POST['reg_bio'] ?? '';

    if (!empty($username)) {
        try {
            $stmt = $db->prepare("INSERT INTO registered_users (username, display_name, bio) VALUES (?, ?, ?)");
            $stmt->execute([$username, $display_name, $bio]);
            $msg = "Pengguna berhasil didaftarkan! Sekarang buka profil di Tahap 2 untuk meledakkan payload.";
            $msg_type = "success";
        } catch (PDOException $e) {
            $msg = "Gagal mendaftar: " . $e->getMessage();
            $msg_type = "error";
        }
    }
}

// Menangani Inspeksi Profil (Tahap 2: Ledakan / Detonasi)
if (isset($_POST['action']) && $_POST['action'] === 'view_profile' && !isset($_POST['action_progress'])) {
    $target_id = $_POST['profile_id'] ?? 0;
    try {
        $stmt = $db->prepare("SELECT * FROM registered_users WHERE id = ?");
        $stmt->execute([$target_id]);
        $user_record = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_record) {
            $stored_username = $user_record['username'];
            $second_query = "SELECT id, username, role, secret FROM users WHERE username = '$stored_username'";
            $second_query_display = $second_query;

            $exec_stmt = $db->query($second_query);
            $second_order_result = $exec_stmt->fetchAll(PDO::FETCH_ASSOC);
            $msg = "Query tahap kedua berhasil dieksekusi!";
            $msg_type = "success";
        } else {
            $msg = "Data pengguna tidak ditemukan.";
            $msg_type = "error";
        }
    } catch (PDOException $e) {
        $msg = "SQL Error pada query kedua: " . $e->getMessage();
        $msg_type = "error";
    }
}

$all_registered = $db->query("SELECT * FROM registered_users ORDER BY id DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 06 — Injeksi Second-Order | Lab SQLi</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<canvas id="matrix-canvas"></canvas>

<div class="lab-page wrapper">
    <a href="../../index.php" class="nav-back">← KEMBALI KE DASHBOARD</a>

    <div class="lab-header">
        <div class="lab-header-info">
            <div style="color:var(--text-dim); font-size:0.78rem; font-family:'JetBrains Mono',monospace; letter-spacing:2px; margin-bottom:8px;">MODUL 06 / 08</div>
            <h1 style="font-size:1.85rem; margin-bottom:8px;">💣 Injeksi SQL Second-Order</h1>
            <div class="meta" style="display:flex; align-items:center; gap:10px;">
                <span class="badge badge-hard">Sulit</span>
                <span class="vuln-label">⚠ RENTAN</span>
                <span style="color:var(--text-dim); font-size:0.82rem;">Teknik: Eksekusi Tertunda dari Data Tersimpan</span>
                <?php if (isLabCompleted('06')): ?>
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
            Second-Order SQL Injection terjadi ketika input disimpan secara aman ke database pada tahap awal, namun kemudian dibaca kembali dan digunakan tanpa sanitasi pada query sekunder.
        </p>
    </div>

    <!-- Kotak Submit Flag / Progres Modul 06 -->
    <?php renderFlagSubmissionBox('06'); ?>

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
                    <div class="panel-title">TAHAP 1: TANAM PAYLOAD KE DATABASE</div>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <label>Nama Pengguna (Suntikkan Sintaks SQL Di Sini)</label>
                        <input type="text" name="reg_username" id="reg_username" placeholder="Contoh: ' UNION SELECT 1,name,value,description FROM flags-- " required>
                    </div>
                    <div class="form-group">
                        <label>Nama Tampilan (Display Name)</label>
                        <input type="text" name="reg_display" value="Alias Penyerang">
                    </div>
                    <div class="form-group">
                        <label>Bio Profil</label>
                        <input type="text" name="reg_bio" value="Uji coba second-order sqli">
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">
                        📥 &nbsp; 1. SIMPAN KE DATABASE SECARA AMAN
                    </button>
                </form>
            </div>

            <div class="panel" style="border-color:rgba(6,182,212,0.3);">
                <div class="panel-header">
                    <div class="panel-title" style="color:#06b6d4;">TAHAP 2: PICU DETONASI PAYLOAD</div>
                </div>
                <p style="font-size:0.82rem; color:var(--text-dim); margin-bottom:12px;">
                    Pilih akun pengguna yang sudah tersimpan untuk menjalankan query sekunder:
                </p>
                <?php if (empty($all_registered)): ?>
                    <div style="color:var(--text-dim); font-size:0.8rem;">Belum ada akun terdaftar. Buat satu akun di atas!</div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:8px;">
                    <?php foreach ($all_registered as $reg): ?>
                        <form method="POST" style="display:flex; justify-content:space-between; align-items:center; background:#030712; padding:10px 14px; border-radius:6px; border:1px solid var(--border-subtle);">
                            <input type="hidden" name="action" value="view_profile">
                            <input type="hidden" name="profile_id" value="<?= $reg['id'] ?>">
                            <div style="font-size:0.82rem;">
                                <span style="color:#34d399; font-weight:700;">#<?= $reg['id'] ?></span>
                                <span style="color:#fbbf24; margin-left:8px; font-family:'JetBrains Mono',monospace;"><?= htmlspecialchars($reg['username']) ?></span>
                            </div>
                            <button type="submit" class="btn btn-cyan btn-sm">
                                💥 DETONASI
                            </button>
                        </form>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">QUERY SQL SEKUNDER (TAHAP DETONASI)</div>
                </div>
                <div class="code-block" style="color:#06b6d4; word-break:break-all;">
<?= $second_query_display ? htmlspecialchars($second_query_display) : '-- Menunggu pemicuan Tahap 2...' ?>
                </div>
            </div>

            <div class="result-panel <?= !empty($second_order_result) ? 'result-success' : '' ?>">
                <div class="panel-header">
                    <div class="panel-title" style="color:#34d399;">DATA HASIL EKSTRAKSI</div>
                </div>
                <?php if (!empty($second_order_result)): ?>
                    <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Peran</th>
                                <th>Flag Rahasia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($second_order_result as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['username'] ?? '') ?></td>
                                <td style="color:#06b6d4;"><?= htmlspecialchars($row['role'] ?? '') ?></td>
                                <td class="flag" style="cursor:pointer;" title="Klik untuk salin" onclick="copyText('<?= htmlspecialchars($row['secret'] ?? '') ?>')">
                                    <?= htmlspecialchars($row['secret'] ?? '') ?> 📋
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php else: ?>
                    <div style="color:var(--text-dim); font-size:0.85rem;">Belum ada data. Klik DETONASI pada profil di sebelah kiri.</div>
                <?php endif; ?>
            </div>

            <div class="hint-box">
                <button class="hint-toggle" onclick="toggleHint('hint6')">
                    💡 PAYLOAD SECOND-ORDER <span class="arrow">▼</span>
                </button>
                <div class="hint-content" id="hint6">
                    <p><strong style="color:#fbbf24;">Payload untuk ditanam di kolom username:</strong></p>
                    <ul class="payload-list">
                        <li data-target="reg_username" data-payload="' UNION SELECT 1,name,value,description FROM flags--">' UNION SELECT 1,name,value,description FROM flags-- (Ambil Flag)</li>
                        <li data-target="reg_username" data-payload="admin'--">admin'-- (Bypass akun admin)</li>
                        <li data-target="reg_username" data-payload="' OR '1'='1">' OR '1'='1 (Dump seluruh data)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">MODUL 06 — INJEKSI SECOND-ORDER · LAB PRAKTIK SQL INJECTION</footer>
<script src="../../assets/js/main.js"></script>
</body>
</html>
