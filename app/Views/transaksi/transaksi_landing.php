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
        <div class="mb-1 text-center">TRANSAKSI</div>
        <div class="d-grid">
            <button class="btn btn-light lists" data-jenis="Transaksi"><i class="fa-solid fa-list"></i></button>
        </div>
    </div>
</div>
<div class="form-floating position-relative mb-2">
    <input type="text" class="form-control bg-dark text-light cari_barang" data-id="${id}" data-order="${order}" placeholder="Cari..." autofocus>
    <label class="text-secondary">Cari Produk</label>
    <div class="bg-dark text-light body_list_barang position-absolute border border-secondary" style="width: 100%;z-index:3;">
    </div>
</div>
<div class="form-floating mb-2">
    <input type="text" class="form-control bg-dark text-light border border-warning harga" value="0" readonly>
    <label class="text-secondary">Harga</label>
</div>
<div class="form-floating mb-2">
    <input type="text" class="form-control bg-dark text-light qty angka cari_biaya" value="1">
    <label class="text-secondary">Qty</label>
</div>
<div class="form-floating mb-2">
    <input type="text" class="form-control bg-dark text-light diskon angka cari_biaya" value="0">
    <label class="text-secondary">Diskon</label>
</div>
<div class="form-floating mb-2">
    <input type="text" class="form-control bg-dark text-light border border-warning total" value="0" readonly>
    <label class="text-secondary">Total</label>
</div>
<div class="form-floating mb-2">
    <input type="text" class="form-control bg-secondary opacity-50 text-light fw-bold border border-warning biaya" value="0" readonly>
    <label class="text-light">Biaya</label>
</div>
<div class="pencuci">

</div>


