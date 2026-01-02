@extends('layouts.layoutCustomer')

@section('title', 'Thanh toán vé phim')

@section('content')
<div class="container py-4">
    <h3 class="text-center text-success mb-4">💳 XÁC NHẬN THANH TOÁN</h3>

    <div class="card shadow p-4">
        <h5 class="fw-bold mb-3">{{ $payment->booking->movie->title }}</h5>
        <p>🎟️ Ghế: {{ $payment->booking->seat->seat_number }}</p>
        <p>🕒 Giờ chiếu: {{ \Carbon\Carbon::parse($payment->booking->showtime->start_time)->format('d/m/Y H:i') }}</p>
        <p>💰 Tổng tiền: <strong>{{ number_format($payment->amount) }} VNĐ</strong></p>
        <p>📦 Trạng thái: 
            <span class="badge bg-warning">{{ ucfirst($payment->status) }}</span>
        </p>

        <div class="text-center mt-4">
            <a href="{{ route('booking.history') }}" class="btn btn-primary">
                📜 Xem lịch sử vé
            </a>
        </div>
    </div>
</div>
@endsection
