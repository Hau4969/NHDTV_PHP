<div class="section-head">
  <button id="btnDrawer" class="btn-hamburger" aria-label="Mở menu">
    <i class="bi bi-list" style="font-size:18px"></i>
  </button>

  <h2 class="section-title-center">
    {{ $title ?? (trim($__env->yieldContent('page_title')) ?: 'TIÊU ĐỀ TRANG') }}
  </h2>

  <span class="head-underline"></span>
</div>
