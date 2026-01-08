<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>
<div class="main"></div>

<script>
    loading();
    let datas = []; //arrray barang yang dipilih dengan qty yang dibeli, ini akan berubah ubah sesuai yang dimasukkan kasir
    let data_awal_pesanan = []; //tambah pesanan: array barang yang dipilih dengan qty yang dibeli, ini tidak akan berubah
    let barangs = []; // semua barang dg qty bawaan
    let tahuns = [];
    let bulans = [];
    let barang_selected = {}; //objek barang yang dipilih saat ini
    let options = [];
    let metodes = [];
    let user_hutang = {}; // orang yang berhutang
    let datases = []; // untuk transaksi untuk kasir
    let is_tambah_pesanan = false;
    let no_nota = '';
    let pencucis = [];
    let tgl_tambah_pesanan = 0;


    let templates = {
        top: function() {
            let html = `<div class="d-flex mb-3">
                            <div class="p-2 flex-fill">
                                <div class="text-warning text-center">
                                    <div class="mb-1">TOTAL</div>
                                    <input type="text" value="0" class="form-control super_total bg-warning fw-bold text-center text-dark border border-light border-3">
                                </div>
                            </div>

                            <div class="p-2 flex-fill">
                                <div class="mb-1 text-center">DATA</div>
                                <div class="d-grid">
                                    <button class="btn btn-light data" data-order="<?= url();  ?>" data-kategori="Kantin" data-jenis="All"><i class="fa-solid fa-list"></i></button>
                                </div>
                            </div>
                            <div class="p-2 flex-fill">
                                <div class="mb-1 text-center">KASIR</div>
                                <div class="d-grid">
                                    <button class="btn btn-light kasir" data-order="Show" data-filter="by nota"><i class="fa-solid fa-cash-register"></i></button>
                                </div>
                            </div>
                        </div>`;
            return html;
        },
        middle: function() {
            let html = `<div class="form-floating position-relative mb-2">
                            <input type="text" class="form-control form-control-sm bg-dark text-light cari_barang" placeholder="Cari..." autofocus>
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
                            <div><button class="btn btn-outline-info pembayaran" data-ket="bayar" style="width: 115px;"><i class="fa-solid fa-arrow-up-from-bracket"></i> NEXT</button></div>
                        </div>`;
            return html;
        },
        bottom: function() {
            let html = `<table class="table table-borderless text-light table-sm mt-4" style="font-size: 12px;">
                            <thead>
                                <tr>
                                    <td>#</td>
                                    <td>Barang</td>
                                    <td>Biaya</td>
                                    <td>Qty</td>
                                    <td>Del</td>
                                </tr>
                            </thead>
                            <tbody class="list_items">

                            </tbody>
                        </table>`;

            return html;
        },
        update_qty: function(data, message) {
            let html = `<div class="text-center bg-dark p-3">
                <p style="font-size:12px">${message}</p>
                <p style="font-size:18px">${data.barang}</p>
                <small class="text-danger msg_stok"></small>
                <input class="form-control bg-dark text-light form-control-sm text-center mb-3 val_qty angka" type="text" value="${angka(data.qty)}">
              <div class="mb-4">
                <button class="cancel_qty btn btn-sm btn-secondary">Cancel</button>
                <button class="save_qty btn btn-sm btn-light" data-id="${(is_tambah_pesanan && data.is_update!=="new"?data.barang_id:data.id)}">Simpan</button>
              </div>
            </div>`;

            return html;
        }
    }


    let show = () => {

        let data = {
            order: "Show",
            id: 0,
            kategori: "Transaksi",
            format: "array",
            tabel: 'transaksi'
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`transaksi/${req.data}`).then(res => {
                    loading("close");
                    if (res.status == "200") {
                        tahuns = res.data;
                        bulans = res.data2;
                        options = res.data3;
                        metodes = res.data4;
                        barangs = res.data5;
                        let html = templates.top();
                        html += templates.middle();
                        html += templates.bottom();

                        $(".main").html(html);

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


    $(document).on('keyup', '.cari_barang', function(e) {
        e.preventDefault();
        let text = $(this).val();
        let body_class_list = $('.body_list_barang');
        let id = $(this).data("id");
        let order = $(this).data("order");

        if (text == "") {
            $(".body_list_barang").html("");
            return;
        }

        let data = {
            id,
            text,
            order: "Cari Barang",
            format: "array",
            tabel: "transaksi",
            filters: options
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`transaksi/${req.data}`).then(res => {
                    if (res.status == "200") {
                        if (res.data.length > 0) {
                            let html = '';
                            res.data.forEach(e => {
                                html += `
                            <div class="list_barang" data-barang_id="${id}"`;
                                for (const key in e) {
                                    if (e.hasOwnProperty(key)) {
                                        html += `data-${key.toLowerCase()}="${e[key]}" `;
                                    }
                                }
                                html += `>
                                <div class="d-flex justify-content-between ${(e.link !==""?"text-warning":"")}">
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
        let id = $(this).data("id");

        // cek apakah barang sudah ada di pesanan
        let barang_exist = {};
        datas.forEach(ex => {
            if ((is_tambah_pesanan && ex.is_update !== "new" ? ex.barang_id : ex.id) == id) {
                barang_exist = ex;

            }
        })

        if (barang_exist.id === undefined) {

            const val = barangs.find(e => e.id == id);
            const newVal = structuredClone(val); // ES2021+
            newVal.is_update = 'new';

            if (!cek_stok(newVal, 1)) {
                return;
            }

            newVal.karyawan = '';
            newVal.qty = 1;

            $(".harga").val(angka(newVal.harga));
            $(".total").val(angka(newVal.harga * 1));
            $(".biaya").val(angka(newVal.harga * 1));
            $(".cari_barang").val(newVal.barang);

            $('.body_list_barang').html("");
            $('.body_list_barang').hide();
            barang_selected = newVal;
        } else {
            let html = templates.update_qty(barang_exist, "BARANG SUDAH ADA, UBAH QTY?");
            $(".message").html(html);
            $(".message").show();
            $(".body_list_barang").html("");
            clear_input_transaksi();
            return;

        }
    });

    $(document).on('click', '.update_qty', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let data_update = {};
        datas.forEach(e => {
            if (e.id == id) {
                data_update = e;
            }
        })
        if (data_update.id === undefined) {
            message("400", "Barang tidak ada");
            return;
        }

        $(".message").html(templates.update_qty(data_update, "UPDATE QTY"));
        $(".message").show();
    });


    $(document).on('click', '.save_qty', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let qty = parseInt(str_replace(".", "", $(".val_qty").val()));

        let temp_datas = [];

        let is_stok = true;

        temp_datas = datas.map(e => {
            const matchId = (is_tambah_pesanan && e.is_update !== "new" ? e.barang_id : e.id) == id;

            if (matchId) {
                if (cek_stok(e, qty, ".msg_stok")) {
                    // buat salinan baru agar tidak merusak referensi asli
                    return {
                        ...e,
                        qty,
                        total: qty * e.harga,
                        biaya: (qty * e.harga) - e.diskon,
                        is_update: e.is_update === "false" ? "true" : e.is_update
                    };
                } else {
                    is_stok = false;
                }
            }

            return e; // kalau tidak match, tetap kembalikan e
        });

        if (!is_stok) return;


        datas = temp_datas;
        $(".list_items").html(list_items());
        $(".super_total").val(angka(super_total().biaya));

        $(".message").html("");
        $(".message").hide();
        $(".cari_barang").focus();
    });

    $(document).on('click', '.cancel_qty', function(e) {
        e.preventDefault();

        $(".message").html("");
        $(".message").hide();
        $(".cari_barang").focus();
    });




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
        // let cb = cari_biaya();

        $(".list_items").html(list_items());
        $(".super_total").val(angka(super_total().biaya));
        $(".cari_barang").focus();
    });


    const pencuci = () => {

        let html = `<div class="container">
        <div class="text-center mb-3">
            <a href="" class="text-end close_cari_user text-danger" style="text-decoration:none;font-size:large"><i class="fa-solid fa-circle-xmark"></i></a>
        </div>
                        <div class="form-floating position-relative">
                            <input type="text" class="form-control bg-dark text-light cari_user" data-order="pencuci" placeholder="Cari...">
                            <label class="text-secondary">Cari Nama</label>
                            <div class="bg-dark text-light body_list_hasil"></div>
                        </div>
                    </div>`;
        $(".body_modal_static").html(html);
        modal_static.show();

        $('#main_modal_static').on('shown.bs.modal', () => {
            $('.cari_user').trigger('focus').select();
        });
    }

    $(document).on('click', '.tambah_barang', function(e) {
        e.preventDefault();
        if ($(this).data("order") == "playground") {
            console.log('Ok');
            tambah_barang();
            return;
        }
        if (barang_selected.id == undefined) {
            message("400", "Barang belum dipilih");
            $(".cari_barang").focus();
            return;
        }

        let cb = cari_biaya();

        // cek apakah layanan
        if (barang_selected.jenis == "Layanan" && pencucis.length == 0) {
            pencuci();
            return;
        }

        if (cb.diskon > (cb.harga * cb.qty)) {
            message("400", "Diskon over");
            blink('diskon');
            return;
        }

        barang_selected['is_update'] = "new";

        if (!cek_stok(barang_selected, cb.qty)) {
            return;
        }
        barang_selected["harga"] = cb.harga;
        barang_selected["qty"] = cb.qty; // qty yang dibeli
        barang_selected["total"] = (cb.harga * cb.qty);
        barang_selected["diskon"] = cb.diskon;
        barang_selected["biaya"] = (cb.harga * cb.qty) - cb.diskon;

        let hasil_pencuci = $.map(pencucis, function(item) {
            return item.id;
        });

        barang_selected.karyawan = hasil_pencuci.join(",");

        datas.push(barang_selected);
        pencucis = [];
        $(".list_items").html(list_items());
        $(".super_total").val(angka(super_total().biaya));
        $(".cari_barang").focus();
        $(".pencuci").html("");
        clear_input_transaksi('hapus');
    });


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

    const penghutang = (nama, hutang, id) => {
        let html = `<div class="rounded bg-danger mb-2 p-2">
                        <h6 class="text-center">PENGHUTANG</h6>
                        <input type="hidden" class="form-control mb-2 id_hutang" value="${id}">
                        <input type="text" class="form-control mb-2 nama_hutang" value="${nama}">
                        <input type="text" class="form-control jml_hutang" value="${angka(hutang)}">
                    </div>`;

        return html;
    }

    $(document).on('click', '.hutang', function(e) {
        e.preventDefault();

        let html = `<div class="container">
        
                        <div class="bg-dark rounded mb-3">
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control bg-dark text-light nama_user" placeholder="Nama" required>
                                <label>Nama</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control bg-dark text-light wa_user" placeholder="No. W.a" required>
                                <label>No. W.a</label>
                            </div> 
                            <div class="d-grid">
                                <button class="btn btn-secondary btn_simpan_user">SIMPAN</button>
                            </div>
                        </div>
                        <hr>
                        <div class="form-floating position-relative">
                            <input type="text" class="form-control bg-dark text-light border-warning cari_user" data-order="hutang" placeholder="Cari...">
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
        let text = $(this).val();
        let order = $(this).data("order");
        let body_class_list = $('.body_list_hasil');

        if (text == "") {
            body_class_list.html('').hide();
            return;
        }

        let data = {
            order,
            text,
            id: 0,
            filters: ["Admin", "Karyawan"],
            tabel: "user",
            is_data: "karyawan"
        };

        if (order == "hutang") {
            data['order'] = "Cari User";
            delete data.filters;
            data['is_data'] = "hutang";
        }

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`transaksi/${req.data}`).then(res => {
                    if (res.data.length > 0) {
                        let html = '';
                        res.data.forEach(e => {
                            html += `
                            <div class="list_hasil" data-hasil_id="${e.id}" data-nama="${e.nama}" data-order="${order}" data-wa="${e.wa}" data-hutang="${e.hutang}">
                                <div class="d-flex justify-content-between">
                                    <span>${e.nama}</span>`;
                            if (order == "hutang") {
                                html += `<span class="text-muted">${angka(e.hutang)}</span>`;
                            } else {
                                html += `<span class="text-muted">${e.role} [${e.wa}]</span>`;
                            }
                            html += `</div>
                            </div>`;
                        });

                        body_class_list.html(html).show();
                    } else {
                        body_class_list.html('<div class="list_hasil text-muted">No data found</div>').show();
                    }
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })
    });

    $(document).on('click', '.btn_simpan_user', function(e) {
        e.preventDefault();
        cek_form();
        let nama = $(".nama_user").val();
        let wa = $(".wa_user").val();

        let data = {
            id: 0,
            order: 'Simpan User',
            tabel: 'user',
            nama,
            wa
        }

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`transaksi/${req.data}`).then(res => {
                    message(res.status, res.message);
                    $(".nama_user").val("");
                    $(".wa_user").val("");
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })

    });

    $(document).on('click', '.list_hasil', function(e) {
        e.preventDefault();
        let id = $(this).data("hasil_id");
        let nama = $(this).data("nama");
        let hutang = $(this).data("hutang");
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

            html += '<a href="" class="tambah_pencuci text-success" style="text-decoration:none;font-size:18px"><i class="fa-solid fa-circle-plus"></i></a>';
            $(".pencuci").html(html);
            $(".body_list_hasil").html("");
            $(".cari_user").val("");
            $(".cari_user").focus();
        } else {
            const existing = $('.nama_hutang');

            if (existing.length === 0) {
                user_hutang = {
                    id,
                    nama
                }

                $('.before_penghutang').after(penghutang(nama, hutang, id));
            } else {
                $('.nama_hutang').val(nama);
                $('.jml_hutang').val(hutang);
                $('.id_hutang').val(id);
            }

            modal.hide();
            $(".body_modal ").html('');

            const simpan_hutang = $('.simpan_hutang');

            let html = ``;
            if (simpan_hutang.length === 0) {
                html += `<div class="d-grid simpan_hutang mt-3">
                            <button type="button" data-ket="hutang" class="btn btn-secondary btn_simpan_transaksi">SiMPAN HUTANG</button>
                        </div>`;
                $('.before_hutang').after(html);
                $('.before_hutang').remove();
            } else {
                $(".btn_simpan_transaksi").remove();
                html += `<button type="button" data-ket="hutang" class="btn btn-outline-danger btn_simpan_transaksi">SiMPAN HUTANG</button>`;
                $('.simpan_hutang').html(html);
            }

            $(".body_metode").remove();
        }
    });

    $(document).on('click', '.close_cari_user', function(e) {
        e.preventDefault();
        $(".body_modal_static").html("");
        modal_static.hide();
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
        html += '<a href="" class="tambah_pencuci text-success" style="text-decoration:none;font-size:18px"><i class="fa-solid fa-circle-plus"></i></a>';
        $(".pencuci").html(html);
    });

    let kasir = (datases, tanggal) => {

        let html = `<div class="form-floating mb-3">
                        <select class="form-select tanggal bg-dark text-secondary" data-order="Show" data-filter="by nota">`;
        for (let i = 0; i < parseInt(datases.sub_menu); i++) {
            let tgl = ((i + 1) < 10 ? "0" + (i + 1) : (i + 1));
            html += ` <option value="${tgl}" ${(tgl==tanggal?"selected":"")}>${tgl}</option>`;
        }
        html += `</select>
                        <label>Pilih Tanggal</label>
                    </div>`;


        html += `
        <h6 class="text-warning">TOTAL: ${angka(datases.total)}</h6>
            <div class="input-group input-group-sm mb-2">
                    <input type="text" class="form-control bg-dark text-light border-secondary cari_card" placeholder="Cari..." aria-label="Recipient's username" aria-describedby="button-addon2">
                </div>
        `;
        if (datases.data.length == 0) {
            html += `<div class="text-start text-warning"><i class="fa-solid fa-triangle-exclamation"></i> Data tidak ada...</div>`;
        } else {
            datases.data.forEach((e, i) => {
                html +=
                    `
                            <div class="card text-bg-dark mb-3" data-menu="${e.nama} ${e.no_nota}" style="border-bottom: 1px solid white">
                                <div class="card-header text-start">${(i+1)}. ${e.nama} / ${e.no_nota} - ${time_php_to_js(e.data[0].tgl,"H:i")}</div>
                                <div class="card-body d-flex justify-content-between ps-4">
                                    <div class="text-secondary"><small>${angka(e.biaya)}</small></div>
                                    <div>
                                        <button class="btn btn-sm btn-secondary detail_hutang" data-order="kasir" data-no_nota="${e.no_nota}"><i class="fa-solid fa-circle-info"></i></button>
                                        <button class="btn btn-sm btn-success btn_wa" data-no_nota="${e.no_nota}"><i class="fa-brands fa-whatsapp"></i></button>`;
                if (today() == tanggal) {
                    html += `<button class="btn btn-sm btn-warning tambah_pesanan ms-1" data-user_id="${e.user_id}" data-nama="${e.nama}" data-tgl_tambah_pesanan="${e.tgl}" data-order="kasir" data-no_nota="${e.no_nota}" data-ket="tambah pesanan"><i class="fa-solid fa-cart-plus"></i> Item</button>`

                }
                html += `<button class="btn btn-sm btn-light pembayaran ms-1" data-order="kasir" data-ket="bayar hutang" data-no_nota="${e.no_nota}"><i class="fa-solid fa-arrow-up-right-from-square"></i> Bayar</button>
                                    </div>
                                </div>
                            </div>`;
            })

        }
        return html;
    }

    $(document).on('click', '.kasir', function(e) {
        e.preventDefault();
        loading();
        let order = $(this).data("order");
        let filter = $(this).data("filter");
        let tanggal = ($(".tanggal").val() == undefined || $(".tanggal").val() == "" ? "<?= date('d'); ?>" : $(".tanggal").val());

        let data = {
            order,
            filter,
            tanggal,
            tabel: "transaksi"
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`hutang/${req.data}`).then(res => {
                    loading("close");
                    datases = res.data.data;
                    temp_data = res.data;
                    let html = build_html("KASIR", "offcanvas");
                    html += `<div class="body_kasir">${kasir(temp_data, tanggal)}</div`;
                    $(".body_canvas").html(html);
                    if ($('.tanggal').length > 0) {
                        canvas.show();
                    }
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })
    });

    $(document).on('change', '.tanggal', function(e) {
        e.preventDefault();
        loading();
        let order = $(this).data("order");
        let filter = $(this).data("filter");
        let tanggal = $(".tanggal").val();

        let data = {
            order,
            filter,
            tanggal,
            tabel: "transaksi"
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`hutang/${req.data}`).then(res => {
                    loading("close");
                    datases = res.data.data;
                    $(".body_kasir").html(kasir(res.data, tanggal));
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })
    });
</script>


<?= $this->endSection() ?>