@extends('layouts.app')
@section('title','Trang chủ')

@section('content')
<style>
  /* ===== Strip (carousel ngang) – poster TO HƠN ===== */
  .movie-strip {
    position: relative;
  }

  .movie-strip .strip-track {
    display: flex;
    gap: 1rem;
    overflow-x: auto;
    scroll-behavior: smooth;
    padding: 0.5rem 0.5rem 0.75rem;
    scrollbar-width: thin;
  }

  .movie-strip .strip-track::-webkit-scrollbar {
    height: 6px;
  }

  .movie-strip .strip-track::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
  }

  /* Poster to hơn – giữ tỉ lệ 2:3 */
  .movie-strip .strip-item {
    width: 180px; /* mobile */
    flex: 0 0 auto;
    border-radius: 14px;
    overflow: hidden;
    transition: transform 0.2s ease;
  }

  @media (min-width: 768px) {
    .movie-strip .strip-item {
      width: 220px; /* tablet */
    }
  }

  @media (min-width: 1200px) {
    .movie-strip .strip-item {
      width: 260px; /* desktop */
    }
  }

  .movie-strip .strip-item .card-img-top {
    width: 100%;
    aspect-ratio: 2 / 3;
    object-fit: cover;
    display: block;
  }

  .movie-strip .strip-item .card-body {
    padding: 0.6rem 0.7rem 0.8rem !important;
  }

  .movie-strip .strip-item .small {
    font-size: 0.8rem;
  }

  .movie-strip .strip-item:hover {
    transform: scale(1.03);
  }

  .movie-strip .strip-prev,
  .movie-strip .strip-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.15);
  }

  .movie-strip .strip-prev {
    left: -6px;
  }

  .movie-strip .strip-next {
    right: -6px;
  }

  /* Hero tinh gọn – giữ nguyên tỉ lệ */
  .object-fit-cover {
    object-fit: cover;
  }

  .line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
</style>


