<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <div>
            <h4 class="fw-bold mb-0">Data As Ms Unit</h4>
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
                    <th data-sort='idunit' onclick="sortTable('idunit')" style='cursor:pointer'>
            Idunit <span class='sort-icon'></span>
           </th>
<th data-sort='kodeunit' onclick="sortTable('kodeunit')" style='cursor:pointer'>
            Kodeunit <span class='sort-icon'></span>
           </th>
<th data-sort='namaunit' onclick="sortTable('namaunit')" style='cursor:pointer'>
            Namaunit <span class='sort-icon'></span>
           </th>
<th data-sort='namapanjang' onclick="sortTable('namapanjang')" style='cursor:pointer'>
            Namapanjang <span class='sort-icon'></span>
           </th>
<th data-sort='parentunit' onclick="sortTable('parentunit')" style='cursor:pointer'>
            Parentunit <span class='sort-icon'></span>
           </th>
<th data-sort='level' onclick="sortTable('level')" style='cursor:pointer'>
            Level <span class='sort-icon'></span>
           </th>
<th data-sort='isleaf' onclick="sortTable('isleaf')" style='cursor:pointer'>
            Isleaf <span class='sort-icon'></span>
           </th>
<th data-sort='deflokasi' onclick="sortTable('deflokasi')" style='cursor:pointer'>
            Deflokasi <span class='sort-icon'></span>
           </th>
<th data-sort='t_userid' onclick="sortTable('t_userid')" style='cursor:pointer'>
            T Userid <span class='sort-icon'></span>
           </th>
<th data-sort='t_updatetime' onclick="sortTable('t_updatetime')" style='cursor:pointer'>
            T Updatetime <span class='sort-icon'></span>
           </th>
<th data-sort='t_ipaddress' onclick="sortTable('t_ipaddress')" style='cursor:pointer'>
            T Ipaddress <span class='sort-icon'></span>
           </th>
<th data-sort='kodeupb' onclick="sortTable('kodeupb')" style='cursor:pointer'>
            Kodeupb <span class='sort-icon'></span>
           </th>
<th data-sort='nippetugas' onclick="sortTable('nippetugas')" style='cursor:pointer'>
            Nippetugas <span class='sort-icon'></span>
           </th>
<th data-sort='namapetugas' onclick="sortTable('namapetugas')" style='cursor:pointer'>
            Namapetugas <span class='sort-icon'></span>
           </th>
<th data-sort='jabatanpetugas' onclick="sortTable('jabatanpetugas')" style='cursor:pointer'>
            Jabatanpetugas <span class='sort-icon'></span>
           </th>
<th data-sort='nippetugas2' onclick="sortTable('nippetugas2')" style='cursor:pointer'>
            Nippetugas2 <span class='sort-icon'></span>
           </th>
<th data-sort='namapetugas2' onclick="sortTable('namapetugas2')" style='cursor:pointer'>
            Namapetugas2 <span class='sort-icon'></span>
           </th>
<th data-sort='jabatanpetugas2' onclick="sortTable('jabatanpetugas2')" style='cursor:pointer'>
            Jabatanpetugas2 <span class='sort-icon'></span>
           </th>
<th data-sort='islast' onclick="sortTable('islast')" style='cursor:pointer'>
            Islast <span class='sort-icon'></span>
           </th>
<th data-sort='isunit_aktif' onclick="sortTable('isunit_aktif')" style='cursor:pointer'>
            Isunit Aktif <span class='sort-icon'></span>
           </th>

                    <th width="120" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="dataTable">
                <tr><td colspan="22" class="text-center py-5">Memuat data...</td></tr>
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

    fetch(`?action=units.index&ajax=1&page=${page}&search=${encodeURIComponent(search)}&sort=${sortField ?? ''}&order=${sortOrder}`)                .then(res => res.json())
        .then(res => {
            renderTable(res.data || [], res.page, res.limit);
            renderPagination(res.totalPages, res.page);
            updateSortIcons();
        })
        .catch(err => {
            console.error(err);
            tableBody.innerHTML = `<tr><td colspan="22" class="text-center text-danger">Gagal memuat data</td></tr>`;
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
        html = `<tr><td colspan="22" class="text-center py-5 text-muted small">Tidak ada data ditemukan</td></tr>`;
    } else {
        rows.forEach((row, i) => {
            html += `<tr><td>${start + i + 1}</td>`;
                    html += `<td>${row.idunit ?? ''}</td>`;
        html += `<td>${row.kodeunit ?? ''}</td>`;
        html += `<td>${row.namaunit ?? ''}</td>`;
        html += `<td>${row.namapanjang ?? ''}</td>`;
        html += `<td>${row.parentunit ?? ''}</td>`;
        html += `<td>${row.level ?? ''}</td>`;
        html += `<td>${row.isleaf ?? ''}</td>`;
        html += `<td>${row.deflokasi ?? ''}</td>`;
        html += `<td>${row.t_userid ?? ''}</td>`;
        html += `<td>${row.t_updatetime ?? ''}</td>`;
        html += `<td>${row.t_ipaddress ?? ''}</td>`;
        html += `<td>${row.kodeupb ?? ''}</td>`;
        html += `<td>${row.nippetugas ?? ''}</td>`;
        html += `<td>${row.namapetugas ?? ''}</td>`;
        html += `<td>${row.jabatanpetugas ?? ''}</td>`;
        html += `<td>${row.nippetugas2 ?? ''}</td>`;
        html += `<td>${row.namapetugas2 ?? ''}</td>`;
        html += `<td>${row.jabatanpetugas2 ?? ''}</td>`;
        html += `<td>${row.islast ?? ''}</td>`;
        html += `<td>${row.isunit_aktif ?? ''}</td>`;

            html += `
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button onclick="editData(${row.idunit})" class="btn btn-outline-warning"><i class="bi bi-pencil"></i></button>
                        <button onclick="deleteData(${row.idunit})" class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
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
    document.getElementById('modalTitle').innerText = 'Tambah As Ms Unit';

    fetch('?action=units.create&ajax=1')
        .then(res => res.text())
        .then(html => {
            document.getElementById('modalBody').innerHTML = html;

            bindFormSubmit(); // 🔥 penting

            new bootstrap.Modal(document.getElementById('dataModal')).show();
        });
}

function editData(id){
    document.getElementById('modalTitle').innerText = 'Edit As Ms Unit';

    fetch(`?action=units.edit&id=${id}&ajax=1`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('modalBody').innerHTML = html;

            bindFormSubmit(); // 🔥 penting

            new bootstrap.Modal(document.getElementById('dataModal')).show();
        });
}

function deleteData(id){
    if(!confirm('Apakah Anda yakin ingin menghapus data ini?')) return;

    fetch(`?action=units.delete&ajax=1`, {
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
        let id = formData.get('idunit');
        let action = id ? 'update' : 'store';

        console.log('save', action);

        fetch('?action=units.' + action + '&ajax=1', {
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