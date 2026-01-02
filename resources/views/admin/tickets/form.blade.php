@extends('layouts.app')
@section('title','Sửa vé #'.$ticket->id)

@section('content')
<div class="container py-3">

  <a href="{{ route('admin.tickets.index') }}" class="btn btn-link px-0 mb-3">
    <i class="bi bi-arrow-left"></i> Quay lại danh sách
  </a>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="card shadow-sm">
    <div class="card-header bg-white">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <div class="fw-bold">Vé #{{ $ticket->id }}</div>
          <div class="small text-muted">
            Phim: <b>{{ $ticket->movie_title }}</b> · Phòng: <b>{{ $ticket->room_name }}</b> ·
            Suất: {{ \Carbon\Carbon::parse($ticket->start_time)->format('d/m/Y H:i') }}
          </div>
          <div class="small text-muted">
            Khách: {{ $ticket->user_name }} ({{ $ticket->user_email }})
          </div>
        </div>
        <span class="badge bg-secondary">QR: {{ $ticket->qr_code ?? '—' }}</span>
      </div>
    </div>

    <form method="post" action="{{ route('admin.tickets.update',$ticket->id) }}">
      @csrf @method('PUT')
      <div class="card-body">

        <div class="row g-3">
          {{-- Ghế --}}
          <div class="col-md-4">
            <label class="form-label">Ghế</label>
            <select name="seat_id" class="form-select">
              <option value="">-- Không gán ghế --</option>
              @foreach($seats as $s)
                @php
                  $disabled = in_array($s->id, $takenSeatIds) && (int)$ticket->seat_id !== (int)$s->id;
                @endphp
                <option value="{{ $s->id }}"
                        @selected($ticket->seat_id == $s->id)
                        @disabled($disabled)>
                  {{ $s->label }} @if($disabled) (đã đặt) @endif
                </option>
              @endforeach
            </select>
            <div class="form-text">Các ghế “(đã đặt)” là ghế đã có vé khác cùng suất.</div>
          </div>

          {{-- Trạng thái --}}
          <div class="col-md-4">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
              @foreach($statuses as $k=>$v)
                <option value="{{ $k }}" @selected($ticket->status===$k)>{{ $v }}</option>
              @endforeach
            </select>
          </div>

          {{-- Giảm cố định --}}
          <div class="col-md-4">
            <label class="form-label">Giảm cố định (đ)</label>
            <input type="number" step="1" min="0" class="form-control"
                   name="discount_amount" value="{{ old('discount_amount',$ticket->discount_amount) }}">
          </div>

          {{-- % giảm thành viên --}}
          <div class="col-md-4">
            <label class="form-label">% Giảm thành viên</label>
            <input type="number" step="0.01" min="0" max="100" class="form-control"
                   name="membership_discount_rate" value="{{ old('membership_discount_rate',$ticket->membership_discount_rate) }}">
          </div>

          {{-- Giá cuối --}}
          <div class="col-md-4">
            <label class="form-label">Giá cuối (đ)</label>
            <input type="number" step="1" min="0" class="form-control"
                   name="final_price" value="{{ old('final_price',$ticket->final_price) }}" required>
            <div class="form-text">Bạn có thể tự nhập giá cuối sau khi điều chỉnh.</div>
          </div>
        </div>

      </div>
      <div class="card-footer bg-white d-flex justify-content-between">
        <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary">Hủy</a>
        <button class="btn btn-primary">
          <i class="bi bi-save"></i> Lưu thay đổi
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
