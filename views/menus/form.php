<div class="container-fluid py-4">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="fw-bold mb-0">
                <?= isset($menu) ? 'Edit Menu' : 'Tambah Menu' ?>
            </h5>
        </div>

        <div class="card-body">

            <form method="post"
                action="?action=menus.<?= isset($menu) ? 'update' : 'store' ?>">

                <?= csrf_field(); ?>

                <?php
                $id           = $menu['id'] ?? '';
                $name         = htmlspecialchars($menu['name'] ?? '', ENT_QUOTES, 'UTF-8');
                $route        = htmlspecialchars($menu['route'] ?? '', ENT_QUOTES, 'UTF-8');
                $icon         = htmlspecialchars($menu['icon'] ?? '', ENT_QUOTES, 'UTF-8');
                $parent_id    = $menu['parent_id'] ?? '';
                ?>

                <?php if ($id): ?>
                    <input type="hidden" name="id" value="<?= $id ?>">
                <?php endif; ?>

                <div class="row">

                    <!-- Nama -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Nama Menu</label>
                        <input type="text"
                            name="name"
                            class="form-control"
                            required
                            value="<?= $name ?>">
                    </div>

                    <!-- Route -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Route</label>
                        <input type="text"
                            name="route"
                            class="form-control"
                            value="<?= $route ?>">
                        <small class="text-muted">Contoh: users, dashboard, barang</small>
                    </div>

                    <!-- Icon -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Icon</label>
                        <input type="text"
                            name="icon"
                            class="form-control"
                            value="<?= $icon ?>">
                        <small class="text-muted">Contoh: bi bi-house / fas fa-user</small>
                    </div>

                    <!-- Parent -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Parent Menu</label>

                        <select name="parent_id" id="parentMenu" class="form-select">

                            <option value="">-- Menu Utama --</option>

                            <?php foreach ($parents as $p): ?>
                                <?php if ($id && $id == $p['id']) continue; ?>

                                <option value="<?= $p['id'] ?>"
                                    <?= ($parent_id == $p['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <!-- Target Menu -->
                    <div class="col-md-6 mb-3">

                        <label class="form-label fw-semibold">Posisi Setelah Menu</label>

                        <select name="target_menu" id="targetMenu" class="form-select">
                            <option value="">-- Paling Bawah --</option>
                        </select>

                        <small class="text-muted">
                            Menu akan ditempatkan setelah menu yang dipilih
                        </small>

                    </div>

                </div>
                <?php if (!isset($menu)) { ?>

                    <hr class="my-4">

                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input"
                            type="checkbox"
                            id="toggleGenerator"
                            name="use_generator"
                            value="1">

                        <label class="form-check-label fw-semibold">
                            Aktifkan CRUD Generator
                        </label>
                    </div>

                    <div id="generatorBox" style="display:none;">

                        <h6 class="fw-bold mb-3">Generator CRUD</h6>

                        <div class="row">

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Nama Controller</label>
                                <input type="text"
                                    name="controller"
                                    class="form-control"
                                    placeholder="User">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Nama Model</label>
                                <input type="text"
                                    name="model"
                                    class="form-control"
                                    placeholder="User">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold">Nama Folder View</label>
                                <input type="text"
                                    name="view"
                                    class="form-control"
                                    placeholder="users">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Pilih Tabel Database</label>

                                <select name="tables[]" class="form-select" multiple size="6">

                                    <?php foreach ($tables as $t): ?>
                                        <option value="<?= htmlspecialchars($t) ?>">
                                            <?= htmlspecialchars($t) ?>
                                        </option>
                                    <?php endforeach; ?>

                                </select>

                            </div>

                        </div>
                    </div>

                <?php } ?>
                <div class="mt-4 d-flex justify-content-between align-items-center">

                    <button type="submit" class="btn btn-primary px-4">
                        <?= isset($menu) ? 'Update' : 'Simpan' ?>
                    </button>

                    <a href="?action=menus.index" class="btn btn-secondary px-4">
                        Kembali
                    </a>

                </div>

            </form>
        </div>
    </div>
</div>
<script>
    /* TOGGLE GENERATOR */

    const toggle = document.getElementById("toggleGenerator");
    const box = document.getElementById("generatorBox");

    if (toggle) {
        toggle.addEventListener("change", function() {

            box.style.display = this.checked ? "block" : "none";

        });
    }



    const parentMenu = document.getElementById("parentMenu");
    const targetMenu = document.getElementById("targetMenu");

    function loadTargetMenu(parentId) {

        targetMenu.innerHTML = '<option>Loading...</option>';

        fetch(`?action=menus.getMenusByParent&parent_id=${parentId}`)

            .then(res => res.json())

            .then(res => {

                targetMenu.innerHTML = '<option value="">-- Paling Bawah --</option>';

                if (res.success) {

                    res.data.forEach(menu => {

                        const opt = document.createElement("option");

                        opt.value = menu.id;
                        opt.textContent = menu.name;

                        targetMenu.appendChild(opt);

                    });

                }

            })

            .catch(() => {
                targetMenu.innerHTML = '<option value="">Gagal load menu</option>';
            });

    }

    if (parentMenu) {

        parentMenu.addEventListener("change", function() {

            const parentId = this.value;

            loadTargetMenu(parentId);

        });

    }

    /* load pertama (edit mode) */
    if (parentMenu) {
        loadTargetMenu(parentMenu.value);
    }
</script>