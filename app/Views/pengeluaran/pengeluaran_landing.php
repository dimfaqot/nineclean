<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>


<div class="main"></div>

<script>
    loading();
    let status = "";
    let datas = [];
    let jenis = [];
    let divisions = [];
    let divisi = "Kantin"

    let main = (order = undefined) => {
        let html = '';

        if (divisions.length > 1) {
            html += `<div class="d-flex justify-content-center gap-2 my-3 py-2 bg-secondary rounded border">`;

            divisions.forEach(e => {
                html += `<div class="form-check form-switch">
                                <input class="form-check-input" type="radio" ${(e==divisi?"checked":"")} role="switch" name="divisi" value="${e}">
                                <label class="form-check-label">${e}</label>
                            </div>`;
            })

            html += `</div>`;

        }

        html += `<div class="input-group input-group-sm mb-2">
                <input type="text" class="form-control bg-dark text-light border-secondary cari_card" placeholder="Cari..." aria-label="Recipient's username" aria-describedby="button-addon2">
                <button class="btn btn-outline-light form_input" data-order="Add" type="button"><i class="fa-solid fa-circle-plus"></i> <?= menu()['menu']; ?></button>
            </div>`;

        datas.data.forEach((e, i) => {
            html += `
                                <div class="card text-bg-dark mb-3" data-menu="${e.barang}">
                                    <div class="card-header ${(e.link !==""?"text-warning":"")}">${angka((i+1))}. ${e.barang}  ${(e.link !==""?"("+str_replace(",", "-",e.barang)+")":"")}</div>
                                    <div class="card-body d-flex justify-content-between ps-4">
                                        <div class="text-secondary"><small>${angka(e.harga)} [${angka(e.qty)}] - ${e.jenis}/${e.tipe}</small></div>
                                        <div>
                                            <button class="btn btn-sm btn-light me-2 form_input" data-order="Edit" data-id="${e.id}">Edit</button>`;
            if (role == "Root") {
                html += `<button class="btn btn-sm btn-danger delete" data-id="${e.id}" data-is_show="show" data-message="Yakin hapus?" data-tabel="<?= menu()['tabel']; ?>" data-is_reload="">Delete</button>`;

            }

            html += `</div>
                                    </div>
                                </div>
                            `;

        })

        $(".main").html(html);
        canvas.hide();
        loading("close");
    }
    let show = () => {

        let data = {
            order: "Show",
            id: 0,
            divisi,
            jenis: "All",
            tabel: 'pengeluaran',
            kategori: "Kantin",
            format: "array"
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`pengeluaran/${req.data}`).then(res => {
                    loading("close");
                    if (res.status == "200") {
                        datas = res.data;
                        status = res.data2;
                        jenis = res.data3;
                        divisions = res.data4;
                        main();

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
    }, 100);

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
                        <select class="form-select bg-dark text-light border-secondary rounded" name="jenis">`;
        if (order == "Add") {
            html += `<option selected value="">Pilih Jenis</option>`;
        }
        datas.sub_menu.forEach((e, i) => {
            html += `<option ${(order=="Edit" && e==data.jenis?"selected":(i==0?"selected":""))} value="${e}">${e}</option>`;

        })
        html += `</select>
                        <label class="text-secondary">Jenis</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="barang" ${(order=="Edit"?'value="'+data.barang+'"':"")} class="form-control bg-dark text-light" placeholder="Barang" ${(order=="Add"?"required":(role=="Root"?"required":"readonly"))}>
                        <label class="text-secondary">Barang</label>
                    </div>`;



        if (order == "Edit") {
            if (status == "open") {
                html += `<div class="form-floating mb-3">
                        <input type="text" name="qty" ${(order=="Edit"?'value="'+angka(data.qty)+'"':"")} class="form-control bg-dark text-light angka" placeholder="Qty" ${(status == "open"?"required":"readonly")}>
                        <label class="text-secondary">Qty</label>
                    </div>`;
            }
            html += `<input type="hidden" name="id" value="${data.id}">`;
        }
        html += `<div class="form-floating mb-3">
                        <input type="text" name="harga" ${(order=="Edit"?'value="'+angka(data.harga)+'"':"")} class="form-control bg-dark text-light angka" placeholder="Harga Jual" required>
                        <label class="text-secondary">Harga ${(data.jenis=="Kulakan"?"Beli":"Jual")}</label>
                    </div>`;
        html += `<div class="form-floating mb-3">
                        <input type="text" name="links" ${(order=="Edit"?'value="'+data.barangs+'"':"")} data-order="${order}" class="form-control bg-dark text-light link_barang" placeholder="Link" readonly>
                        <label class="text-secondary">Link</label>
                    </div>`;

        html += `<input type="hidden" name="link">`;

        html += ` <div class="my-3 border border-light rounded p-2 d-flex justify-content-center">
                        <div class="form-check form-switch">
                            <input class="form-check-input" name="tipe" type="checkbox" role="switch" ${(order=="Edit"?(data.tipe=="Mix"?"checked":""):"")}>
                            <label class="form-check-label">Mix</label>
                        </div>
                    </div>`;

        html += `<div class="d-grid">
                        <button type="button" data-order="${order}" data-id="${id}" class="btn btn-outline-info submit">Simpan</button>
                    </div>`

        return html;
    }

    $(document).on('change', 'input[name="divisi"]', function(e) {
        e.preventDefault();
        let val = $(this).val();
        divisi = val;
        show();
    });
    $(document).on('change', 'input[name="barang"]', function(e) {
        e.preventDefault();
        let text = $(this).val();
        let jenis = $('select[name="jenis"]').val();

        let data = {
            order: "Cari Barang",
            text,
            jenis
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`pengeluaran/${req.data}`).then(res => {
                    message(res.status, res.message);
                    if (res.status == "200") {
                        datas = res.data;
                        main('show');

                    }
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })

    });

    $(document).on('click', '.save_checked', function(e) {
        e.preventDefault();
        let barangs = [];
        let ids = [];
        $('.barang_checked:checked').each(function() {
            barangs.push($(this).val());
            ids.push($(this).data("id"));
        });

        $('input[name="links"]').val(barangs.join(","));

        $('input[name="link"]').val(ids.join(","));
        modal.hide();
    });

    $(document).on('click', '.form_input', function(e) {
        e.preventDefault();
        loading();
        let order = $(this).data("o;rder");
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
            divisi,
            jenis: $('select[name="jenis"]').val(),
            barang: $('input[name="barang"]').val(),
            qty: $('input[name="qty"]').val(),
            harga: $('input[name="harga"]').val(),
            link: $('input[name="links"]').val(),
            tipe: ($('input[name="tipe"]').is(':checked') === true ? "on" : ""),
            tabel: "barang"
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`pengeluaran/${req.data}`).then(res => {
                    message(res.status, res.message);
                    if (res.status == "200") {
                        datas = res.data;
                        main('show');

                    }
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })
    });
</script>
<?= $this->endSection() ?>