@extends('layouts.app')
@section('title', 'Phim')

@section('content')
<div class="container py-4">

  {{-- Slider banner (Bootstrap Carousel) --}}
  <div id="movieCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
    <div class="carousel-inner rounded-3 shadow-sm">
      @forelse($banners as $i => $m)
        @php
          $subtitle = $m->tagline ?? ($m->description ?? null);
        @endphp
        <div class="carousel-item @if($i===0) active @endif">
          <img src="{{ $m->poster_url ?: asset('images/placeholder-poster.jpg') }}"
               class="d-block w-100 object-fit-cover"
               alt="{{ $m->title }}"
               style="height: 380px;">
          <div class="carousel-caption d-none d-md-block text-start">
            <h5 class="fw-bold">{{ $m->title }}</h5>
            @if(!empty($subtitle))
              <p class="mb-2">{{ \Illuminate\Support\Str::limit($subtitle, 120) }}</p>
            @endif
            <a href="{{ route('movies.show', $m->id) }}" class="btn btn-primary btn-sm">Xem chi tiết</a>
          </div>
        </div>
      @empty
        <div class="carousel-item active">
          <img src="{{ asset('images/placeholder-wide.jpg') }}" class="d-block w-100" style="height:380px" alt="...">
        </div>
      @endforelse
    </div>

    @if(($banners ?? collect())->count() > 1)
      <button class="carousel-control-prev" type="button" data-bs-target="#movieCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Trước</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#movieCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Sau</span>
      </button>
    @endif
  </div>

  {{-- Tabs --}}
  <div class="d-flex justify-content-center gap-2 mb-3">
    <a href="{{ route('movies.index', ['tab'=>'now']) }}"
       class="btn {{ $tab==='now' ? 'btn-dark' : 'btn-outline-dark' }}">
      Phim đang chiếu
    </a>
    <a href="{{ route('movies.index', ['tab'=>'upcoming']) }}"
       class="btn {{ $tab==='upcoming' ? 'btn-dark' : 'btn-outline-dark' }}">
      Phim sắp chiếu
    </a>
  </div>

  @php $list = $tab === 'upcoming' ? $upcoming : $nowShowing; @endphp

{{-- Grid danh sách phim (card to hơn) --}}
<div class="row g-4">
  @forelse($list as $m)
    {{-- to hơn: mỗi hàng 2/3/4 card tuỳ breakpoint --}}
    <div class="col-6 col-sm-4 col-md-3 col-lg-3">
      <div class="card movie-card h-100 shadow-sm border-0">
        <div class="ratio ratio-2x3">
          <img
            src="{{ $m->poster_url ?: asset('images/placeholder-poster.jpg') }}"
            class="card-img-top object-fit-cover"
            alt="{{ $m->title }}">
        </div>

        <div class="card-body p-3">
          {{-- Tên phim TRƯỚC --}}
          <h6 class="card-title mb-1 text-truncate" title="{{ $m->title }}">
            {{ $m->title }}
          </h6>

          {{-- Thể loại + thời lượng SAU --}}
          <div class="small text-muted mb-2">
            {{ $m->genre ?? '---' }}
            @if(!empty($m->duration)) • {{ $m->duration }} phút @endif
          </div>

          @if(isset($m->release_date))
            <div class="text-muted small">
              Khởi chiếu: {{ \Illuminate\Support\Carbon::parse($m->release_date)->format('d/m/Y') }}
            </div>
          @endif
        </div>

        <div class="card-footer bg-white border-0 pt-0 pb-3 px-3">
          <div class="d-grid gap-2">
            <a href="{{ route('movies.show', $m->id) }}" class="btn btn-outline-primary btn-sm">Chi tiết</a>
            <a href="{{ route('movies.show', $m->id).'#showtimes' }}" class="btn btn-primary btn-sm">Đặt vé</a>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12">
      <div class="alert alert-secondary">Chưa có phim để hiển thị.</div>
    </div>
  @endforelse
</div>


  <div class="mt-3">
    {{ $list->links() }}
  </div>
</div>

<style>
  .object-fit-cover { object-fit: cover; }
  .ratio-2x3 { aspect-ratio: 2 / 3; }

  /* Card to hơn một chút, ảnh bo tròn đẹp hơn */
  .movie-card { border-radius: 14px; overflow: hidden; }
  .movie-card .card-img-top { border-bottom-left-radius: 0; border-bottom-right-radius: 0; }
  .movie-card .card-title { font-weight: 700; }
</style>

@endsection
