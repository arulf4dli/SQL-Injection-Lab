// ============================================================
// LAB SQL INJECTION — JavaScript Interaktif & Utilitas
// ============================================================

// Animasi Latar Belakang Matrix Halus
function initMatrixRain() {
  const canvas = document.getElementById('matrix-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');

  function resize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  }
  resize();

  const chars = '01ABCDEFSELECTWHEREUNIONDROPTABLE10';
  const fontSize = 14;
  const columns = Math.floor(canvas.width / fontSize);
  const drops = new Array(columns).fill(1);

  function draw() {
    ctx.fillStyle = 'rgba(8, 12, 20, 0.08)';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#10b981';
    ctx.font = fontSize + 'px "JetBrains Mono", monospace';

    for (let i = 0; i < drops.length; i++) {
      const char = chars[Math.floor(Math.random() * chars.length)];
      ctx.fillText(char, i * fontSize, drops[i] * fontSize);
      if (drops[i] * fontSize > canvas.height && Math.random() > 0.985) {
        drops[i] = 0;
      }
      drops[i]++;
    }
  }

  setInterval(draw, 45);
  window.addEventListener('resize', resize);
}

// Buka / Tutup Accordion Petunjuk (Hint)
function toggleHint(id) {
  const content = document.getElementById(id);
  if (!content) return;

  const btn = content.previousElementSibling || document.querySelector(`[onclick*="${id}"]`);
  const arrow = btn ? btn.querySelector('.arrow') : null;

  const isOpen = content.classList.contains('open');

  if (isOpen) {
    content.classList.remove('open');
    if (btn) btn.classList.remove('active');
    if (arrow) arrow.textContent = '▼';
  } else {
    content.classList.add('open');
    if (btn) btn.classList.add('active');
    if (arrow) arrow.textContent = '▲';
  }
}

// Buka / Tutup Solusi pada Halaman Walkthrough
function toggleSolution(id) {
  const body = document.getElementById(id);
  if (!body) return;

  const header = body.previousElementSibling || document.querySelector(`[onclick*="${id}"]`);
  const isOpen = body.classList.contains('open');

  if (isOpen) {
    body.classList.remove('open');
    if (header) header.classList.remove('active');
  } else {
    body.classList.add('open');
    if (header) header.classList.add('active');
  }
}

// Otomatis masukkan payload ke kolom input yang dituju
function fillPayload(payload, targetId) {
  const el = document.getElementById(targetId);
  if (el) {
    el.value = payload;
    el.focus();
    showNotification('Payload berhasil dimasukkan ke input target! 🚀');
  } else {
    copyText(payload);
  }
}

// Salin teks ke Clipboard dengan notifikasi
function copyText(text) {
  navigator.clipboard.writeText(text).then(() => {
    showNotification('Teks disalin ke clipboard! 📋');
  }).catch(() => {
    // Fallback jika permission browser ditolak
    const temp = document.createElement('textarea');
    temp.value = text;
    document.body.appendChild(temp);
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);
    showNotification('Teks disalin ke clipboard! 📋');
  });
}

// Toast Notifikasi Modern
function showNotification(msg, type = 'success') {
  const existing = document.querySelector('.notification');
  if (existing) existing.remove();

  const notif = document.createElement('div');
  notif.className = 'notification';
  if (type === 'error') {
    notif.style.borderColor = '#f43f5e';
    notif.style.color = '#fb7185';
  }
  notif.innerHTML = '⚡ ' + msg;
  document.body.appendChild(notif);

  setTimeout(() => {
    notif.style.opacity = '0';
    notif.style.transform = 'translateY(10px)';
    notif.style.transition = 'all 0.3s ease';
    setTimeout(() => notif.remove(), 350);
  }, 2800);
}

// Inisialisasi saat DOM siap
document.addEventListener('DOMContentLoaded', function () {
  initMatrixRain();

  // Daftarkan listener klik untuk semua payload list
  document.querySelectorAll('.payload-list li').forEach(li => {
    const target = li.getAttribute('data-target');
    const rawPayload = li.getAttribute('data-payload');
    
    // Jika ada atribut target atau payload, tambahkan click handler
    if (target || rawPayload) {
      li.style.cursor = 'pointer';
      li.title = 'Klik untuk otomatis mengisi input target';
      li.addEventListener('click', function (e) {
        e.preventDefault();
        const payload = rawPayload || this.textContent.replace(/^[❯>]\s*/, '').trim();
        fillPayload(payload, target);
      });
    }
  });

  // Tombol Salin Cepat untuk semua code block jika diklik
  document.querySelectorAll('.code-block').forEach(cb => {
    cb.style.position = 'relative';
    cb.title = 'Klik dua kali untuk menyalin query';
    cb.addEventListener('dblclick', function () {
      copyText(this.innerText.trim());
    });
  });
});
