@extends('layouts.app')
@section('title','Quản lý tài khoản')

@section('content')
<div class="container-fluid py-3">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h4 class="fw-bold mb-0">Quản lý tài khoản</h4>

    {{-- 👇 thêm class user-manage-tools --}}
    <div class="d-flex gap-2 user-manage-tools">
      <form method="get" class="d-flex gap-2">
        <input type="text" name="q" class="form-control"
               placeholder="Tìm tên / email / SĐT" value="{{ $q }}">
        <button class="btn btn-outline-secondary">
          <i class="bi bi-search"></i> Tìm
        </button>
        @if($q)
          <a href="{{ route('admin.users.index') }}"
             class="btn btn-outline-light border">Xoá lọc</a>
        @endif
      </form>

      <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Thêm tài khoản
      </a>
    </div>
  </div>

  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger mb-3">
      <ul class="mb-0 ps-3">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="table-responsive">
      <table class="table align-middle mb-0 user-manage-table">
        <thead class="table-light">
          <tr>
            <th style="width:60px">#</th>
            <th>Họ và tên</th>
            <th>Email</th>
            <th>SĐT</th>
            <th>Vai trò</th>
            <th>Ngày sinh</th>
            <th>Ngày tạo</th>
            <th class="text-start" style="width:240px">Thao tác</th>
          </tr>
        </thead>

        <tbody>
          @forelse($users as $idx => $u)
            @php
              $isAdmin = (int)$u->role_id === 1;
              $isSelf  = auth()->id() === $u->id;
            @endphp
            <tr>
              <td>{{ $users->firstItem() + $idx }}</td>
              <td class="fw-semibold">{{ $u->name }}</td>
              <td>{{ $u->email }}</td>
              <td>{{ $u->phone }}</td>
              <td>
                @if($isAdmin)
                  <span class="badge rounded-pill"
                        style="background:#7c210b;color:#fff">ADMIN</span>
                @else
                  <span class="badge bg-secondary-subtle text-secondary">Customer</span>
                @endif
              </td>
              <td>{{ $u->birthday ? \Carbon\Carbon::parse($u->birthday)->format('d/m/Y') : '—' }}</td>
              <td>{{ $u->created_at?->format('d/m/Y H:i') }}</td>

              <td class="text-start">
                <div class="ua-actions">
                  <a class="btn btn-sm btn-outline-primary @if($isSelf) disabled @endif"
                     href="{{ $isSelf ? 'javascript:void(0)' : route('admin.users.edit',$u) }}">
                    <i class="bi bi-pencil-square"></i> Sửa
                  </a>

                  <form class="ua-form" method="post"
                        action="{{ route('admin.users.resetPassword',$u) }}"
                        onsubmit="return confirm('Reset mật khẩu cho {{ $u->name }}?');">
                    @csrf
                    <button class="btn btn-sm btn-warning" @if($isSelf) disabled @endif>
                      <i class="bi bi-key"></i> Reset MK
                    </button>
                  </form>

                  <form class="ua-form" method="post"
                        action="{{ route('admin.users.destroy',$u) }}"
                        onsubmit="return confirm('Xoá tài khoản {{ $u->name }}?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" @if($isSelf) disabled @endif>
                      <i class="bi bi-trash"></i> Xoá
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center text-muted py-4">
                Chưa có tài khoản.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if ($users->hasPages())
      <div class="card-footer">
        {{ $users->links() }}
      </div>
    @endif
  </div>
</div>

{{-- ================= CSS CHỈ TRANG QUẢN LÝ TÀI KHOẢN ================= --}}
<style>
  /* ===== Fix cột Thao tác ===== */
  .user-manage-table td:last-child{
    vertical-align: top;
    padding-top: 6px; /* chỉnh 4–6px nếu muốn sát hơn */
  }

  .ua-actions{
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: nowrap;
  }
  .ua-form{ margin: 0; }

  .ua-actions .btn{
    display: inline-flex;
    align-items: center;
    gap: 6px;
    line-height: 1;
    padding: 6px 10px;
    white-space: nowrap;
  }

  /* ===== Fix thanh tìm kiếm phía trên (không cho giãn rộng) ===== */
  .user-manage-tools input.form-control{
    width: 260px;     /* chỉnh 220–280px tuỳ ý */
    flex: 0 0 auto;
  }
  .user-manage-tools .btn{
    flex: 0 0 auto;
    white-space: nowrap;
  }
</style>

@endsection
