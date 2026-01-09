@extends('layouts.app')
@section('title','Sửa vé')

@section('content')
<div class="container py-3">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Sửa vé #{{ $ticket->id }}</h5>
    <a href="{{ route('admin.tickets.index') }}" class="btn btn-outline-secondary btn-sm">Quay lại</a>
  </div>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
  @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="row g-3 mb-3">
        <div class="col-md-6">
          <div><strong>Khách:</strong> {{ $ticket->user_name ?? '—' }} ({{ $ticket->user_email ?? '—' }})</div>
          <div><strong>Phim:</strong> {{ $ticket->movie_title ?? '—' }}</div>
          <div><strong>Phòng:</strong> {{ $ticket->room_name ?? '—' }}</div>
          <div><strong>Suất chiếu:</strong> {{ $ticket->start_time ?? '—' }}</div>
          <div><strong>Ghế:</strong> {{ $ticket->seat_label ?? '—' }}</div>
        </div>

        <div class="col-md-6">
          <div><strong>Loại ghế:</strong> {{ $ticket->seat_type_name ?? '—' }}</div>
          <div><strong>Giá gốc:</strong> {{ number_format($ticket->seat_base_price ?? 0) }} đ</div>
          <div><strong>Điều chỉnh:</strong> {{ number_format($ticket->price_modifier ?? 0) }} đ</div>
          <div><strong>Giá dự kiến:</strong> <span class="fw-bold text-danger">{{ number_format($ticket->expected_final_price ?? 0) }} đ</span></div>
        </div>
      </div>

      {{-- Form cập nhật trạng thái (nếu bạn muốn) --}}
      <form method="POST" action="{{ route('admin.tickets.update', $ticket->id) }}">
        @csrf
        @method('PUT')

        <div class="mb-3" style="max-width: 320px;">
          <label class="form-label fw-semibold">Trạng thái</label>
          <select name="status" class="form-select">
            @php
              $statuses = ['reserved'=>'Giữ chỗ','paid'=>'Đã thanh toán','canceled'=>'Đã hủy','refunded'=>'Đã hoàn tiền','expired'=>'Hết hạn'];
              $current = $ticket->status ?? 'reserved';
            @endphp
            @foreach($statuses as $k=>$v)
              <option value="{{ $k }}" @selected($current === $k)>{{ $v }}</option>
            @endforeach
          </select>
        </div>

        <button class="btn btn-primary">
          Lưu thay đổi
        </button>
      </form>

      <div class="text-muted small mt-3">
    </div>
  </div>
</div>
@endsection

