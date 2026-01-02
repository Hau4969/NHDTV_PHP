<nav class="admin-nav">
  <div class="container d-flex align-items-center">

    <button id="btnAdminNav" class="btn-hamburger d-md-none me-2" aria-label="Menu">
      <i class="bi bi-list" style="font-size:18px"></i>
    </button>

    <ul class="admin-menu">
      <li class="{{ request()->routeIs('admin.movies.*') ? 'active' : '' }}">
        <a href="{{ route('admin.movies.index') }}">Quản lý phim</a>
      </li>
      <li class="{{ request()->routeIs('admin.showtimes.*') ? 'active' : '' }}">
        <a href="{{ route('admin.showtimes.index') }}">Quản lý suất chiếu</a>
      </li>
      <li class="{{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}">
        <a href="{{ route('admin.rooms.index') }}">Quản lý phòng chiếu</a>
      </li>
      <li class="{{ request()->routeIs('admin.prices.*') ? 'active' : '' }}">
        <a href="{{ route('admin.prices.index') }}">Quản lý giá vé</a>
      </li>
      <li class="{{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
        <a href="{{ route('admin.tickets.index') }}">Quản lý đơn vé</a>
      </li>
      <li class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
        <a href="{{ route('admin.reports.index') }}">Báo cáo & thống kê</a>
      </li>
      <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <a href="{{ route('admin.users.index') }}">Quản lý tài khoản</a>
      </li>
    </ul>
  </div>
</nav>