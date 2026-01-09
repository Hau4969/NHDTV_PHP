@extends('layouts.layoutCustomer')

@section('title', 'Đặt Vé Xem Phim')

@section('content')

<div class="container py-4">
    <h4 class="text-primary fw-bold mb-4 text-center">
        🎟️ ĐẶT VÉ - {{ $movie->title }}
    </h4>

    {{-- Thông tin phim --}}
    <div class="text-center mb-4">
        <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" 
             class="rounded shadow" style="max-width: 250px; height: auto;">
        <p class="mt-2 mb-0 fw-bold">{{ $movie->title }}</p>
        <small>{{ $movie->duration }} phút | {{ $movie->genre }}</small>
    </div>

    {{-- Form đặt vé --}}
    <form method="POST" action="{{ route('booking.store', $movie->id) }}">
        @csrf

        {{-- Lịch chiếu --}}
        <div class="text-center mb-4">
            <h5>🕒 Chọn suất chiếu</h5>
            <div class="d-flex flex-wrap justify-content-center gap-2">
                @foreach ($showtimes as $show)
                    <label class="btn btn-outline-dark">
                        <input type="radio" name="showtime_id" value="{{ $show->id }}" required>
                        {{ \Carbon\Carbon::parse($show->start_time)->format('d/m/Y H:i') }}
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Sơ đồ ghế --}}
        <div class="seat-container text-center mb-4">
            <h5 class="fw-bold mb-3">💺 Chọn ghế</h5>

            @foreach ($seats->groupBy('row_letter') as $row => $rowSeats)
                <div class="d-flex justify-content-center align-items-center mb-2">
                    <span class="me-2 fw-bold">{{ $row }}</span>
                    @foreach ($rowSeats as $seat)
                        <label class="seat {{ isset($seat->status) && $seat->status === 'booked' ? 'booked' : '' }}">
                            <input type="checkbox" name="seat_ids[]" 
                                   value="{{ $seat->id }}" 
                                   {{ isset($seat->status) && $seat->status === 'booked' ? 'disabled' : '' }}>
                            <span>{{ $seat->seat_number }}</span>
                        </label>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Tổng tiền hiển thị động --}}
        <div class="text-center mb-3">
            <h5>💰 Tổng tiền: <span id="totalPrice" class="text-success fw-bold">0</span> VNĐ</h5>
        </div>

        {{-- Nút xác nhận --}}
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary px-4">
                ✅ Xác nhận & Thanh toán
            </button>
        </div>
    </form>
</div>

{{-- CSS nội bộ --}}
<style>
.seat input { display: none; }
.seat span {
    display: inline-block;
    width: 28px; height: 28px;
    line-height: 28px;
    text-align: center;
    border-radius: 4px;
    background-color: #f8f9fa;
    border: 1px solid #ccc;
    margin: 3px;
    cursor: pointer;
}
.seat input:checked + span {
    background-color: #198754;
    color: white;
}
.seat.booked span {
    background-color: #dc3545;
    color: white;
    cursor: not-allowed;
}
</style>

{{-- Script tính tổng tiền --}}
<script>
const pricePerSeat = 75000; // 💰 mỗi ghế 75k
const checkboxes = document.querySelectorAll('input[name="seat_ids[]"]');
const totalPrice = document.getElementById('totalPrice');

checkboxes.forEach(chk => {
    chk.addEventListener('change', () => {
        const selected = document.querySelectorAll('input[name="seat_ids[]"]:checked').length;
        totalPrice.textContent = (selected * pricePerSeat).toLocaleString();
    });
});
</script>

@endsection
