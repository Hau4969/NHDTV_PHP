@extends('layouts.app')
@section('title','Tài khoản')

@section('content')
<div class="container py-4">
  @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
  @if(session('ok'))      <div class="alert alert-success">{{ session('ok') }}</div>      @endif

  <h4 class="fw-bold mb-3">Tài khoản</h4>

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm text-center">
        <div class="card-body">
          @php
            $avatarUrl = $user->avatar ? asset('storage/'.$user->avatar) : null;
          @endphp

          @if($avatarUrl)
            <img src="{{ $avatarUrl }}" alt="avatar" class="rounded-circle mb-3"
                 style="width:110px;height:110px;object-fit:cover">
          @else
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                 style="width:110px;height:110px;font-size:40px;">
              {{ strtoupper(mb_substr($user->name,0,1)) }}
            </div>
          @endif

          <div class="fw-semibold">{{ $user->name }}</div>
          <div class="text-muted small">{{ $user->email }}</div>
          <div class="mt-2">
            <span class="badge bg-secondary">{{ $user->role_id==1 ? 'Admin' : 'Customer' }}</span>
          </div>

          <a href="{{ route('profile.edit') }}" class="btn btn-primary w-100 mt-3">Chỉnh sửa</a>
        </div>
      </div>
    </div>

    <div class="col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h6 class="fw-bold mb-3">Thông tin chi tiết</h6>

          <div class="row mb-2">
            <div class="col-sm-4 text-muted">Họ tên</div>
            <div class="col-sm-8">{{ $user->name }}</div>
          </div>
          <div class="row mb-2">
            <div class="col-sm-4 text-muted">Email</div>
            <div class="col-sm-8">{{ $user->email }}</div>
          </div>
          <div class="row mb-2">
            <div class="col-sm-4 text-muted">Số điện thoại</div>
            <div class="col-sm-8">{{ $user->phone ?? '—' }}</div>
          </div>
          <div class="row mb-2">
            <div class="col-sm-4 text-muted">Địa chỉ</div>
            <div class="col-sm-8">{{ $user->address ?? '—' }}</div>
          </div>
          <div class="row mb-2">
            <div class="col-sm-4 text-muted">Ngày sinh</div>
            <div class="col-sm-8">
              {{ $user->birthday ? \Illuminate\Support\Carbon::parse($user->birthday)->format('d/m/Y') : '—' }}
            </div>
          </div>
          <div class="row">
            <div class="col-sm-4 text-muted">Cập nhật lúc</div>
            <div class="col-sm-8">{{ optional($user->updated_at)->format('d/m/Y H:i') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
