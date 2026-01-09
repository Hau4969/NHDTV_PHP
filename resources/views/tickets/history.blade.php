@extends('layouts.app')
@section('title','Lịch sử vé')

@section('content')
<div class="container py-3">
  <h4 class="fw-bold mb-3">Lịch sử vé</h4>

  @if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
  @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

  <div class="card">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Phim</th>
            <th>Suất</th>
            <th>Phòng</th>
            <th>Ghế</th>
            <th>Trạng thái</th>
            <th>Giá</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        @forelse($tickets as $i => $t)
          <tr>
            <td>{{ $tickets->firstItem() + $i }}</td>
            <td class="fw-semibold">{{ $t->movie_title }}</td>
            <td>{{ \Illuminate\Support\Carbon::parse($t->start_time)->format('H:i d/m/Y') }}</td>
            <td>{{ $t->room_name }}</td>
            <td>{{ $t->seat_label }}</td>
            <td>
              <span class="badge 
                @if($t->status==='paid') bg-success 
                @elseif($t->status==='pending') bg-warning text-dark 
                @elseif($t->status==='used') bg-primary 
                @elseif($t->status==='canceled') bg-secondary 
                @else bg-info @endif">
                {{ strtoupper($t->status) }}
              </span>
            </td>
            <td>{{ number_format($t->final_price,0,',','.') }} đ</td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="{{ route('payments.show', $t->id) }}">Chi tiết/Thanh toán</a>
            </td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted py-4">Chưa có vé nào.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
    @if($tickets->hasPages())
      <div class="card-footer">{{ $tickets->links() }}</div>
    @endif
  </div>
</div>
@endsection
