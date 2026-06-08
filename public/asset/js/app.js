(function () {
  const base = window.KOSTRACK_BASE || 'index.php';
  const publicBase = window.KOSTRACK_PUBLIC_BASE || base.replace('/index.php', '');
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
 const historyMonth = document.getElementById('historyMonth');
  const historyYear = document.getElementById('historyYear');
  if (historyMonth && historyYear) {
    [historyMonth, historyYear].forEach(el => el.addEventListener('change', loadHistory));
  }

  async function loadHistory() {
    const url = `${base}?route=api/history&month=${historyMonth.value}&year=${historyYear.value}`;
    const data = await (await fetch(url)).json();
    document.getElementById('historyRows').innerHTML = data.rows.map(row => `<tr>
      <td>${esc(row.description)}</td>
      <td>${esc(row.paid_at || '-')}</td>
      <td>${monthName(row.period_month)} ${esc(row.period_year)}</td>
      <td>${rupiah(row.amount)}</td>
      <td><span class="badge ${row.status === 'lunas' ? 'green' : 'red'}">${esc(row.status).replace('_', ' ').toUpperCase()}</span></td>
    </tr>`).join('');
  }

  const financeMonth = document.getElementById('financeMonth');
  const financeYear = document.getElementById('financeYear');
  const financeSearch = document.getElementById('financeSearch');
  if (financeMonth && financeYear) {
    [financeMonth, financeYear].forEach(el => el.addEventListener('change', loadFinance));
    financeSearch && financeSearch.addEventListener('input', debounce(loadFinance, 250));
  }

  async function loadFinance() {
    const url = `${base}?route=api/finance&month=${financeMonth.value}&year=${financeYear.value}&q=${encodeURIComponent(financeSearch.value)}`;
    const data = await (await fetch(url)).json();
    document.getElementById('incomeRows').innerHTML = data.income.map(row => `<tr>
      <td>${esc(row.full_name)} / ${esc(row.room_number)}</td>
      <td>${esc(row.paid_at)}</td>
      <td>${monthName(row.period_month)} ${esc(row.period_year)}</td>
      <td>${rupiah(row.amount)}</td>
    </tr>`).join('');
    document.getElementById('expenseRows').innerHTML = data.expenses.map(row => `<tr>
      <td>${esc(row.expense_date)}</td><td>${esc(row.description)}</td><td>${rupiah(row.amount)}</td>
      <td><form method="post" action="${base}?route=owner/deleteExpense" data-confirm="Hapus pengeluaran ini?"><input type="hidden" name="id" value="${Number(row.id)}"><button class="btn small danger">Hapus</button></form></td>
    </tr>`).join('');
    document.getElementById('incomeTotal').textContent = rupiah(data.incomeTotal);
    document.getElementById('expenseTotal').textContent = rupiah(data.expenseTotal);
    document.getElementById('netProfit').textContent = rupiah(Number(data.incomeTotal) - Number(data.expenseTotal));
  }

  const chart = document.getElementById('financeChart');
  if (chart) drawChart(chart);

  const dpInputWrap = document.getElementById('dpInputWrap');
  if (dpInputWrap) {
    const syncDpInput = () => {
      const selected = document.querySelector('input[name="payment_method"]:checked')?.value;
      dpInputWrap.classList.toggle('is-visible', selected === 'dp');
      const input = dpInputWrap.querySelector('input');
      if (input) input.required = selected === 'dp';
    };
    document.querySelectorAll('input[name="payment_method"]').forEach(input => input.addEventListener('change', syncDpInput));
    syncDpInput();
  }

  const paymentJump = document.querySelector('[data-payment-jump]');
  if (paymentJump) {
    paymentJump.addEventListener('change', () => {
      window.location.href = `${base}?route=penghuni/payment&bill_id=${paymentJump.value}`;
    });
  }

  function drawChart(canvas) {
    const ctx = canvas.getContext('2d');
    const data = JSON.parse(canvas.dataset.chart || '{}');
    const width = canvas.width = canvas.clientWidth * window.devicePixelRatio;
    const height = canvas.height = 260 * window.devicePixelRatio;
    const values = Object.values(data).flatMap(v => [Number(v.income), Number(v.expense)]);
    const max = Math.max(...values, 1);
    const pad = 34 * window.devicePixelRatio;
    const gap = 8 * window.devicePixelRatio;
    const group = (width - pad * 2) / 12;
    ctx.clearRect(0, 0, width, height);
    ctx.font = `${12 * window.devicePixelRatio}px Arial`;
    for (let i = 1; i <= 12; i++) {
      const x = pad + (i - 1) * group + gap;
      const incomeH = (Number(data[i]?.income || 0) / max) * (height - pad * 2);
      const expenseH = (Number(data[i]?.expense || 0) / max) * (height - pad * 2);
      ctx.fillStyle = '#0f766e';
      ctx.fillRect(x, height - pad - incomeH, group / 2 - gap, incomeH);
      ctx.fillStyle = '#dc2626';
      ctx.fillRect(x + group / 2, height - pad - expenseH, group / 2 - gap, expenseH);
      ctx.fillStyle = '#64748b';
      ctx.fillText(String(i), x + group / 3, height - 8 * window.devicePixelRatio);
    }
  }

  function debounce(fn, wait) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn(...args), wait);
    };
  }
})();
/* Done */
