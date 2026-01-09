@extends('layouts.app')
@section('title', $movie->title)

@section('content')
<div class="container py-5">
    <div class="row g-4">
        {{-- Poster phim --}}
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <img src="{{ $movie->poster_url ?? asset('assets/images/placeholder-movie.jpg') }}"
                     class="card-img-top rounded" alt="{{ $movie->title }}"
                     style="aspect-ratio: 2/3; object-fit: cover;">
            </div>
        </div>

        {{-- Thông tin chi tiết --}}
        <div class="col-md-9">
            <h1 class="fw-bold mb-3">{{ $movie->title }}</h1>

            <div class="d-flex flex-wrap gap-3 text-muted mb-4 align-items-center">
                <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-clock"></i> {{ $movie->duration }} phút</span>
                <span><i class="bi bi-calendar3"></i> Khởi chiếu: {{ \Carbon\Carbon::parse($movie->release_date)->format('d/m/Y') }}</span>
                <span><i class="bi bi-tags-fill"></i> {{ $movie->genre }}</span>
            </div>

            <h5 class="fw-bold">Nội dung</h5>
            <p class="text-secondary" style="line-height: 1.6;">
                {{ $movie->description ?? 'Đang cập nhật nội dung...' }}
            </p>

            @if($movie->trailer_url)
                <div class="mb-5">
                    <a href="{{ $movie->trailer_url }}" target="_blank" class="btn btn-danger">
                        <i class="bi bi-youtube"></i> Xem Trailer
                    </a>
                </div>
            @endif

            <hr class="my-4">

            {{-- === KHU VỰC LỊCH CHIẾU (GROUP THEO NGÀY) === --}}
            <h3 class="fw-bold mb-4 text-primary"><i class="bi bi-ticket-perforated"></i> Lịch Chiếu Sắp Tới</h3>

            @if(isset($groupedShowtimes) && $groupedShowtimes->count() > 0)
                <div class="d-flex flex-column gap-4">
                    @foreach($groupedShowtimes as $date => $showtimes)
                        @php
                            $dateObj = \Carbon\Carbon::parse($date);
                            $isToday = $dateObj->isToday();
                            // Format ngày: "Hôm nay (05/01)" hoặc "Thứ Hai, 06/01/2026"
                            $displayDate = $isToday ? 'Hôm nay (' . $dateObj->format('d/m') . ')' : $dateObj->translatedFormat('l, d/m/Y');
                        @endphp

                        <div class="card border-0 shadow-sm bg-white">
                            <div class="card-header bg-transparent border-0 pt-3 pb-0">
                                <h5 class="fw-bold text-dark mb-0">
                                    <i class="bi bi-calendar-day text-secondary me-2"></i>{{ $displayDate }}
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($showtimes as $st)
                                        @php
                                            $time = \Carbon\Carbon::parse($st->start_time);
                                        @endphp

                                        {{-- SỬA LỖI TẠI ĐÂY: Thay route('bookings.show') thành route('tickets.create') --}}
                                        <a href="{{ route('tickets.create', $st->id) }}"
                                           class="btn btn-outline-primary px-4 py-2 text-center"
                                           style="min-width: 100px;"
                                           title="Phòng chiếu: {{ $st->room_name }}">
                                            <div class="fw-bold fs-5">{{ $time->format('H:i') }}</div>
                                            <div style="font-size: 0.75rem;" class="text-muted">
                                                {{ $st->room_name }}
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-light text-center py-5 border border-dashed">
                    <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
                    <h5 class="text-muted">Chưa có lịch chiếu nào trong thời gian tới.</h5>
                    <p class="mb-0">Vui lòng quay lại sau hoặc tham khảo các phim khác.</p>
                </div>
            @endif
            {{-- === KẾT THÚC === --}}

        </div>
    </div>
</div>
@endsection