<div class="d-flex gap-2 mt-2">
    <div class="flex-grow-1">
        <button class="btn btn-outline-warning tambah_barang" style="width: 100%;"><i class="fa-solid fa-box-open"></i> TAMBAH BARANG</button>
    </div>
    <div><button class="btn btn-outline-info next" style="width: 115px;"><i class="fa-solid fa-arrow-up-from-bracket"></i> NEXT</button></div>
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
    let barang_selected = {};
    $(document).on('keyup', '.cari_barang', function(e) {
        e.preventDefault();
        let text = $(this).val().toLowerCase();
        let body_class_list = $('.body_list_barang');

        post("transaksi/cari_barang", {
            text,
            jenis: ["Layanan", "Barang"]
        }, "No").then(res => {
            barangs = res.data;
            let barang_arr = res.data;

            if (barang_arr.length > 0) {
                let html = '';
                barang_arr.forEach(e => {
                    html += `
                            <div class="list_barang" data-id="${e.id}">
                                <div class="d-flex justify-content-between">
                                    <span>${e.barang}</span>
                                    <span class="text-muted">${angka(e.harga)} [${angka(e.qty)}]</span>
                                </div>
                            </div>`;
                });
                body_class_list.html(html).show();
            } else {
                body_class_list.html('<div class="list_hasil text-muted">No data found</div>').show();
            }
        })

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


    }

    $(document).on('click', '.list_barang', function(e) {
        e.preventDefault();
        const id = $(this).data("id");

        let id_exist = [];
        datas.forEach(e => {
            if (e.id == id) {
                id_exist.push(e);
            }
        })

        if (id_exist.length > 0) {
            message("400", "Barang existed");
            return;
        }

        let val = {};
        barangs.forEach(e => {
            if (e.id == id) {
                val = e;
            }
        })

        if (val.jenis !== "Layanan") {
            if (parseInt(val.qty) < 1) {
                message("400", "Stok kosong");
                return;
            }

        }

        $(".harga").val(angka(val.harga));
        $(".total").val(angka(val.harga * 1));
        $(".biaya").val(angka(val.harga * 1));
        $(".cari_barang").val(val.barang);

        $('.body_list_barang').html("");
        $('.body_list_barang').hide();
        barang_selected = val;
    });

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
        $(".cari_barang").val("");
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
                <td><a href="" class="text-danger delete_item" data-barang_id="${e.id}" style="text-decoration:none"><i class="fa-solid fa-circle-xmark"></i></a></td>
            </tr>`;
        })

        return html;
    }

    $(document).on('click', '.delete_item', function(e) {
        e.preventDefault();
        let id = $(this).data("barang_id");

        let temp_datas = [];
        datas.forEach(e => {
            if (e.id != id) {
                temp_datas.push(e);
            }
        })

        datas = temp_datas;
        console.log(datas);
        // let cb = cari_biaya();

        $(".list_items").html(list_items());
        $(".super_total").val(angka(super_total().biaya));
        $(".cari_barang").focus();
    });

    let pencucis = [];
    const pencuci = () => {

        let html = `<div class="container">
                        <div class="form-floating position-relative">
                            <input type="text" class="form-control bg-dark text-light cari_user" data-order="pencuci" placeholder="Cari...">
                            <label class="text-secondary">Cari Nama</label>
                            <div class="bg-dark text-light body_list_hasil"></div>
                        </div>
                    </div>`;
        $(".body_modal").html(html);
        modal.show();

        $('#main_modal').on('shown.bs.modal', () => {
            $('.cari_user').trigger('focus').select();
        });
    }

    $(document).on('click', '.tambah_barang', function(e) {
        e.preventDefault();

        let cb = cari_biaya();
        if (barang_selected.jenis !== "Layanan") {
            if (parseInt(barang_selected.qty) < cb.qty) {
                blink("qty");
                message("400", "Stok kurang");
                return;
            }
        }
        if (cb.diskon > (cb.harga * cb.qty)) {
            message("400", "Diskon over");
            blink('diskon');
            return;
        }

        barang_selected['karyawan'] = '';

        if (barang_selected.jenis == "Layanan") {
            if (pencucis.length == 0) {
                pencuci();
                return;

            } else {
                let karyawan = [];
                pencucis.forEach(e => {
                    karyawan.push(e.id);
                })
                barang_selected["karyawan"] = karyawan.join(",");
            }
        }

        barang_selected["harga"] = cb.harga;
        barang_selected["qty"] = cb.qty;
        barang_selected["total"] = (cb.harga * cb.qty);
        barang_selected["diskon"] = cb.diskon;
        barang_selected["biaya"] = (cb.harga * cb.qty) - cb.diskon;

        datas.push(barang_selected);
        pencucis = [];
        $(".list_items").html(list_items());
        $(".super_total").val(angka(super_total().biaya));
        $(".cari_barang").focus();
        $(".pencuci").html("");
        clear_input();
    });

    const cari_biaya = () => {
        let harga = $(".harga").val();
        harga = (harga == "" ? "0" : harga);
        harga = angka_to_int(harga);

        let qty = $(".qty").val();
        qty = (qty == "" ? "1" : qty);
        qty = angka_to_int(qty);

        let diskon = $(".diskon").val();
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
        let cb = cari_biaya();
        $(".total").val(angka(cb.harga * cb.qty));
        if (cb.diskon > (cb.harga * cb.qty)) {
            $(".biaya").val("- " + angka((cb.harga * cb.qty) - cb.diskon));
        } else {
            $(".biaya").val(angka((cb.harga * cb.qty) - cb.diskon));
        }
    });

    const penghutang = (nama, wa, id) => {
        let html = `<div class="rounded bg-danger mb-2 p-2">
                        <h6 class="text-center">PENGHUTANG</h6>
                        <input type="hidden" class="form-control mb-2 id_hutang" value="${id}">
                        <input type="text" class="form-control mb-2 nama_hutang" value="${nama}">
                        <input type="text" class="form-control wa_hutang" value="${wa}">
                    </div>`;

        return html;
    }

    const next = (super_total) => {
        let html = ``;
        html += `<div class="border border-secondary rounded p-3">
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" style="width: 100px;">SUB TOTAL</span>
                        <input type="text" class="form-control" value="${angka(super_total.total)}">
                    </div>
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" style="width: 100px;">DISKON</span>
                        <input type="text" class="form-control"  value="${angka(super_total.diskon)}">
                    </div>
                    <div class="input-group input-group-sm mb-3 before_penghutang">
                        <span class="input-group-text" style="width: 100px;">TOTAL</span>
                        <input type="text" class="form-control" value="${angka(super_total.biaya)}">
                    </div>
                   
                    <h6 class="text-center">UANG PEMBAYARAN</h6>
                    <input class="form-control form-control-lg text-light text-center border border-light border-3 bg-success uang_pembayaran angka" value="${angka(super_total.biaya)}" value="0" type="text">
                    

                    <div class="d-flex gap-2 mt-4 before_hutang">
                        <div class="flex-grow-1">
                             <button class="btn btn-info btn_bayar" style="width:100%"><i class="fa-solid fa-arrow-right-to-bracket"></i> BAYAR</button>
                        </div>
                        <div>
                            <button class="btn btn-outline-info hutang" style="width: 115px;"><i class="fa-solid fa-face-frown"></i> HUTANG</button>
                        </div>
                    </div>
                </div>`;

        return html;
    }

    $(document).on('click', '.next', function(e) {
        e.preventDefault();
        if (datas.length == 0) {
            message("400", "Barang kosong");
            return;
        }
        let html = build_html("TRANSAKSI", "offcanvas");
        html += next(super_total());

        $(".body_canvas").html(html);
        canvas.show();

        $('#main_canvas').on('shown.bs.offcanvas', function() {
            $('.uang_pembayaran').trigger('focus').select();
        });


    });
    $(document).on('click', '.btn_bayar', function(e) {
        e.preventDefault();
        let uang = $(".uang_pembayaran").val();
        uang = (uang == "" ? "0" : uang);
        uang = angka_to_int(uang);

        if (uang < super_total().biaya) {
            message("400", "Uang kurang");
            return;
        }

        post("transaksi/bayar", {
            uang,
            datas,
            super_total: super_total()
        }).then(res => {
            loading("close");
            message(res.status, res.message);
            if (res.status == "200") {
                setTimeout(() => {
                    const no_nota = res.data; // pastikan backend mengembalikan string no_nota
                    const iframe_url = `<?= base_url(); ?>guest/nota/${no_nota}`;

                    let html = build_html("INVOICE", "modal", ["judul", "garis"]);
                    html += `<iframe id="nota_frame" src="${iframe_url}" style="border: none; width: 100%; height: 600px;"></iframe>`;
                    html += `
                <div class="d-grid mt-5">
                    <button class="btn btn-secondary selesai">Selesai</button>
                </div>
            `;

                    $(".body_modal_static").html(html);
                    modal_static.show();

                }, 1200);
            }
        })

    });

    $(document).on('click', '.selesai', function(e) {
        e.preventDefault();
        location.reload();
    });

    $(document).on('click', '.hutang', function(e) {
        e.preventDefault();

        let html = `<div class="container">
                        <div class="bg-light p-3 rounded mb-3">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control nama_user" placeholder="Nama">
                                <label class="text-dark">Nama</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control wa_user" placeholder="No. W.a">
                                <label class="text-dark">No. W.a</label>
                            </div> 
                            <div class="d-grid">
                                <button class="btn btn-success btn_simpan_user">SIMPAN</button>
                            </div>
                        </div>
                        <div class="form-floating position-relative">
                            <input type="text" class="form-control bg-dark text-light cari_user" placeholder="Cari...">
                            <label class="text-secondary">Cari Nama</label>
                            <div class="bg-dark text-light body_list_hasil"></div>
                        </div>
                    </div>`;
        $(".body_modal").html(html);
        modal.show();

        $('#main_modal').on('shown.bs.modal', () => {
            $('.cari_user').trigger('focus').select();
        });

    });

    $(document).on('keyup', '.cari_user', function(e) {
        e.preventDefault();
        let text = $(this).val().toLowerCase();
        let order = $(this).data("order");
        let body_class_list = $('.body_list_hasil');

        if (text == "") {
            body_class_list.html('').hide();
            return;
        }

        let roles = ['Member'];
        if (order == "pencuci") {
            roles = ["Karyawan"];
        }


        post("transaksi/cari_user", {
            text,
            roles
        }, "No").then(res => {
            let users = res.data;

            if (users.length > 0) {
                let html = '';
                users.forEach(e => {
                    html += `
                            <div class="list_hasil" data-hasil_id="${e.id}" data-nama="${e.nama}" data-order="${order}" data-wa="${e.wa}">
                                <div class="d-flex justify-content-between">
                                    <span>${e.nama}</span>
                                    <span class="text-muted">${e.role} [${e.wa}]</span>
                                </div>
                            </div>`;
                });
                body_class_list.html(html).show();
            } else {
                body_class_list.html('<div class="list_hasil text-muted">No data found</div>').show();
            }
        })
    });
    $(document).on('click', '.btn_simpan_user', function(e) {
        e.preventDefault();
        let nama = $(".nama_user").val();
        let wa = $(".wa_user").val();

        if (nama == "") {
            message("400", "Nama kosong");
            return;
        }
        if (wa == "") {
            message("400", "Wa kosong");
            return;
        }

        post("transaksi/add_user", {
            nama,
            wa
        }).then(res => {
            loading("close");
            message(res.status, res.message);
        })

    });

    $(document).on('click', '.list_hasil', function(e) {
        e.preventDefault();
        let id = $(this).data("hasil_id");
        let nama = $(this).data("nama");
        let wa = $(this).data("wa");
        let order = $(this).data("order");

        let exist = false;
        pencucis.forEach(e => {
            if (id == e.id) {
                exist = true;
            }
        })

        if (exist) {
            message("400", "Nama sudah masuk");
            return;
        }

        if (order == "pencuci") {
            pencucis.push({
                id,
                nama
            });

            let html = ``;
            pencucis.forEach(e => {
                html += `<span class="tambah_pencuci">${e.nama}</span> <a style="text-decoration:none;" class="hapus_pencuci text-danger me-2" data-id="${e.id}"><i class="fa-solid fa-circle-xmark"></i></a>`;
            })
            $(".pencuci").html(html);
        } else {
            const existing = $('.nama_hutang');

            if (existing.length === 0) {

                $('.before_penghutang').after(penghutang(nama, wa, id));
            } else {
                $('.nama_hutang').val(nama);
                $('.wa_hutang').val(wa);
                $('.id_hutang').val(id);
            }

            modal.hide();
            $(".body_modal ").html('');

            const simpan_hutang = $('.simpan_hutang');

            let html = ``;
            if (simpan_hutang.length === 0) {
                html += `<div class="d-grid simpan_hutang mt-3">
                            <button type="button" class="btn btn-outline-danger btn_simpan_hutang">SiMPAN HUTANG</button>
                        </div>`;
                $('.before_hutang').after(html);
 $('.before_hutang').remove();
            } else {
                $(".btn_simpan_hutang").remove();
                html += `<button type="button" class="btn btn-outline-danger btn_simpan_hutang">SiMPAN HUTANG</button>`;
                $('.simpan_hutang').html(html);
            }

        }
    });

    $(document).on('click', '.tambah_pencuci', function(e) {
        e.preventDefault();
        pencuci();
    });
    $(document).on('click', '.hapus_pencuci', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        temp_pencucis = [];

        pencucis.forEach(e => {
            if (e.id != id) {
                temp_pencucis.push(e);
            }
        })

        pencucis = temp_pencucis;
        let html = ``;
        pencucis.forEach(e => {
            html += `<span class="tambah_pencuci">${e.nama}</span> <a style="text-decoration:none;" class="hapus_pencuci text-danger me-2" data-id="${e.id}"><i class="fa-solid fa-circle-xmark"></i></a>`;
        })
        $(".pencuci").html(html);
    });
    $(document).on('click', '.btn_simpan_hutang', function(e) {
        e.preventDefault();
        let nama = $(".nama_hutang").val();
        let id = $(".id_hutang").val();

        post("transaksi/add_hutang", {
            datas,
            nama,
            id
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

    // data transaksi
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

            <button class="btn btn-sm btn-secondary mb-2 lists" data-jenis="Transaksi">Show</button>
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="text-warning nav-link lists ${(jenis=='Transaksi'?'active':'')}" data-jenis="Transaksi" href="#">Transaksi</a>
                    </li>
                    <li class="nav-item">
                        <a class="text-warning nav-link lists ${(jenis=='Hutang'?'active':'')}" data-jenis="Hutang" href="#">Hutang</a>
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
                                <th class="text-center">Qty</th>
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

        post("transaksi/list", {
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
    $(document).on('keyup', '.cari', function(e) {
        e.preventDefault();
        let value = $(this).val().toLowerCase();
        $('.tabel_search tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });

    });
</script>


<?= $this->endSection() ?>