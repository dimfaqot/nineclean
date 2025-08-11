<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>

<div class="input-group input-group-sm mb-2">
    <input type="text" class="form-control bg-dark text-light border-secondary cari_card" placeholder="Cari..." aria-label="Recipient's username" aria-describedby="button-addon2">
    <button class="btn btn-outline-light form_input" data-order="Add" type="button"><i class="fa-solid fa-circle-plus"></i> <?= menu()['menu']; ?></button>
</div>



<?php foreach ($data as $k => $i): ?>
    <div class="card text-bg-dark mb-3" data-menu="<?= $i['menu']; ?>">
        <div class="card-header"><?= ($k + 1) . '. <i class="' . $i['icon'] . '"></i> ' . $i['menu']; ?></div>
        <div class="card-body d-flex justify-content-between ps-4">
            <div class="text-secondary"><small><?= $i['role']; ?></small></div>
            <div>
                <button class="btn btn-sm btn-outline-warning copy" data-role="<?= $i['role']; ?>" data-menu="<?= $i['menu']; ?>" data-id="<?= $i['id']; ?>">Copy</button>
                <button class="btn btn-sm btn-light mx-2 form_input" data-order="Edit" data-id="<?= $i['id']; ?>">Edit</button>
                <button class="btn btn-sm btn-danger delete" data-id="<?= $i['id']; ?>" data-message="Yakin hapus data ini?" data-tabel="<?= menu()['tabel']; ?>" data-is_reload="reload">Delete</button>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    let form_input = (order, id) => {
        let roles = <?= json_encode(options("Role")); ?>;
        let role = $(this).data("role");

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
                        <select class="form-select bg-dark text-light border-secondary rounded" name="role" required>`;
        if (order == "Add") {
            html += `<option selected value="" required>Pilih Role</option>`;
        }
        roles.forEach(e => {
            html += `<option ${(e.value==data.role?"selected":"")} value="${e.value}">${e.value}</option>`;

        })
        html += `</select>
                        <label class="text-secondary">Role</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="menu" ${(order=="Edit"?'value="'+data.menu+'"':"")} class="form-control bg-dark text-light" placeholder="Menu" required>
                        <label class="text-secondary">Menu</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="tabel" ${(order=="Edit"?'value="'+data.tabel+'"':"")} class="form-control bg-dark text-light" placeholder="Tabel" required>
                        <label class="text-secondary">Tabel</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="controller" ${(order=="Edit"?'value="'+data.controller+'"':"")} class="form-control bg-dark text-light" placeholder="Controller" required>
                        <label class="text-secondary">Controller</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="icon" ${(order=="Edit"?'value="'+data.icon+'"':"")} class="form-control bg-dark text-light" placeholder="Icon" required>
                        <label class="text-secondary">Icon</label>
                    </div>`;
        if (order == "Edit") {
            html += `<input type="hidden" name="id" value="${data.id}">
                    <div class="form-floating mb-3">
                        <input type="text" name="urutan" value="${data.urutan}" class="form-control bg-dark text-light" placeholder="Urutan" required>
                        <label class="text-secondary">Urutan</label>
                    </div>
                    `;
        }
        html += `<div class="form-floating mb-3">
                        <input type="text" name="grup" ${(order=="Edit"?'value="'+data.grup+'"':"")} class="form-control bg-dark text-light" placeholder="Grup" required>
                        <label class="text-secondary">Grup</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-info">Simpan</button>
                    </div>`

        return html;
    }

    $(document).on('click', '.form_input', function(e) {
        e.preventDefault();
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
    $(document).on('click', '.copy', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let role = $(this).data("role");
        let menu = $(this).data("menu");
        let roles = <?= json_encode(options("Role")); ?>;

        let html = build_html("Copy " + menu);

        html += `<div class="container">
                    <div class="form-floating mb-3">
                        <select class="form-select copy_role">
                            <option selected value="">Pilih Role</option>`;
        roles.forEach(e => {
            if (role !== e.value) {
                html += `<option value="${e.value}">${e.value}</option>`;
            }
        })
        html += `</select>
                        <label for="floatingSelect">Works with selects</label>
                    </div>        
                    <div class="d-grid"><button data-menu_id="${id}" class="btn btn-lg btn-secondary btn_copy"><i class="fa-solid fa-copy"></i> Copy</button></div>
                </div>`;


        $(".body_modal").html(html);
        loading("close");
        modal.show();


    });
    $(document).on('click', '.btn_copy', function(e) {
        e.preventDefault();
        let menu_id = $(this).data("menu_id");
        let role = $(".copy_role").val();

        post("menu/copy", {
            menu_id,
            role
        }).then(res => {
            loading("close");
            message(res.status, res.message);

            if (res.status == "200") {
                setTimeout(() => {
                    location.reload();
                }, 1200);
            }
        })
    });
</script>
<?= $this->endSection() ?>