@extends('layouts.app')

@section('title','Quản lý đơn vé')

@section('content')
<div class="container-fluid">
  <h4 class="fw-bold mb-3">Quản lý đơn vé</h4>

  {{-- Bộ lọc --}}
  <form method="get" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
      <label class="form-label mb-1">Trạng thái</label>
      <select name="status" class="form-select form-select-sm">
        <option value="">-- Tất cả --</option>
        @foreach (['booked'=>'Booked','used'=>'Used','canceled'=>'Canceled'] as $k=>$v)
          <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-auto">
      <label class="form-label mb-1">Ngày chiếu</label>
      <input type="date" name="date" value="{{ request('date') }}" class="form-control form-control-sm">
    </div>

    <div class="col-auto">
      <label class="form-label mb-1">Tìm kiếm</label>
      <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Tên khách / Email / Phim">
    </div>

    <div class="col-auto">
      <button class="btn btn-sm btn-primary">
        <i class="bi bi-funnel"></i> Lọc
      </button>
    </div>
  </form>

  {{-- Bảng dữ liệu --}}
  <div class="table-responsive">
    <table class="table table-sm table-striped table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Mã vé</th>
          <th>Khách</th>
          <th>Phim</th>
          <th>Phòng</th>
          <th>Suất</th>
          <th>Ghế</th>
          <th>Giá</th>
          <th>Giảm</th>
          <th>Rate TV</th>
          <th>Trạng thái</th>
          <th>Ngày tạo</th>
          <th>Thao tác</th>
        </tr>
      </thead>

      <tbody>
      @forelse($tickets as $t)
        <tr>
          <td><code>{{ $t->qr_code }}</code></td>

          <td>
            <div class="small fw-semibold">{{ $t->user_name }}</div>
            <div class="text-muted small">{{ $t->user_email }}</div>
          </td>

          <td>{{ $t->movie_title }}</td>
          <td>{{ $t->room_name }}</td>
          <td>{{ \Carbon\Carbon::parse($t->start_time)->format('d/m/Y H:i') }}</td>
          <td>{{ $t->seat_label }}</td>
          <td>{{ number_format($t->final_price,0,',','.') }}</td>
          <td>{{ number_format($t->discount_amount,0,',','.') }}</td>
          <td>{{ rtrim(rtrim(number_format($t->membership_discount_rate,2), '0'),'.') }}</td>

          <td>
            @php 
              $badge = [
                'booked' => 'warning',
                'used' => 'success',
                'canceled' => 'secondary'
              ][$t->status] ?? 'light'; 
            @endphp
            <span class="badge text-bg-{{ $badge }}">{{ ucfirst($t->status) }}</span>
          </td>

          <td>{{ \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') }}</td>

          <td class="text-nowrap">
            <div class="d-flex gap-1">
              {{-- Nút sửa --}}
              <a href="{{ route('admin.tickets.edit', $t->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Sửa
              </a>

              {{-- Huỷ vé --}}
              @if($t->status !== 'used')
                <form action="{{ route('admin.tickets.cancel', $t->id) }}" method="post" class="d-inline">
                  @csrf
                  <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Huỷ vé này?')">
                    <i class="bi bi-x-circle"></i> Huỷ
                  </button>
                </form>
              @endif

              {{-- Hoàn tiền (demo) --}}
              @if($t->status === 'canceled')
                <form action="{{ route('admin.tickets.refund', $t->id) }}" method="post" class="d-inline">
                  @csrf
                  <button class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Hoàn
                  </button>
                </form>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="12" class="text-center text-muted">Chưa có vé nào được ghi nhận.</td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>

  {{-- Phân trang --}}
  <div class="mt-3">
    {{ $tickets->links() }}
  </div>
</div>
@endsection
