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
            <button class="btn btn-light data" data-order="<?= url();  ?>" data-kategori="Inv" data-jenis="All"><i class="fa-solid fa-list"></i></button>
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
                    html += `<button class="btn btn-sm btn-danger delete" data-is_show="show" data-id="${e.id}" data-message="Yakin hapus?" data-tabel="pengeluaran" data-kategori="Inv" data-is_reload="">Delete</button>`;
                } else {
                    html += `<button class="btn btn-sm btn-secondary" style="width:60px"><i class="fa-solid fa-hand"></i></button>`;

                }

            } else {
                html += `<button class="btn btn-sm btn-danger delete" data-is_show="show" data-id="${e.id}" data-message="Yakin hapus?" data-kategori="Inv" data-tabel="pengeluaran" data-is_reload="">Delete</button>`;

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
            kategori: "Inv",
            format: "array",
            tabel: 'pengeluaran'
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`inv/${req.data}`).then(res => {
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
                        <select class="form-select bg-dark text-light border-secondary rounded" name="jenis">`;
        if (role == "Root" || role == "Advisor") {
            options.forEach(e => {
                html += `<option ${(e==data.jenis?"selected":"")} value="${e}">${e}</option>`;
            })
        } else {
            options.forEach(e => {
                if (e !== "Modal" && e !== "Bisyaroh") {
                    html += `<option ${(e==data.jenis?"selected":"")} value="${e}">${e}</option>`;

                }
            })

        }
        html += `</select>
                        <label class="text-secondary">Jenis</label></div>`;
        html += `<div class="form-floating mb-3">
                        <input type="text" name="pj" ${(order=="Edit"?'value="'+data.pj+'"':"")} class="form-control bg-dark text-light" data-order="${order}" data-id="${id}" placeholder="Pj" required>
                        <label class="text-secondary">Pj</label>
                    </div>
                <div class="form-floating mb-3">
                        <input type="text" name="barang" ${(order=="Edit"?'value="'+data.barang+'"':"")} class="form-control bg-dark text-light text-light" data-order="${order}" data-id="${id}" placeholder="Barang" required>
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
            jenis: $('select[name="jenis"]').val(),
            pj: $('input[name="pj"]').val(),
            barang: $('input[name="barang"]').val(),
            harga: $('input[name="harga"]').val(),
            qty: $('input[name="qty"]').val(),
            diskon: $('input[name="diskon"]').val(),
            total: $('input[name="total"]').val(),
            biaya: $('input[name="biaya"]').val(),
            tabel: "pengeluaran",
            kategori: "Inv",
            format: "array"
        };

        // console.log(data);
        // return;
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`inv/${req.data}`).then(res => {
                    message(res.status, res.message);
                    if (res.status == "200") {
                        datas = res;
                        show("show");

                    }
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })
    });


    $(document).on('blur', 'input[name="barang"]', function(e) {
        e.preventDefault();
        $('input[name="harga"]').val(0);
        $('input[name="qty"]').val(1);
        $('input[name="diskon"]').val(0);
        $('input[name="total"]').val(0);
        $('input[name="biaya"]').val(0);
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