<div class="container py-3">
  {{-- ================== HERO + BOX OFFICE ================== --}}
  <div class="row g-3 align-items-stretch">
    {{-- HERO --}}
    <div class="col-lg-8">
      <div class="card h-100 border-0 shadow-sm">
        <div class="row g-0 h-100">
          <div class="col-md-6">
            @php
              $__featured = $featured ?? ($boxOffice->first() ?? ($nowShowing->first() ?? ($comingSoon->first() ?? null)));
            @endphp
            @if($__featured)
              <img class="img-fluid rounded-start w-100 h-100 object-fit-cover"
                   src="{{ $__featured->poster_url ?? asset('assets/images/placeholder-movie.jpg') }}"
                   alt="{{ $__featured->title }}">
            @else
              <div class="ratio ratio-16x9 bg-light rounded-start"></div>
            @endif
          </div>
          <div class="col-md-6 p-3 d-flex flex-column">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="fw-bold small text-uppercase text-muted">Phim hot tuần này</span>
              @if(isset($boxOffice) && $boxOffice->first())
                <span class="badge bg-primary">{{ number_format($boxOffice->first()->revenue ?? 0) }} đ</span>
              @endif
            </div>
            <h4 class="mb-2 flex-shrink-0">{{ $__featured->title ?? 'Đang cập nhật' }}</h4>
            <p class="text-muted small mb-3 line-clamp-4">{{ $__featured->summary ?? 'Nội dung đang cập nhật...' }}</p>
            <div class="mt-auto">
              @if($__featured)
                <a href="{{ url('/movies/'. $__featured->id) }}" class="btn btn-primary btn-sm">Xem chi tiết</a>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- BOX OFFICE --}}
    <div class="col-lg-4">
      <div class="card h-100 border-0 shadow-sm">
        <div class="card-header bg-dark text-white py-2"><strong>BOX OFFICE</strong></div>
        <div class="list-group list-group-flush small">
          @forelse(($boxOffice ?? collect()) as $idx => $m)
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
               href="{{ url('/movies/'.$m->id) }}">
              <span class="text-truncate">
                <span class="badge bg-secondary me-2">{{ $idx+1 }}</span>{{ $m->title }}
              </span>
              <span class="fw-semibold">{{ number_format($m->revenue ?? 0) }} đ</span>
            </a>
          @empty
            <div class="list-group-item text-muted">Chưa có dữ liệu.</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- ================== STRIP: ĐANG CHIẾU ================== --}}
  <div class="mt-4">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h5 class="mb-0 fw-bold">Đang chiếu</h5>
      <a href="{{ url('/movies') }}" class="small text-decoration-none">Xem tất cả</a>
    </div>

    <div class="movie-strip position-relative">
      <button class="strip-prev btn btn-light btn-sm" aria-label="Prev"><i class="bi bi-chevron-left"></i></button>
      <div class="strip-track">
        @forelse(($nowShowing ?? collect()) as $m)
          <a class="strip-item card border-0 shadow-sm" href="{{ url('/movies/'.$m->id) }}" title="{{ $m->title }}">
            <img src="{{ $m->poster_thumb ?? $m->poster_url ?? asset('assets/images/placeholder-movie.jpg') }}"
                 class="card-img-top" alt="{{ $m->title }}">
            <div class="card-body p-2">
              <div class="small fw-semibold text-truncate">{{ $m->title }}</div>
              <div class="small text-muted">{{ \Illuminate\Support\Str::limit($m->genre ?? '', 22) }}</div>
            </div>
          </a>
        @empty
          <div class="text-muted px-2">Đang cập nhật…</div>
        @endforelse
      </div>
      <button class="strip-next btn btn-light btn-sm" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
    </div>
  </div>

  {{-- ================== STRIP: SẮP CHIẾU ================== --}}
  <div class="mt-4">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <h5 class="mb-0 fw-bold">Sắp chiếu</h5>
    </div>

    <div class="movie-strip position-relative">
      <button class="strip-prev btn btn-light btn-sm" aria-label="Prev"><i class="bi bi-chevron-left"></i></button>
      <div class="strip-track">
        @forelse(($comingSoon ?? collect()) as $m)
          <a class="strip-item card border-0 shadow-sm" href="{{ url('/movies/'.$m->id) }}" title="{{ $m->title }}">
            <img src="{{ $m->poster_thumb ?? $m->poster_url ?? asset('assets/images/placeholder-movie.jpg') }}"
                 class="card-img-top" alt="{{ $m->title }}">
            <div class="card-body p-2">
              <div class="small fw-semibold text-truncate">{{ $m->title }}</div>
              <div class="small text-muted">Khởi chiếu: {{ optional($m->release_date)->format('d/m/Y') }}</div>
            </div>
          </a>
        @empty
          <div class="text-muted px-2">Đang cập nhật…</div>
        @endforelse
      </div>
      <button class="strip-next btn btn-light btn-sm" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
    </div>
  </div>

  {{-- ================== EVENT ================== --}}
  <div class="mt-4">
    <h5 class="fw-bold text-center mb-3">EVENT</h5>
    <div class="row g-3">
      @forelse(($events ?? collect()) as $e)
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <img class="card-img-top" src="{{ $e->banner ?? asset('assets/images/placeholder-wide.jpg') }}"
                 alt="{{ $e->title }}">
            <div class="card-body">
              <h6 class="fw-bold">{{ $e->title }}</h6>
              <p class="small text-muted mb-2">{{ \Illuminate\Support\Str::limit($e->summary, 90) }}</p>
              <a href="{{ url('/events/'.$e->id) }}" class="btn btn-sm btn-primary">Xem chi tiết</a>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12 text-center text-muted">Chưa có sự kiện.</div>
      @endforelse
    </div>
  </div>

  {{-- ================== THÔNG BÁO ================== --}}
  <div class="mt-4">
    <h6 class="mb-2">Thông báo</h6>
    <div class="row g-3">
      @forelse(($notices ?? collect()) as $n)
        <div class="col-md-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <a href="{{ url('/posts/'.$n->id) }}" class="h6 d-block text-decoration-none fw-bold mb-1">
                {{ $n->title }}
              </a>
              <div class="small text-muted mb-2">{{ optional($n->created_at)->format('d/m/Y H:i') }}</div>
              <p class="mb-0 text-muted">
                {{ \Illuminate\Support\Str::limit($n->excerpt ?? strip_tags($n->content ?? ''), 120) }}
              </p>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12 text-muted">Chưa có thông báo.</div>
      @endforelse
    </div>
  </div>
</div>

{{-- JS cuộn ngang cho 2 strip --}}
<script>
  document.querySelectorAll('.movie-strip').forEach(strip => {
    const track = strip.querySelector('.strip-track');
    const prev  = strip.querySelector('.strip-prev');
    const next  = strip.querySelector('.strip-next');

    const itemW = () => (strip.querySelector('.strip-item')?.getBoundingClientRect().width || 140) + 12;
    const step  = () => (window.innerWidth >= 1200 ? 5 : window.innerWidth >= 768 ? 4 : 3) * itemW();

    prev?.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
    next?.addEventListener('click', () => track.scrollBy({ left:  step(), behavior: 'smooth' }));
  });
</script>
@endsection
