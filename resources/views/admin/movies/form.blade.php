@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 900px;">
  <h3 class="mb-3">{{ $movie->exists ? 'Sửa phim' : 'Thêm phim' }}</h3>

  @if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif
  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif

  <form method="POST" action="{{ $movie->exists ? route('admin.movies.update',$movie) : route('admin.movies.store') }}">
    @csrf
    @if($movie->exists) @method('PUT') @endif

    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Tiêu đề</label>
        <input type="text" name="title" class="form-control" required
               value="{{ old('title', $movie->title) }}">
      </div>
      <div class="col-md-4">
        <label class="form-label">Thể loại</label>
        <input type="text" name="genre" class="form-control" required
               value="{{ old('genre', $movie->genre) }}">
      </div>

      <div class="col-md-4">
        <label class="form-label">Thời lượng (phút)</label>
        <input type="number" name="duration" class="form-control" min="1" max="600" required
               value="{{ old('duration', $movie->duration) }}">
      </div>
      <div class="col-md-4">
        <label class="form-label">Ngày khởi chiếu</label>
        <input type="date" name="release_date" class="form-control" required
               value="{{ old('release_date', optional($movie->release_date)->format('Y-m-d')) }}">
      </div>

      <div class="col-md-4 d-flex align-items-center">
        <div class="form-check mt-4">
          {{-- luôn gửi 0 khi unchecked --}}
          <input type="hidden" name="is_now_showing" value="0">
          {{-- checked -> gửi 1 --}}
          <input class="form-check-input" type="checkbox" id="is_now_showing" name="is_now_showing" value="1"
                 {{ old('is_now_showing', (int)$movie->is_now_showing) ? 'checked' : '' }}>
          <label class="form-check-label" for="is_now_showing">Đang chiếu</label>
        </div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Poster URL</label>
        <input type="url" name="poster_url" class="form-control"
               value="{{ old('poster_url', $movie->poster_url) }}">
      </div>
      <div class="col-md-6">
        <label class="form-label">Trailer URL</label>
        <input type="url" name="trailer_url" class="form-control"
               value="{{ old('trailer_url', $movie->trailer_url) }}">
      </div>

      <div class="col-12">
        <label class="form-label">Mô tả (summary)</label>
        <textarea name="summary" rows="4" class="form-control">{{ old('summary', $movie->summary) }}</textarea>
      </div>
    </div>

    <div class="d-flex gap-2 mt-4">
      <button class="btn btn-primary">
        {{ $movie->exists ? 'Cập nhật' : 'Thêm mới' }}
      </button>
      <a href="{{ route('admin.movies.index') }}" class="btn btn-outline-secondary">Hủy</a>
    </div>
  </form>
</div>
@endsection
