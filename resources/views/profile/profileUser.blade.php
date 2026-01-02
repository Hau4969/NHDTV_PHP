@extends('layouts.layoutCustomer')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="container mt-4">
    <h3 class="text-danger text-center mb-4">Hồ sơ cá nhân</h3>
    <div class="card shadow p-4">
        <p><strong>Họ tên:</strong> {{ Auth::user()->name }}</p>
        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
        <p><strong>Số điện thoại:</strong> {{ Auth::user()->phone }}</p>
        <p><strong>Địa chỉ:</strong> {{ Auth::user()->address ?? 'Chưa cập nhật' }}</p>
        <p><strong>Ngày sinh:</strong> {{ Auth::user()->birthday ?? 'Chưa cập nhật' }}</p>
    </div>
</div>
@endsection
