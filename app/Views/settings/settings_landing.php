<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>

<div class="input-group input-group-sm mb-2">
    <input type="text" class="form-control bg-dark text-light border-secondary cari_card" placeholder="Cari..." aria-label="Recipient's username" aria-describedby="button-addon2">
    <button class="btn btn-outline-light form_input" data-order="Add" type="button"><i class="fa-solid fa-circle-plus"></i> <?= menu()['menu']; ?></button>
</div>
<div class="main"></div>


<script>
    loading();
    let datas = [];
    const show = () => {
        let data = {
            order: "Show",
            id: 0,
            tabel: 'settings'
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`settings/${req.data}`).then(res => {
                    if (res.status == "200") {
                        datas = res.data;
                        let html = ``;
                        res.data.forEach((e, i) => {
                            html += `<div class="card text-bg-dark mb-3" data-menu="${e.nama}">
                                        <div class="card-header">${(i+1)}. ${e.nama}</div>
                                        <div class="card-body d-flex justify-content-between ps-4">
                                            <div class="text-secondary"><small>${e.value}</small></div>
                                            <div>
                                                <button class="btn btn-sm btn-light me-2 form_input" data-order="Edit" data-id="${e.id}">Edit</button>
                                                <button class="btn btn-sm btn-danger delete" data-id="${e.id}" data-message="Yakin hapus data ini?" data-tabel="<?= menu()['tabel']; ?>" data-is_reload="reload">Delete</button>
                                            </div>
                                        </div>
                                    </div>`;

                        })
                        $(".main").html(html);

                        loading("close");
                    } else {
                        loading("close");
                        message(res.status, res.message);
                    }
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })

    }

    setTimeout(() => {
        show();
    }, 120);
    let form_input = (order, id) => {

        let data = {};
        if (order == "Edit") {

            datas.forEach(e => {
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
                        <button type="button" data-order="${order}" data-id="${id}" class="btn btn-outline-info submit">Simpan</button>
                    </div>`

        return html;
    }

    $(document).on('click', '.form_input', function(e) {
        e.preventDefault();
        loading();
        let order = $(this).data("order");
        let id = $(this).data("id");


        let html = build_html(order, "offcanvas");


        html += `<div class="container">`;
        html += form_input(order, id);
        html == `</div>`;

        $(".body_canvas").html(html);
        loading("close");
        canvas.show();
    });


    $(document).on('click', '.submit', function(e) {
        e.preventDefault();
        if (!cek_form()) {
            return;
        }
        loading();
        let order = $(this).data("order");
        let id = $(this).data("id");

        let data = {
            order,
            id,
            nama: $('input[name="nama"]').val(),
            value: $('input[name="value"]').val(),
            tabel: "settings"
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            loading("close");
            if (req.status == "200") {
                fetchData(`settings/${req.data}`).then(res => {
                    loading("close");
                    message(res.status, res.message);
                    if (res.status == "200") {
                        setTimeout(() => {
                            location.reload();
                        }, 1000);

                    }
                })
            } else {
                loading("close");
                message(res.status, res.message);
            }
        })
    });
</script>
<?= $this->endSection() ?>