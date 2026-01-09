@extends('layouts.app')
@section('title','Quản lý Phòng chiếu')

@section('content')
<div class="container py-3">
  {{-- Header --}}
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold mb-0">Quản lý Phòng chiếu</h5>
    <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle"></i> Thêm phòng
    </a>
  </div>

  @if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
  @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="width:70px">#</th>
              <th>Tên phòng</th>
              <th style="width:120px">Sức chứa</th>
              <th style="width:160px">Số ghế hiện có</th>
              <th style="width:520px">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            @forelse($rooms as $r)
              @php
                $currentSeats = $r->seats_count ?? 0;
                $overflow = $currentSeats > (int)$r->capacity;
              @endphp
              <tr>
                <td>{{ $r->id }}</td>
                <td class="fw-semibold">{{ $r->name }}</td>
                <td>{{ $r->capacity }}</td>
                <td>
                  @if($overflow)
                    <span class="badge text-bg-danger">{{ $currentSeats }}</span>
                    <small class="text-danger">> {{ $r->capacity }}</small>
                  @else
                    {{ $currentSeats }}
                  @endif
                </td>
                <td>
                  <div class="d-inline-flex align-items-center gap-1 flex-wrap">
                    {{-- Nút Sửa --}}
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.rooms.edit',$r) }}">
                      <i class="bi bi-pencil-square"></i> Sửa
                    </a>
                    {{-- Nút Xóa --}}
                    <button type="button" class="btn btn-outline-danger btn-sm"
                            title="Xoá phòng"
                            data-bs-toggle="modal"
                            data-bs-target="#deleteRoomModal"
                            data-url="{{ route('admin.rooms.destroy', $r) }}"
                            data-name="{{ $r->name }}"
                            data-capacity="{{ $r->capacity }}"
                            data-seats="{{ $currentSeats }}">
                      <i class="bi bi-trash"></i> Xoá
                    </button>

                    {{-- Nút TẠO GHẾ (Truyền max-capacity) --}}
                    <button type="button" class="btn btn-outline-primary btn-sm"
                            title="Cấu hình ghế"
                            data-bs-toggle="modal"
                            data-bs-target="#generateSeatsModal"
                            data-url="{{ route('admin.rooms.generateSeats', $r) }}"
                            data-name="{{ $r->name }}"
                            data-current="{{ $currentSeats }}"
                            data-max-capacity="{{ $r->capacity }}">
                      <i class="bi bi-grid-3x3-gap"></i> Tạo ghế
                    </button>

                    {{-- Form Cắt ghế dư --}}
                    @if($overflow)
                      <form method="POST" action="{{ route('admin.rooms.trimSeats',$r) }}"
                            onsubmit="return confirm('Cắt bớt ghế dư?');">
                        @csrf
                        <button class="btn btn-warning btn-sm">
                          <i class="bi bi-scissors"></i> Cắt bớt
                        </button>
                      </form>
                    @endif
                  </div>
                </td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center py-4 text-muted">Chưa có phòng nào.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if ($rooms->hasPages())
      <div class="card-footer bg-white">
        {{ $rooms->links() }}
      </div>
    @endif
  </div>
</div>

