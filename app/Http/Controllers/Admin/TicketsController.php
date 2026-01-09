<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketsController extends Controller
{
    /**
     * Danh sách đơn vé (Admin)
     */
    public function index(Request $request)
    {
        // Query từ bảng tickets
        $query = DB::table('tickets as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->join('showtimes as st', 'st.id', '=', 't.showtime_id')
            ->join('movies as m', 'm.id', '=', 'st.movie_id')
            ->join('rooms as r', 'r.id', '=', 'st.room_id')
            ->join('seats as s', 's.id', '=', 't.seat_id')

            // JOIN seat_types để biết ghế thuộc loại nào (Thường/VIP/Đôi...)
            ->leftJoin('seat_types as stt', 'stt.id', '=', 's.seat_type_id')

            // JOIN showtime_prices để lấy giá admin đã cấu hình theo showtime + seat_type
            ->leftJoin('showtime_prices as sp', function ($join) {
                $join->on('sp.showtime_id', '=', 't.showtime_id')
                     ->on('sp.seat_type_id', '=', 's.seat_type_id');
            })

            ->select(
                't.*',
                'u.name as user_name',
                'u.email as user_email',
                'm.title as movie_title',
                'r.name as room_name',
                'st.start_time',

                // Nhãn ghế A1, B12,...
                DB::raw("CONCAT(s.row_letter, s.seat_number) as seat_label"),

                // Loại ghế
                DB::raw("COALESCE(stt.name, '') as seat_type_name"),

                // Base price theo loại ghế
                DB::raw("COALESCE(stt.base_price, 0) as seat_base_price"),

                // Giá cấu hình theo suất chiếu (tuỳ DB bạn đang dùng cột nào)
                DB::raw("COALESCE(sp.price_modifier, NULL) as price_modifier"),
                DB::raw("COALESCE(sp.price, NULL) as config_price"),

                // GIÁ KỲ VỌNG (để test đúng/sai):
                // Ưu tiên: base_price + price_modifier (nếu có), fallback base_price + price (nếu dùng cột price)
                DB::raw("
                    (COALESCE(stt.base_price,0) + COALESCE(sp.price_modifier, sp.price, 0)) as expected_final_price
                ")
            );

        // --- BỘ LỌC ---

        // 1. Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('t.status', $request->status);
        }

        // 2. Lọc theo ngày chiếu
        if ($request->filled('date')) {
            $query->whereDate('st.start_time', $request->date);
        }

        // 3. Tìm kiếm (Tên khách, Email, Tên phim, Mã vé)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('u.name', 'like', "%{$q}%")
                    ->orWhere('u.email', 'like', "%{$q}%")
                    ->orWhere('m.title', 'like', "%{$q}%")
                    ->orWhere('t.qr_code', 'like', "%{$q}%");
            });
        }

        // Sắp xếp vé mới nhất lên đầu và phân trang
        $tickets = $query->orderByDesc('t.created_at')->paginate(15)->withQueryString();

        return view('admin.tickets.index', compact('tickets'));
    }

    /**
     * Hủy vé (Chuyển trạng thái sang canceled)
     */
    public function cancel($id)
    {
        DB::table('tickets')->where('id', $id)->update([
            'status' => 'canceled',
            'updated_at' => now()
        ]);

        return back()->with('success', 'Đã hủy vé thành công.');
    }

    /**
     * Hoàn tiền (Demo chức năng)
     */
    public function refund($id)
    {
        DB::table('tickets')->where('id', $id)->update([
            'status' => 'refunded',
            'updated_at' => now()
        ]);

        return back()->with('success', 'Đã xác nhận hoàn tiền.');
    }

    /**
     * Trang sửa vé (Nếu cần)
     */
    public function edit($id)
{
    $ticket = DB::table('tickets as t')
        ->join('users as u', 'u.id', '=', 't.user_id')
        ->join('showtimes as st', 'st.id', '=', 't.showtime_id')
        ->join('movies as m', 'm.id', '=', 'st.movie_id')
        ->join('rooms as r', 'r.id', '=', 'st.room_id')
        ->join('seats as s', 's.id', '=', 't.seat_id')
        ->leftJoin('seat_types as stt', 'stt.id', '=', 's.seat_type_id')
        ->leftJoin('showtime_prices as sp', function ($join) {
            $join->on('sp.showtime_id', '=', 't.showtime_id')
                 ->on('sp.seat_type_id', '=', 's.seat_type_id');
        })
        ->select(
            't.*',
            'u.name as user_name',
            'u.email as user_email',
            'm.title as movie_title',
            'r.name as room_name',
            'st.start_time',
            DB::raw("CONCAT(s.row_letter, s.seat_number) as seat_label"),
            DB::raw("COALESCE(stt.name, '') as seat_type_name"),
            DB::raw("COALESCE(stt.base_price, 0) as seat_base_price"),
            DB::raw("COALESCE(sp.price_modifier, NULL) as price_modifier"),
            DB::raw("COALESCE(sp.price, NULL) as config_price"),
            DB::raw("(COALESCE(stt.base_price,0) + COALESCE(sp.price_modifier, sp.price, 0)) as expected_final_price")
        )
        ->where('t.id', $id)
        ->first();

    if (!$ticket) {
        return back()->with('error', 'Không tìm thấy vé.');
    }

    return view('admin.tickets.edit', compact('ticket'));
}

    /**
 * Cập nhật vé (Admin) - hiện chỉ cho cập nhật trạng thái
 */
public function update(Request $request, $id)
{
    // 1) Validate trạng thái cho đồng nhất
    $data = $request->validate([
        'status' => 'required|in:reserved,paid,canceled,refunded,expired',
    ]);

    // 2) Kiểm tra vé tồn tại
    $ticket = DB::table('tickets')->where('id', $id)->first();
    if (!$ticket) {
        return back()->with('error', 'Không tìm thấy vé.');
    }

    // 3) (Tuỳ chọn) Chặn đổi trạng thái khi đã paid mà muốn quay về reserved
    // Bạn có thể chỉnh theo nghiệp vụ của bạn
    if ($ticket->status === 'paid' && in_array($data['status'], ['reserved'], true)) {
        return back()->withErrors(['error' => 'Không thể chuyển vé đã thanh toán về trạng thái Giữ chỗ.']);
    }

    // 4) Update
    DB::table('tickets')->where('id', $id)->update([
        'status'     => $data['status'],
        'updated_at' => now(),
    ]);

    return redirect()->route('admin.tickets.edit', $id)->with('success', 'Đã lưu thay đổi.');
}
}
