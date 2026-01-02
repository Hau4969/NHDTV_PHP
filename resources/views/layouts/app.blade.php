<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title','LCinema')</title>

  {{-- Bootstrap & Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  {{-- CSS app --}}
  <link rel="stylesheet" href="{{ asset('css/cinema.css') }}">

  {{-- Styles bổ sung từ view con --}}
  @stack('styles')
</head>

<body class="has-fixed-top">
  {{-- TOPBAR CỐ ĐỊNH --}}
  @include('layouts.partials.topbar')

  {{-- LOGO TO GIỮA --}}
  @include('layouts.partials.masthead')

  {{-- THANH MENU THEO VAI TRÒ --}}
  @auth
    @php
      $u = auth()->user();
      $isAdmin = (optional($u->role)->name === 'Admin') || (($u->role_id ?? 0) === 1);
    @endphp

    @if($isAdmin)
      @include('layouts.partials.page_toolbarAdmin')
    @else
      @include('layouts.partials.page_toolbarCustomer')
    @endif
  @else
    {{-- Khách chưa đăng nhập: toolbar công khai (nếu có) --}}
    @includeIf('layouts.partials.public-nav')
  @endauth

  {{-- NỘI DUNG CHÍNH --}}
  <div class="app-wrap d-flex">
    {{-- SIDEBAR (nếu view con khai báo @section('sidebar')) --}}
    @hasSection('sidebar')
      @yield('sidebar')
    @endif

    <main class="content flex-grow-1">
      @yield('content')
    </main>
  </div>

  {{-- FOOTER --}}
  @include('layouts.partials.footer')

  {{-- Script cho topbar & nav --}}
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    // Toggle dropdown user
    const btn = document.getElementById('btnUserDropdown');
    const menu = document.getElementById('userDropdownMenu');
    if (btn && menu) {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('show');
      });
      document.addEventListener('click', (e) => {
        if (!menu.contains(e.target) && !btn.contains(e.target)) {
          menu.classList.remove('show');
        }
      });
    }

    // Toggle nav mobile (Admin)
    const btnAdmin = document.getElementById('btnAdminNav');
    const adminMenu = document.querySelector('.admin-nav .admin-menu');
    if (btnAdmin && adminMenu) {
      btnAdmin.addEventListener('click', () => {
        adminMenu.classList.toggle('d-none');
      });
    }

    // Toggle nav mobile (Customer)
    const btnCus = document.getElementById('btnCustomerNav');
    const cusMenu = document.querySelector('.customer-nav .customer-menu');
    if (btnCus && cusMenu) {
      btnCus.addEventListener('click', () => {
        cusMenu.classList.toggle('d-none');
      });
    }
  });
  </script>

  {{-- Bootstrap JS --}}
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
          integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
          crossorigin="anonymous"></script>
      @auth
        @php
          $u = auth()->user();
          $isAdmin = (optional($u->role)->name === 'Admin') || (($u->role_id ?? 0) === 1);
        @endphp
        @if(!$isAdmin)
          @include('layouts.partials.ai_movie_widget')
        @endif
      @endauth


  {{-- Scripts bổ sung từ view con --}}
  @stack('scripts')
</body>
</html>
