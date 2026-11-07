<?php
/**
 * =============================================================
 * LAB 04 — Blind SQL Injection Berbasis Boolean
 * KERENTANAN: Hanya mengembalikan respon Benar/Salah tanpa output data
 * =============================================================
 */
require_once '../../db.php';

$username = '';
$query_display = "SELECT id FROM users WHERE username='\$username'";
$response = null; // null = belum dikirim, true = ada, false = tidak ada
$error_msg = '';
$flagFeedback = null;

// Penanganan pengiriman flag atau konfirmasi manual
if (isset($_POST['action_progress'])) {
    if ($_POST['action_progress'] === 'submit_flag') {
        $flagFeedback = submitLabFlag('04', $_POST['flag_input'] ?? '');
    } elseif ($_POST['action_progress'] === 'toggle_manual') {
        $flagFeedback = toggleManualLab('04');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action_progress'])) {
    $username = $_POST['username'] ?? '';

    $raw_query = "SELECT id FROM users WHERE username='$username'";
    $query_display = $raw_query;

    try {
        $db = getDB();
        $stmt = $db->query($raw_query);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $response = ($row !== false && !empty($row));
    } catch (PDOException $e) {
        $error_msg = 'Error query (kesalahan sintaks pada payload?)';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 04 — Blind Berbasis Boolean | Lab SQLi</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<canvas id="matrix-canvas"></canvas>

<div class="lab-page wrapper">
    <a href="../../index.php" class="nav-back">← KEMBALI KE DASHBOARD</a>

    <div class="lab-header">
        <div class="lab-header-info">
            <div style="color:var(--text-dim); font-size:0.78rem; font-family:'JetBrains Mono',monospace; letter-spacing:2px; margin-bottom:8px;">MODUL 04 / 08</div>
            <h1 style="font-size:1.85rem; margin-bottom:8px;">👁️ Blind SQLi: Berbasis Boolean</h1>
            <div class="meta" style="display:flex; align-items:center; gap:10px;">
                <span class="badge badge-hard">Sulit</span>
                <span class="vuln-label">⚠ RENTAN</span>
                <span style="color:var(--text-dim); font-size:0.82rem;">Teknik: Menyimpulkan Data dari Respon Benar / Salah</span>
                <?php if (isLabCompleted('04')): ?>
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
            <strong style="color:#f43f5e;">Tidak ada data yang ditampilkan</strong> — aplikasi hanya merespon "Pengguna Ditemukan" atau "Pengguna Tidak Ditemukan". 
            Meskipun demikian, celah SQL injection tetap bisa dieksploitasi dengan menyusun kondisi boolean (Ya / Tidak) untuk menebak isi flag rahasia karakter per karakter.
        </p>
    </div>

    <!-- Kotak Submit Flag / Progres Modul 04 -->
    <?php renderFlagSubmissionBox('04'); ?>

    <div class="grid-2">
        <div>
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">🎯 TARGET: PEMERIKSA KEBERADAAN USERNAME</div>
                </div>

                <?php if ($response !== null): ?>
                <div style="text-align:center; padding:18px; background:<?= $response ? 'rgba(16,185,129,0.06)' : 'rgba(244,63,94,0.06)' ?>; 
                     border:1px solid <?= $response ? '#10b981' : '#f43f5e' ?>; border-radius:10px; margin-bottom:20px;">
                    <div style="font-size:2rem; margin-bottom:6px;"><?= $response ? '✓' : '✗' ?></div>
                    <div style="font-size:1.15rem; font-weight:700; color:<?= $response ? '#34d399' : '#fb7185' ?>;">
                        <?= $response ? 'PENGGUNA DITEMUKAN' : 'PENGGUNA TIDAK DITEMUKAN' ?>
                    </div>
                    <div style="color:var(--text-dim); font-size:0.75rem; margin-top:4px; letter-spacing:1px;">
                        RESPON BOOLEAN: <?= $response ? 'BENAR (TRUE)' : 'SALAH (FALSE)' ?>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Nama Pengguna yang Dicari</label>
                        <input type="text" name="username" id="username"
                               placeholder="admin atau masukkan payload..."
                               value="<?= htmlspecialchars($username) ?>">
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">▶ &nbsp; CEK USERNAME</button>
                </form>

                <?php if ($error_msg): ?>
                <div style="color:#fbbf24; padding:10px; background:rgba(245,158,11,0.06); border:1px solid rgba(245,158,11,0.3); border-radius:6px; font-size:0.82rem; margin-top:10px;">
                    ⚠ <?= htmlspecialchars($error_msg) ?>
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

            <!-- Simulator sqlmap -->
            <div class="panel" style="border-color:rgba(6,182,212,0.3);">
                <div class="panel-header">
                    <div class="panel-title" style="color:#06b6d4;">🤖 SIMULATOR EKSTRAKSI OTOMATIS (ALA SQLMAP)</div>
                </div>
                <p style="font-size:0.82rem; color:var(--text-dim); margin-bottom:12px;">
                    Simulasi pengiriman request berulang untuk membongkar data tanpa tampilan visual:
                </p>
                <select id="extract_target" class="form-group" style="background:#030712; border:1px solid var(--border-subtle); color:#34d399; padding:10px; border-radius:6px; width:100%; font-family:'JetBrains Mono',monospace; margin-bottom:12px;">
                    <option value="">Pilih target ekstraksi...</option>
                    <option value="admin_password">Kata Sandi Akun Admin</option>
                    <option value="admin_email">Email Akun Admin</option>
                    <option value="admin_role">Hak Akses (Role) Admin</option>
                </select>
                <button onclick="startExtraction()" class="btn btn-cyan btn-full" id="extract_btn">
                    ▶ JALANKAN SIMULASI EKSTRAKSI
                </button>
                <div id="extraction_progress" style="margin-top:15px; display:none;">
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" id="progress_fill" style="width:0%"></div>
                    </div>
                    <div id="extracted_chars" style="font-family:'JetBrains Mono',monospace; font-size:1.1rem; color:#34d399; min-height:28px; margin-top:10px; font-weight:700;"></div>
                    <div id="request_count" style="color:var(--text-dim); font-size:0.75rem; margin-top:4px;"></div>
                </div>
            </div>

            <div class="hint-box">
                <button class="hint-toggle" onclick="toggleHint('hint4')">
                    💡 CONTOH PAYLOAD <span class="arrow">▼</span>
                </button>
                <div class="hint-content" id="hint4">
                    <p><strong style="color:#fbbf24;">Petunjuk mengekstrak flag modul 04:</strong></p>
                    <div class="code-block" style="font-size:0.8rem; margin-bottom:10px;">admin' AND (SELECT SUBSTR(value,1,5) FROM flags WHERE name='blind_flag')='FLAG{' --</div>
                    <ul class="payload-list">
                        <li data-target="username" data-payload="admin' AND (SELECT SUBSTR(value,1,5) FROM flags WHERE name='blind_flag')='FLAG{' --">Verifikasi awal flag blind: 'FLAG{'</li>
                        <li data-target="username" data-payload="admin' AND '1'='1">admin' AND '1'='1 (Selalu Benar)</li>
                        <li data-target="username" data-payload="admin' AND '1'='2">admin' AND '1'='2 (Selalu Salah)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">MODUL 04 — BLIND BERBASIS BOOLEAN · LAB PRAKTIK SQL INJECTION</footer>
<script src="../../assets/js/main.js"></script>
<script>
const knownData = {
    admin_password: 'sup3r_s3cr3t_p4ss',
    admin_email: 'admin@sqlilab.local',
    admin_role: 'admin'
};

function startExtraction() {
    const target = document.getElementById('extract_target').value;
    if (!target) { showNotification('Pilih target ekstraksi terlebih dahulu!', 'error'); return; }
    
    const data = knownData[target];
    const progress = document.getElementById('extraction_progress');
    const fill = document.getElementById('progress_fill');
    const chars = document.getElementById('extracted_chars');
    const count = document.getElementById('request_count');
    const btn = document.getElementById('extract_btn');
    
    progress.style.display = 'block';
    btn.disabled = true;
    btn.textContent = '⏳ SEDANG MENGEKSTRAK...';
    chars.textContent = '';
    
    let i = 0;
    let requests = 0;
    
    function extractChar() {
        if (i >= data.length) {
            btn.disabled = false;
            btn.textContent = '▶ JALANKAN SIMULASI EKSTRAKSI';
            count.textContent = `✓ Selesai! Total request: ~${requests}`;
            return;
        }
        
        const char = data[i];
        requests += 7;
        
        setTimeout(() => {
            chars.textContent += char;
            fill.style.width = ((i + 1) / data.length * 100) + '%';
            count.textContent = `Request: ~${requests} | Karakter ${i+1}/${data.length}`;
            i++;
            extractChar();
        }, 120);
    }
    
    extractChar();
}
</script>
</body>
</html>
