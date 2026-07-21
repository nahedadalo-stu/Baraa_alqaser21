
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.toast').forEach(function(el) {
        var toast = new bootstrap.Toast(el, { delay: 5000 });
        toast.show();
    });

    document.querySelectorAll('.needs-validation').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
                showToast('يرجى ملء جميع الحقول المطلوبة بشكل صحيح', 'danger');
            }
            form.classList.add('was-validated');
        });
    });

    document.querySelectorAll('.btn-delete').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var href = this.getAttribute('href');
            showDeleteConfirm(href);
        });
    });

    var imgInput = document.getElementById('course_image');
    if (imgInput) {
        imgInput.addEventListener('change', function() {
            var preview = document.getElementById('image_preview');
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    document.querySelectorAll('a[href*="#"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            var url = this.getAttribute('href');
            var hash = url.indexOf('#') !== -1 ? url.split('#')[1] : null;
            if (!hash) return;
            var target = document.getElementById(hash);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});

function showToast(message, type) {
    type = type || 'info';

    var old = document.getElementById('jsToast');
    if (old) old.remove();

    var container = document.getElementById('jsToastContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'jsToastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }

    var toastEl = document.createElement('div');
    toastEl.id = 'jsToast';
    toastEl.className = 'toast align-items-center text-bg-' + type + ' border-0';
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML =
        '<div class="d-flex">' +
            '<div class="toast-body">' + message + '</div>' +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>' +
        '</div>';

    container.appendChild(toastEl);
    new bootstrap.Toast(toastEl, { delay: 4000 }).show();
}

function showDeleteConfirm(href) {
    var old = document.getElementById('deleteModal');
    if (old) old.remove();

    var modal = document.createElement('div');
    modal.innerHTML =
        '<div class="modal fade" id="deleteModal" tabindex="-1">' +
            '<div class="modal-dialog modal-dialog-centered">' +
                '<div class="modal-content border-0 shadow">' +
                    '<div class="modal-header bg-danger text-white border-0">' +
                        '<h5 class="modal-title">تأكيد الحذف</h5>' +
                        '<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>' +
                    '</div>' +
                    '<div class="modal-body text-center py-4">' +
                        '<p class="mb-0 fw-semibold fs-5">هل أنت متأكد من الحذف؟</p>' +
                        '<p class="text-muted small mt-1">لا يمكن التراجع عن هذه العملية</p>' +
                    '</div>' +
                    '<div class="modal-footer border-0 justify-content-center gap-3">' +
                        '<button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">إلغاء</button>' +
                        '<a href="' + href + '" class="btn btn-danger px-4" id="confirmDeleteBtn">نعم، احذف</a>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';

    document.body.appendChild(modal);
    var bsModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    bsModal.show();

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        showToast('جاري الحذف...', 'warning');
    });
}