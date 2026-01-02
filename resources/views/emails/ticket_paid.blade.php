@component('mail::message')
# Thanh toán thành công

Cảm ơn bạn đã thanh toán.  
**Mã đơn:** {{ $payment->reference }}  
**Tổng tiền:** {{ number_format($payment->amount,0,',','.') }} đ  
**Thời gian:** {{ $payment->paid_at?->format('H:i d/m/Y') }}

@php $first = $tickets->first(); @endphp
@if($first)
**Phim:** {{ $first->movie }}  
**Suất:** {{ \Illuminate\Support\Carbon::parse($first->start_time)->format('H:i d/m/Y') }}  
**Ghế:** {{ $tickets->map(fn($t)=> $t->row_letter.$t->seat_number)->join(', ') }}
@endif

@component('mail::button', ['url' => route('tickets.history')])
Xem vé của tôi
@endcomponent

Chúc bạn xem phim vui vẻ!
@endcomponent
