<div class="card shadow-sm border-0">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="feather-menu me-2 text-primary"></i>Menu Management
            </h4>
            <small class="text-muted">Manage system navigation</small>
        </div>

        <a href="?action=menus.create" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Tambah Menu
        </a>
    </div>

    <!-- FILTER -->
    <div class="card-body border-bottom">
        <div class="row g-3">

            <div class="col-md-4">
                <input type="text"
                    id="searchInput"
                    class="form-control"
                    placeholder="Search menu name or route...">
            </div>

            <div class="col-md-3">
                <select id="parentFilter" class="form-select">
                    <option value="">All Parent</option>
                    <?php foreach ($parents as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= $p['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>
    </div>

    <!-- TABLE -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">

            <thead>
                <tr>
                    <th width="60">#</th>
                    <th>Name</th>
                    <th>Route</th>
                    <th>Parent</th>
                    <th>Order</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody id="menuTable"></tbody>

        </table>
    </div>

    <!-- PAGINATION -->
    <div class="card-footer">
        <ul class="list-unstyled d-flex align-items-center justify-content-end gap-2 mb-0 pagination-common-style"
            id="paginationArea">
        </ul>
    </div>

</div>
<script>
    let currentPage = 1;
    let debounceTimer;

    function loadMenus(page = 1) {

        currentPage = page;

        let search = document.getElementById('searchInput').value;
        let parent = document.getElementById('parentFilter').value;

        fetch(`?action=menus.index&ajax=1&page=${page}&search=${encodeURIComponent(search)}&parent=${parent}`)
            .then(res => res.json())
            .then(data => {

                renderTable(data.menus, data.currentPage, data.limit);
                renderPagination(data.totalPages, data.currentPage);

            })
            .catch(err => {
                console.error('Fetch error:', err);
            });
    }

    function renderTable(menus, currentPage, limit) {

        let html = '';
        let startNumber = (currentPage - 1) * limit;

        if (!menus || menus.length === 0) {
            html = `
        <tr>
            <td colspan="6" class="text-center text-muted py-4">
                No data found
            </td>
        </tr>`;
        } else {

            menus.forEach((m, i) => {

                let isChild = m.parent_id !== null;
                let indent = isChild ? '&nbsp;&nbsp;&nbsp;&nbsp;↳ ' : '';

                html += `
            <tr>
                <td>${startNumber + i + 1}</td>
                <td>${indent}${m.name}</td>
                <td>${m.route ?? '-'}</td>
                <td>${m.parent_name ?? '-'}</td>
                <td>${m.order_number ?? 0}</td>
                <td class="text-center">
                    <div class="btn-group">
                        <a href="?action=menus.edit&id=${m.id}"
                            class="btn btn-sm btn-outline-warning">
                            <i class="feather-edit"></i>
                        </a>

                        <a onclick="return confirm('Yakin hapus menu ini?')"
                            href="?action=menus.delete&id=${m.id}"
                            class="btn btn-sm btn-outline-danger">
                            <i class="feather-trash-2"></i>
                        </a>
                    </div>
                </td>
            </tr>`;
            });
        }

        document.getElementById('menuTable').innerHTML = html;
    }

    function renderPagination(totalPages, currentPage) {

        const ul = document.querySelector('.pagination-common-style');
        ul.innerHTML = '';

        if (totalPages <= 1) return;

        const createLi = (label, page = null, disabled = false) => {

            const li = document.createElement('li');
            const a = document.createElement('a');

            a.href = "javascript:void(0);";
            a.innerHTML = label;

            if (disabled) {
                a.classList.add('disabled');
            } else if (page !== null) {
                a.addEventListener('click', () => loadMenus(page));
            }

            li.appendChild(a);
            return li;
        };

        // Prev
        ul.appendChild(
            createLi('&laquo;', currentPage - 1, currentPage === 1)
        );

        for (let i = 1; i <= totalPages; i++) {
            ul.appendChild(
                createLi(i, i, false)
            );
        }

        // Next
        ul.appendChild(
            createLi('&raquo;', currentPage + 1, currentPage === totalPages)
        );
    }

    /* AUTO SEARCH */
    document.getElementById('searchInput').addEventListener('keyup', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            loadMenus(1);
        }, 400);
    });

    document.getElementById('parentFilter').addEventListener('change', function() {
        loadMenus(1);
    });

    /* INITIAL LOAD */
    document.addEventListener('DOMContentLoaded', function() {
        loadMenus(1);
    });
</script>