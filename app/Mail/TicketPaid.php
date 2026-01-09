<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class TicketPaid extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function build()
    {
        $tickets = DB::table('tickets as t')
            ->join('seats as s','s.id','=','t.seat_id')
            ->join('showtimes as st','st.id','=','t.showtime_id')
            ->join('movies as m','m.id','=','st.movie_id')
            ->select('m.title as movie','st.start_time','s.row_letter','s.seat_number','t.final_price')
            ->where('t.user_id', $this->payment->user_id)
            ->where('t.showtime_id', $this->payment->showtime_id)
            ->where('t.status','paid')
            ->get();

        return $this->subject('Xác nhận thanh toán vé thành công')
            ->markdown('emails.ticket_paid', [
                'payment' => $this->payment,
                'tickets' => $tickets,
            ]);
    }
}
