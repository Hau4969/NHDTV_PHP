<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    {{-- Header --}}
    <header class="bg-dark text-white py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <h4 class="mb-0">🎥 Rạp Chiếu Phim</h4>
            <nav class="d-flex align-items-center">
                <a href="{{ route('user.dashboard') }}" class="text-white me-3 text-decoration-none">Phim</a>
                <a href="#" class="text-white me-3 text-decoration-none">Vé của tôi</a>

                @auth
                    <div class="dropdown">
                        <a class="text-white text-decoration-none dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            👤 {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.profileUser') }}">Hồ sơ cá nhân</a></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">Đăng xuất</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-white text-decoration-none">Đăng nhập</a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Nội dung chính --}}
    <main class="container my-4">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-light text-center py-3 border-top">
        <small class="text-muted">© 2025 - Rạp Chiếu Phim Online | Thiết kế bởi Luân-San 😊❤️</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/resetIframe.js') }}"></script>
</body>
</html>
