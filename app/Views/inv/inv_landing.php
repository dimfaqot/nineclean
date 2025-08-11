<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>
<div class="d-flex mb-3">
    <div class="p-2 flex-fill">
        <div class="text-warning text-center">
            <div class="mb-1">TOTAL</div>
            <input type="text" value="0" class="form-control super_total bg-warning fw-bold text-center text-dark border border-light border-3">
        </div>
    </div>

    <div class="p-2 flex-fill">
        <div class="mb-1 text-center">INV</div>
        <div class="d-grid">
            <button class="btn btn-light lists" data-jenis="Inv"><i class="fa-solid fa-list"></i></button>
        </div>
    </div>
</div>


<div class="form-floating mb-2">
    <select class="form-select bg-dark text-light jenis">
        <option selected value="">Pilih Jenis</option>
        <?php foreach (options('Inv') as $i): ?>
            <option value="<?= $i['value']; ?>"><?= $i['value']; ?></option>
        <?php endforeach; ?>
    </select>
    <label>Pilih Jenis</label>
</div>
<div class="form-floating mb-2">
    <input type="text" class="form-control bg-dark text-light border border-warning pj">
    <label class="text-secondary">Pj</label>
</div>
<div class="form-floating mb-2">
    <input type="text" class="form-control bg-dark text-light border border-warning barang" value="">
    <label class="text-secondary">Barang</label>
</div>
<div class="form-floating mb-2">
    <input type="text" class="form-control bg-dark text-light border border-warning harga angka cari_biaya" data-order="Add" value="0">
    <label class="text-secondary">Harga</label>
</div>
<div class="form-floating mb-2">
    <input type="text" class="form-control bg-dark text-light qty angka cari_biaya" data-order="Add" value="1">
    <label class="text-secondary">Qty</label>
</div>
<div class="form-floating mb-2">
    <input type="text" class="form-control bg-dark text-light diskon angka cari_biaya" data-order="Add" value="0">
    <label class="text-secondary">Diskon</label>
</div>
<div class="form-floating mb-3">
    <input type="text" class="form-control bg-dark text-light border border-warning total" value="0" readonly>
    <label class="text-secondary">Total</label>
</div>
<div class="form-floating mb-3">
    <input type="text" class="form-control bg-secondary opacity-50 text-light fw-bold border border-warning biaya" value="0" readonly>
    <label class="text-light">Biaya</label>
</div>


<div class="d-flex gap-2">
    <div class="flex-grow-1">
        <button class="btn btn-outline-warning tambah_barang" style="width: 100%;"><i class="fa-solid fa-box-open"></i> TAMBAH INV</button>
    </div>
    <div><button class="btn btn-outline-info btn_save" style="width: 115px;"><i class="fa-solid fa-floppy-disk"></i> SAVE</button></div>
</div>


<table class="table table-borderless text-light table-sm mt-4" style="font-size: 12px;">
    <thead>
        <tr>
            <td>#</td>
            <td>Barang</td>
            <td>Harga</td>
            <td>Qty</td>
            <td>Del</td>
        </tr>
    </thead>
    <tbody class="list_items">

    </tbody>
</table>

