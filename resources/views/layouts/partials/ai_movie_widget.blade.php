<div id="aiFab" class="position-fixed bottom-0 end-0 m-3" style="z-index: 2000;">
  <button class="btn btn-primary rounded-circle shadow" id="aiOpenBtn" style="width:56px;height:56px;">
    <i class="bi bi-chat-dots"></i>
  </button>
</div>

<div id="aiPanel" class="position-fixed bottom-0 end-0 m-3 d-none" style="z-index: 2001; width: 360px;">
  <div class="card shadow-lg border-0">
    <div class="card-header d-flex align-items-center justify-content-between">
      <div>
        <div class="fw-bold">Chatbot gợi ý phim 🎬</div>
        <div class="text-muted small">Hỏi phim đang chiếu / sắp chiếu / hợp gu</div>
      </div>
      <button class="btn btn-sm btn-outline-secondary" id="aiCloseBtn">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <div class="card-body" id="aiBody" style="height:320px; overflow:auto;">
      <div id="aiMsgs" class="small"></div>
    </div>

    <div class="card-footer">
      <div class="d-flex gap-2 mb-2">
        {{-- ✅ gắn data-mode cho chắc --}}
        <button class="btn btn-sm btn-dark"
                data-quick="Gợi ý 5 phim đang chiếu hợp với mình"
                data-mode="now">Gợi ý ngay</button>

        <button class="btn btn-sm btn-outline-dark"
                data-quick="Hiện danh sách phim đang chiếu"
                data-mode="now">Đang chiếu</button>

        <button class="btn btn-sm btn-outline-dark"
                data-quick="Hiện danh sách phim sắp chiếu"
                data-mode="soon">Sắp chiếu</button>
      </div>

      <div class="input-group">
        <input class="form-control" id="aiInput" placeholder="Ví dụ: mình thích hoạt hình, gợi ý phim...">
        <button class="btn btn-primary" id="aiSend">
          <i class="bi bi-send"></i>
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
(() => {
  const panel   = document.getElementById('aiPanel');
  const openBtn = document.getElementById('aiOpenBtn');
  const closeBtn= document.getElementById('aiCloseBtn');
  const msgs    = document.getElementById('aiMsgs');
  const body    = document.getElementById('aiBody');
  const input   = document.getElementById('aiInput');
  const send    = document.getElementById('aiSend');

  const csrfEl  = document.querySelector('meta[name="csrf-token"]');
  const csrf    = csrfEl ? csrfEl.content : '';

  const API_URL = '/api/ai/chat';

  const history = [];
  const MAX_HISTORY = 10;
  let isLoading = false;

  // ✅ mode mặc định
  let currentMode = 'now'; // now | soon | all

  function esc(s){
    return (s||'').replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
    }[m]));
  }

  function linkify(s){
    return esc(s||'')
      .replace(/(\/movies\/\d+)/g, `<a href="$1" target="_blank" class="text-decoration-underline">$1</a>`)
      .replace(/\n/g,'<br>');
  }

  function add(role, text){
    const wrap = document.createElement('div');
    wrap.className = 'mb-2';

    wrap.innerHTML = role === 'user'
      ? `<div class="text-end"><span class="d-inline-block p-2 rounded bg-primary text-white" style="max-width:90%">${esc(text)}</span></div>`
      : `<div class="text-start"><span class="d-inline-block p-2 rounded bg-light" style="max-width:90%">${linkify(text)}</span></div>`;

    msgs.appendChild(wrap);
    body.scrollTop = body.scrollHeight;
    return wrap;
  }

  function setLoading(on){
    isLoading = on;
    send.disabled = on;
    input.disabled = on;
    document.querySelectorAll('[data-quick]').forEach(b => b.disabled = on);
  }

  function trimHistory(){
    if (history.length > MAX_HISTORY) {
      history.splice(0, history.length - MAX_HISTORY);
    }
  }

  async function askAI(message, modeOverride = null){
    if (isLoading) return;

    const mode = modeOverride || currentMode;

    add('user', message);
    history.push({ role:'user', content: message });
    trimHistory();

    setLoading(true);
    const typing = add('bot', 'Đang trả lời…');
    const span = typing.querySelector('span');

    try {
      const res = await fetch(API_URL, {
        method: 'POST',
        headers: {
          'Content-Type':'application/json',
          ...(csrf ? {'X-CSRF-TOKEN': csrf} : {})
        },
        // ✅ luôn gửi mode đúng
        body: JSON.stringify({ message, mode, history })
      });

      let data = {};
      try { data = await res.json(); } catch(e) {}

      if (!res.ok) {
        const msg = data.answer || data.message || `AI lỗi (HTTP ${res.status})`;
        span.innerHTML = linkify(msg);
        history.push({ role:'assistant', content: msg });
        trimHistory();
        return;
      }

      const answer = data.answer || 'Mình chưa trả lời được 😅';
      span.innerHTML = linkify(answer);
      history.push({ role:'assistant', content: answer });
      trimHistory();

    } catch (e) {
      const msg = 'AI đang lỗi, bạn thử lại sau.';
      span.innerHTML = linkify(msg);
      history.push({ role:'assistant', content: msg });
      trimHistory();
    } finally {
      setLoading(false);
      body.scrollTop = body.scrollHeight;
    }
  }

  openBtn.onclick = () => {
    panel.classList.remove('d-none');
    document.getElementById('aiFab').classList.add('d-none');
    input.focus();
  };

  closeBtn.onclick = () => {
    panel.classList.add('d-none');
    document.getElementById('aiFab').classList.remove('d-none');
  };

  send.onclick = () => {
    const m = input.value.trim();
    if (!m) return;

    input.value = '';

    // ✅ tự đoán mode nếu user gõ "sắp chiếu/đang chiếu" (không bắt buộc)
    const lower = m.toLowerCase();
    if (lower.includes('sắp chiếu')) currentMode = 'soon';
    if (lower.includes('đang chiếu')) currentMode = 'now';

    askAI(m);
  };

  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      e.preventDefault();
      send.click();
    }
  });

  // ✅ quick buttons: lấy mode từ data-mode, không phụ thuộc text
  document.querySelectorAll('[data-quick]').forEach(btn => {
    btn.onclick = () => {
      const q = btn.dataset.quick || '';
      const m = btn.dataset.mode || null;  // now|soon|all
      if (m) currentMode = m;
      askAI(q, m); // ✅ ép mode luôn cho chắc
    };
  });

  add('bot', 'Chào bạn 👋 Bạn thích thể loại phim nào để mình gợi ý cho bạn nè?');
})();
</script>
@endpush
