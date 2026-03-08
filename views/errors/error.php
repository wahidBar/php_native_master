<div class="content-area d-flex justify-content-center align-items-center">

    <div class="text-center px-3" style="max-width:600px;">

        <h1 class="display-1 fw-bold text-danger mb-3">
            <?= htmlspecialchars($code) ?>
        </h1>

        <h3 class="mb-3 fw-semibold">
            <?= htmlspecialchars($title) ?>
        </h3>

        <?php if (!empty($message)): ?>
            <p class="text-muted mb-4 fs-5">
                <?= nl2br(htmlspecialchars($message)) ?>
            </p>
        <?php endif; ?>

        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="javascript:history.back()" class="btn btn-outline-secondary px-4">
                ← Kembali
            </a>
            <a href="?action=dashboard" class="btn btn-primary px-4">
                Ke Dashboard
            </a>
        </div>

    </div>

</div>
