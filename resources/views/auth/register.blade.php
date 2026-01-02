@extends('layouts.guest')
@section('title','Đăng ký')

@section('content')
<div class="register-page">
  {{-- ====== HEADER ====== --}}
  <div class="navbar-top">
    <div class="nav-left">
      <a href="https://facebook.com" target="_blank">
        <img src="https://upload.wikimedia.org/wikipedia/commons/0/05/Facebook_Logo_%282019%29.png" alt="Facebook">
        Lotte Cinema Facebook
      </a>
    </div>
  
    <div class="nav-right">
      <a href="{{ route('login') }}">Đăng nhập</a>
      <a href="#">Thẻ thành viên</a>
      <a href="#">Hỗ trợ khách hàng</a>
      <button class="lang-btn">English</button>
    </div>
  </div>
  {{-- ====== END HEADER ====== --}}

  <div class="text-center mb-3">
    <img src="{{ asset('assets/images/logo.png') }}" 
         alt="Logo Cinema" 
         style="height: 80px; object-fit: contain;">
  </div>

  <div class="section-title text-center">TẠO TÀI KHOẢN MỚI</div>

  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-8 col-xl-6 mx-auto">
        <div class="register-card p-4">
          <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
              <label for="name" class="form-label fw-bold">Họ và tên:</label>
              <input id="name" type="text" name="name" class="form-control input-soft" value="{{ old('name') }}" required autofocus>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label fw-bold">Email:</label>
              <input id="email" type="email" name="email" class="form-control input-soft" value="{{ old('email') }}" required>
            </div>

            <div class="mb-3">
              <label for="phone" class="form-label fw-bold">Số điện thoại:</label>
              <input id="phone" type="tel" name="phone" class="form-control input-soft" required>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label fw-bold">Mật khẩu:</label>
              <input id="password" type="password" name="password" class="form-control input-soft" required>
            </div>

            <div class="mb-4">
              <label for="password_confirmation" class="form-label fw-bold">Xác nhận mật khẩu:</label>
              <input id="password_confirmation" type="password" name="password_confirmation" class="form-control input-soft" required>
            </div>

            <div class="text-center">
              <button type="submit" class="btn btn-danger px-5">Đăng ký</button>
            </div>

            <p class="text-center mt-3">
              Đã có tài khoản?
              <a href="{{ route('login') }}" class="fw-bold link-dark">Đăng nhập</a>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
