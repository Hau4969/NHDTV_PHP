@extends('layouts.app')

@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Quản lý phim</h3>
    <a href="{{ route('admin.movies.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-lg"></i> Thêm phim
    </a>
  </div>

  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
  @endif

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Tiêu đề</th>
          <th>Thể loại</th>
          <th>Thời lượng</th>
          <th>Khởi chiếu</th>
          <th>Hiển thị</th>
          <th class="text-end">Hành động</th>
        </tr>
      </thead>
      <tbody>
        @forelse($movies as $i => $m)
          <tr>
            <td>{{ $movies->firstItem() + $i }}</td>
            <td>{{ $m->title }}</td>
            <td>{{ $m->genre }}</td>
            <td>{{ $m->duration_min }}p</td>
            <td>{{ optional($m->release_date)->format('d/m/Y') }}</td>
            <td>
              @if($m->is_active)
                <span class="badge bg-success">Đang hiển thị</span>
              @else
                <span class="badge bg-secondary">Ẩn</span>
              @endif
            </td>
            <td class="text-end">
              <a href="{{ route('admin.movies.edit', $m) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil-square"></i> Sửa
              </a>
              <form action="{{ route('admin.movies.destroy', $m) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('Xóa phim này?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">
                  <i class="bi bi-trash"></i> Xóa
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="text-center text-muted">Chưa có phim.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $movies->links() }}
</div>
@endsection
