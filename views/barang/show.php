<div class="container-fluid py-4">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h5 class="fw-bold mb-0">Detail Barang</h5>
        </div>

        <div class="card-body">

            <table class="table table-bordered">
                <tr>
                    <th width="200">Kode Barang</th>
                    <td><?= htmlspecialchars($barang['kodebarang']) ?></td>
                </tr>
                <tr>
                    <th>Nama Barang</th>
                    <td><?= htmlspecialchars($barang['namabarang']) ?></td>
                </tr>
                <tr>
                    <th>Tipe Barang</th>
                    <td>
                        <?php
                        $map = [
                            'TT' => 'Aset Tetap',
                            'HP' => 'Aset Lancar',
                            'JS' => 'Bahan dan Jasa',
                            'KH' => 'Khusus'
                        ];
                        echo $map[$barang['tipebarang']] ?? '-';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>Aktiva</th>
                    <td><?= htmlspecialchars($barang['aktiva'] ?? '-') ?></td>
                </tr>
                <tr>
                    <th>Satuan</th>
                    <td><?= htmlspecialchars($barang['idsatuan']) ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td><?= $barang['statuskode'] == 1 ? 'Aktif' : 'Non Aktif' ?></td>
                </tr>
            </table>

            <a href="?action=barang.index" class="btn btn-secondary">
                Kembali
            </a>

        </div>
    </div>

</div>
