<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\Payment;

class PaymentController extends Controller
{
    /** Trang hiển thị QR & vé (theo payment) */
    public function show(Payment $payment)
    {
        // Chỉ chủ sở hữu được xem
        if ($payment->user_id !== Auth::id()) {
            abort(403);
        }

        // Bảo đảm có reference (nếu vì lý do nào đó vẫn null -> phát sinh tạm và lưu)
        if (empty($payment->reference)) {
            $tempRef = 'PM' . now()->format('ymdHis') . $payment->user_id . str_pad((string)$payment->id, 6, '0', STR_PAD_LEFT);
            DB::table('payments')->where('id', $payment->id)->update([
                'reference'  => $tempRef,
                'updated_at' => now(),
            ]);
            $payment->reference = $tempRef;
        }

        // Load thông tin vé liên quan (payment per ticket)
        $tickets = DB::table('tickets as t')
            ->join('seats as s','s.id','=','t.seat_id')
            ->join('rooms as r','r.id','=','s.room_id')
            ->join('showtimes as st','st.id','=','t.showtime_id')
            ->join('movies as m','m.id','=','st.movie_id')
            ->where('t.id', $payment->ticket_id)
            ->where('t.user_id', $payment->user_id)
            ->select('t.id','t.price','t.final_price','t.status',
                     's.row_letter','s.seat_number',
                     'r.name as room_name',
                     'st.start_time',
                     'm.title as movie_title')
            ->get();

        // Ép kiểu thời gian cho view
        $payment->expires_at = $payment->expires_at ? Carbon::parse($payment->expires_at) : null;

        return view('payments.show', compact('payment','tickets'));
    }

    /** Xác nhận đã thanh toán (demo) */
    public function confirm(Payment $payment)
{
    if (now()->greaterThan($payment->expires_at)) {
        return back()->withErrors('Giao dịch đã hết hạn.');
    }

    DB::transaction(function () use ($payment) {
        // payments: dùng 'completed' để khớp ENUM hiện tại
        DB::table('payments')->where('id', $payment->id)->update([
            'status'     => 'completed',
            'paid_at'    => now(),
            'updated_at' => now(),
        ]);

        // tickets: vé vẫn có thể dùng 'paid' như bạn đang làm
        DB::table('tickets')->where('id', $payment->ticket_id)->update([
            'status'     => 'paid',
            'updated_at' => now(),
        ]);
    });

    return redirect()->route('tickets.history')->with('ok','Thanh toán thành công!');
}

    /** Hủy payment (auto hoặc user bấm Hủy) */
    public function cancel(Payment $payment)
    {
        if ($payment->user_id !== Auth::id()) abort(403);

        if ($payment->status !== 'pending') {
            return back()->withErrors('Giao dịch không ở trạng thái chờ.');
        }

        DB::transaction(function () use ($payment) {
            // Hủy payment
            DB::table('payments')->where('id', $payment->id)->update([
                'status'     => 'failed',
                'updated_at' => now(),
            ]);

            // Trả ghế (đổi ticket -> canceled nếu còn reserved)
            DB::table('tickets')->where('id', $payment->ticket_id)
                ->where('status','reserved')
                ->update([
                    'status'     => 'canceled',
                    'updated_at' => now(),
                ]);
        });

        return redirect()->route('tickets.history')->with('ok','Đã hủy giao dịch và trả ghế.');
    }
}
