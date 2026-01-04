<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>

<div class="input-group input-group-sm mb-2">
    <input type="text" class="form-control bg-dark text-light border-secondary cari_card" placeholder="Cari..." aria-label="Recipient' s username" aria-describedby="button-addon2">
    <button class="btn btn-outline-light form_input" data-order="Add" type="button"><i class="fa-solid fa-circle-plus"></i> <?= menu()['menu']; ?></button>
</div>
<div class="main"></div>

<script>
    loading();
    let roles = [];
    let datas = [];
    const show = () => {
        let data = {
            order: "Show",
            id: 0,
            kategori: "Role",
            format: "array",
            tabel: 'user'
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`user/${req.data}`).then(res => {
                    if (res.status == "200") {
                        datas = res.data;
                        roles = res.data2;
                        let html = ``;
                        res.data.forEach((e, i) => {
                            html += `<div class="card text-bg-dark mb-3" data-menu="${e.nama}">
                                        <div class="card-header">${(i+1)}. ${e.nama}</div>
                                        <div class="card-body d-flex justify-content-between ps-4">
                                            <div class="text-secondary"><small>${e.role}</small></div>
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
                        <select class="form-select bg-dark text-light border-secondary rounded" name="role" required>`;
        if (order == "Add") {
            html += `<option selected value="">Pilih Role</option>`;
        }
        roles.forEach(e => {
            html += `<option ${(e==data.role?"selected":"")} value="${e}">${e}</option>`;

        })
        html += `</select>
                        <label class="text-secondary">Role</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" name="nama" ${(order=="Edit"?'value="'+data.nama+'"':"")} class="form-control bg-dark text-light" placeholder="Nama" required>
                        <label class="text-secondary">Nama</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="username" ${(order=="Edit"?'value="'+data.username+'"':"")} class="form-control bg-dark text-light" placeholder="Username">
                        <label class="text-secondary">Username</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="wa" ${(order=="Edit"?'value="'+data.wa+'"':"")} class="form-control bg-dark text-light" placeholder="W.a" required>
                        <label class="text-secondary">W.a</label>
                    </div>`;
        if (order == "Edit") {
            html += `<input type="hidden" name="id" value="${data.id}">
                    <div class="form-floating mb-3">
                        <input type="text" name="password" value="" class="form-control bg-dark text-light" placeholder="Password">
                        <label class="text-secondary">Password</label>
                    </div>`;
        }
        html += ` <div class="form-floating mb-3">
                        <input type="text" name="db" ${(order=="Edit"?'value="'+data.db+'"':"")} class="form-control bg-dark text-light dbs" data-id="${id}" placeholder="Dbs" required readonly>
                        <label class="text-secondary">Db</label>
                    </div>
                    <div class="d-grid">
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
            role: $('select[name="role"]').val(),
            nama: $('input[name="nama"]').val(),
            username: $('input[name="username"]').val(),
            wa: $('input[name="wa"]').val(),
            password: $('input[name="password"]').val(),
            db: $('input[name="db"]').val(),
            tabel: "user"
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`user/${req.data}`).then(res => {
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

    $(document).on('keyup', '.cari_db', function(e) {
        e.preventDefault();
        let keyword = $(this).val().toLowerCase();

        $(".form-check-inline").filter(function() {
            // ambil teks label
            let text = $(this).find("label").text().toLowerCase();
            // tampilkan hanya yang cocok
            $(this).toggle(text.indexOf(keyword) > -1);
        });
    });

    $(document).on('click', '.dbs', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let dbs = <?= json_encode(get_dbs()); ?>;
        let dbs_user = $(this).val().split(",");


        let html = build_html("Add Db", "modal");
        html += `<div class="container">
                        <div class="input-group input-group-sm mb-2">
                        <input type="text" class="form-control bg-dark text-light border-secondary cari_db" placeholder="Cari..." aria-label="Recipient' s username" aria-describedby="button-addon2">
                        <label class="btn btn-outline-light" type="button">Cari Db</label>
                    </div>`;
        dbs.forEach(e => {
            html += `<div class="form-check form-check-inline">
                        <input class="form-check-input" name="dbs" type="checkbox" value="${e}" ${(dbs_user.includes(e)?"checked":"")}>
                        <label class="form-check-label">${upper_first(e)}</label>
                    </div>
                    `;
        })
        html += `<div class="d-grid mt-3"><button class="btn btn-sm btn-light save_dbs">Save</button></div></div>`;
        $(".body_modal").html(html);
        modal.show();
    });
    $(document).on('click', '.save_dbs', function(e) {
        e.preventDefault();
        const checked = document.querySelectorAll('input[name="dbs"]:checked');
        const values = Array.from(checked).map(cb => cb.value);

        $('input[name="db"]').val(values.join(","));
        modal.hide();
    });
</script>
<?= $this->endSection() ?>