<?php
/**
 * Database Connection & Progress Tracker
 * SQL Injection Lab - Educational Purpose
 */

$LAB_FLAGS = [
    '01' => 'FLAG{admin_account_compromised_via_sqli}',
    '02' => 'FLAG{union_based_exfiltration_success}',
    '03' => 'FLAG{errors_are_information_disclosure}',
    '04' => 'FLAG{blind_boolean_no_output_needed}',
    '05' => 'FLAG{time_delays_reveal_all_secrets}',
    '06' => 'FLAG{stored_payload_detonated_second_order}',
    '07' => 'FLAG{stacked_insert_win}',
    '08' => 'FLAG{sql_injection_master_pwned_all_labs_2024}'
];

function getDB() {
    $dbPath = __DIR__ . '/database.db';
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        ensureProgressTable($db);
        return $db;
    } catch (PDOException $e) {
        die('<div style="color:#f43f5e;font-family:monospace;padding:20px;background:#0f172a;border:1px solid #f43f5e;border-radius:8px;margin:20px;">
            [DB ERROR] Tidak dapat terhubung ke basis data: ' . htmlspecialchars($e->getMessage()) . '<br><br>
            Silakan jalankan <a href="/setup.php" style="color:#10b981;text-decoration:underline;">setup.php</a> terlebih dahulu untuk inisialisasi basis data.
        </div>');
    }
}

function dbExists() {
    return file_exists(__DIR__ . '/database.db');
}

function ensureProgressTable($db) {
    $db->exec("CREATE TABLE IF NOT EXISTS lab_progress (
        lab_id TEXT PRIMARY KEY,
        flag_submitted TEXT,
        completed_at DATETIME DEFAULT (datetime('now', 'localtime'))
    )");
}

function getCompletedLabs() {
    if (!dbExists()) return [];
    try {
        $db = getDB();
        $stmt = $db->query("SELECT lab_id, completed_at FROM lab_progress");
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['lab_id']] = $row['completed_at'];
        }
        return $results;
    } catch (Exception $e) {
        return [];
    }
}

function isLabCompleted($labId) {
    $completed = getCompletedLabs();
    return isset($completed[$labId]);
}

function submitLabFlag($labId, $submittedFlag) {
    global $LAB_FLAGS;
    $cleanFlag = trim($submittedFlag);

    if (empty($cleanFlag)) {
        return ['success' => false, 'message' => 'Flag tidak boleh kosong!'];
    }

    $expectedFlag = $LAB_FLAGS[$labId] ?? null;

    if ($expectedFlag && strcasecmp($cleanFlag, $expectedFlag) === 0) {
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT OR REPLACE INTO lab_progress (lab_id, flag_submitted, completed_at) VALUES (?, ?, datetime('now', 'localtime'))");
            $stmt->execute([$labId, $cleanFlag]);
            return ['success' => true, 'message' => '🎉 Flag Valid! Modul ' . $labId . ' berhasil diselesaikan!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Gagal menyimpan progres: ' . $e->getMessage()];
        }
    }

    return ['success' => false, 'message' => '❌ Flag tidak valid. Periksa kembali payload dan hasil ekstraksi Anda!'];
}

