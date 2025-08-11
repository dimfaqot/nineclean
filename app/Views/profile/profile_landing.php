<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>

<form action="<?= base_url(menu()['controller']); ?>/edit" method="post">
    <div class="form-floating mb-2">
        <input type="text" class="form-control bg-dark text-light border border-warning" name="nama" value="<?= $data['nama']; ?>" placeholder="Nama">
        <label class="text-secondary">Nama</label>
    </div>
    <div class="form-floating mb-2">
        <input type="text" class="form-control bg-dark text-light border border-warning" name="pendiri" value="<?= $data['pendiri']; ?>" placeholder="Pendiri">
        <label class="text-secondary">Pendiri</label>
    </div>
    <div class="form-floating mb-2">
        <input type="text" class="form-control bg-dark text-light border border-warning" name="manager" value="<?= $data['manager']; ?>" placeholder="Manager">
        <label class="text-secondary">Manager</label>
    </div>
    <div class="form-floating mb-2">
        <input type="date" class="form-control bg-dark text-light border border-warning" name="tgl_berdiri" value="<?= date('Y-m-d', $data['tgl_berdiri']); ?>">
        <label class="text-secondary">Tgl. Berdiri</label>
    </div>
    <div class="form-floating mb-2">
        <input type="text" class="form-control bg-dark text-light border border-warning" name="sub_unit" value="<?= $data['sub_unit']; ?>" placeholder="Sub Unit">
        <label class="text-secondary">Sub Unit</label>
    </div>
    <div class="form-floating mb-3 detail" style="cursor: pointer;">
        <input type="text" class="form-control bg-dark text-light border border-secondary" value="<?= angka(uang_modal()['total']); ?>" readonly>
        <label class="text-secondary">Jml. Modal</label>
    </div>
    <div class="form-floating mb-3">
        <input type="text" class="form-control bg-dark text-light border border-warning" name="modal_asal" value="<?= $data['modal_asal']; ?>" placeholder="Asal Modal">
        <label class="text-secondary">Asal Modal</label>
    </div>
    <input type="hidden" name="id" value="<?= $data['id']; ?>">

    <div class="d-grid">
        <button type="submit" class="btn btn-lg btn-secondary">Save</button>
    </div>
</form>

<script>
    $(document).on('click', '.detail', function(e) {
        e.preventDefault();
        let data = <?= json_encode(uang_modal()['data']); ?>;
        let html = build_html("DETAIL MODAL", "offcanvas");
        html += `<input class="form-control form-control-sm bg-dark text-light cari mb-2" placeholder="Cari">
                    <table class="table table-sm table-dark" style="font-size:12px">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Tgl</th>
                                <th class="text-center">Barang</th>
                                <th class="text-center">qty</th>
                                <th class="text-center">Biaya</th>
                            </tr>
                        </thead>
                        <tbody class="tabel_search">`;
        data.forEach((e, i) => {
            html += `<tr>
                                <th scope="row">${(i+1)}</th>
                                <td>${time_php_to_js(e.tgl)}</td>
                                <td class="text-start">${e.barang}</td>
                                <td>${angka(e.qty)}</td>
                                <td class="text-end">${angka(e.biaya)}</td>
                            </tr>`;
        })
        html += `</tbody>
                    </table>`;
        $(".body_canvas").html(html);
        canvas.show();
    });

    $(document).on('keyup', '.cari', function(e) {
        e.preventDefault();
        let value = $(this).val().toLowerCase();
        $('.tabel_search tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });

    });
</script>
<?= $this->endSection() ?>