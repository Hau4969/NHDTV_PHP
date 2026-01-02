@extends('layouts.app')
@section('title','Báo cáo & Thống kê')

@section('page_toolbar')
<div class="page-toolbar">
  <div class="container d-flex align-items-center justify-content-between gap-2">
    <div class="dropdown">
      <button class="pt-btn dropdown-toggle" data-bs-toggle="dropdown">Quản trị</button>
      <ul class="dropdown-menu shadow-sm">
        <li><a class="dropdown-item" href="{{ route('admin.movies.index') }}">Quản lý phim</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.showtimes.index') }}">Quản lý suất chiếu</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.rooms.index') }}">Quản lý phòng chiếu</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.prices.index') }}">Quản lý giá vé</a></li>
        <li><a class="dropdown-item" href="{{ route('admin.tickets.index') }}">Quản lý đơn vé</a></li>
        <li><a class="dropdown-item active" href="{{ route('admin.reports.index') }}">Báo cáo & thống kê</a></li>
      </ul>
    </div>
    <div class="page-title flex-grow-1 text-center"><h5 class="m-0 fw-bold">Báo cáo & Thống kê</h5></div>
    <div></div>
  </div>
</div>
@endsection

@section('content')
<div class="container mt-3">
  <form class="card card-body shadow-sm mb-3" method="get">
    <div class="row g-2">
      <div class="col-md-4">
        <label class="form-label">Từ ngày</label>
        <input type="date" name="from" value="{{ $from }}" class="form-control">
      </div>
      <div class="col-md-4">
        <label class="form-label">Đến ngày</label>
        <input type="date" name="to" value="{{ $to }}" class="form-control">
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-outline-secondary">Xem báo cáo</button>
      </div>
    </div>
  </form>

  <div class="row g-3">
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted">Doanh thu</div>
          <div class="display-6 fw-bold">{{ number_format($revenue) }} đ</div>
          <div class="small text-muted">{{ $from }} → {{ $to }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted">Tỷ lệ lấp đầy</div>
          <div class="display-6 fw-bold">{{ $occupancy }}%</div>
          <div class="small text-muted">{{ $sold }} vé / {{ $totalCapacity }} ghế (trong khoảng)</div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="text-muted">Top phim</div>
          <div class="small text-muted">Top 10 theo doanh thu</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mt-3">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Phim</th>
              <th>Vé bán</th>
              <th>Doanh thu</th>
            </tr>
          </thead>
          <tbody>
          @forelse($topMovies as $i=>$m)
            <tr>
              <td>{{ $i+1 }}</td>
              <td class="fw-semibold">{{ $m->title }}</td>
              <td>{{ $m->tickets }}</td>
              <td class="fw-bold">{{ number_format($m->total_rev) }} đ</td>
            </tr>
          @empty
            <tr><td colspan="4" class="text-center py-4 text-muted">Chưa có dữ liệu.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
