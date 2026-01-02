@extends('layouts.app')

@section('content')
<div class="container py-4">
  <h3>{{ $movie->title }}</h3>
  <p>Thể loại: {{ $movie->genre }}</p>
  <p>Thời lượng: {{ $movie->duration_min }} phút</p>
  <p>Ngày chiếu: {{ $movie->release_date }}</p>
  @if($movie->trailer_url)
    <p><a href="{{ $movie->trailer_url }}" target="_blank">Xem trailer</a></p>
  @endif
  @if($movie->poster_url)
    <img src="{{ $movie->poster_url }}" alt="poster" style="max-width:240px">
  @endif
</div>
@endsection
