<div class="container-fluid py-4">

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white d-flex justify-content-between">
            <div>
                <h4 class="fw-bold mb-0">Master Barang</h4>
                <small class="text-muted">Tree Based Item Management</small>
            </div>
            <a href="?action=barang.create" class="btn btn-primary">
                <i class="feather-plus me-1"></i> Tambah Barang
            </a>
        </div>

        <!-- FILTER -->
        <div class="card-body border-bottom">
            <?php flash_show(); ?>

            <div class="row g-3">

                <div class="col-md-4">
                    <input type="text"
                        id="searchInput"
                        class="form-control"
                        placeholder="Search kode / nama barang">
                </div>

                <div class="col-md-3">
                    <select id="tipeFilter" class="form-select">
                        <option value="1">Aset Tetap</option>
                        <option value="2">Aset Lancar</option>
                        <option value="3">Bahan & Jasa</option>
                        <option value="4">Khusus</option>
                    </select>
                </div>

            </div>
        </div>
        <div class="card-body custom-card-action p-0">
            <!-- TABLE -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th onclick="changeSort('kodebarang')" style="cursor:pointer">
                                Kode <span id="icon-kodebarang"></span>
                            </th>
                            <th onclick="changeSort('namabarang')" style="cursor:pointer">
                                Nama Barang <span id="icon-namabarang"></span>
                            </th>
                            <th onclick="changeSort('rak')" style="cursor:pointer">
                                Rak <span id="icon-rak"></span>
                            </th>
                            <th onclick="changeSort('idsatuan')" style="cursor:pointer">
                                Satuan <span id="icon-idsatuan"></span>
                            </th>
                            <th onclick="changeSort('isbrg_aktif')" style="cursor:pointer">
                                Status <span id="icon-isbrg_aktif"></span>
                            </th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>


                    <tbody id="barangTable">

                    </tbody>

                </table>
            </div>
        </div>

        <!-- PAGINATION -->
        <div class="card-footer">
            <ul class="list-unstyled d-flex align-items-center justify-content-end gap-2 mb-0 pagination-common-style"
                id="paginationArea">
            </ul>
        </div>

    </div>

</div>
<script>
    let currentSort = 'kodebarang';
    let currentOrder = 'ASC';

    let debounceTimer;
    let currentPage = 1;

    document.addEventListener("DOMContentLoaded", function() {
        loadBarang(1);
    });
    document.getElementById('searchInput').addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => loadBarang(1), 300);
    });

    document.getElementById('tipeFilter').addEventListener('change', function() {
        loadBarang(1);
    });

    function changeSort(column) {

        if (currentSort === column) {
            currentOrder = currentOrder === 'ASC' ? 'DESC' : 'ASC';
        } else {
            currentSort = column;
            currentOrder = 'ASC';
        }

        loadBarang(1);
    }

    function updateSortIcons() {

        document.querySelectorAll('[id^="icon-"]').forEach(el => el.innerHTML = '');

        let icon = currentOrder === 'ASC' ?
            '<i class="bi bi-arrow-up"></i>' :
            '<i class="bi bi-arrow-down"></i>';

        let target = document.getElementById('icon-' + currentSort);
        if (target) target.innerHTML = icon;
    }

    function loadBarang(page = 1) {

        currentPage = page;

        let search = document.getElementById('searchInput').value;
        let tipe = document.getElementById('tipeFilter').value;

        fetch(`?action=barang.index&ajax=1&page=${page}&search=${encodeURIComponent(search)}&tipe=${tipe}&sort=${currentSort}&order=${currentOrder}`)
            .then(res => res.json())
            .then(data => {
                renderTable(data.data);
                renderPagination(data.totalPages, data.currentPage);
                updateSortIcons();
            });

    }

    function renderTable(rows) {

        let html = '';

        if (rows.length === 0) {
            html = `
        <tr>
            <td colspan="7" class="text-center py-4 text-muted">
                Tidak ada data
            </td>
        </tr>`;
        }

        rows.forEach((b, i) => {

            let indent = (b.level - 1) * 20;
            let prefix = b.level > 1 ? '└─ ' : '';

            html += `
        <tr>
            <td>${(currentPage - 1) * 15 + (i + 1)}</td>
            <td>${b.kodebarang ?? ''}</td>
            <td>
                <span style="padding-left:${indent}px">
                    ${prefix}${b.namabarang ?? ''}
                </span>
            </td>
            <td>${b.rak ?? '-'}</td>
            <td>${b.idsatuan ?? '-'}</td>
            <td>
                ${b.isbrg_aktif == 1
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Non Aktif</span>'}
            </td>
              <td class="text-center">
                                    <div class="btn-group">
                <a href="?action=barang.show&id=${b.idbarang}"
                    class="btn btn-sm btn-outline-info">
                    <i class="feather-eye"></i>
                </a>

                <a href="?action=barang.edit&id=${b.idbarang}"
                    class="btn btn-sm btn-outline-warning">
                    <i class="feather-edit"></i>
                </a>

                <a onclick="return confirm('Yakin hapus barang ini?')"
                    href="?action=barang.delete&id=${b.idbarang}"
                    class="btn btn-sm btn-outline-danger">
                    <i class="feather-trash-2"></i>
                </a>
            </td>
            </div>
        </tr>`;
        });

        document.getElementById('barangTable').innerHTML = html;
    }

    function renderPagination(totalPages, currentPage) {
        const ul = document.querySelector('.pagination-common-style');
        ul.innerHTML = '';

        const createLi = (content, page = null, isActive = false, isDisabled = false) => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = "javascript:void(0);";
            a.innerHTML = content;
            if (isActive) a.classList.add('active');
            if (isDisabled) a.classList.add('disabled');
            if (page && !isDisabled && !isActive) {
                a.addEventListener('click', () => loadBarang(page));
            }
            li.appendChild(a);
            return li;
        };

        // Prev
        ul.appendChild(createLi('<i class="bi bi-arrow-left"></i>', currentPage - 1, false, currentPage === 1));

        let start = Math.max(1, currentPage - 2);
        let end = Math.min(totalPages, currentPage + 2);

        if (start > 1) {
            ul.appendChild(createLi('1', 1));
            if (start > 2) {
                ul.appendChild(createLi('<i class="bi bi-dot"></i>', null, false, true));
            }
        }

        for (let i = start; i <= end; i++) {
            ul.appendChild(createLi(i, i, i === currentPage));
        }

        if (end < totalPages) {
            if (end < totalPages - 1) {
                ul.appendChild(createLi('<i class="bi bi-dot"></i>', null, false, true));
            }
            ul.appendChild(createLi(totalPages, totalPages));
        }

        // Next
        ul.appendChild(createLi('<i class="bi bi-arrow-right"></i>', currentPage + 1, false, currentPage === totalPages));
    }
</script>