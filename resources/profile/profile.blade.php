@extends('layouts.app')
@section('title','Thông tin')

@section('content')
<div class="container" style="max-width:980px;">
  <h5 class="text-uppercase fw-bold text-muted mb-2">THÔNG TIN</h5>

  <div class="profile-header">
    <div class="profile-avatar"></div>
  </div>

  <div class="profile-box">
    <div>Họ và tên: {{ auth()->user()->name }}</div>
      <div>Ngày sinh: {{ auth()->user()->birthday?->format('d/m/Y') }}</div>
      <div>Số điện thoại: {{ auth()->user()->phone }}</div>
      <div>Email: {{ auth()->user()->email }}</div>
      <div>Địa chỉ: {{ auth()->user()->address }}</div>
  </div>
</div>
@endsection
