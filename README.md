# 🎬 HỆ THỐNG QUẢN LÝ VÉ XEM PHIM (QL_Cinema)

## 🧾 Thông tin chung
**Tên đề tài:** Hệ thống quản lý vé xem phim  
**Môn học:** Lập trình PHP
**Thành viên nhóm:** Nhóm NHDTV
**Giảng viên hướng dẫn:** GV. Nguyễn Quốc Trung

### 🔧 Công nghệ sử dụng
- **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5, Blade Template  
- **Backend:** PHP 8.2.12 (Laravel Framework 12.34.0)  
- **Database:** MySQL  
- **Công cụ hỗ trợ:** Composer, Git, GitHub, XAMPP  

---

## 🎯 Mục tiêu đề tài
Xây dựng một website giúp khách hàng:
- Tra cứu thông tin phim (tên, thể loại, thời lượng, ngày chiếu, trailer)
- Chọn suất chiếu, ghế và **đặt vé trực tuyến**
- Thanh toán qua **mã QR mô phỏng** và nhận vé điện tử  

Đồng thời, hệ thống hỗ trợ **Admin** quản lý:
- Phim, phòng chiếu, suất chiếu  
- Giá vé, loại ghế, voucher  
- Người dùng và thống kê doanh thu

---

## ⚙️ Các chức năng chính

### 👤 Khách hàng (Customer)
- Đăng ký, đăng nhập, đăng xuất  
- Xem danh sách phim đang chiếu và sắp chiếu  
- Chọn suất chiếu → chọn ghế → đặt vé  
- Thanh toán bằng QR code (mô phỏng)  
- Xem lịch sử đặt vé và chi tiết vé  

### 🧑‍💼 Quản trị viên (Admin)
- Đăng nhập vào giao diện quản trị  
- Quản lý phim (CRUD)  
- Quản lý suất chiếu, phòng chiếu, loại ghế, giá vé  
- Quản lý voucher khuyến mãi  
- Quản lý người dùng (phân quyền, khóa/mở tài khoản)  
- Xem **thống kê doanh thu**, tổng số vé bán ra  

---

## 🧰 Hướng dẫn cài đặt môi trường

### 1️⃣ Clone project về máy
```
git clone https://github.com/Hau4969/NHDTV_PHP
cd Quan-ly-ve-xem-phim
```
### 2️⃣ Cài đặt package
```
composer install
```
### 3️⃣ Cấu hình file môi trường
```
cp .env.example .env
php artisan key:generate
```
// Mở file .env và chỉnh sửa phần database:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ql_cinema
DB_USERNAME=root
DB_PASSWORD=

### 4️⃣ Tạo cơ sở dữ liệu
Truy cập: http://localhost/phpmyadmin  
Tạo database mới tên ql_cinema (collation: utf8mb4_unicode_ci)

### 5️⃣ Chạy migrate và tạo storage link
```
php artisan migrate
php artisan storage:link
```
### 6️⃣ Tạo tài khoản Admin bằng Tinker
```
php artisan tinker

Nhập:
\App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('123456'),
    'role_id' => 1,
]);
```
### 7️⃣ Chạy server
```
php artisan serve
```
Truy cập: http://127.0.0.1:8000  
Đăng nhập bằng tài khoản Admin:  
Email: admin@example.com  
Mật khẩu: 123456

---

## 📊 Cấu trúc thư mục Laravel
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/
.env.example
composer.json
artisan
README.md

---

## 💡 Kết luận và Hướng phát triển

### Kết luận
Hệ thống đã hoàn thiện các chức năng cơ bản của một web đặt vé phim trực tuyến, đảm bảo quy trình **đặt vé – thanh toán – lưu trữ vé** hoạt động ổn định.  
Ứng dụng áp dụng thành công mô hình **MVC trong Laravel**, sử dụng cơ chế **transaction và lockForUpdate** để đảm bảo tính toàn vẹn dữ liệu khi xử lý thanh toán.

### Hạn chế
- Thanh toán mới ở mức mô phỏng (chưa kết nối ví điện tử thật)  
- Chưa hiển thị thống kê dạng biểu đồ hoặc dashboard trực quan  

### Hướng phát triển
- Tích hợp cổng thanh toán thực tế (Momo, VNPAY, PayOS)  
- Gửi email xác nhận vé, mã QR tự động sau thanh toán  
- Xây dựng module báo cáo doanh thu bằng biểu đồ  
- Cung cấp REST API phục vụ mobile app hoặc client khác  
- Triển khai hệ thống lên server thực (Render / Hostinger / AWS)

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
