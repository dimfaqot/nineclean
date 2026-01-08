<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>


<div class="main"></div>

<script>
    loading();
    let tahuns = [];
    let bulans = [];
    let datas = [];
    let jenis = [];
    let divisions = [];
    let divisi = "Kantin";
    let barangs = [];

    let main = (tahun, bulan) => {
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

        html += `
        <div class="d-flex justify-content-center gap-3 mb-3">
        <div class="input-group">
        <label class="input-group-text bg-dark text-light">Tahun</label>
                        <select class="form-select bg-dark text-light border-secondary rounded" name="tahun">`;
        tahuns.forEach((e, i) => {
            html += `<option ${(e.tahun==tahun?"selected":"")} value="${e.tahun}">${e.tahun}</option>`;

        })
        html += `</select>
                    </div>`;

        html += `<div class="input-group">
         <label class="input-group-text bg-dark text-light">Bulan</label>
                        <select class="form-select bg-dark text-light border-secondary rounded" name="bulan">`;
        bulans.forEach((e, i) => {
            html += `<option ${(e.satuan==bulan?"selected":"")} value="${e.satuan}">${e.bulan}</option>`;

        })
        html += `</select>
                    </div>
                    <button class="btn btn-light btn_show">Show</button>
                    </div>`;

        html += `<div class="input-group input-group-sm mb-2">
                <input type="text" class="form-control bg-dark text-light border-secondary cari_card" placeholder="Cari..." aria-label="Recipient's username" aria-describedby="button-addon2">
                <button class="btn btn-outline-light form_input" data-order="Add" type="button"><i class="fa-solid fa-circle-plus"></i> <?= menu()['menu']; ?></button>
            </div>`;

        datas.data.forEach((e, i) => {
            html += `
                                <div class="card text-bg-dark mb-3" data-menu="${e.barang}">
                                    <div class="card-header">${angka((i+1))}. ${e.barang} - [${angka(e.biaya)}]</div>
                                    <div class="card-body d-flex justify-content-between ps-4">
                                        <div class="text-secondary"><small>${angka(e.harga)} [${angka(e.qty)}] - ${e.jenis}</small></div>
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
    let show = (tahun = undefined, bulan = undefined) => {
        let thn = (tahun === undefined ? "<?= date('Y') ?>" : tahun);
        let bln = (bulan === undefined ? "<?= date('n') ?>" : bulan);
        let data = {
            order: "Show",
            id: 0,
            divisi,
            tahun: thn,
            bulan: bln,
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
                        tahuns = res.data2;
                        bulans = res.data3;
                        divisions = res.data4;
                        barangs = res.data5;
                        main(thn, bln);

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
        let html = ``;
        if (order == "Add") {
            html = `<div class="form-floating mb-3">
                        <select class="form-select bg-dark text-light border-secondary rounded" name="jenis">`;
            datas.sub_menu.forEach((e, i) => {
                html += `<option ${(i==0?"selected":"")} value="${e}">${e}</option>`;

            })
            html += `</select>
                        <label class="text-secondary">Jenis</label>
                    </div>`;
        } else {
            html += `<div class="form-floating mb-3">
                        <input type="text" name="jenis" value="${data.jenis}" class="form-control bg-dark text-light" placeholder="Jenis" readonly>
                        <label class="text-secondary">Jenis</label>
                    </div>`;
        }

        html += `<div class="form-floating mb-3">
                        <input type="text" name="pj" ${(order=="Edit"?'value="'+data.pj+'"':"")} class="form-control bg-dark text-light" placeholder="Pj" required>
                        <label class="text-secondary">Pj</label>
                    </div>
                    <div class="form-floating mb-3 position-relative">
                        <input type="text" name="barang" data-order="${order}" ${(order=="Edit"?'value="'+data.barang+'"':"")} class="form-control bg-dark text-light" placeholder="Barang" required>
                        <label class="text-secondary">Barang</label>
                        <div class="bg-dark text-light body_list_hasil position-absolute top-12 start-0 w-100" style="z-index:1"></div>
                    </div>`;

        html += `
                <input name="barang_id" type="hidden">
                <div class="form-floating mb-3">
                        <input type="text" name="harga" ${(order=="Edit"?'value="'+angka(data.harga)+'"':"")} class="form-control bg-dark text-light angka harga cari_biaya" placeholder="Harga" required>
                        <label class="text-secondary">Harga</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="qty" value="${(order=="Edit"?angka(data.qty):"1")}" class="form-control bg-dark text-light angka qty cari_biaya" placeholder="Qty" required>
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

    $(document).on('click', '.list_hasil', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let barang_id = $(this).data("barang_id");
        let order = $(this).data("otrder");
        $('input[name="barang"]').val($(this).data("barang"));
        $('input[name="barang_id"]').val($(barang_id));
        $(".body_list_hasil").removeClass("border rounded border-light");
        $(".body_list_hasil").html("");
        $(".submit").attr("data-barang_id", barang_id);

    });

    $(document).on('keyup', 'input[name="barang"]', function(e) {
        e.preventDefault();
        let text = $(this).val();
        let order = $(this).data("order");
        let jenis = $('select[name="jenis"]').val();
        let hasil = barangs.filter(item =>
            item.jenis == jenis && item.barang.toLowerCase().includes(text.toLowerCase())
        );
        if (text == "") {
            $(".body_list_hasil").removeClass("border rounded border-light");
            $(".body_list_hasil").html("");
        } else if (hasil.length == 0) {
            $(".body_list_hasil").html('<div class="text-muted text-start">No data found</div>').show();
            $(".body_list_hasil").removeClass("border rounded border-light");
        } else {
            let html = '';
            hasil.forEach(e => {
                html += `
                            <div class="list_hasil" data-order="${order}" data-id="${e.id}" data-barang_id="${e.id}"  data-barang="${e.barang}">
                                <div class="d-flex justify-content-between">
                                    <span>${e.barang}</span>
                                    <span class="text-muted">${angka(e.harga)} [${angka(e.qty)}]</span>
                                </div>
                            </div>`;
            });
            $('.body_list_hasil').html(html).show();
        }

        $(".body_list_hasil").addClass("border rounded border-light");
    });


    $(document).on('keyup', '.cari_biaya', function(e) {
        e.preventDefault();
        let cb = cari_biaya();
        $(".harga").val(angka(cb.harga));
        $(".qty").val(angka(cb.qty));
        $(".diskon").val(angka(cb.diskon));
        $(".total").val(angka(cb.harga * cb.qty));
        $(".biaya").val(angka((cb.harga * cb.qty) - cb.diskon));

    });
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

        $('input[name="barang"]').focus();
    });

    $(document).on('click', '.submit', function(e) {
        e.preventDefault();
        if (!cek_form()) {
            return;
        }

        let tahun = $('select[name="tahun"]').val();
        let bulan = $('select[name="bulan"]').val();
        let order = $(this).data("order");
        let id = $(this).data("id");
        let barang_id = $(this).data("barang_id");


        let cb = cari_biaya();

        let data = {
            order,
            id,
            barang_id,
            harga: cb.harga,
            tahun,
            divisi,
            bulan,
            qty: cb.qty,
            total: cb.qty * cb.harga,
            pj: $('input[name="pj"]').val(),
            diskon: cb.diskon,
            biaya: (cb.harga * cb.qty) - cb.diskon,
            tabel: "pengeluaran",
            kategori: "Kantin",
            format: "array"
        };

        if (data.diskon > data.biaya) {
            message("400", "Diskon terlalu besar");
            blink("diskon");
            return;
        }

        loading();
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`pengeluaran/${req.data}`).then(res => {
                    message(res.status, res.message);
                    if (res.status == "200") {
                        show(tahun, bulan);
                        canvas.hide();

                    }
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })
    });

    $(document).on('change', 'input[name="divisi"]', function(e) {
        e.preventDefault();
        let val = $(this).val();
        divisi = val;
        let tahun = $('select[name="tahun"]').val();
        let bulan = $('select[name="bulan"]').val();
        show(tahun, bulan);

    });

    $(document).on('click', '.btn_show', function(e) {
        e.preventDefault();
        let tahun = $('select[name="tahun"]').val();
        let bulan = $('select[name="bulan"]').val();
        let div = $('input[name="divisi"]').val();
        divisi = div;
        show(tahun, bulan);
    });
</script>
<?= $this->endSection() ?>