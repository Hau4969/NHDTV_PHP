@extends('layouts.app')
@section('title','Đặt vé')

@section('content')
<div class="container py-4">

  {{-- Header + đổi suất chiếu --}}
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
            {{ \Illuminate\Support\Carbon::parse($st->start_time)->format('H:i d/m/Y') }} — {{ $st->room_name }} {{ $st->id==$showtime->id ? '(hiện tại)' : '' }}
          </option>
        @endforeach
      </select>

      <a href="{{ route('movies.show',$movie->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Quay lại phim
      </a>
    </div>
  </div>

  {{-- Chọn loại ghế --}}
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <h6 class="fw-bold text-uppercase mb-2">Chọn loại ghế</h6>
      <div class="d-flex flex-wrap gap-2">
        @foreach($ticketTypes as $t)
          <button
            type="button"
            class="btn btn-outline-primary btn-sm tt-btn {{ $loop->first ? 'active' : '' }}"
            data-type="{{ $t->id }}"
            data-price="{{ $t->display_price }}"
          >
            {{ $t->name }} — {{ number_format($t->display_price, 0, ',', '.') }} đ
          </button>
        @endforeach

      </div>
    </div>
  </div>

  <div class="row g-4">
    {{-- Ghế --}}
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h6 class="fw-bold text-uppercase text-center mb-3">Chọn ghế</h6>
          <div class="text-center small text-muted mb-2">Màn hình</div>

          <form id="seatForm" method="POST" action="{{ route('tickets.store',$showtime) }}">
            @csrf
            <input type="hidden" name="voucher_id" id="voucher_id" value="">
            <input type="hidden" name="pay_now" value="0"> {{-- giữ chỗ, không thanh toán ngay --}}

            <input type="hidden" name="seat_type_id" id="seat_type_id" value="{{ $ticketTypes->first()->id ?? 1 }}">

            <div class="seat-grid mx-auto">
              @php $groups = $seats->groupBy('row_letter'); @endphp
              @foreach($groups as $row => $items)
                <div class="seat-row">
                  <div class="seat-row-label">{{ $row }}</div>
                  @foreach($items as $s)
                    @php
                      $isTaken = in_array($s->id,$occupied,true);
                      $price   = $priceMap[$s->seat_type_id] ?? 0;
                      $label   = $s->code ?: ($s->row_letter.$s->seat_number);
                    @endphp
                    <button type="button"
                      class="seat {{ $isTaken ? 'taken' : '' }}"
                      title="{{ $label }} • {{ number_format($price) }}đ"
                      data-id="{{ $s->id }}"
                      data-base="{{ $price }}"
                      {{ $isTaken ? 'disabled' : '' }}
                    >{{ $s->seat_number }}</button>
                  @endforeach
                </div>
              @endforeach
            </div>

            <div id="hiddenInputs"></div>
          </form>

          <div class="row mt-3 g-2 small">
            <div class="col-auto d-flex align-items-center"><span class="legend seat me-2"></span> Ghế trống</div>
            <div class="col-auto d-flex align-items-center"><span class="legend seat selected me-2"></span> Ghế đang chọn</div>
            <div class="col-auto d-flex align-items-center"><span class="legend seat taken me-2"></span> Ghế đã đặt</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Tóm tắt + Thanh toán --}}
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm position-sticky" style="top: 1rem">
        <div class="card-body">
          <h6 class="fw-bold mb-2">Tóm tắt</h6>
          <div class="mb-1 small text-muted">Phim</div>
          <div class="fw-semibold mb-2">{{ $movie->title }}</div>

          <div class="mb-1 small text-muted">Thời gian</div>
          <div class="mb-2">{{ \Illuminate\Support\Carbon::parse($showtime->start_time)->format('H:i d/m/Y') }}</div>

          <div class="mb-1 small text-muted">Ghế đã chọn</div>
          <div id="seatList" class="mb-2">—</div>

          <div class="mb-1 small text-muted">Mã ưu đãi</div>
          <div class="d-flex gap-2">
            <select id="voucherSelect" class="form-select form-select-sm">
              <option value="">— Chọn mã —</option>
              @foreach($vouchers as $v)
                <option
                  value="{{ $v->id }}"
                  data-type="{{ $v->type }}"                 {{-- percent | fixed --}}
                  data-value="{{ (float)$v->value }}"
                  data-min="{{ (float)($v->min_order ?? 0) }}" {{-- nếu DB có cột min_order --}}
                >
                  {{ $v->code }}
                  @if($v->type==='percent')
                    ( -{{ (int)$v->value }}% )
                  @else
                    ( -{{ number_format($v->value,0,',','.') }}đ/ghế )
                  @endif
                </option>
              @endforeach
            </select>

            <button type="button" id="applyVoucher" class="btn btn-outline-primary btn-sm">
              Áp dụng
            </button>
          </div>
          <div id="voucherNote" class="small text-muted mt-1">Chọn mã để áp dụng.</div>


          
          <hr class="my-2">
          <div class="d-flex justify-content-between small">
            <div class="text-muted">Tạm tính</div>
            <div id="subtotalText">0 đ</div>
          </div>
          <div class="d-flex justify-content-between">
            <div class="fw-bold">Tổng sau ưu đãi</div>
            <div class="fw-bold" id="totalText">0 đ</div>
          </div>


          <button type="button" class="btn btn-primary w-100 mt-3" id="btnPay" disabled>
            Thanh toán
          </button>
          <div class="text-muted small mt-2">
            * Vé ở trạng thái <b>giữ chỗ</b>. Bạn có thể thanh toán trong mục “Vé của tôi”.
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- CSS --}}
<style>
  .seat-grid{display:inline-block;padding:10px;background:#f8fafc;border-radius:12px}
  .seat-row{display:flex;align-items:center;gap:.35rem;margin:.25rem 0}
  .seat-row-label{width:24px;text-align:center;font-weight:600;color:#475569}
  .seat{
    width:28px;height:28px;border-radius:6px;border:1px solid #cbd5e1;background:#fff;
    display:inline-flex;align-items:center;justify-content:center;font-size:.8rem;
    transition:.15s; user-select:none;
  }
  .seat:hover{transform:translateY(-2px);box-shadow:0 2px 8px rgba(0,0,0,.08)}
  .seat.selected{background:#2563eb;color:#fff;border-color:#1d4ed8}
  .seat.taken{background:#e2e8f0;color:#94a3b8;border-color:#cbd5e1;cursor:not-allowed}
  .legend.seat{width:18px;height:18px}
  .tt-btn.active{color:#fff !important;background:#0d6efd;border-color:#0d6efd}
</style>

{{-- JS --}}
<script>
(function(){
  // ====== DOM phần tử ======
  const seatBtns       = document.querySelectorAll('.seat:not(.taken)');
  const listBox        = document.getElementById('seatList');
  const totalTxt       = document.getElementById('totalText');
  const subtotalEl     = document.getElementById('subtotalText');
  const hidden         = document.getElementById('hiddenInputs');
  const payBtn         = document.getElementById('btnPay');
  const typeBtns       = document.querySelectorAll('.tt-btn');

  const voucherSel     = document.getElementById('voucherSelect');
  const voucherBtn     = document.getElementById('applyVoucher');
  const voucherIdInput = document.getElementById('voucher_id');
  const payNowInput    = document.querySelector('input[name="pay_now"]');
  const seatTypeInput  = document.getElementById('seat_type_id');
  const voucherNote    = document.getElementById('voucherNote');

  // ====== State ======
  let selected = [];         // danh sách ghế {id,label,base}
  let appliedVoucher = null; // voucher {id,type,value,min}

  // ====== Helper ======
  const fmt = n => new Intl.NumberFormat('vi-VN').format(n) + ' đ';
  const rawSubtotal = () => selected.reduce((s,x)=> s + (Number(x.base)||0), 0);

  // ====== Tính toán voucher & tổng ======
  function calcTotal(subtotal){
    if(!appliedVoucher || subtotal <= 0){
      return { total: subtotal, msg: 'Chọn mã để áp dụng.' };
    }
    const t  = (appliedVoucher.type || '').toLowerCase();
    const v  = Number(appliedVoucher.value || 0);
    const mn = Number(appliedVoucher.min || 0);

    if (mn && subtotal < mn)
      return { total: subtotal, msg: `Chưa đạt tối thiểu ${fmt(mn)}.` };

    let total = subtotal;
    if (t === 'percent') {
      total = Math.max(Math.round(subtotal * (100 - v) / 100), 0);
      return { total, msg: `Đã áp dụng -${v}%` };
    } else if (t === 'fixed') {
      total = Math.max(subtotal - v * selected.length, 0);
      return { total, msg: `Đã áp dụng -${fmt(v)} mỗi ghế` };
    }
    return { total: subtotal, msg: 'Mã không hợp lệ.' };
  }

  // ====== Render giao diện ======
  function render(){
    // Danh sách ghế
    if (selected.length === 0) {
      listBox.textContent = '—';
      payBtn.disabled = true;
    } else {
      listBox.textContent = selected.map(x=>x.label).join(', ');
      payBtn.disabled = false;
    }

    // Tiền
    const subtotal = rawSubtotal();
    subtotalEl.textContent = fmt(subtotal);

    const { total, msg } = calcTotal(subtotal);
    totalTxt.textContent = fmt(total);
    voucherNote.textContent = appliedVoucher ? msg : 'Chọn mã để áp dụng.';

    // hidden inputs ghế
    hidden.innerHTML = '';
    selected.forEach(x=>{
      const i=document.createElement('input');
      i.type='hidden';
      i.name='seat_ids[]';
      i.value=x.id;
      hidden.appendChild(i);
    });
  }

  // ====== Chọn ghế ======
  seatBtns.forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const id    = Number(btn.dataset.id);
      const label = (btn.title||'').split(' • ')[0];
      const base  = Number(btn.dataset.base || 0);

      btn.classList.toggle('selected');
      if (btn.classList.contains('selected')) {
        selected.push({id,label,base});
      } else {
        selected = selected.filter(x=>x.id!==id);
      }
      render();
    });
  });

  // ====== Chọn loại ghế (chỉ đồng bộ, không đổi giá) ======
  typeBtns.forEach(b=>{
    b.addEventListener('click', ()=>{
      typeBtns.forEach(x=>x.classList.remove('active'));
      b.classList.add('active');
      if (seatTypeInput) seatTypeInput.value = b.dataset.type || '';
      render();
    });
  });

  // ====== Chọn & áp dụng voucher ======
  function setVoucherFromSelect(){
    const id = voucherSel?.value || '';
    if (!id) {
      appliedVoucher = null;
      voucherIdInput.value = '';
      render();
      return;
    }

    const opt = voucherSel.selectedOptions[0];
    appliedVoucher = {
      id: Number(id),
      type: (opt?.dataset?.type || '').toLowerCase(),
      value: Number(opt?.dataset?.value || 0),
      min: Number(opt?.dataset?.min || 0),
    };
    voucherIdInput.value = id;
    render();
  }

  voucherBtn.addEventListener('click', setVoucherFromSelect);
  voucherSel.addEventListener('change', setVoucherFromSelect);

  // ====== Nút Thanh toán ======
  payBtn.addEventListener('click', ()=>{
    // Gạt sang chế độ thanh toán ngay
    if (payNowInput) payNowInput.value = '1';

    // Nếu user chỉ chọn mã mà chưa bấm "Áp dụng", vẫn sync voucher
    if (voucherSel && voucherIdInput && !voucherIdInput.value) {
      voucherIdInput.value = voucherSel.value || '';
    }

    // Submit form
    document.getElementById('seatForm').submit();
  });

  // ====== Khởi tạo ======
  render();
})();
</script>




@endsection
