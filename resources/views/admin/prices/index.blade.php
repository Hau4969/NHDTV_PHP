@extends('layouts.app')
@section('title','Quản lý giá vé')

@section('page_toolbar')
  @include('layouts.partials.page_toolbarAdmin')
@endsection

@section('content')
<div class="container py-3">

  @if(session('ok'))   <div class="alert alert-success">{{ session('ok') }}</div> @endif
  @if(session('err'))  <div class="alert alert-warning">{{ session('err') }}</div> @endif
  @if($errors->any())  <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

  {{-- Bộ lọc --}}
  <form method="get" class="card border-0 shadow-sm mb-3">
    <div class="card-body row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label">Phim</label>
        <select class="form-select" name="movie_id">
          <option value="">-- Tất cả --</option>
          @foreach($movies as $m)
            <option value="{{ $m->id }}" @selected((int)($movieId??0)===$m->id)>{{ $m->title }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Phòng</label>
        <select class="form-select" name="room_id">
          <option value="">-- Tất cả --</option>
          @foreach($rooms as $rm)
            <option value="{{ $rm->id }}" @selected((int)($roomId??0)===$rm->id)>{{ $rm->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Ngày chiếu</label>
        <input type="date" class="form-control" name="date" value="{{ $date ?? '' }}">
      </div>
      <div class="col-md-1 d-grid">
        <button class="btn btn-outline-primary">Lọc</button>
      </div>
    </div>
  </form>

  {{-- Nút khởi tạo cấu hình giá (khi bảng trống) --}}
  @if($prices->isEmpty())
    <div class="alert alert-info d-flex justify-content-between align-items-center">
      <div>Chưa có cấu hình giá hoặc không có dữ liệu khớp bộ lọc.</div>
      <form method="post" action="{{ route('admin.prices.bootstrap') }}">
        @csrf
        {{-- truyền lại filter hiện tại để khởi tạo đúng phạm vi --}}
        <input type="hidden" name="movie_id" value="{{ $movieId }}">
        <input type="hidden" name="room_id"  value="{{ $roomId }}">
        <input type="hidden" name="date"     value="{{ $date }}">
        <button class="btn btn-sm btn-primary">
          <i class="bi bi-rocket"></i> Khởi tạo cấu hình giá
        </button>
      </form>
    </div>
  @endif

  {{-- Bảng giá (có ô điều chỉnh) --}}
  @if(!$prices->isEmpty())
  <form method="post" action="{{ route('admin.prices.store') }}" class="card border-0 shadow-sm">
    @csrf
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Suất chiếu</th>
            <th>Phòng</th>
            <th>Phim</th>
            <th>Loại ghế</th>
            <th class="text-end">Giá gốc</th>
            <th class="text-end" style="width:180px">Điều chỉnh</th>
            <th class="text-end">Giá cuối</th>
          </tr>
        </thead>
        <tbody>
          @foreach($prices as $i => $p)
            @php
              $final = (float)$p->base_price + (float)$p->price_modifier;
            @endphp
            <tr>
              <td>
                {{ \Illuminate\Support\Carbon::parse($p->start_time)->format('H:i d/m/Y') }}
              </td>
              <td>{{ $p->room_name }}</td>
              <td>{{ $p->movie_title }}</td>
              <td>{{ $p->seat_type_name }}</td>
              <td class="text-end">{{ number_format($p->base_price) }} đ</td>
              <td class="text-end">
                <input
                  type="number"
                  class="form-control text-end"
                  name="price[{{ $i }}][price_modifier]"
                  value="{{ (float)$p->price_modifier }}"
                  step="1000" min="-10000000" max="100000000"
                >
                <input type="hidden" name="price[{{ $i }}][id]" value="{{ $p->id }}">
              </td>
              <td class="text-end fw-semibold">{{ number_format($final) }} đ</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="card-footer d-flex justify-content-end">
      <button class="btn btn-primary">
        <i class="bi bi-save"></i> Lưu thay đổi
      </button>
    </div>

    {{-- giữ filter khi submit --}}
    <input type="hidden" name="movie_id" value="{{ $movieId }}">
    <input type="hidden" name="room_id"  value="{{ $roomId }}">
    <input type="hidden" name="date"     value="{{ $date }}">
  </form>

  <div class="mt-3">
    {{ $prices->links() }}
  </div>
  @endif

</div>
@endsection
