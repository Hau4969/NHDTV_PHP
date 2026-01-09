@extends('layouts.layoutCustomer')

@section('title', 'Thanh Toán Vé')

@section('content')
<div class="container py-5">
    <div class="card shadow p-4">
        <h4 class="text-center text-primary mb-4">💳 XÁC NHẬN THANH TOÁN</h4>

        {{-- Thông tin vé --}}
        <div class="mb-3">
            <p><strong>🎬 Phim:</strong> {{ $payment->booking->movie->title }}</p>
            <p><strong>💺 Ghế:</strong> {{ $payment->booking->seat->seat_number }}</p>
            <p><strong>💰 Tổng tiền:</strong> {{ number_format($payment->amount, 0, ',', '.') }} đ</p>
            <p><strong>Trạng thái:</strong> 
                <span class="badge bg-warning text-dark">{{ ucfirst($payment->status) }}</span>
            </p>
        </div>

        {{-- Form chọn phương thức thanh toán --}}
        <form method="POST" action="{{ route('payment.complete', $payment->id) }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-bold">Phương thức thanh toán:</label>
                <select name="payment_method" class="form-select" required>
                    <option value="COD">💵 Thanh toán khi nhận vé (COD)</option>
                    <option value="ATM">🏦 Chuyển khoản ngân hàng</option>
                    <option value="MoMo">📱 Ví MoMo</option>
                    <option value="ZaloPay">💸 ZaloPay</option>
                </select>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-success px-4">✅ Thanh toán</button>
                <a href="{{ route('user.dashboard') }}" class="btn btn-secondary ms-2">⬅ Quay lại</a>
            </div>
        </form>
    </div>
</div>
@endsection
