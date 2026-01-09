<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','Trang khách')</title>

  {{-- Bootstrap 5 --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- Font Awesome (icon con mắt) --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  {{-- CSS riêng --}}
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body style="background:#fff9f0;">
  <main class="py-4">
    @yield('content')
  </main>
  @include('layouts.partials.footer')
  <!-- <footer class="text-center py-4 small text-muted">
    Chính Sách Khách Hàng Thường Xuyên | Chính Sách Bảo Mật Thông Tin | Điều Khoản Sử Dụng
  </footer> -->

  {{-- JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
