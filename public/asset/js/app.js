(function () {
  const base = window.KOSTRACK_BASE || 'index.php';
  const publicBase = base.replace('/index.php', '');
  const rupiah = (value) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(Number(value || 0));
  const monthName = (m) => ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][Number(m)] || '-';
  const esc = (text) => String(text ?? '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[s]));

  document.addEventListener('click', (event) => {
    const openBtn = event.target.closest('[data-open-modal]');
    if (openBtn) {
      const modal = document.getElementById(openBtn.dataset.openModal);
      if (openBtn.dataset.fillRoom) fillRoomForm(JSON.parse(openBtn.dataset.fillRoom));
      if (openBtn.dataset.complaintId) {
        document.getElementById('respondComplaintId').value = openBtn.dataset.complaintId;
        document.getElementById('respondComplaintText').textContent = openBtn.dataset.complaintText;
      }
      modal && modal.showModal();
    }
    if (event.target.matches('[data-close-modal]')) {
      event.target.closest('dialog').close();
    }
  });

  document.addEventListener('submit', (event) => {
    const form = event.target;
    if (form.dataset.confirm && !confirm(form.dataset.confirm)) {
      event.preventDefault();
    }
  });

  document.querySelectorAll('[data-numeric]').forEach(input => {
    input.addEventListener('input', () => {
      const clean = input.value.replace(/[^0-9]/g, '');
      if (input.value !== clean) {
        input.value = clean;
        alert('Kolom nominal hanya boleh berisi angka.');
      }
    });
  });

  document.querySelectorAll('[data-table-search]').forEach(input => {
    const table = document.querySelector(input.dataset.tableSearch);
    if (!table) return;
    input.addEventListener('input', () => {
      const q = input.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  });

  document.querySelectorAll('[data-password-strength]').forEach(input => {
    input.addEventListener('input', () => {
      const fill = document.getElementById('strengthFill');
      const label = document.getElementById('strengthLabel');
      if (!fill || !label) return;
      let score = 0;
      if (input.value.length >= 8) score++;
      if (/[A-Z]/.test(input.value)) score++;
      if (/[0-9]/.test(input.value)) score++;
      if (/[^A-Za-z0-9]/.test(input.value)) score++;
      const levels = [
        { pct: '0%', color: '#e2e8f0', text: 'Masukkan password' },
        { pct: '25%', color: '#dc2626', text: 'Sangat Lemah' },
        { pct: '50%', color: '#b45309', text: 'Sedang' },
        { pct: '75%', color: '#12499d', text: 'Kuat' },
        { pct: '100%', color: '#15803d', text: 'Sangat Kuat' },
      ];
      const level = input.value.length === 0 ? levels[0] : levels[score];
      fill.style.width = level.pct;
      fill.style.background = level.color;
      label.textContent = level.text;
      label.style.color = level.color;
    });
  });

  const registerRole = document.getElementById('registerRole');
  const roomSelectWrap = document.getElementById('roomSelectWrap');
  const registerRoom = document.getElementById('registerRoom');
  const leaseMonthsWrap = document.getElementById('leaseMonthsWrap');
  const leaseMonths = document.getElementById('leaseMonths');
  const ownerCodeWrap = document.getElementById('ownerCodeWrap');
  const ownerCode = document.getElementById('ownerCode');
  if (registerRole && roomSelectWrap && registerRoom) {
    const syncRoomRequirement = () => {
      const isTenant = registerRole.value === 'penghuni';
      const isOwner = registerRole.value === 'owner';
      roomSelectWrap.style.display = isTenant ? '' : 'none';
      if (leaseMonthsWrap) leaseMonthsWrap.style.display = isTenant ? '' : 'none';
      if (ownerCodeWrap) ownerCodeWrap.style.display = isOwner ? '' : 'none';
      registerRoom.required = isTenant;
      if (leaseMonths) leaseMonths.required = isTenant;
      if (ownerCode) ownerCode.required = isOwner;
      if (!isTenant) registerRoom.value = '';
      if (!isOwner && ownerCode) ownerCode.value = '';
    };
    registerRole.addEventListener('change', syncRoomRequirement);
    syncRoomRequirement();
  }

  function fillRoomForm(room) {
    document.getElementById('editRoomId').value = room.id;
    document.getElementById('editRoomNumber').value = room.room_number;
    document.getElementById('editRoomType').value = room.type;
    document.getElementById('editRoomPrice').value = String(Math.floor(Number(room.price)));
  }

  const roomSearch = document.getElementById('roomSearch');
  if (roomSearch) {
    roomSearch.addEventListener('input', debounce(async () => {
      const res = await fetch(`${base}?route=api/rooms&q=${encodeURIComponent(roomSearch.value)}`);
      const data = await res.json();
      document.getElementById('roomRows').innerHTML = data.rows.map(roomRow).join('');
    }, 250));
  }

  function roomRow(row) {
    const image = row.image ? `<img class="thumb" src="${publicBase}/uploads/rooms/${esc(row.image)}" alt="Kamar">` : '-';
    return `<tr>
      <td>${esc(row.room_number)}</td>
      <td>${esc(row.type)}</td>
      <td>${rupiah(row.price)}</td>
      <td><span class="badge ${row.status === 'terisi' ? 'green' : 'neutral'}">${esc(row.status).toUpperCase()}</span></td>
      <td>${esc(row.tenant_name || '-')}</td>
      <td>${image}</td>
      <td class="actions">
        <button class="btn small" data-fill-room='${esc(JSON.stringify(row))}' data-open-modal="roomEditModal">Edit</button>
        <form method="post" action="${base}?route=owner/deleteRoom" data-confirm="Hapus kamar ini?">
          <input type="hidden" name="id" value="${Number(row.id)}">
          <button class="btn small danger" type="submit">Hapus</button>
        </form>
      </td>
    </tr>`;
  }
/* Lanjutin */