<script>
    let barangs = [];
    let datas = [];

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


    }

    const blink = (cls, duration = 2000, interval = 300) => {
        let el = $("." + cls);
        let isOn = false;

        const blinkInterval = setInterval(() => {
            el.toggleClass("bg-dark bg-danger");
            isOn = !isOn;
        }, interval);

        // Hentikan blinking setelah `duration` ms
        setTimeout(() => {
            clearInterval(blinkInterval);
            el.removeClass("bg-danger").addClass("bg-dark"); // Reset ke awal
        }, duration);
    };

    const clear_input = () => {
        $(".barang").val("");
        $(".harga").val("0");
        $(".qty").val("1");
        $(".diskon").val("0");
        $(".total").val("0");
        $(".biaya").val("0");
    }

    const super_total = () => {
        let total = 0;
        let diskon = 0;
        let biaya = 0;
        datas.forEach(e => {
            total += e.total;
            diskon += e.diskon;
            biaya += e.biaya;
        })

        let res = {
            total,
            diskon,
            biaya
        }
        return res;
    }

    const list_items = () => {
        let html = "";
        datas.forEach((e, i) => {
            html += `<tr>
                <td>${(i+1)}</td>
                <td>${e.barang}</td>
                <td>${angka(e.harga)}</td>
                <td>${angka(e.qty)}</td>
                <td><a href="" class="text-danger delete_item" data-index="${i}" style="text-decoration:none"><i class="fa-solid fa-circle-xmark"></i></a></td>
            </tr>`;
        })

        return html;
    }

    $(document).on('click', '.delete_item', function(e) {
        e.preventDefault();
        let index = $(this).data("index");

        let temp_datas = [];
        datas.forEach((e, idx) => {
            if (idx != index) {
                temp_datas.push(e);
            }
        })

        datas = temp_datas;
        // let cb = cari_biaya();

        $(".list_items").html(list_items());
        $(".super_total").val(angka(super_total().biaya));
        $(".barang").focus();
    });

    $(document).on('click', '.tambah_barang', function(e) {
        e.preventDefault();

        let cb = cari_biaya();

        if ($(".harga").val() == 0 || $(".harga").val() == "") {
            message("400", "Harga kosong");
            blink('harga');
            return;
        }
        if ($(".barang").val() == 0 || $(".barang").val() == "") {
            message("400", "Barang kosong");
            blink('barang');
            return;
        }
        if (cb.diskon > (cb.harga * cb.qty)) {
            message("400", "Diskon over");
            blink('diskon');
            return;
        }

        let barang = {};
        barang["barang"] = $(".barang").val();
        barang["harga"] = cb.harga;
        barang["qty"] = cb.qty;
        barang["total"] = (cb.harga * cb.qty);
        barang["diskon"] = cb.diskon;
        barang["biaya"] = (cb.harga * cb.qty) - cb.diskon;

        datas.push(barang);

        $(".list_items").html(list_items());
        $(".super_total").val(angka(super_total().biaya));
        $(".barang").focus();
        clear_input();
    });

    const cari_biaya = (order = "Add") => {
        let harga = (order == "Add" ? $(".harga").val() : $(".edit_harga").val());
        harga = (harga == "" ? "0" : harga);
        harga = angka_to_int(harga);

        let qty = (order == "Add" ? $(".qty").val() : $(".edit_qty").val());
        qty = (qty == "" ? "1" : qty);
        qty = angka_to_int(qty);

        let diskon = (order == "Add" ? $(".diskon").val() : $(".edit_diskon").val());
        diskon = (diskon == "" ? "0" : diskon);
        diskon = angka_to_int(diskon);
        let res = {
            harga,
            qty,
            diskon
        };

        return res;
    }

    $(document).on('keyup', '.cari_biaya', function(e) {
        e.preventDefault();
        let order = $(this).data("order");

        let cb = cari_biaya(order);
        if (order == "Add") {
            $(".total").val(angka(cb.harga * cb.qty));
        } else {
            $(".edit_total").val(angka(cb.harga * cb.qty));
        }

        if (cb.diskon > (cb.harga * cb.qty)) {
            if (order == "Add") {
                $(".biaya").val("- " + angka((cb.harga * cb.qty) - cb.diskon));
            } else {
                $(".edit_biaya").val("- " + angka((cb.harga * cb.qty) - cb.diskon));
            }
        } else {
            if (order == "Add") {
                $(".biaya").val(angka((cb.harga * cb.qty) - cb.diskon));
            } else {
                $(".edit_biaya").val(angka((cb.harga * cb.qty) - cb.diskon));
            }
        }
    });


    $(document).on('click', '.btn_save', function(e) {
        e.preventDefault();
        let pj = $(".pj").val();
        let jenis = $(".jenis").val();
        post("inv/add", {
            datas,
            pj,
            jenis
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

    const lists = (data, total, tahun, bulan, jenis) => {
        let tahuns = <?= json_encode(tahuns('pengeluaran')); ?>;
        let bulans = <?= json_encode(bulans()); ?>;
        let html = '';
        html += `
            <div class="form-floating mb-2">
                <select class="form-select bg-dark text-light tahun">`;
        tahuns.forEach(e => {
            html += `<option ${(e.tahun==tahun?"selected":"")} value="${e.tahun}">${e.tahun}</option>`;
        })

        html += `</select>
                <label>Tahun</label>
            </div>

            <div class="form-floating mb-3">
                <select class="form-select bg-dark text-light bulan">`;
        bulans.forEach(e => {
            html += `<option ${(e.satuan==bulan?"selected":"")} value="${e.satuan}">${e.bulan}</option>`;
        })

        html += `</select>
                <label>Bulan</label>
            </div>

            <button class="btn btn-sm btn-secondary mb-2 lists" data-jenis="Inv">Show</button>
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="text-warning nav-link lists ${(jenis=='Inv'?'active':'')}" data-jenis="Inv" href="#">Inv</a>
                    </li>
                    <li class="nav-item">
                        <a class="text-warning nav-link lists ${(jenis=='Modal'?'active':'')}" data-jenis="Modal" href="#">Modal</a>
                    </li>
                </ul>
                
                <div class="mt-3">
                <h4 class="text-center bg-secondary p-2">-[ ${angka(total)} ]-</h4>

                <input class="form-control form-control-sm bg-dark text-light cari mb-2" placeholder="Cari">
                    <table class="table table-sm table-dark" style="font-size:12px">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Tgl</th>
                                <th class="text-center">Barang</th>
                                <th class="text-center">Harga</th>
                                <th class="text-center">Act</th>
                            </tr>
                        </thead>
                        <tbody class="tabel_search">`;
        data.forEach((e, i) => {
            html += `<tr>
                                <th scope="row">${(i+1)}</th>
                                <td>${time_php_to_js(e.tgl)}</td>
                                <td class="text-start">${e.barang}</td>
                                <td class="text-end">${angka(e.biaya)}</td>
                                <td class="text-center"><a class="edit text-warning" data-id="${e.id}"><i class="fa-solid fa-pen-to-square"></i></a></td>
                            </tr>`;
        })
        html += `</tbody>
                    </table>
                </div>
                `;

        return html;
    }

    let datases = [];

    $(document).on('click', '.lists', function(e) {
        e.preventDefault();
        let tahun = ($(".tahun").val() == undefined || $(".tahun").val() == "" ? "<?= date('Y'); ?>" : $(".tahun").val());
        let bulan = ($(".bulan").val() == undefined || $(".bulan").val() == "" ? "<?= date('n'); ?>" : $(".bulan").val());
        let jenis = $(this).data("jenis");

        post("inv/list", {
            tahun,
            bulan,
            jenis
        }).then(res => {
            loading("close");
            datases = res.data;
            if (res.data.length < 1) {
                message("400", "Data tidak ada");
                return;
            }
            let html = build_html(jenis, "offcanvas");
            html += lists(res.data, res.data2, tahun, bulan, jenis);

            $(".body_canvas").html(html);

            if ($('.tahun').length > 0) {
                canvas.show();
            }
        })

    });

    let form_input = (order, id) => {
        let jenis = <?= json_encode(options("Inv")); ?>;

        let data = {};
        datases.forEach(e => {
            if (e.id == id) {
                data = e;
                return;
            }
        });

        let html = `<div class="form-floating mb-3">
                        <select class="form-select bg-dark text-light border-secondary rounded edit_jenis" name="jenis">`;
        jenis.forEach(e => {
            html += `<option ${(e.value==data.jenis?"selected":"")} value="${e.value}">${e.value}</option>`;

        })
        html += `</select>
                        <label class="text-secondary">Jenis</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="pj" ${(order=="Edit"?'value="'+data.pj+'"':"")} class="form-control bg-dark text-light border border-warning edit_pj" placeholder="Pj" required>
                        <label class="text-secondary">Pj</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="barang" ${(order=="Edit"?'value="'+data.barang+'"':"")} class="form-control bg-dark text-light border border-warning edit_barang" placeholder="Barang" required>
                        <label class="text-secondary">Barang</label>
                    </div>
                    `;

        html += `<div class="form-floating mb-3">
                        <input type="text" name="harga" ${(order=="Edit"?'value="'+angka(data.harga)+'"':"")} class="form-control bg-dark text-light angka cari_biaya edit_harga" data-order="Edit" placeholder="Harga" required>
                        <label class="text-secondary">Harga</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="qty" ${(order=="Edit"?'value="'+angka(data.qty)+'"':"")} class="form-control bg-dark text-light angka cari_biaya edit_qty"  data-order="Edit" placeholder="Qty" required>
                        <label class="text-secondary">Qty</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="diskon" ${(order=="Edit"?'value="'+angka(data.diskon)+'"':"")} class="form-control bg-dark text-light angka cari_biaya edit_diskon"  data-order="Edit" placeholder="Diskon" required>
                        <label class="text-secondary">Diskon</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="total" ${(order=="Edit"?'value="'+angka(data.total)+'"':"")} class="form-control bg-dark text-light angka edit_total"  data-order="Edit" placeholder="Total" readonly>
                        <label class="text-secondary">Total</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="biaya" ${(order=="Edit"?'value="'+angka(data.biaya)+'"':"")} class="form-control bg-dark text-light angka edit_biaya" placeholder="Biaya" readonly>
                        <label class="text-secondary">Biaya</label>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="btn btn-outline-info btn_edit" data-id="${id}">Simpan</button>
                    </div>`

        return html;
    }

    $(document).on('click', '.edit', function(e) {
        e.preventDefault();
        loading();
        let id = $(this).data("id");

        let html = build_html("Edit");

        html += `<div class="container">`;
        html += form_input("Edit", id);
        html += `</div>`;

        $(".body_modal").html(html);
        loading("close");
        modal.show();
    });

    $(document).on('keyup', '.cari', function(e) {
        e.preventDefault();
        let value = $(this).val().toLowerCase();
        $('.tabel_search tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });

    });

    $(document).on('click', '.btn_edit', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let jenis = $(".edit_jenis").val();
        let pj = $(".edit_pj").val();
        let barang = $(".edit_barang").val();
        let harga = angka_to_int($(".edit_harga").val());
        let qty = angka_to_int($(".edit_qty").val());
        let total = angka_to_int($(".edit_total").val());
        let diskon = angka_to_int($(".edit_diskon").val());
        let biaya = angka_to_int($(".edit_biaya").val());
        let tahun = $(".tahun").val();
        let bulan = $(".bulan").val();

        post("inv/edit", {
            id,
            jenis,
            pj,
            barang,
            harga,
            qty,
            total,
            diskon,
            biaya,
            tahun,
            bulan
        }).then(res => {
            message(res.status, res.message);

            if (res.status == 200) {
                loading("close");
                let html = build_html(jenis, "offcanvas");
                html += lists(res.data, res.data2, tahun, bulan, 'Edit');

                $(".body_canvas").html(html);
                modal.hide();
            }
        })

    });
</script>


<?= $this->endSection() ?>