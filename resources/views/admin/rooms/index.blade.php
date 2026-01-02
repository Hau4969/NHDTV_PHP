@extends('layouts.app')
@section('title','Quản lý Phòng chiếu')

@section('content')
<div class="container py-3">
  {{-- Header hàng trên cùng: tiêu đề + nút Thêm phòng --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold mb-0">Quản lý Phòng chiếu</h5>
    <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Thêm phòng
    </a>
  </div>

  @if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
  @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:70px">#</th>
              <th>Tên phòng</th>
              <th style="width:120px">Sức chứa</th>
              <th style="width:160px">Số ghế hiện có</th>
              <th style="width:520px">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rooms as $r)
              @php $overflow = ($r->seats_count ?? 0) > (int)$r->capacity; @endphp
              <tr>
                <td>{{ $r->id }}</td>
                <td class="fw-semibold">{{ $r->name }}</td>
                <td>{{ $r->capacity }}</td>
                <td>
                  @if($overflow)
                    <span class="badge text-bg-danger">{{ $r->seats_count }}</span>
                    <small class="text-danger">> {{ $r->capacity }}</small>
                  @else
                    {{ $r->seats_count ?? 0 }}
                  @endif
                </td>
                <td>
                  <div class="d-inline-flex align-items-center gap-1 flex-wrap">

                    <a class="btn btn-outline-secondary btn-sm"
                       href="{{ route('admin.rooms.edit',$r) }}">
                      <i class="bi bi-pencil-square"></i> Sửa
                    </a>

                    <form method="POST" action="{{ route('admin.rooms.destroy',$r) }}"
                          onsubmit="return confirm('Xoá phòng {{ $r->name }}?');">
                      @csrf @method('DELETE')
                      <button class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash"></i> Xoá
                      </button>
                    </form>

                    {{-- Sinh ghế: rows/cols/reset gọn và cùng chiều cao các nút --}}
                    <form method="POST" action="{{ route('admin.rooms.generateSeats',$r) }}"
                          class="d-inline-flex align-items-center gap-1"
                          onsubmit="return confirm('Tạo ghế cho phòng {{ $r->name }}? {{ ($r->seats_count??0) ? 'Ghế cũ sẽ bị thay nếu chọn Reset.' : '' }}');">
                      @csrf

                      <div class="input-group input-group-sm" style="width: 190px;">
                        <span class="input-group-text">rows</span>
                        <input type="number" name="rows" class="form-control" min="0" placeholder="auto">
                        <span class="input-group-text">cols</span>
                        <input type="number" name="cols" class="form-control" min="1" max="100" value="10">
                      </div>

                      <div class="form-check form-check-inline m-0">
                        <input class="form-check-input" type="checkbox" name="reset" value="1" id="reset{{$r->id}}">
                        <label class="form-check-label small" for="reset{{$r->id}}">Reset</label>
                      </div>

                      <button class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-grid-3x3-gap"></i> Tạo ghế
                      </button>
                    </form>

                    {{-- Nếu ghế hiện có > sức chứa, cho phép "cắt ghế dư (an toàn)" --}}
                    @if($overflow)
                      <form method="POST" action="{{ route('admin.rooms.trimSeats',$r) }}"
                            onsubmit="return confirm('Cắt bớt ghế dư (không bị vé tham chiếu) cho phòng {{ $r->name }}?');">
                        @csrf
                        <button class="btn btn-warning btn-sm">
                          <i class="bi bi-scissors"></i> Cắt bớt ghế dư
                        </button>
                      </form>
                    @endif

                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có phòng nào.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if ($rooms->hasPages())
      <div class="card-footer bg-white">
        {{ $rooms->links() }}
      </div>
    @endif
  </div>
</div>
@endsection
