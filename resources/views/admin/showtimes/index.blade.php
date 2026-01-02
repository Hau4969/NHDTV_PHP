{{-- resources/views/admin/showtimes/index.blade.php --}}
@extends('layouts.app')
@section('title','Quản lý Suất chiếu')

@section('content')
<div class="container py-3">

  {{-- Header + nút thêm luôn hiển thị --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Quản lý Suất chiếu</h5>
    <a href="{{ route('admin.showtimes.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Thêm suất chiếu
    </a>
  </div>

  {{-- Flash messages (layout cũng có, giữ dự phòng) --}}
  @if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
  @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:80px">#</th>
              <th>Phim</th>
              <th style="width:180px">Phòng</th>
              <th style="width:200px">Bắt đầu</th>
              <th style="width:200px">Kết thúc</th>
              <th style="width:140px">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @forelse($showtimes as $s)
              @php
                $start = $s->start_time ? \Illuminate\Support\Carbon::parse($s->start_time)->format('H:i d/m/Y') : '—';
                $end   = $s->end_time   ? \Illuminate\Support\Carbon::parse($s->end_time)->format('H:i d/m/Y')   : '—';
              @endphp
              <tr>
                <td>{{ $s->id }}</td>
                <td class="fw-semibold text-truncate" title="{{ $s->movie->title ?? '' }}">
                  {{ $s->movie->title ?? '—' }}
                </td>
                <td>{{ $s->room->name ?? '—' }}</td>
                <td>{{ $start }}</td>
                <td>{{ $end }}</td>
                <td>
                  <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.showtimes.edit', $s) }}" title="Sửa">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <form class="d-inline" method="POST" action="{{ route('admin.showtimes.destroy', $s) }}"
                        onsubmit="return confirm('Xoá suất chiếu này?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" title="Xoá">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                  Chưa có suất chiếu.
                  <div class="mt-2">
                    <a href="{{ route('admin.showtimes.create') }}" class="btn btn-sm btn-primary">
                      <i class="bi bi-plus-circle"></i> Thêm suất chiếu
                    </a>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-footer bg-white">
      {{ $showtimes->links() }}
    </div>
  </div>
</div>
@endsection