{{-- === MODAL 1: CẤU HÌNH TẠO GHẾ === --}}
<div class="modal fade" id="generateSeatsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form id="generateSeatsForm" method="POST" action="">
        @csrf

        <div class="modal-header bg-primary text-white py-2">
          <h5 class="modal-title fw-bold">Thiết lập sơ đồ: <span id="gen-room-name"></span></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-3">

          <div class="alert alert-info py-2 px-3 mb-3 small bg-light border-start border-4 border-info">
            <div class="d-flex justify-content-between align-items-center">
                <div><i class="bi bi-info-circle-fill text-info"></i> Ghế hiện có: <strong id="gen-current-seats">0</strong></div>
                <div class="text-danger fw-bold text-uppercase" style="font-size: 0.9rem;">
                    Max Sức chứa: <span id="gen-max-capacity" class="fs-6">0</span>
                </div>
                <div>Dự kiến tạo: <strong id="totalCreatedDisplay" class="text-primary fs-6">0</strong> ghế</div>
            </div>
          </div>

          <h6 class="fw-bold text-primary mb-2 small text-uppercase bg-light p-1 ps-2 rounded">
              1. Kích thước lưới
          </h6>
          <div class="row g-2 mb-3 ps-1">
            <div class="col-md-6">
              <div class="input-group input-group-sm">
                <span class="input-group-text fw-bold bg-white">Số hàng (A-Z)</span>
                <input type="number" name="rows" id="inpRows" class="form-control text-center fw-bold text-primary" min="1" max="26" placeholder="Nhập số hàng..." required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="input-group input-group-sm">
                <span class="input-group-text fw-bold bg-white">Số cột (Ghế/Hàng)</span>
                <input type="number" name="cols" id="inpCols" class="form-control text-center fw-bold text-primary" min="1" max="50" placeholder="Nhập số cột..." required>
              </div>
            </div>
          </div>

          <h6 class="fw-bold text-danger mb-2 small text-uppercase bg-light p-1 ps-2 rounded">
              2. Chi tiết loại ghế
          </h6>

          <div class="row g-0 mb-1 text-muted fw-bold" style="font-size: 0.85rem;">
              <div class="col-3 ps-4">Loại ghế</div>
              <div class="col-5 ps-3">Phạm vi (Hàng số)</div>
              <div class="col-4 text-center">Thống kê dự kiến</div>
          </div>

          {{-- A. Ghế Thường --}}
          <div class="row g-0 align-items-center mb-2 p-2 border rounded shadow-sm">
              <div class="col-3">
                  <div class="form-check">
                    <input class="form-check-input checkbox-toggle" type="checkbox" id="checkNormal" checked data-target="boxNormal">
                    <label class="form-check-label fw-bold small ms-1" for="checkNormal">Ghế Thường</label>
                  </div>
              </div>
              <div class="col-5 px-1">
                  <div id="boxNormal" class="input-group input-group-sm transition-opacity">
                    <span class="input-group-text text-muted">Từ</span>
                    <input type="number" name="normal_from" id="nFrom" class="form-control text-center calc-input fw-bold" min="0" placeholder="0">
                    <span class="input-group-text text-muted">Đến</span>
                    <input type="number" name="normal_to" id="nTo" class="form-control text-center calc-input fw-bold" min="0" placeholder="0">
                  </div>
              </div>
              <div class="col-4 text-center">
                  <span class="badge bg-light text-dark border p-2 w-100 fw-normal" id="statNormal">
                      0 hàng x 0 = <strong>0</strong> ghế
                  </span>
              </div>
          </div>

          {{-- B. Ghế VIP --}}
          <div class="row g-0 align-items-center mb-2 p-2 border rounded shadow-sm">
              <div class="col-3">
                  <div class="form-check">
                    <input class="form-check-input checkbox-toggle" type="checkbox" id="checkVip" checked data-target="boxVip">
                    <label class="form-check-label fw-bold small text-warning ms-1" for="checkVip">Ghế VIP</label>
                  </div>
              </div>
              <div class="col-5 px-1">
                  <div id="boxVip" class="input-group input-group-sm transition-opacity">
                    <span class="input-group-text text-muted">Từ</span>
                    <input type="number" name="vip_from" id="vFrom" class="form-control text-center calc-input fw-bold" min="0" placeholder="0">
                    <span class="input-group-text text-muted">Đến</span>
                    <input type="number" name="vip_to" id="vTo" class="form-control text-center calc-input fw-bold" min="0" placeholder="0">
                  </div>
              </div>
              <div class="col-4 text-center">
                  <span class="badge bg-light text-dark border p-2 w-100 fw-normal" id="statVip">
                      0 hàng x 0 = <strong>0</strong> ghế
                  </span>
              </div>
          </div>

          {{-- C. Ghế Đôi --}}
          <div class="row g-0 align-items-center mb-3 p-2 border rounded shadow-sm">
              <div class="col-3">
                  <div class="form-check">
                    <input class="form-check-input checkbox-toggle" type="checkbox" id="checkCouple" data-target="boxCouple">
                    <label class="form-check-label fw-bold small text-danger ms-1" for="checkCouple">Ghế Đôi</label>
                  </div>
              </div>
              <div class="col-5 px-1">
                  <div id="boxCouple" class="input-group input-group-sm transition-opacity" style="opacity: 0.4;">
                    <span class="input-group-text text-muted">Từ</span>
                    <input type="number" name="couple_from" id="cFrom" class="form-control text-center calc-input fw-bold" min="0" placeholder="0" disabled>
                    <span class="input-group-text text-muted">Đến</span>
                    <input type="number" name="couple_to" id="cTo" class="form-control text-center calc-input fw-bold" min="0" placeholder="0" disabled>
                  </div>
              </div>
              <div class="col-4 text-center">
                  <span class="badge bg-light text-dark border p-2 w-100 fw-normal" id="statCouple">
                      0 hàng x 0 = <strong>0</strong> ghế
                  </span>
              </div>
          </div>

          <hr class="my-2">

          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="reset" value="1" id="checkReset" checked>
            <label class="form-check-label text-danger fw-bold small ms-1" for="checkReset">
              <i class="bi bi-trash"></i> Xóa hết ghế cũ và tạo lại mới (Reset)
            </label>
          </div>

        </div>

        <div class="modal-footer py-2 bg-light">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
          <button type="submit" class="btn btn-primary btn-sm px-4">Xác nhận Tạo</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- === MODAL 2: XÁC NHẬN XÓA PHÒNG === --}}
