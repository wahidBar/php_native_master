<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <div>
            <h4 class="fw-bold mb-0">Data As Ms Jenistransaksi</h4>
            <small class="text-muted">Kelola data melalui tabel di bawah ini</small>
        </div>
        <button onclick="openForm()" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg"></i> Tambah Data
        </button>
    </div>

    <div class="card-body border-bottom">
        <div class="row">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text  border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="searchInput" class="form-control border-start-0" 
                        placeholder="Cari data..." onkeyup="debounceLoad()">
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table text-secondary">
                <tr>
                    <th width="60">#</th>
                    <th data-sort='idjenistrans' onclick="sortTable('idjenistrans')" style='cursor:pointer'>
            Idjenistrans <span class='sort-icon'></span>
           </th>
<th data-sort='keterangan' onclick="sortTable('keterangan')" style='cursor:pointer'>
            Keterangan <span class='sort-icon'></span>
           </th>
<th data-sort='nourut' onclick="sortTable('nourut')" style='cursor:pointer'>
            Nourut <span class='sort-icon'></span>
           </th>

                    <th width="120" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="dataTable">
                <tr><td colspan="5" class="text-center py-5">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>

    <div class="card-footer">
        <ul class="list-unstyled d-flex align-items-center justify-content-end gap-2 mb-0 pagination-common-style"
            id="paginationArea">
        </ul>
    </div>
</div>

<div class="modal fade" id="dataModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Form Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let debounceTimer;


let sortField = null;
let sortOrder = 'asc';

function debounceLoad(){
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => loadData(1), 400);
}

function loadData(page = 1){
    currentPage = page;
    let search = document.getElementById('searchInput').value;
    let tableBody = document.getElementById('dataTable');

    fetch(`?action=asal_usul.index&ajax=1&page=${page}&search=${encodeURIComponent(search)}&sort=${sortField ?? ''}&order=${sortOrder}`)                .then(res => res.json())
        .then(res => {
            renderTable(res.data || [], res.page, res.limit);
            renderPagination(res.totalPages, res.page);
            updateSortIcons();
        })
        .catch(err => {
            console.error(err);
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data</td></tr>`;
        });
}

function sortTable(field){

    if(sortField === field){
        sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
    }else{
        sortField = field;
        sortOrder = 'asc';
    }

    loadData(1);
}

function updateSortIcons(){

    document.querySelectorAll('th[data-sort]').forEach(th => {

        const field = th.dataset.sort;
        const icon  = th.querySelector('.sort-icon');

        if(!icon) return;

        if(field === sortField){

            icon.innerHTML = sortOrder === 'asc'
                ? '<i class="bi bi-caret-up-fill ms-1"></i>'
                : '<i class="bi bi-caret-down-fill ms-1"></i>';

        }else{
            icon.innerHTML = '';
        }

    });

}

function renderTable(rows, page, limit){
    let html = '';
    let start = (page - 1) * limit;

    if(rows.length === 0){
        html = `<tr><td colspan="5" class="text-center py-5 text-muted small">Tidak ada data ditemukan</td></tr>`;
    } else {
        rows.forEach((row, i) => {
            html += `<tr><td>${start + i + 1}</td>`;
                    html += `<td>${row.idjenistrans ?? ''}</td>`;
        html += `<td>${row.keterangan ?? ''}</td>`;
        html += `<td>${row.nourut ?? ''}</td>`;

            html += `
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button onclick="editData(${row.idjenistrans})" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></button>
                        <button onclick="deleteData(${row.idjenistrans})" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            </tr>`;
        });
    }
    document.getElementById('dataTable').innerHTML = html;
}

function renderPagination(totalPages, currentPage) {

    const ul = document.querySelector('.pagination-common-style');

    if (!ul) return;

    ul.innerHTML = '';

    if (totalPages <= 1) return;

    const createLi = (content, page = null, isActive = false, isDisabled = false) => {

        const li = document.createElement('li');
        const a  = document.createElement('a');

        a.href = "#";
        a.innerHTML = content;

        if (isActive) a.classList.add('active');
        if (isDisabled) a.classList.add('disabled');

        if (page !== null && !isActive && !isDisabled) {
            a.addEventListener('click', (e) => {
                e.preventDefault();
                loadData(page);
            });
        }

        li.appendChild(a);
        return li;
    };

    // 🔙 PREV
    ul.appendChild(
        createLi(
            '<i class="bi bi-arrow-left"></i>',
            currentPage - 1,
            false,
            currentPage === 1
        )
    );

    let start = Math.max(1, currentPage - 2);
    let end   = Math.min(totalPages, currentPage + 2);

    // gap awal
    if (start > 1) {

        ul.appendChild(createLi('1', 1));

        if (start > 2) {
            ul.appendChild(createLi('<i class="bi bi-three-dots"></i>', null, false, true));
        }
    }

    // page numbers
    for (let i = start; i <= end; i++) {

        ul.appendChild(
            createLi(i, i, i === currentPage)
        );
    }

    // gap akhir
    if (end < totalPages) {

        if (end < totalPages - 1) {
            ul.appendChild(createLi('<i class="bi bi-three-dots"></i>', null, false, true));
        }

        ul.appendChild(createLi(totalPages, totalPages));
    }

    // 🔜 NEXT
    ul.appendChild(
        createLi(
            '<i class="bi bi-arrow-right"></i>',
            currentPage + 1,
            false,
            currentPage === totalPages
        )
    );
}

function openForm(){
    document.getElementById('modalTitle').innerText = 'Tambah As Ms Jenistransaksi';

    fetch('?action=asal_usul.create&ajax=1')
        .then(res => res.text())
        .then(html => {
            document.getElementById('modalBody').innerHTML = html;

            bindFormSubmit(); // 🔥 penting

            new bootstrap.Modal(document.getElementById('dataModal')).show();
        });
}

function editData(id){
    document.getElementById('modalTitle').innerText = 'Edit As Ms Jenistransaksi';

    fetch(`?action=asal_usul.edit&id=${id}&ajax=1`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('modalBody').innerHTML = html;

            bindFormSubmit(); // 🔥 penting

            new bootstrap.Modal(document.getElementById('dataModal')).show();
        });
}

function deleteData(id){
    if(!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;

    fetch(`?action=asal_usul.delete&ajax=1`, {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(res => {
        if(res.success){
            loadData(currentPage);
        } else {
            alert(res.message || 'Gagal menghapus data');
        }
    });
}

/* ===============================
AUTO BIND FORM SUBMIT (GENERATOR)
================================= */

function bindFormSubmit(){
    const form = document.getElementById('dataForm');
    if(!form) return;

    form.addEventListener('submit', function(e){
        e.preventDefault();

        let formData = new FormData(form);
        let id = formData.get('id');
        let action = id ? 'update' : 'store';

        console.log('save', action);

        fetch('?action=asal_usul.' + action + '&ajax=1', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {

            if(res.success){

                let modalEl = document.getElementById('dataModal');
                let modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();

                loadData(id ? currentPage : 1);

            } else {
                alert(res.message || 'Terjadi kesalahan saat menyimpan');
            }

        })
        .catch(err => {
            console.error(err);
            alert('Gagal terhubung ke server');
        });
    });
}

document.addEventListener("DOMContentLoaded", () => loadData());
</script>