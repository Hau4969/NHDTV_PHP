document.addEventListener("DOMContentLoaded", function () {
    // Khi click vào poster -> hiện / ẩn overlay chứa 3 nút
    document.querySelectorAll('.poster-toggle').forEach(poster => {
        poster.addEventListener('click', function () {
            const overlay = this.nextElementSibling;
            overlay.classList.toggle('show-overlay');
        });
    });

    // Dừng video khi đóng modal trailer
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function () {
            const iframe = modal.querySelector('iframe');
            if (iframe) {
                const src = iframe.src;
                iframe.src = '';
                iframe.src = src;
            }
        });
    });
});