<div class="modal fade" id="deleteRoomModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white py-2">
        <h5 class="modal-title fw-bold fs-6">XÁC NHẬN XÓA PHÒNG</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3">Bạn có chắc chắn muốn xóa phòng chiếu này không?</p>
        <div class="alert alert-light border small">
            <div><strong>Tên phòng:</strong> <span id="modal-room-name" class="fw-bold text-danger"></span></div>
            <div><strong>Sức chứa:</strong> <span id="modal-room-capacity"></span> người</div>
            <div><strong>Số ghế hiện có:</strong> <span id="modal-room-seats"></span> ghế</div>
        </div>
        <p class="text-muted small fst-italic mb-0">Lưu ý: Không thể xóa nếu đang có suất chiếu hoặc vé.</p>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Hủy</button>
        <form id="deleteRoomForm" method="POST" action="">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Xác nhận Xóa</button>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- === MODAL 3: BÁO LỖI === --}}
<div class="modal fade" id="validationErrorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white py-2">
        <h5 class="modal-title fw-bold fs-6">
            <i class="bi bi-exclamation-octagon-fill me-2"></i> Lỗi cấu hình ghế
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center py-4">
        <p class="fs-6 text-danger fw-bold mb-2" id="errorModalMessage"></p>
        <p class="text-secondary small mb-0">Vui lòng kiểm tra lại cấu hình để đảm bảo hợp lệ.</p>
      </div>
      <div class="modal-footer justify-content-center py-2 bg-light">
        <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">Đã hiểu</button>
      </div>
    </div>
  </div>
</div>

