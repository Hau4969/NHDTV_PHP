@extends('layouts.app')
@section('title','Đặt vé - ' . $movie->title)

@section('content')
<div class="container py-4">

  {{-- 1. Header + Đổi suất chiếu --}}
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
      <h4 class="fw-bold mb-1">{{ $movie->title }}</h4>
      <div class="text-muted">
        Phòng <b>{{ $room->name }}</b> —
        {{ \Illuminate\Support\Carbon::parse($showtime->start_time)->format('H:i d/m/Y') }}
      </div>
    </div>

    <div class="d-flex gap-2">
      <select class="form-select form-select-sm" onchange="if(this.value) location.href=this.value;">
        @foreach($otherShowtimes as $st)
          @php $url = route('tickets.create',$st->id); @endphp
          <option value="{{ $url }}" {{ $st->id==$showtime->id ? 'selected' : '' }}>
            {{ \Illuminate\Support\Carbon::parse($st->start_time)->format('H:i d/m/Y') }} — {{ $st->room_name }}
          </option>
        @endforeach
      </select>

      <a href="{{ route('movies.show',$movie->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Quay lại phim
      </a>
    </div>
  </div>

  {{-- 2. BẢNG GIÁ VÉ --}}
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <h6 class="fw-bold text-uppercase mb-2">Bảng giá vé suất này</h6>
      <div class="d-flex flex-wrap gap-2">
        @foreach($ticketTypes as $type)
            @php
                $pName = strtolower($type->name);
                $dotColor = '#cbd5e1';
                if(str_contains($pName, 'vip')) $dotColor = '#fbbf24';
                if(str_contains($pName, 'couple') || str_contains($pName, 'đôi')) $dotColor = '#f472b6';
            @endphp

            <div class="d-inline-flex align-items-center border px-3 py-1 rounded-pill bg-white">
                <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:{{$dotColor}};margin-right:8px;"></span>
                <span class="fw-semibold me-1">{{ $type->name }}:</span>
                <span class="fw-bold text-primary">{{ number_format($type->display_price, 0, ',', '.') }} đ</span>
            </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="row g-4">
    {{-- 3. Sơ đồ Ghế --}}
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
          <h6 class="fw-bold text-uppercase text-center mb-3">Màn hình</h6>
          <div class="screen-bar mx-auto mb-5"></div>

          <form id="seatForm" method="POST" action="{{ route('tickets.store',$showtime) }}">
            @csrf
            <input type="hidden" name="voucher_id" id="voucher_id" value="">
            <input type="hidden" name="pay_now" value="0">
            <div id="hiddenInputs"></div>

            <div class="seat-grid mx-auto d-inline-block">
              @php $groups = $seats->groupBy('row_letter'); @endphp
              @foreach($groups as $row => $items)
                <div class="seat-row">
                  <div class="seat-row-label">{{ $row }}</div>
                  @foreach($items as $s)
                    @php
                      $isTaken = in_array($s->id, $occupied, true);
                      $price   = $priceMap[$s->seat_type_id] ?? 0;
                      $label   = $s->row_letter . $s->seat_number;
                      $typeName = strtolower($s->seat_type_name ?? '');

                      $seatClass = 'seat-normal';
                      if (str_contains($typeName, 'vip')) $seatClass = 'seat-vip';
                      elseif (str_contains($typeName, 'couple') || str_contains($typeName, 'đôi')) $seatClass = 'seat-couple';
                    @endphp

                    <button type="button"
                      class="seat {{ $seatClass }} {{ $isTaken ? 'taken' : '' }}"
                      title="{{ $s->seat_type_name }} - {{ $label }} ({{ number_format($price) }}đ)"
                      data-id="{{ $s->id }}"
                      data-price="{{ $price }}"
                      data-label="{{ $label }}"
                      {{ $isTaken ? 'disabled' : '' }}
                    >
                        {{ $s->seat_number }}
                    </button>
                  @endforeach
                </div>
              @endforeach
            </div>
          </form>

          {{-- Chú thích --}}
          <div class="d-flex justify-content-center flex-wrap gap-4 mt-5 pt-3 border-top small text-muted">
            <div class="d-flex align-items-center"><span class="legend seat seat-normal me-2"></span> Thường</div>
            <div class="d-flex align-items-center"><span class="legend seat seat-vip me-2"></span> VIP</div>
            <div class="d-flex align-items-center"><span class="legend seat seat-couple me-2"></span> Đôi</div>
            <div class="d-flex align-items-center"><span class="legend seat taken me-2"></span> Đã bán</div>
            <div class="d-flex align-items-center"><span class="legend seat selected me-2"></span> Đang chọn</div>
          </div>

        </div>
      </div>
    </div>

    {{-- 4. Tóm tắt & Thanh toán --}}
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm position-sticky" style="top: 1rem">
        <div class="card-body">
          <h5 class="fw-bold mb-3 border-bottom pb-2">Thông tin đặt vé</h5>

          <div class="mb-2">
            <div class="small text-muted">Phim</div>
            <div class="fw-bold fs-5">{{ $movie->title }}</div>
          </div>

          <div class="mb-3">
             <div class="small text-muted">Suất chiếu</div>
             <div>{{ \Illuminate\Support\Carbon::parse($showtime->start_time)->format('H:i - d/m/Y') }}</div>
             <div class="small">{{ $room->name }}</div>
          </div>

          <div class="mb-3">
            <div class="small text-muted">Ghế đã chọn</div>
            <div id="seatList" class="fw-bold text-primary text-break" style="min-height: 24px;">—</div>
          </div>

          <div class="mb-3">
            <label class="small text-muted mb-1">Mã ưu đãi (nếu có)</label>
            <div class="input-group input-group-sm">
                <select id="voucherSelect" class="form-select">
                <option value="">— Chọn mã giảm giá —</option>
                @foreach($vouchers as $v)
                    <option
                    value="{{ $v->id }}"
                    data-type="{{ $v->type }}"
                    data-value="{{ (float)$v->value }}"
                    data-min="{{ (float)($v->min_order ?? 0) }}"
                    >
                    {{ $v->code }}
                    @if($v->type==='percent') (-{{ (int)$v->value }}%)
                    @else (-{{ number_format($v->value,0,',','.') }}đ)
                    @endif
                    </option>
                @endforeach
                </select>
                <button type="button" id="applyVoucher" class="btn btn-primary">Áp dụng</button>
            </div>
            <div id="voucherNote" class="small mt-1"></div>
          </div>

          <hr class="dashed">

          <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Tạm tính</span>
            <span class="fw-semibold" id="subtotalText">0 đ</span>
          </div>
          <div class="d-flex justify-content-between mb-2" id="discountRow" style="display:none !important;">
            <span class="text-success">Giảm giá</span>
            <span class="fw-semibold text-success" id="discountText">-0 đ</span>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <span class="fw-bold fs-6">TỔNG CỘNG</span>
            <span class="fw-bold fs-4 text-danger" id="totalText">0 đ</span>
          </div>

          <button type="button" class="btn btn-dark w-100 py-2 mt-3 fw-bold text-uppercase" id="btnPay" disabled>
            Đặt vé ngay
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  .screen-bar { height: 8px; width: 100%; max-width: 400px; background: #e2e8f0; border-radius: 0 0 40px 40px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); position: relative; }
  .screen-bar::after { content: "SCREEN"; position: absolute; top: 12px; left: 50%; transform: translateX(-50%); font-size: 10px; color: #94a3b8; letter-spacing: 2px; }

  .seat-grid { padding: 20px; background: #f8fafc; border-radius: 16px; border: 1px solid #f1f5f9; }
  .seat-row { display: flex; gap: 6px; margin-bottom: 6px; justify-content: center; align-items: center; }
  .seat-row-label { width: 20px; font-weight: bold; color: #64748b; font-size: 0.8rem; }

  .seat {
    width: 36px; height: 36px; border-radius: 8px; border: 1px solid transparent;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.2s;
    background: #fff; border-color: #cbd5e1; color: #475569;
  }
  .seat:hover:not(.taken) { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 10; }

  /* Màu ghế */
  .seat-vip { border-color: #fcd34d; color: #d97706; background: #fffbeb; }
  .seat-couple { border-color: #fbcfe8; color: #db2777; background: #fdf2f8; width: 78px; }

  .seat.taken { background: #e2e8f0; color: #cbd5e1; border-color: transparent; cursor: not-allowed; }
  .seat.selected { background: #0f172a !important; color: #fff !important; border-color: #0f172a !important; box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.2); }

  .legend.seat { width: 20px; height: 20px; cursor: default; }
  .legend.seat:hover { transform: none; box-shadow: none; }

  hr.dashed { border-top: 1px dashed #cbd5e1; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
  const seatBtns       = document.querySelectorAll('.seat:not(.taken)');
  const listEl         = document.getElementById('seatList');
  const subtotalEl     = document.getElementById('subtotalText');
  const discountRow    = document.getElementById('discountRow');
  const discountEl     = document.getElementById('discountText');
  const totalEl        = document.getElementById('totalText');
  const hiddenInputDiv = document.getElementById('hiddenInputs');
  const payBtn         = document.getElementById('btnPay');

  const voucherSel     = document.getElementById('voucherSelect');
  const voucherBtn     = document.getElementById('applyVoucher');
  const voucherIdInput = document.getElementById('voucher_id');
  const voucherNote    = document.getElementById('voucherNote');
  const payNowInput    = document.querySelector('input[name="pay_now"]');

  let selectedSeats = [];
  let appliedVoucher = null;

  const fmtMoney = (n) => new Intl.NumberFormat('vi-VN').format(n) + ' đ';

  function updateUI() {
    if (selectedSeats.length === 0) {
      listEl.textContent = '—';
      payBtn.disabled = true;
    } else {
      const labels = selectedSeats.map(s => s.label).sort();
      listEl.textContent = labels.join(', ');
      payBtn.disabled = false;
    }

    const subTotal = selectedSeats.reduce((sum, seat) => sum + seat.price, 0);
    subtotalEl.textContent = fmtMoney(subTotal);

    let discountAmount = 0;
    let finalTotal = subTotal;
    let note = '';
    let noteClass = '';

    if (appliedVoucher) {
        if (appliedVoucher.min > 0 && subTotal < appliedVoucher.min) {
            note = `Cần đơn tối thiểu ${fmtMoney(appliedVoucher.min)}`;
            noteClass = 'text-danger';
        } else {
            if (appliedVoucher.type === 'percent') {
                discountAmount = Math.round((subTotal * appliedVoucher.value) / 100);
                note = `Đã áp dụng giảm ${appliedVoucher.value}%`;
            } else {
                discountAmount = appliedVoucher.value;
                note = `Đã áp dụng giảm ${fmtMoney(appliedVoucher.value)}`;
            }
            noteClass = 'text-success fw-bold';
        }
    }

    if (discountAmount > subTotal) discountAmount = subTotal;
    finalTotal = subTotal - discountAmount;

    if (discountAmount > 0) {
        discountRow.style.display = 'flex';
        discountRow.style.setProperty('display', 'flex', 'important');
        discountEl.textContent = '-' + fmtMoney(discountAmount);
    } else {
        discountRow.style.display = 'none';
        discountRow.style.setProperty('display', 'none', 'important');
    }

    totalEl.textContent = fmtMoney(finalTotal);
    voucherNote.textContent = note;
    voucherNote.className = 'small mt-1 ' + noteClass;

    hiddenInputDiv.innerHTML = '';
    selectedSeats.forEach(seat => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'seat_ids[]';
        inp.value = seat.id;
        hiddenInputDiv.appendChild(inp);
    });
  }

  seatBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const id = Number(this.dataset.id);
      const price = Number(this.dataset.price);
      const label = this.dataset.label;

      if (this.classList.contains('selected')) {
        this.classList.remove('selected');
        selectedSeats = selectedSeats.filter(s => s.id !== id);
      } else {
        if (selectedSeats.length >= 10) {
            alert('Bạn chỉ được chọn tối đa 10 ghế.');
            return;
        }
        this.classList.add('selected');
        selectedSeats.push({ id, price, label });
      }
      updateUI();
    });
  });

  function applyVoucherHandler() {
    const val = voucherSel.value;
    if (!val) {
        appliedVoucher = null;
        voucherIdInput.value = '';
    } else {
        const opt = voucherSel.options[voucherSel.selectedIndex];
        appliedVoucher = {
            id: val,
            type: opt.dataset.type,
            value: Number(opt.dataset.value),
            min: Number(opt.dataset.min)
        };
        voucherIdInput.value = val;
    }
    updateUI();
  }

  if (voucherBtn) voucherBtn.addEventListener('click', applyVoucherHandler);
  if (voucherSel) voucherSel.addEventListener('change', function(){});

  if (payBtn) {
    payBtn.addEventListener('click', function() {
        if (payNowInput) payNowInput.value = '1';
        document.getElementById('seatForm').submit();
    });
  }
});
</script>
@endsection
