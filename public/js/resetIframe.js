document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function () {
            const iframe = modal.querySelector('iframe');
            if (iframe) {
                const src = iframe.src;
                iframe.src = '';   // Dừng video
                iframe.src = src;  // Reset lại link
            }
        });
    });
});
