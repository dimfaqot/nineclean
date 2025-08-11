<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>

<div class="input-group input-group-sm mb-2">
    <input type="text" class="form-control bg-dark text-light border-secondary cari_card" placeholder="Cari..." aria-label="Recipient's username" aria-describedby="button-addon2">
    <button class="btn btn-outline-light form_input" data-order="Add" type="button"><i class="fa-solid fa-circle-plus"></i> <?= menu()['menu']; ?></button>
</div>
<?php foreach ($data as $k => $i): ?>
    <div class="card text-bg-dark mb-3" data-menu="<?= $i['nama']; ?>">
        <div class="card-header"><?= ($k + 1) . ". " . $i['nama']; ?></div>
        <div class="card-body d-flex justify-content-between ps-4">
            <div class="text-secondary"><small><?= $i['value']; ?></small></div>
            <div>
                <button class="btn btn-sm btn-light me-2 form_input" data-order="Edit" data-id="<?= $i['id']; ?>">Edit</button>
                <button class="btn btn-sm btn-danger delete" data-id="<?= $i['id']; ?>" data-message="Yakin hapus data ini?" data-tabel="<?= menu()['tabel']; ?>" data-is_reload="reload">Delete</button>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    let form_input = (order, id) => {

        let data = {};
        if (order == "Edit") {
            let val = <?= json_encode($data); ?>;
            val.forEach(e => {
                if (e.id == id) {
                    data = e;
                    return;
                }
            });
        }
        let html = `<div class="form-floating mb-3">
                        <input type="text" name="nama" ${(order=="Edit"?'value="'+data.nama+'"':"")} class="form-control bg-dark text-light" placeholder="Nama" required>
                        <label class="text-secondary">Nama</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="value" ${(order=="Edit"?'value="'+data.value+'"':"")} class="form-control bg-dark text-light" placeholder="Value" required>
                        <label class="text-secondary">Value</label>
                    </div>`;
        if (order == "Edit") {
            html += `<input type="hidden" name="id" value="${data.id}">`;
        }
        html += `<div class="d-grid">
                        <button type="submit" class="btn btn-outline-info">Simpan</button>
                    </div>`

        return html;
    }

    $(document).on('click', '.form_input', function(e) {
        e.preventDefault();
        loading();
        let order = $(this).data("order");
        let id = $(this).data("id");

        let html = build_html(order, "offcanvas");


        html += `<div class="container">
                        <form method="post" action="<?= base_url(menu()['controller'] . "/"); ?>${order.toLowerCase()}">`;
        html += form_input(order, id);
        html += `</form>
                    </div>`;

        $(".body_canvas").html(html);
        loading("close");
        canvas.show();
    });
</script>
<?= $this->endSection() ?>