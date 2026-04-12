/**
 * ParkWise - app.js
 * Global JS: sidebar, clock, search, QR, print, helpers
 */

'use strict';

// =========================================================
// SIDEBAR TOGGLE
// =========================================================
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebarOverlay');
  if (!sidebar) return;
  sidebar.classList.toggle('open');
  overlay.classList.toggle('open');
  document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
}

// =========================================================
// LIVE CLOCK
// =========================================================
function startClock() {
  const el = document.getElementById('topbarTime');
  if (!el) return;

  function tick() {
    const now  = new Date();
    const days = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
    const day  = days[now.getDay()];
    const date = now.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' });
    const time = now.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit', second:'2-digit' });
    el.textContent = `${day}, ${date} — ${time}`;
  }

  tick();
  setInterval(tick, 1000);
}

// =========================================================
// FLASH AUTO DISMISS
// =========================================================
function initFlash() {
  const flash = document.getElementById('flashMsg');
  if (flash) {
    setTimeout(() => {
      flash.style.transition = 'opacity 0.5s, transform 0.5s';
      flash.style.opacity = '0';
      flash.style.transform = 'translateY(-10px)';
      setTimeout(() => flash.remove(), 500);
    }, 4500);
  }
}

// =========================================================
// TABLE SEARCH (client-side, no reload)
// =========================================================
function initTableSearch(inputId, tableBodyId) {
  const input = document.getElementById(inputId);
  const tbody = document.getElementById(tableBodyId);
  if (!input || !tbody) return;

  input.addEventListener('input', () => {
    const q = input.value.toLowerCase().trim();
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.forEach(row => {
      row.style.display = q === '' || row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}

// =========================================================
// MODAL HELPERS
// =========================================================
function openModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.add('open');
}

function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('open');
}

// Close on overlay click
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});

// ESC to close modal
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
  }
});

// =========================================================
// QR CODE GENERATOR (inline, no external lib needed for basic)
// Using a simple data-URI approach; for production use qrcode.js
// =========================================================
function generateQR(containerId, data) {
  const container = document.getElementById(containerId);
  if (!container) return;

  // Load qrcode.js dynamically
  if (window.QRCode) {
    new QRCode(container, {
      text: data || 'PARKWISE-PAYMENT',
      width: 120,
      height: 120,
      colorDark: '#002147',
      colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.M,
    });
  } else {
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
    script.onload = () => {
      new QRCode(container, {
        text: data || 'PARKWISE-PAYMENT',
        width: 120,
        height: 120,
        colorDark: '#002147',
        colorLight: '#ffffff',
        correctLevel: QRCode.CorrectLevel.M,
      });
    };
    document.head.appendChild(script);
  }
}

// =========================================================
// PRINT STRUK
// =========================================================
function printStruk(elId) {
  const el = document.getElementById(elId);
  if (!el) return;

  const win = window.open('', '_blank', 'width=420,height=600');
  win.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Struk Parkir — ParkWise</title>
      <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Courier New', monospace; padding: 20px; font-size: 12px; }
        .struk-wrap { max-width: 360px; margin: 0 auto; }
        .text-center { text-align: center; }
        .struk-title { font-size: 1.3rem; font-weight: bold; }
        .struk-sub { font-size: 10px; color: #666; }
        hr { border: none; border-top: 1px dashed #ccc; margin: 10px 0; }
        .struk-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .struk-total { background: #002147; color: #d2b48c; padding: 8px 12px; display: flex; justify-content: space-between; font-weight: bold; margin-top: 10px; border-radius: 4px; }
        .struk-footer { text-align: center; margin-top: 12px; font-size: 10px; color: #666; }
        .qr-box { width: 100px; height: 100px; border: 1px solid #002147; margin: 10px auto; display: flex; align-items: center; justify-content: center; font-size: 9px; color: #999; }
      </style>
    </head>
    <body>
      <div class="struk-wrap">
        ${el.innerHTML}
      </div>
      <script>window.onload = () => { window.print(); window.close(); }<\/script>
    </body>
    </html>
  `);
  win.document.close();
}

// =========================================================
// KONFIRMASI DELETE
// =========================================================
function confirmDelete(formId, nama) {
  if (confirm(`Hapus "${nama}"? Tindakan ini tidak dapat dibatalkan.`)) {
    document.getElementById(formId).submit();
  }
}

// =========================================================
// FORMAT NOMINAL (input mask rupiah)
// =========================================================
function formatNominal(input) {
  let val = input.value.replace(/\D/g, '');
  input.value = val ? parseInt(val).toLocaleString('id-ID') : '';
}

// =========================================================
// CAPACITY BAR ANIMATION
// =========================================================
function animateCapacityBars() {
  document.querySelectorAll('.capacity-fill[data-pct]').forEach(bar => {
    const pct = parseFloat(bar.dataset.pct) || 0;
    bar.style.width = '0%';
    setTimeout(() => {
      bar.style.width = pct + '%';
      if (pct >= 90) bar.classList.add('full');
      else if (pct >= 70) bar.classList.add('warn');
    }, 100);
  });
}

// =========================================================
// NOTIFICATION BADGE (check via AJAX)
// =========================================================
function checkNotifications() {
  const dot = document.getElementById('notifDot');
  if (!dot) return;

  fetch('/api/notifications.php')
    .then(r => r.json())
    .then(data => {
      if (data.count > 0) {
        dot.style.display = 'block';
        dot.title = `${data.count} notifikasi baru`;
      }
    })
    .catch(() => {}); // silent
}

// =========================================================
// METODE BAYAR TOGGLE (Tunai / QRIS)
// =========================================================
function initMetodeBayar() {
  const radios = document.querySelectorAll('input[name="metode_bayar"]');
  const qrSection = document.getElementById('qrSection');
  if (!radios.length || !qrSection) return;

  radios.forEach(radio => {
    radio.addEventListener('change', () => {
      if (radio.value === 'qris' && radio.checked) {
        qrSection.classList.remove('hidden');
        const trxId = document.getElementById('trxIdForQr')?.value || 'TRX-001';
        generateQR('qrCanvas', `PARKWISE|${trxId}|QRIS`);
      } else {
        qrSection.classList.add('hidden');
      }
    });
  });
}

// =========================================================
// HITUNG DURASI & BIAYA OTOMATIS (client preview)
// =========================================================
function hitungBiayaPreview() {
  const masuk    = document.getElementById('previewMasuk')?.value;
  const tarif    = parseFloat(document.getElementById('previewTarif')?.value || 0);
  const durEl    = document.getElementById('previewDurasi');
  const biayaEl  = document.getElementById('previewBiaya');

  if (!masuk || !tarif || !durEl || !biayaEl) return;

  const now      = new Date();
  const masukDt  = new Date(masuk);
  const diffMin  = Math.max(0, (now - masukDt) / 60000);
  const durasi   = Math.max(1, Math.ceil(diffMin / 60));
  const biaya    = durasi * tarif;

  durEl.textContent  = durasi + ' jam';
  biayaEl.textContent = 'Rp ' + biaya.toLocaleString('id-ID');
}

// =========================================================
// INIT ALL
// =========================================================
document.addEventListener('DOMContentLoaded', () => {
  startClock();
  initFlash();
  animateCapacityBars();
  initMetodeBayar();
  checkNotifications();

  // Re-create lucide icons (if loaded)
  if (window.lucide) lucide.createIcons();
});
