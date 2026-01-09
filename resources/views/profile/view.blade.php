@extends('layouts.app')
@section('title','Tài khoản')

@section('content')
<div class="container py-4">
  <h4 class="fw-bold mb-3">Tài khoản</h4>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="row g-4">
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body text-center">
          <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center"
               style="width:96px;height:96px;font-size:36px;">
            {{ strtoupper(mb_substr(auth()->user()->name,0,1)) }}
          </div>
          <div class="mt-3">
            <div class="fw-semibold">{{ auth()->user()->name }}</div>
            <div class="text-muted small">{{ auth()->user()->email }}</div>
            <span class="badge bg-secondary mt-2">
              {{ (auth()->user()->role_id==1)?'Admin':'Customer' }}
            </span>
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
            <div class="col-sm-8">{{ auth()->user()->name }}</div>
          </div>
          <div class="row mb-2">
            <div class="col-sm-4 text-muted">Email</div>
            <div class="col-sm-8">{{ auth()->user()->email }}</div>
          </div>
          <div class="row mb-2">
            <div class="col-sm-4 text-muted">Ngày tạo</div>
            <div class="col-sm-8">{{ optional(auth()->user()->created_at)->format('d/m/Y H:i') }}</div>
          </div>
          <div class="row">
            <div class="col-sm-4 text-muted">Lần cập nhật</div>
            <div class="col-sm-8">{{ optional(auth()->user()->updated_at)->format('d/m/Y H:i') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
