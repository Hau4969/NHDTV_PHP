{{-- Nền mờ khi mở --}}
<div class="drawer-backdrop"></div>

{{-- Drawer trượt từ mép trái, có chừa viền (không sát bìa) --}}
<aside class="sidebar-drawer">
  <div class="drawer-title">QUẢN TRỊ HỆ THỐNG</div>
  <nav class="drawer-menu">
    <a href="{{ route('admin.movies.index') }}"
       class="{{ request()->routeIs('admin.movies.*') ? 'active' : '' }}">Quản lý phim</a>

    <a href="{{ route('admin.showtimes.index') }}"
       class="{{ request()->routeIs('admin.showtimes.*') ? 'active' : '' }}">Quản lý suất chiếu</a>

    <a href="{{ route('admin.rooms.index') }}"
       class="{{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}">Quản lý phòng chiếu</a>

    <a href="{{ route('admin.prices.index') }}"
       class="{{ request()->routeIs('admin.prices.*') ? 'active' : '' }}">Quản lý giá vé</a>

    <a href="{{ route('admin.tickets.index') }}"
       class="{{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">Quản lý đơn vé</a>

    <a href="{{ route('admin.reports.index') }}"
       class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">Báo cáo & thống kê</a>
  </nav>
</aside>
