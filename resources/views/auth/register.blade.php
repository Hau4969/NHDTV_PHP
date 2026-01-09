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
                <div id="password-hint" class="form-text text-muted" style="display: none;">
                    <small class="text-danger">
                        <i class="fas fa-info-circle"></i>
                        Mật khẩu phải có ít nhất <strong>8 ký tự</strong>, bao gồm <strong>chữ hoa</strong>, <strong>chữ thường</strong>, <strong>số</strong> và <strong>ký tự đặc biệt</strong>.
                    </small>
                </div>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const hintText = document.getElementById('password-hint');
        function checkPassword(value) {
            const hasUpperCase = /[A-Z]/.test(value);
            const hasLowerCase = /[a-z]/.test(value);
            const hasNumbers = /\d/.test(value);
            const hasSpecial = /[^A-Za-z0-9]/.test(value);
            const hasLength = value.length >= 8;
            return hasUpperCase && hasLowerCase && hasNumbers && hasSpecial && hasLength;
        }

        passwordInput.addEventListener('focus', function () {
            if (!checkPassword(this.value)) {
                hintText.style.display = 'block';
            }
        });

        passwordInput.addEventListener('input', function () {
            if (checkPassword(this.value)) {
                hintText.style.display = 'none';
            } else {
                hintText.style.display = 'block';
                this.classList.remove('is-valid');
            }
        });

        passwordInput.addEventListener('blur', function () {
             hintText.style.display = 'none';
        });
    });
</script>
<div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body fs-5 text-center" id="modalMessage">
                </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary px-4" id="modalOkBtn" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var myModal = new bootstrap.Modal(document.getElementById('notificationModal'));
        var modalTitle = document.getElementById('modalTitle');
        var modalMessage = document.getElementById('modalMessage');
        var modalOkBtn = document.getElementById('modalOkBtn');

        @if ($errors->any())
            modalTitle.innerText = "Đăng ký thất bại";
            modalTitle.classList.add('text-danger');
            modalMessage.innerHTML = "<span class='text-danger'>Vui lòng kiểm tra lại thông tin nhập vào!</span>";
            myModal.show();
        @endif

        @if (session('success'))
            modalTitle.innerText = "Đăng ký thành công";
            modalTitle.classList.add('text-success');
            modalMessage.innerText = "{{ session('success') }}";

            modalOkBtn.onclick = function() {
                window.location.href = "{{ route('login') }}";
            };

            myModal.show();
        @endif
    });
</script>
@endsection
