{{-- resources/views/admin/showtimes/index.blade.php --}}
@extends('layouts.app')
@section('title','Quản lý Suất chiếu')

@section('content')
<div class="container py-3">

  {{-- Header + nút thêm --}}
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Quản lý Suất chiếu</h5>
    <a href="{{ route('admin.showtimes.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Thêm suất chiếu
    </a>
  </div>

  {{-- Flash messages --}}
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
                $movieTitle = $s->movie->title ?? '—';
                $roomName = $s->room->name ?? '—';
              @endphp
              <tr>
                <td>{{ $showtimes->firstItem() + $loop->index }}</td>
                <td class="fw-semibold text-truncate" title="{{ $movieTitle }}">
                  {{ $movieTitle }}
                </td>
                <td>{{ $roomName }}</td>
                <td>{{ $start }}</td>
                <td>{{ $end }}</td>
                <td>
                  <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.showtimes.edit', $s) }}" title="Sửa">
                    <i class="bi bi-pencil-square"></i>
                  </a>

                  {{-- Thay nút submit form bằng nút kích hoạt Modal --}}
                  <button type="button"
                          class="btn btn-outline-danger btn-sm"
                          title="Xoá"
                          data-bs-toggle="modal"
                          data-bs-target="#deleteModal"
                          data-url="{{ route('admin.showtimes.destroy', $s) }}"
                          data-id="{{ $s->id }}"
                          data-movie="{{ $movieTitle }}"
                          data-room="{{ $roomName }}"
                          data-time="{{ $start }}">
                    <i class="bi bi-trash"></i>
                  </button>
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

{{-- === MODAL XÁC NHẬN XÓA (Thêm mới phần này) === --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold" id="deleteModalLabel">XÁC NHẬN XÓA</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <p class="mb-3">Bạn có chắc chắn muốn xóa suất chiếu này không?</p>

        {{-- Thông tin suất chiếu sẽ hiển thị ở đây --}}
        <div class="alert alert-light border">
            <div class="mb-1"><strong>ID:</strong> <span id="modal-id" class="text-danger"></span></div>
            <div class="mb-1"><strong>Phim:</strong> <span id="modal-movie" class="fw-bold text-primary"></span></div>
            <div class="mb-1"><strong>Phòng:</strong> <span id="modal-room"></span></div>
            <div><strong>Thời gian:</strong> <span id="modal-time"></span></div>
        </div>

        <p class="text-muted small fst-italic mb-0">
          <i class="bi bi-exclamation-triangle"></i> Lưu ý: Hành động này không thể hoàn tác.
        </p>
      </div>

      <div class="modal-footer">
        {{-- Nút Hủy --}}
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>

        {{-- Form thực sự để gửi lệnh xóa --}}
        <form id="deleteForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">Xác nhận Xóa</button>
        </form>
      </div>

    </div>
  </div>
</div>

{{-- Script để xử lý dữ liệu động cho Modal --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var deleteModal = document.getElementById('deleteModal');

        deleteModal.addEventListener('show.bs.modal', function (event) {
            // 1. Lấy nút vừa bấm
            var button = event.relatedTarget;

            // 2. Lấy thông tin từ data-attribute của nút đó
            var url = button.getAttribute('data-url');
            var id = button.getAttribute('data-id');
            var movie = button.getAttribute('data-movie');
            var room = button.getAttribute('data-room');
            var time = button.getAttribute('data-time');

            // 3. Cập nhật thông tin vào Modal
            var modalForm = deleteModal.querySelector('#deleteForm');
            var modalId = deleteModal.querySelector('#modal-id');
            var modalMovie = deleteModal.querySelector('#modal-movie');
            var modalRoom = deleteModal.querySelector('#modal-room');
            var modalTime = deleteModal.querySelector('#modal-time');

            modalForm.action = url;
            modalId.textContent = id;
            modalMovie.textContent = movie;
            modalRoom.textContent = room;
            modalTime.textContent = time;
        });
    });
</script>
@endsection