function toggleManualLab($labId) {
    try {
        $db = getDB();
        if (isLabCompleted($labId)) {
            $stmt = $db->prepare("DELETE FROM lab_progress WHERE lab_id = ?");
            $stmt->execute([$labId]);
            return ['success' => true, 'completed' => false, 'message' => 'Status penyelesaian modul ' . $labId . ' telah direset.'];
        } else {
            $stmt = $db->prepare("INSERT INTO lab_progress (lab_id, flag_submitted, completed_at) VALUES (?, ?, datetime('now', 'localtime'))");
            $stmt->execute([$labId, 'MANUAL_CONFIRMED']);
            return ['success' => true, 'completed' => true, 'message' => '✓ Modul ' . $labId . ' berhasil ditandai selesai!'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Gagal mengubah status: ' . $e->getMessage()];
    }
}

function resetAllLabProgress() {
    try {
        $db = getDB();
        $db->exec("DELETE FROM lab_progress");
        return ['success' => true, 'message' => 'Seluruh progres lab berhasil direset ke 0%.'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Gagal mereset progres: ' . $e->getMessage()];
    }
}

/**
 * Render Flag Submission & Completion Component
 */
function renderFlagSubmissionBox($labId) {
    global $LAB_FLAGS;
    $isDone = isLabCompleted($labId);
    $completedAt = '';
    if ($isDone) {
        $all = getCompletedLabs();
        $completedAt = $all[$labId] ?? '';
    }
    ?>
    <div class="panel" style="border-color: <?= $isDone ? 'rgba(16, 185, 129, 0.4)' : 'rgba(6, 182, 212, 0.3)' ?>; background: <?= $isDone ? 'rgba(16, 185, 129, 0.04)' : 'var(--bg-surface)' ?>;">
        <div class="panel-header">
            <div class="panel-title" style="color: <?= $isDone ? '#34d399' : '#06b6d4' ?>;">
                <?= $isDone ? '🏆 MODUL SELESAI' : '🚩 SUBMIT FLAG / KONFIRMASI PROGRES' ?>
            </div>
            <?php if ($isDone): ?>
                <span class="badge" style="background:rgba(16,185,129,0.15); color:#34d399; border:1px solid #10b981;">✓ Terverifikasi</span>
            <?php endif; ?>
        </div>

        <?php if ($isDone): ?>
            <div style="padding:12px 16px; background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.25); border-radius:8px; margin-bottom:16px;">
                <div style="color:#34d399; font-weight:700; font-size:0.95rem; margin-bottom:4px;">
                    ✓ Selamat! Anda telah menyelesaikan Modul <?= $labId ?>
                </div>
                <div style="color:var(--text-dim); font-size:0.8rem;">
                    Terselesaikan pada: <?= htmlspecialchars($completedAt) ?>
                </div>
            </div>
            <form method="POST" style="display:inline-block;">
                <input type="hidden" name="action_progress" value="toggle_manual">
                <input type="hidden" name="target_lab" value="<?= $labId ?>">
                <button type="submit" class="btn btn-cyan btn-sm" style="border-color:var(--border-subtle); color:var(--text-muted);">
                    ↺ Reset Status Modul Ini (Coba Lagi)
                </button>
            </form>
        <?php else: ?>
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:14px; line-height:1.5;">
                Temukan flag rahasia (format: <code>FLAG{...}</code>) melalui eksploitasi query di atas, lalu masukkan ke kolom di bawah untuk mencatat progres belajar Anda:
            </p>
            <form method="POST" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px;">
                <input type="hidden" name="action_progress" value="submit_flag">
                <input type="hidden" name="target_lab" value="<?= $labId ?>">
                <input type="text" name="flag_input" placeholder="Masukkan flag yang ditemukan (contoh: FLAG{...})" 
                       style="flex:1; min-width:240px; background:#030712; border:1px solid var(--border-subtle); border-radius:6px; padding:10px 14px; color:#34d399; font-family:'JetBrains Mono',monospace; font-size:0.85rem;" required>
                <button type="submit" class="btn btn-primary" style="padding:10px 18px;">
                    🚀 Kirim Flag
                </button>
            </form>
            <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--border-subtle); padding-top:12px; margin-top:10px;">
                <span style="font-size:0.78rem; color:var(--text-dim);">Sudah paham konsep tanpa submit flag?</span>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action_progress" value="toggle_manual">
                    <input type="hidden" name="target_lab" value="<?= $labId ?>">
                    <button type="submit" class="btn btn-cyan btn-sm">
                        ✓ Tandai Selesai Manual
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
?>
