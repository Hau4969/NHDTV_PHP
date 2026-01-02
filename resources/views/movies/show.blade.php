@extends('layouts.app')
@section('title', $movie->title)

@section('content')
<div class="container py-3">
  <div class="row g-4">
    <div class="col-md-4">
      <img
        src="{{ $movie->poster_url ?: asset('images/no-poster.png') }}"
        class="img-fluid rounded w-100"
        alt="{{ $movie->title }}"
        style="aspect-ratio: 2/3; object-fit: cover;"
      >
      @if(!empty($movie->trailer_url))
        <a target="_blank" href="{{ $movie->trailer_url }}" class="btn btn-dark w-100 mt-2">
          <i class="bi bi-play-circle"></i> Xem trailer
        </a>
      @endif
    </div>

    <div class="col-md-8">
      <h3 class="fw-bold">{{ $movie->title }}</h3>

      <div class="text-muted mb-2">
        Khởi chiếu:
        {{ !empty($movie->release_date)
            ? \Illuminate\Support\Carbon::parse($movie->release_date)->format('d/m/Y')
            : '—' }}
      </div>

      <p>{{ $movie->description ?? '' }}</p>

      <div class="d-flex align-items-center gap-2 mt-3">
        <form method="get" class="d-flex gap-2">
          <input type="date" name="date" class="form-control" value="{{ $day->format('Y-m-d') }}">
          <button class="btn btn-outline-secondary">
            <i class="bi bi-calendar-event"></i> Lọc suất
          </button>
        </form>
      </div>

      <hr>

      <h5 class="fw-bold mb-3">Suất chiếu {{ $day->format('d/m/Y') }}</h5>

      @if($showtimes->isEmpty())
        <div class="text-muted">Chưa có suất cho ngày này.</div>
      @else
        <div class="list-group">
          @foreach($showtimes as $st)
            <div class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                Phòng <b>{{ $st->room_name }}</b> —
                Bắt đầu: <b>{{ \Illuminate\Support\Carbon::parse($st->start_time)->format('H:i d/m') }}</b>
              </div>
              <a class="btn btn-primary" href="{{ route('tickets.create', ['showtime' => $st->id]) }}">
                Chọn ghế
              </a>
            </div>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
