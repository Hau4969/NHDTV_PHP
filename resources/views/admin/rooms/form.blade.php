@extends('layouts.app')
@section('title', ($mode==='edit'?'Sửa':'Thêm').' Phòng chiếu')

@section('content')
<div class="container py-3">
  @if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
  @endif

  <div class="card shadow-sm">
    <div class="card-body">
      <form method="POST" action="{{ $mode==='edit' ? route('admin.rooms.update',$room) : route('admin.rooms.store') }}">
        @csrf
        @if($mode==='edit') @method('PUT') @endif

        <div class="mb-3">
          <label class="form-label">Tên phòng</label>
          <input type="text" name="name" class="form-control" required maxlength="100"
                 value="{{ old('name',$room->name) }}">
          @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label">Sức chứa</label>
          <input type="number" name="capacity" class="form-control" required min="1" max="1000"
                 value="{{ old('capacity',$room->capacity ?: 40) }}">
          @error('capacity') <div class="text-danger small">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex gap-2">
          <button class="btn btn-primary">Lưu</button>
          <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary">Hủy</a>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
