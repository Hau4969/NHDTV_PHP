@extends('layouts.app')
@section('title','Chỉnh sửa tài khoản')

@section('content')
<div class="container py-4">
  <h4 class="fw-bold mb-3">Chỉnh sửa tài khoản</h4>

  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

  @if ($errors->any())
    <div class="alert alert-danger">
      <div class="fw-semibold">Đã có lỗi:</div>
      <ul class="mb-0">
        @foreach ($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm text-center">
        <div class="card-body">
          @php $avatarUrl = $user->avatar ? asset('storage/'.$user->avatar) : null; @endphp
          @if($avatarUrl)
            <img id="preview" src="{{ $avatarUrl }}" class="rounded-circle mb-3"
                 style="width:120px;height:120px;object-fit:cover" alt="avatar">
          @else
            <img id="preview" src="https://placehold.co/240x240?text=Avatar" class="rounded-circle mb-3"
                 style="width:120px;height:120px;object-fit:cover" alt="avatar">
          @endif
          <div class="small text-muted">Ảnh đại diện (jpg, png, webp ≤ 2MB)</div>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Họ và tên</label>
                <input type="text" name="name" class="form-control"
                       value="{{ old('name', $user->name) }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control"
                       value="{{ old('email', $user->email) }}" required>
              </div>

              <div class="col-md-6">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="phone" class="form-control"
                       value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxx">
              </div>

              <div class="col-md-6">
                <label class="form-label">Ngày sinh</label>
                <input type="date" name="birthday" class="form-control"
                       value="{{ old('birthday', $user->birthday) }}">
              </div>

              <div class="col-12">
                <label class="form-label">Địa chỉ</label>
                <input type="text" name="address" class="form-control"
                       value="{{ old('address', $user->address) }}">
              </div>

              <div class="col-12">
                <label class="form-label">Ảnh đại diện</label>
                <input type="file" name="avatar" id="avatar" accept=".jpg,.jpeg,.png,.webp" class="form-control">
              </div>
            </div>

            <div class="d-flex gap-2 mt-3">
              <button class="btn btn-primary">Lưu thay đổi</button>
              <a href="{{ route('profile.view') }}" class="btn btn-outline-secondary">Hủy</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- preview ảnh --}}
<script>
document.getElementById('avatar')?.addEventListener('change', e => {
  const f = e.target.files?.[0];
  if (!f) return;
  const url = URL.createObjectURL(f);
  const img = document.getElementById('preview');
  if (img) img.src = url;
});
</script>
@endsection
