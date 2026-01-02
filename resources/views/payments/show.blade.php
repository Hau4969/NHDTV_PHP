@extends('layouts.app')
@section('title','Thanh toán')

@section('content')
<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h5 class="fw-bold mb-2">Thông tin vé</h5>
          @php
            $movie = $tickets->first()->movie_title ?? '';
            $room  = $tickets->first()->room_name ?? '';
            $time  = $tickets->first()->start_time ?? null;
          @endphp

          <div class="mb-1 text-muted small">Phim</div>
          <div class="fw-semibold mb-2">{{ $movie }}</div>

          <div class="mb-1 text-muted small">Phòng / Suất</div>
          <div class="mb-2">{{ $room }} — {{ $time ? \Illuminate\Support\Carbon::parse($time)->format('H:i d/m/Y') : '' }}</div>

          <div class="mb-1 text-muted small">Ghế</div>
          <div class="mb-3">
            {{ $tickets->map(fn($t)=> $t->row_letter.$t->seat_number)->join(', ') }}
          </div>

          <table class="table table-sm">
            <thead><tr><th>Ghế</th><th class="text-end">Giá</th></tr></thead>
            <tbody>
            @foreach($tickets as $t)
              <tr>
                <td>{{ $t->row_letter.$t->seat_number }}</td>
                <td class="text-end">{{ number_format($t->final_price,0,',','.') }} đ</td>
              </tr>
            @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th>Tổng thanh toán</th>
                <th class="text-end">{{ number_format($payment->amount,0,',','.') }} đ</th>
              </tr>
            </tfoot>
          </table>

          @if($errors->any())
            <div class="alert alert-danger small">{{ $errors->first() }}</div>
          @endif

          <div class="d-flex gap-2 mt-3">
            <form method="POST" action="{{ route('payments.confirm',$payment) }}">
              @csrf
              <button id="btnConfirm" class="btn btn-success">Xác nhận đã thanh toán</button>
            </form>
            <form method="POST" action="{{ route('payments.cancel',$payment) }}">
              @csrf
              <button id="btnCancel" class="btn btn-outline-danger">Hủy</button>
            </form>
          </div>

          <div class="small text-muted mt-2">
            * Giao dịch sẽ hết hạn sau: <b id="countdown">30</b>s
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
          <h6 class="fw-bold mb-2">Quét mã để thanh toán</h6>

          @php
            $qrText = $payment->reference ?? ('PM' . str_pad((string)$payment->id, 6, '0', STR_PAD_LEFT));
          @endphp
          <div class="mb-2 small text-muted">Mã đơn: {{ $qrText }}</div>

          <div class="d-inline-block p-2 bg-white rounded shadow-sm">
            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(220)->generate($qrText) !!}
          </div>

          <div class="small text-muted mt-2">
            Số tiền: <b>{{ number_format($payment->amount,0,',','.') }} đ</b>
          </div>
          <div class="small text-muted">
            Hạn: {{ optional($payment->expires_at)->format('H:i:s') }}
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- JS countdown (chuẩn 30s, không auto-cancel) --}}
<script>
(function () {
  // Lấy mốc hết hạn từ server (UNIX ms) để tránh lệch múi giờ
  const expireAtMs = @json(optional($payment->expires_at)->timestamp ? optional($payment->expires_at)->timestamp * 1000 : null);
  if (!expireAtMs) return;

  // Đồng bộ clock: chênh lệch server-now và client-now
  const serverNowMs = @json(now()->timezone(config('app.timezone'))->timestamp * 1000);
  const clientNowMs = Date.now();
  const offsetMs = serverNowMs - clientNowMs;

  const cd = document.getElementById('countdown');
  const btnConfirm = document.getElementById('btnConfirm');
  const btnCancel  = document.getElementById('btnCancel');

  function leftSec() {
    const nowMs = Date.now() + offsetMs;
    return Math.max(0, Math.floor((expireAtMs - nowMs) / 1000));
  }

  function tick() {
    const s = leftSec();
    cd.textContent = s;

    if (s <= 0) {
      // Hết hạn: khoá nút xác nhận, KHÔNG auto-cancel
      if (btnConfirm) btnConfirm.disabled = true;
      clearInterval(timer);
    }
  }

  tick();
  const timer = setInterval(tick, 1000); // 1 giây/tick
})();
</script>
@endsection