{{-- CSS --}}
<style>
    .transition-opacity { transition: opacity 0.3s ease-in-out; }
    .checkbox-toggle { transform: scale(1.2); cursor: pointer; margin-top: 0.15rem;}
    .calc-input { font-weight: 700; color: #333; }
</style>

{{-- SCRIPT XỬ LÝ --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {

        var currentMaxCapacity = 0;
        var currentTotalCreated = 0;

        // 1. SETUP MODAL XÓA
        var deleteModal = document.getElementById('deleteRoomModal');
        deleteModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            deleteModal.querySelector('#deleteRoomForm').action = btn.getAttribute('data-url');
            deleteModal.querySelector('#modal-room-name').textContent = btn.getAttribute('data-name');
            deleteModal.querySelector('#modal-room-capacity').textContent = btn.getAttribute('data-capacity');
            deleteModal.querySelector('#modal-room-seats').textContent = btn.getAttribute('data-seats');
        });

        // 2. SETUP MODAL TẠO GHẾ
        var genModal = document.getElementById('generateSeatsModal');
        genModal.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            var form = genModal.querySelector('#generateSeatsForm');
            form.action = btn.getAttribute('data-url');
            genModal.querySelector('#gen-room-name').textContent = btn.getAttribute('data-name');
            genModal.querySelector('#gen-current-seats').textContent = btn.getAttribute('data-current') || '0';

            // Lấy Max Capacity
            currentMaxCapacity = parseInt(btn.getAttribute('data-max-capacity')) || 0;
            document.getElementById('gen-max-capacity').textContent = currentMaxCapacity;

            // Reset form (xóa hết dữ liệu cũ)
            form.reset();
            // Tính lại ngay lập tức (về 0)
            calculateAll();
        });

        // 3. VARIABLES
        var inpRows = document.getElementById('inpRows');
        var inpCols = document.getElementById('inpCols');
        var displayTotalCreated = document.getElementById('totalCreatedDisplay');

        var chkNorm = document.getElementById('checkNormal'), nFrom = document.getElementById('nFrom'), nTo = document.getElementById('nTo');
        var chkVip  = document.getElementById('checkVip'),    vFrom = document.getElementById('vFrom'), vTo = document.getElementById('vTo');
        var chkCpl  = document.getElementById('checkCouple'), cFrom = document.getElementById('cFrom'), cTo = document.getElementById('cTo');

        var statNorm = document.getElementById('statNormal');
        var statVip  = document.getElementById('statVip');
        var statCpl  = document.getElementById('statCouple');

        // 4. CALCULATION
        function calculateAll() {
            var r = parseInt(inpRows.value) || 0;
            var c = parseInt(inpCols.value) || 0;

            currentTotalCreated = 0;

            currentTotalCreated += updateRowStat(chkNorm, nFrom, nTo, statNorm, c, false);
            currentTotalCreated += updateRowStat(chkVip,  vFrom, vTo, statVip,  c, false);
            currentTotalCreated += updateRowStat(chkCpl,  cFrom, cTo, statCpl,  c, true);

            displayTotalCreated.textContent = currentTotalCreated;

            // Cảnh báo màu đỏ
            if (currentTotalCreated > currentMaxCapacity) {
                displayTotalCreated.classList.remove('text-primary');
                displayTotalCreated.classList.add('text-danger');
            } else {
                displayTotalCreated.classList.remove('text-danger');
                displayTotalCreated.classList.add('text-primary');
            }
        }

        function updateRowStat(checkbox, inputFrom, inputTo, displayEl, cols, isCouple) {
            if (!checkbox.checked) {
                displayEl.innerHTML = '<span class="text-muted small">Không sử dụng</span>';
                return 0;
            }

            var from = parseInt(inputFrom.value) || 0;
            var to   = parseInt(inputTo.value)   || 0;
            var rowCount = 0;

            if (to >= from && from > 0) {
                rowCount = to - from + 1;
            }

            var seatsPerRow = isCouple ? Math.floor(cols / 2) : cols;
            var totalSeats = rowCount * seatsPerRow;

            var textCols = isCouple ? (Math.floor(cols/2) + " (đôi)") : cols;
            displayEl.innerHTML = `<b>${rowCount}</b> hàng x ${textCols} = <strong class="text-primary">${totalSeats}</strong> ghế`;

            return totalSeats;
        }

        // 5. LISTENERS
        var allInputs = document.querySelectorAll('#generateSeatsModal input');
        allInputs.forEach(input => {
            input.addEventListener('input', calculateAll);
            input.addEventListener('change', calculateAll);
        });

        // 6. TOGGLE INPUTS
        var toggles = document.querySelectorAll('.checkbox-toggle');
        toggles.forEach(function(toggle) {
            function updateState() {
                var targetId = toggle.getAttribute('data-target');
                var targetBox = document.getElementById(targetId);
                var inputs = targetBox.querySelectorAll('input');

                if (toggle.checked) {
                    targetBox.style.opacity = '1';
                    inputs.forEach(i => i.disabled = false);
                } else {
                    targetBox.style.opacity = '0.3';
                    inputs.forEach(i => i.disabled = true);
                }
                calculateAll();
            }
            // Init
            updateState();
            toggle.addEventListener('change', updateState);
        });

        // 7. VALIDATE SUBMIT
        document.getElementById('generateSeatsForm').addEventListener('submit', function(e) {
            var totalRows = parseInt(inpRows.value) || 0;

            // --- A. LẤY DỮ LIỆU CÁC KHOẢNG HÀNG GHẾ ---
            var ranges = [];
            // Chỉ lấy khoảng ghế nào đang được bật (input không disabled) và có số hợp lệ
            if (!nTo.disabled) {
                let s = parseInt(nFrom.value) || 0;
                let e = parseInt(nTo.value) || 0;
                if (s > 0 && e >= s) ranges.push({type: 'Ghế Thường', s: s, e: e});
            }
            if (!vTo.disabled) {
                let s = parseInt(vFrom.value) || 0;
                let e = parseInt(vTo.value) || 0;
                if (s > 0 && e >= s) ranges.push({type: 'Ghế VIP', s: s, e: e});
            }
            if (!cTo.disabled) {
                let s = parseInt(cFrom.value) || 0;
                let e = parseInt(cTo.value) || 0;
                if (s > 0 && e >= s) ranges.push({type: 'Ghế Đôi', s: s, e: e});
            }

            // --- B. KIỂM TRA TRÙNG LẶP HÀNG GHẾ ---
            for(let i=0; i<ranges.length; i++) {
                for(let j=i+1; j<ranges.length; j++) {
                    let r1 = ranges[i];
                    let r2 = ranges[j];
                    // Logic giao nhau: max(start1, start2) <= min(end1, end2)
                    if (Math.max(r1.s, r2.s) <= Math.min(r1.e, r2.e)) {
                        e.preventDefault();
                        showError("LỖI: Hàng ghế bị trùng lặp giữa " + r1.type + " (" + r1.s + "-" + r1.e + ") và " + r2.type + " (" + r2.s + "-" + r2.e + ")!");
                        return;
                    }
                }
            }

            // --- C. KIỂM TRA VƯỢT QUÁ TỔNG SỐ HÀNG ---
            var maxEnd = 0;
            ranges.forEach(r => { if(r.e > maxEnd) maxEnd = r.e; });

            if (maxEnd > totalRows) {
                e.preventDefault();
                showError("Số hàng nhập vào (" + maxEnd + ") vượt quá tổng số hàng của phòng (" + totalRows + ")!");
                return;
            }

            // --- D. KIỂM TRA SỨC CHỨA TỐI ĐA ---
            if (currentTotalCreated > currentMaxCapacity) {
                e.preventDefault();
                showError("Tổng số ghế (" + currentTotalCreated + ") vượt quá Sức chứa tối đa của phòng (" + currentMaxCapacity + ")!");
                return;
            }
        });

        function showError(msg) {
            document.getElementById('errorModalMessage').textContent = msg;
            new bootstrap.Modal(document.getElementById('validationErrorModal')).show();
        }
    });
</script>
@endsection
