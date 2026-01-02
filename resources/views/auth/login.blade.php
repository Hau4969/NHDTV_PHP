@extends('layouts.guest')
@section('title','Đăng nhập')

@section('content')
<div class="login-page">
    <!-- ====== HEADER CINEMA ====== -->
  <div class="navbar-top">
    <div class="nav-left">
      <a href="https://facebook.com" target="_blank">
        <img src="https://upload.wikimedia.org/wikipedia/commons/0/05/Facebook_Logo_%282019%29.png" alt="Facebook">
        Lotte Cinema Facebook
      </a>
    </div>
  
    <div class="nav-right">
      <a href="#">Thẻ thành viên</a>
      <a href="#">Hỗ trợ khách hàng</a>
      <button class="lang-btn">English</button>
    </div>
  </div>


  <!-- ====== END HEADER ====== -->

  {{-- Logo --}}
  <div class="text-center mb-3">
    <img src="{{ asset('assets/images/logo.png') }}" 
         alt="Logo Cinema" 
         style="height: 80px; object-fit: contain;">
  </div>


  {{-- Tiêu đề dải ngang --}}
  <div class="section-title text-center">ĐĂNG NHẬP</div>

  <div class="container py-4">
    <div class="row justify-content-center">
      {{-- Khung tự co: mobile → md(10/12) → lg(8/12) → xl(6/12) --}}
      <div class="col-12 col-md-10 col-lg-8 col-xl-6 mx-auto">
        <div class="login-card p-3 p-md-4 p-lg-5">
          {{-- Thông báo lỗi --}}
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $e)
                  <li>{{ $e }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Tên đăng nhập --}}
            <div class="row align-items-center mb-4">
              <label class="col-12 col-md-4 col-form-label fw-bold mb-2 mb-md-0 text-md-end pe-md-3">                Tên đăng nhập:
              </label>
              <div class="col-12 col-md-8">
                <input id="email" name="email" type="text"
                       class="form-control input-soft"
                       placeholder="Email hoặc số điện thoại"
                       value="{{ old('email') }}" required autofocus>
              </div>
            </div>

            {{-- Mật khẩu --}}
            <div class="row align-items-center mb-4">
              <label class="col-12 col-md-4 col-form-label fw-bold mb-2 mb-md-0 text-md-end pe-md-3">                Mật khẩu:
              </label>
              <div class="col-12 col-md-8">
                <div class="input-group">
                  <input id="password" name="password" type="password"
                         class="form-control input-soft" required>
                  <button class="btn btn-outline-secondary" type="button" id="togglePass">
                    <i class="fa-solid fa-eye"></i>
                  </button>
                </div>
              </div>
            </div>

            {{-- Ghi nhớ --}}
            <div class="row mb-4">
              <div class="col-12 col-md-8 offset-md-4">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="1" id="remember_me" name="remember">
                  <label class="form-check-label fw-semibold" for="remember_me">Ghi nhớ tôi</label>
                </div>
              </div>
            </div>

            {{-- Nút --}}
            <div class="text-center mt-3">
              <button type="submit" class="btn btn-danger px-5">Đăng nhập</button>
            </div>

            {{-- Link đăng ký --}}
            <p class="text-center mt-3">
              Chưa có tài khoản?
              <a href="{{ route('register') }}" class="fw-bold link-dark">Đăng ký</a>
            </p>            
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
