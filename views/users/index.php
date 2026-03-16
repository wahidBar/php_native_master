<div class="card shadow-sm border-0">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0 fw-bold">
                <i class="feather-shield me-2 text-primary"></i>User Management
            </h4>
            <small class="text-muted">Manage system users</small>
        </div>

        <a href="?action=users.create" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Tambah User
        </a>
    </div>

    <!-- FILTER AREA -->
    <div class="card-body border-bottom">
        <?php flash_show(); ?>

        <div class="row g-3">

            <div class="col-md-4">
                <input type="text"
                    id="searchInput"
                    class="form-control"
                    placeholder="Search name or email...">
            </div>

            <div class="col-md-3">
                <select id="roleFilter" class="form-select">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id'] ?>">
                            <?= $r['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

    </div>

    <!-- TABLE -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">

            <thead class="table">
                <tr>
                    <th width="60">#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>

            <tbody id="userTable">             
            </tbody>

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

    function loadUsers(page = 1) {

        currentPage = page;

        let search = document.getElementById('searchInput').value;
        let role = document.getElementById('roleFilter').value;

        fetch(`?action=users.index&ajax=1&page=${page}&search=${search}&role=${role}`)
            .then(res => res.json())
            .then(data => {

                renderTable(data.users, data.currentPage, data.limit);
                renderPagination(data.totalPages, data.currentPage);

            });
    }

    function renderTable(users, currentPage, limit) {

        let html = '';
        let startNumber = (currentPage - 1) * limit;

        if (users.length === 0) {
            html = `<tr>
            <td colspan="6" class="text-center text-muted py-4">
                No data found
            </td>
        </tr>`;
        }

        users.forEach((u, i) => {

            html += `
        <tr>
            <td>${startNumber + i + 1}</td>
            <td>${u.name}</td>
            <td>${u.email}</td>
            <td>${u.role_name ?? '-'}</td>
            <td>
                ${u.is_active == 1 
                    ? '<span class="badge bg-soft-success text-success">Active</span>'
                    : '<span class="badge bg-soft-warning text-warning">Inactive</span>'}
            </td>
            <td class="text-center">
                <div class="btn-group">
                    <a href="?action=users.show&id=${u.id}"
                        class="btn btn-sm btn-outline-info">
                        <i class="feather-eye"></i>
                    </a>

                    <a href="?action=users.edit&id=${u.id}"
                        class="btn btn-sm btn-outline-warning">
                        <i class="feather-edit"></i>
                    </a>

                    <a onclick="return confirm('Yakin hapus user ini?')"
                        href="?action=users.delete&id=${u.id}"
                        class="btn btn-sm btn-outline-danger">
                        <i class="feather-trash-2"></i>
                    </a>
                </div>
            </td>
        </tr>`;
        });

        document.getElementById('userTable').innerHTML = html;
    }

    function renderPagination(totalPages, currentPage) {

        const ul = document.querySelector('.pagination-common-style');

        if (!ul) {
            console.warn('Pagination container tidak ditemukan');
            return;
        }

        ul.innerHTML = '';

        if (totalPages <= 1) return;

        const createLi = (content, page = null, isActive = false, isDisabled = false) => {

            const li = document.createElement('li');
            const a = document.createElement('a');

            a.href = "javascript:void(0);";
            a.innerHTML = content;

            if (isActive) a.classList.add('active');
            if (isDisabled) a.classList.add('disabled');

            if (page && !isDisabled && !isActive) {
                a.addEventListener('click', () => loadUsers(page));
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
        let end = Math.min(totalPages, currentPage + 2);

        // Jika ada gap di awal
        if (start > 1) {
            ul.appendChild(createLi('1', 1));

            if (start > 2) {
                ul.appendChild(createLi('<i class="bi bi-three-dots"></i>', null, false, true));
            }
        }

        // 🔢 Page numbers
        for (let i = start; i <= end; i++) {
            ul.appendChild(
                createLi(i, i, i === currentPage)
            );
        }

        // Jika ada gap di akhir
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



    /* ================= AUTO SEARCH ================= */

    document.getElementById('searchInput').addEventListener('keyup', function() {

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            loadUsers(1);
        }, 400);

    });

    document.getElementById('roleFilter').addEventListener('change', function() {
        loadUsers(1);
    });

    document.addEventListener('DOMContentLoaded', function() {
        loadUsers(1);
    });
</script>