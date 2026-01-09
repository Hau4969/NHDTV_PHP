@extends('layouts.layoutCustomer')

@section('title', 'Lịch sử đặt vé')

@section('content')
<div class="container py-4">
    <h3 class="text-center fw-bold text-primary mb-4">🎞️ LỊCH SỬ ĐẶT VÉ</h3>

    @if ($bookings->isEmpty())
        <div class="alert alert-info text-center">
            Bạn chưa có vé nào được đặt 🎟️
        </div>
    @else
        <table class="table table-bordered table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Tên phim</th>
                    <th>Suất chiếu</th>
                    <th>Ghế</th>
                    <th>Trạng thái</th>
                    <th>Thanh toán</th>
                    <th>Ngày đặt</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $index => $booking)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $booking->movie->title }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($booking->showtime->show_date)->format('d/m/Y') }}
                            - {{ $booking->showtime->show_time }}
                        </td>
                        <td>{{ $booking->seat->seat_number }}</td>
                        <td>
                            @if ($booking->status === 'booked')
                                <span class="badge bg-success">Đã đặt</span>
                            @else
                                <span class="badge bg-secondary">{{ $booking->status }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($booking->payment)
                                @if ($booking->payment->status === 'completed')
                                    <span class="badge bg-success">Đã thanh toán</span>
                                @else
                                    <span class="badge bg-warning text-dark">Chờ thanh toán</span>
                                @endif
                            @else
                                <a href="{{ route('payment.show', $booking->id) }}" class="btn btn-sm btn-primary">
                                    💳 Thanh toán
                                </a>
                            @endif
                        </td>
                        <td>{{ $booking->created_at->format('H:i d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
