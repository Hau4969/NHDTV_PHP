@extends('layouts.app')
@section('title', ($user->exists ? 'Sửa' : 'Thêm').' tài khoản')

@section('content')
<div class="container" style="max-width:860px">
  <h4 class="fw-bold mb-3">{{ $user->exists ? 'Sửa' : 'Thêm' }} tài khoản</h4>

  @if(session('ok'))
    <div class="alert alert-success">{{ session('ok') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0 ps-3">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      <form method="post"
            action="{{ $user->exists ? route('admin.users.update',$user) : route('admin.users.store') }}">
        @csrf
        @if($user->exists) @method('PUT') @endif

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
            <input name="name" class="form-control" required
                   value="{{ old('name', $user->name) }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input name="email" type="email" class="form-control" required
                   value="{{ old('email', $user->email) }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Số điện thoại</label>
            <input name="phone" class="form-control" placeholder="09xxxxxxxx"
                   value="{{ old('phone', $user->phone) }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Ngày sinh</label>
            <input name="birthday" type="date" class="form-control"
                   value="{{ old('birthday', $user->birthday ? \Carbon\Carbon::parse($user->birthday)->format('Y-m-d') : '') }}">
          </div>

          <div class="col-12">
            <label class="form-label">Địa chỉ</label>
            <input name="address" class="form-control"
                   value="{{ old('address', $user->address) }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Vai trò <span class="text-danger">*</span></label>
            <select name="role_id" class="form-select" {{ auth()->id()===$user->id ? 'disabled' : '' }}>
              <option value="2" {{ (old('role_id', $user->role_id ?? 2)==2)?'selected':'' }}>Customer</option>
              <option value="1" {{ (old('role_id', $user->role_id ?? 2)==1)?'selected':'' }}>Admin</option>
            </select>
            @if(auth()->id()===$user->id)
              <input type="hidden" name="role_id" value="{{ $user->role_id }}">
              <div class="form-text text-warning">Bạn không thể thay đổi vai trò của chính mình.</div>
            @endif
          </div>

          <div class="col-md-6">
            <label class="form-label">
              Mật khẩu {{ $user->exists ? '(để trống nếu không đổi)' : '*' }}
            </label>
            <input name="password" type="password" class="form-control" {{ $user->exists ? '' : 'required' }}>
          </div>
        </div>

        <div class="d-flex gap-2 mt-4">
          <button class="btn btn-primary">Lưu</button>
          <a href="{{ route('admin.users.index') }}" class="btn btn-light">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
