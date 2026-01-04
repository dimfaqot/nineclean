<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>

<div class="d-flex mb-3">
    <div class="p-2 flex-fill">
        <div class="text-warning text-center">
            <div class="mb-1">TOTAL</div>
            <input type="text" value="" class="form-control super_total bg-warning fw-bold text-center text-dark border border-light border-3">
        </div>
    </div>

    <div class="p-2 flex-fill">
        <div class="mb-1 text-center">DATA</div>
        <div class="d-grid">
            <button class="btn btn-light data" data-order="<?= url();  ?>" data-kategori="Kantin" data-jenis="All"><i class="fa-solid fa-list"></i></button>
        </div>
    </div>
</div>
<div class="input-group input-group-sm mb-2">
    <input type="text" class="form-control bg-dark text-light border-secondary cari_card" placeholder="Cari..." aria-label="Recipient's username" aria-describedby="button-addon2">
    <button class="btn btn-outline-light form_input" data-order="Add" type="button"><i class="fa-solid fa-circle-plus"></i> <?= menu()['menu']; ?></button>
</div>

<div class="main"></div>
<script>
    loading();
    let datas = [];
    let tahuns = [];
    let bulans = [];
    let options = [];
    let barang_id = 0;

    let html_main = (data, total) => {


        $(".super_total").val(angka(total));

        let html = '';
        data.forEach((e, i) => {
            html += `
                                <div class="card text-bg-dark mb-3" data-menu="${e.barang}">
                                    <div class="card-header">${angka((i+1))}. ${e.barang}</div>
                                    <div class="card-body d-flex justify-content-between ps-4">
                                        <div class="text-secondary"><small>${time_php_to_js(e.tgl)} [${angka(e.biaya)}] [${angka(e.qty)}]</small></div>
                                        <div>
                                            <button class="btn btn-sm btn-light me-2 form_input" data-order="Edit" data-id="${e.id}">Edit</button>`;
            if (e.jenis == "Bisyaroh" || e.jenis == "Modal") {
                if (role == "Root" || role == "Advisor") {
                    html += `<button class="btn btn-sm btn-danger delete" data-kategori="Kantin" data-is_show="show" data-id="${e.id}" data-message="Yakin hapus?" data-tabel="pengeluaran" data-is_reload="">Delete</button>`;
                } else {
                    html += `<button class="btn btn-sm btn-secondary" style="width:60px"><i class="fa-solid fa-hand"></i></button>`;

                }

            } else {
                html += `<button class="btn btn-sm btn-danger delete" data-kategori="Kantin" data-is_show="show" data-id="${e.id}" data-message="Yakin hapus?" data-tabel="pengeluaran" data-is_reload="">Delete</button>`;

            }

            html += `</div>
                                    </div>
                                </div>
                            `;

        })

        $(".main").html(html);
        setTimeout(() => {
            loading("close");
        }, 300);
        canvas.hide();
        return;

    }

    let show = () => {

        let data = {
            order: "Show",
            id: 0,
            kategori: "Kantin",
            format: "array",
            tabel: 'pengeluaran'
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`pengeluaran/${req.data}`).then(res => {
                    loading("close");
                    if (res.status == "200") {
                        datas = res.data;
                        tahuns = res.data2;
                        bulans = res.data3;
                        options = datas.sub_menu;
                        html_main(datas.data, datas.total);

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
            datas.data.forEach(e => {
                if (e.id == id) {
                    data = e;
                    return;
                }
            });
        }

        let html = `
                <div class="form-floating mb-3">
                        <input type="text" name="pj" ${(order=="Edit"?'value="'+data.pj+'"':"")} class="form-control bg-dark text-light" data-order="${order}" data-id="${id}" placeholder="Pj" required>
                        <label class="text-secondary">Pj</label>
                    </div>
                <div class="form-floating mb-3">
                        <input type="text" name="barang" ${(order=="Edit"?'value="'+data.barang+'"':"")} class="form-control bg-dark border-warning text-light text-light" data-order="${order}" data-id="${id}" placeholder="Barang" readonly required>
                        <label class="text-secondary">Barang</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="harga" ${(order=="Edit"?'value="'+angka(data.harga)+'"':"")} class="form-control bg-dark text-light angka harga cari_biaya" placeholder="Harga" required>
                        <label class="text-secondary">Harga</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="qty" ${(order=="Edit"?'value="'+angka(data.qty)+'"':"")} class="form-control bg-dark text-light angka qty cari_biaya" placeholder="Qty" required>
                        <label class="text-secondary">Qty</label>
                    </div>
                    <div class="form-floating mb-3">
                    <input type="text" name="diskon" ${(order=="Edit"?'value="'+angka(data.diskon)+'"':"")} class="form-control bg-dark text-light angka diskon cari_biaya" placeholder="Diskon" required>
                    <label class="text-secondary">Diskon</label>
                    </div>
                    <div class="form-floating mb-3">
                       <input type="text" name="total" ${(order=="Edit"?'value="'+angka(data.total)+'"':"")} class="form-control bg-dark border border-warning text-light total" placeholder="Total" required readonly>
                       <label class="text-secondary">Total</label>
                   </div>
                    <div class="form-floating mb-3">
                    <input type="text" name="biaya" ${(order=="Edit"?'value="'+angka(data.biaya)+'"':"")} class="form-control bg-dark border border-warning text-light biaya" placeholder="Biaya" required readonly>
                    <label class="text-secondary">Biaya</label>
                </div>`;

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

        html += `<div class="container">
                        <form method="post" action="<?= base_url(menu()['controller'] . "/"); ?>${order.toLowerCase()}">`;
        html += form_input(order, id);
        html += `</form>
                    </div>`;

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
            barang_id,
            pj: $('input[name="pj"]').val(),
            barang: $('input[name="barang"]').val(),
            harga: $('input[name="harga"]').val(),
            qty: $('input[name="qty"]').val(),
            diskon: $('input[name="diskon"]').val(),
            total: $('input[name="total"]').val(),
            biaya: $('input[name="biaya"]').val(),
            tabel: "pengeluaran",
            kategori: "Kantin",
            format: "array"
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`pengeluaran/${req.data}`).then(res => {
                    message(res.status, res.message);
                    if (res.status == "200") {
                        datas = res.data;
                        html_main(datas.data, datas.total);
                    }
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })
    });


    $(document).on('click', 'input[name="barang"]', function(e) {
        e.preventDefault();
        let id = $(this).data('id');
        let order = $(this).data('order');

        let html = `<div class="container">
                        <div class="form-floating position-relative">
                            <input type="text" class="form-control bg-dark text-light cari_barang" data-id="${id}" data-order="${order}" placeholder="Cari...">
                            <label class="text-secondary">Cari Produk</label>
                            <div class="bg-dark text-light body_list_barang"></div>
                        </div>
                    </div>`;
        $(".body_modal").html(html);
        modal.show();

        $('#main_modal').on('shown.bs.modal', () => {
            $('.cari_barang').trigger('focus').select();
        });

    });

    $(document).on('keyup', '.cari_barang', function(e) {
        e.preventDefault();
        let text = $(this).val().toLowerCase();
        let id = $(this).data("id");
        let order = $(this).data("order");
        let body_class_list = $('.body_list_barang');

        let data = {
            id,
            text,
            format: "array",
            order: "Cari Barang",
            filters: options
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`pengeluaran/${req.data}`).then(res => {
                    if (res.status == "200") {
                        if (res.data.length > 0) {
                            let html = '';
                            res.data.forEach(e => {
                                html += `
                            <div class="list_barang" data-barang_id="${e.id}" data-order="${order}" data-barang-id="${e.id}" data-barang="${e.barang}" data-id="${id}">
                                <div class="d-flex justify-content-between">
                                    <span>${e.barang}</span>
                                    <span class="text-muted">${angka(e.harga)} [${angka(e.qty)}]</span>
                                </div>
                            </div>`;
                            });
                            body_class_list.html(html).show();
                            loading("close");
                        } else {
                            body_class_list.html('<div class="list_hasil text-muted">No data found</div>').show();
                            loading("close");
                        }

                    }
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })

    });


    $(document).on('click', '.list_barang', function(e) {
        const id = $(this).data("id");
        const order = $(this).data("order");
        barang_id = $(this).data("barang_id");
        const barang = $(this).data("barang");
        console.log(barang_id);
        $('input[name="barang"]').val(barang);

        let harga = $(".harga").val();
        $(".harga").val((harga == "" ? 0 : harga));
        let qty = $(".qty").val();
        $(".qty").val((qty == "" ? 1 : qty));
        let diskon = $(".diskon").val();
        $(".diskon").val((diskon == "" ? 0 : diskon));
        let total = $(".total").val();
        $(".total").val((total == "" ? 0 : total));
        let biaya = $(".biaya").val();
        $(".biaya").val((biaya == "" ? 0 : biaya));


        $('.body_list_barang').html("");
        $('.body_list_barang').hide();
        modal.hide();
    });

    const biaya = () => {
        let harga = $(".harga").val();
        harga = (harga == "" ? "0" : harga);
        harga = angka_to_int(harga);

        let qty = $(".qty").val();
        qty = (qty == "" ? "1" : qty);
        qty = angka_to_int(qty);

        let diskon = $(".diskon").val();
        diskon = (diskon == "" ? "0" : diskon);
        diskon = angka_to_int(diskon);

        $(".total").val(angka(harga * qty));

        $(".biaya").val(angka(((harga * qty) - diskon)));

    }

    $(document).on('keyup', '.cari_biaya', function(e) {
        e.preventDefault();
        biaya();
    });
</script>
<?= $this->endSection() ?>