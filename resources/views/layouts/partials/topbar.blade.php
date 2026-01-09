<div class="topbar fixed-top">
  <div class="container-fluid d-flex align-items-center justify-content-between">

    {{-- LEFT: Facebook --}}
    <div class="d-flex align-items-center gap-3">
      <a class="fb-link d-inline-flex align-items-center text-decoration-none"
         href="https://www.facebook.com" target="_blank" rel="noopener">
        <i class="bi bi-facebook fb-icon"></i>
        <span class="fb-text">Lotte Cinema Facebook</span>
      </a>
    </div>

    {{-- RIGHT: Username + dropdown + English --}}
    <div class="d-flex align-items-center gap-3">

      {{-- Dropdown user --}}
      <div class="dropdown-admin">
        <button id="btnUserDropdown" class="role-chip">
          {{ auth()->user()->name ?? 'Tài khoản' }}
          <i class="bi bi-chevron-down ms-1"></i>
        </button>

        <ul id="userDropdownMenu" class="dropdown-menu-admin">
          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item-admin">
                <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
              </button>
            </form>
          </li>
        </ul>
      </div>

      {{-- English button --}}
      <button class="btn btn-lang" type="button">English</button>
    </div>

  </div>
</div>
