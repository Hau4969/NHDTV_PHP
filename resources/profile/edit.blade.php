@extends('layouts.app')
@section('title','Cập nhật hồ sơ')
@section('content')
<div class="container" style="max-width:720px;">
  <h4 class="mb-3">Cập nhật hồ sơ</h4>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

  <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
    @csrf @method('PATCH')

    <div class="mb-3">
      <label class="form-label">Họ và tên</label>
      <input name="name" class="form-control" value="{{ old('name',$user->name) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="{{ old('email',$user->email) }}" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Số điện thoại</label>
      <input name="phone" class="form-control" value="{{ old('phone',$user->phone) }}" placeholder="Chỉ số, 8–15 ký tự">
    </div>

    <div class="mb-3">
      <label class="form-label">Địa chỉ</label>
      <input name="address" class="form-control" value="{{ old('address',$user->address) }}">
    </div>

    <div class="mb-3">
      <label class="form-label">Ngày sinh</label>
      <input type="date" name="birthday" class="form-control" value="{{ old('birthday', optional($user->birthday)->format('Y-m-d')) }}">
    </div>

    <div class="mb-3">
      <label class="form-label">Ảnh đại diện</label>
      <input type="file" name="avatar" class="form-control">
      @if($user->avatar)
        <div class="mt-2">
          <img src="{{ asset('storage/'.$user->avatar) }}" alt="" style="height:80px;border-radius:8px;">
        </div>
      @endif
    </div>

    <button class="btn btn-primary">Lưu thay đổi</button>
  </form>
</div>
@endsection
