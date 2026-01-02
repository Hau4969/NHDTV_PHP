<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CINEMA')</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}">
    <style>
        /* Tổng thể */
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f9f6ef;
        }

        /* --- HEADER --- */
        header {
            background-color: #fdf9f3;
            border-bottom: 1px solid #ddd;
        }

        .top-bar {
            background-color: #f7f1e7;
            padding: 3px 15px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .top-bar .left {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .top-bar .left img {
            height: 14px;
            width: 14px;
            object-fit: contain;
            vertical-align: middle;
            margin-right: 5px;
        }

        .logo-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px 0;
        }

        .logo-bar img {
            height: 45px;
            margin-right: 10px;
        }

        .logo-bar h1 {
            color: #d82323;
            font-size: 28px;
            letter-spacing: 1px;
            margin: 0;
        }

        /* --- MENU --- */
        nav {
            background-color: #efe6d6;
            text-align: center;
            padding: 10px 0;
        }

        nav a {
            color: #000;
            text-decoration: none;
            font-size: 14px;
            margin: 0 15px;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        /* --- CONTENT --- */
        main {
            padding: 20px;
            min-height: 70vh;
        }

        /* --- FOOTER --- */
        footer {
            background-color: #ffffff;
            border-top: 1px solid #ddd;
            padding: 30px 10px;
            text-align: center;
            font-size: 13px;
            color: #000;
        }

        footer .logo-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        footer .logo-footer img {
            height: 35px;
            margin-right: 8px;
        }

        footer h3 {
            color: #d82323;
            margin: 0;
            font-size: 22px;
        }

        footer a {
            color: #000;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        .footer-links {
            margin-top: 10px;
            color: #555;
        }

        .footer-company {
            margin-top: 15px;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <header>
        <div class="top-bar">
            <!-- Bên trái: logo Facebook -->
            <div class="left">
                <a href="https://facebook.com" target="_blank" 
                style="display: flex; align-items: center; text-decoration: none; color: black; font-weight: bold;">
                    <img src="{{ asset('assets/images/Facebook.png') }}" 
                        alt="Facebook Logo" 
                        style="height: 16px; margin-right: 5px;">
                    Facebook
                </a>
            </div>

            <!-- Bên phải: các liên kết và tài khoản -->
            <div style="text-align:right; padding:5px 30px; font-size:14px;">
                @auth
                    <strong>{{ Auth::user()->name }}</strong> |
                    <a href="{{ route('profile.edit') }}">Hồ sơ</a> |
                    <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" style="background:none; border:none; color:#007bff; cursor:pointer; padding:0;">
                            Đăng xuất
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Đăng nhập</a> |
                    <a href="{{ route('register') }}">Đăng ký</a>
                @endauth
                | <a href="#">English</a>
            </div>
        </div>

        <!-- Logo chỉ còn 1 chữ CINEMA -->
        <div class="logo-bar">
            <img src="{{ asset('assets/images/logo.png') }}" alt="CINEMA Logo">
        </div>

    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <div class="logo-footer">
            <img src="{{ asset('assets/images/logo.png') }}" alt="CINEMA Logo">
        </div>

        <div class="footer-links">
            Chính Sách Khách Hàng Thường Xuyên |
            Chính Sách Bảo Mật Thông Tin |
            Điều Khoản Sử Dụng
        </div>

        <div class="footer-company">
            <b>CÔNG TY TNHH CINEMA VIỆT NAM</b><br>
            Giấy CNĐKKD: 0303675393, cấp ngày 20/05/2008, sửa đổi ngày 10/03/2018,<br>
            cấp bởi Sở KHĐT TP Hồ Chí Minh<br>
            Địa chỉ: Tầng 3, TT TM CINEMA, 69 Nguyễn Hữu Thọ, Quận 7, TP.HCM<br>
            Điện thoại: (028) 3775 2524<br>
            COPYRIGHT © CINEMA.COM - ALL RIGHTS RESERVED.
        </div>
    </footer>
</body>
</html>
