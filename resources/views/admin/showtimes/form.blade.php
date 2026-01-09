@extends('layouts.app')
@section('title', $showtime->exists ? 'Sửa suất chiếu' : 'Thêm suất chiếu')

@section('content')
<div class="container py-3" style="max-width:800px">
  <h5 class="fw-bold mb-3">{{ $showtime->exists ? 'Sửa suất chiếu' : 'Thêm suất chiếu' }}</h5>

  @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

  <form method="POST"
        action="{{ $showtime->exists ? route('admin.showtimes.update',$showtime) : route('admin.showtimes.store') }}">
    @csrf
    @if($showtime->exists) @method('PUT') @endif

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Phim</label>
        <select name="movie_id" class="form-select" required>
          <option value="">-- Chọn phim --</option>
          @foreach($movies as $m)
            <option value="{{ $m->id }}" @selected(old('movie_id',$showtime->movie_id)==$m->id)>
              {{ $m->title }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Phòng chiếu</label>
        <select name="room_id" class="form-select" required>
          <option value="">-- Chọn phòng --</option>
          @foreach($rooms as $r)
            <option value="{{ $r->id }}" @selected(old('room_id',$showtime->room_id)==$r->id)>
              {{ $r->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label">Bắt đầu</label>
        <input type="datetime-local" name="start_time" class="form-control" required
               value="{{ old('start_time', optional($showtime->start_time)->format('Y-m-d\TH:i')) }}">
      </div>

      <div class="col-md-6">
        <label class="form-label">Kết thúc (tự tính nếu trống)</label>
        <input type="datetime-local" name="end_time" class="form-control"
               value="{{ old('end_time', optional($showtime->end_time)->format('Y-m-d\TH:i')) }}">
      </div>
    </div>

    <div class="mt-3">
      <button class="btn btn-primary">{{ $showtime->exists ? 'Cập nhật' : 'Thêm mới' }}</button>
      <a href="{{ route('admin.showtimes.index') }}" class="btn btn-outline-secondary">Hủy</a>
    </div>
  </form>
</div>
@endsection
