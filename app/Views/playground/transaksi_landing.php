<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>
<div class="menu" style="margin-top: -50px;"></div>
<div class="main"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    loading();
    let update_waktu = []; //berisi id transaksi meja aktif untuk mengupdate waktu main

    let barangs = [];
    let datas = []; //data yang akan ditransaksi
    // let data_awal_pesanan = [];
    let bayar_n_hutang = [];
    let is_tambah_pesanan = false;
    let barang_selected = {};
    let metodes = [];
    let divisions = [];
    let divisi = "Kantin";
    let user_hutang = {}; // orang yang berhutang

    const reset = (order = undefined) => {
        update_waktu = [];
        datas = [];
        is_tambah_pesanan = false;
        barang_selected = {};
        user_hutang = {};
        $(".list_items").html("");
        if (order == undefined) {
            canvas.hide();
            modal_static.hide();

        }
    }

    const post_jwt = (data) => {
        return post("home/encode_jwt", {
                data
            })
            .then(req => {
                if (req.status == "200") {
                    return fetchData(`playground/${req.data}`).then(res => {
                        return res; // kembalikan seluruh res
                    });
                } else {
                    throw new Error(req.message);
                }
            });
    };

    let obj_html = {
        Kasir: function() {
            let html = `<div class="row">
                            <div class="col-6 rounded border border-secondary">
                            <div>Items</div>
                            <div style="max-height: 500px;overflow-y: auto;">  
                            <table class="table table-bordered text-white-50 table-sm" style="font-size: 14px;">
                                    <thead>
                                        <tr>
                                            <th class="text-center text-white opacity-75">#</th>
                                            <th class="text-center text-white opacity-75">Divisi</th>
                                            <th class="text-center text-white opacity-75">Barang</th>
                                            <th class="text-center text-white opacity-75">Harga</th>
                                            <th class="text-center text-white opacity-75">Qty</th>
                                            <th class="text-center text-white opacity-75">Diskon</th>
                                            <th class="text-center text-white opacity-75">Biaya</th>
                                            <th class="text-center text-white opacity-75">Del</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list_items">`;
            html += list_items("playground");
            html += `</tbody>
                                </table></div>
                            </div>
                            <div class="col">
                                <div>
                                    <input type="text" value="0" class="form-control super_total bg-warning fw-bold text-center text-dark border border-light border-3">
                                </div>
                                
                                <div class="d-flex justify-content-center gap-2 my-3 py-2 bg-secondary rounded border">`;

            divisions.forEach(e => {
                html += `<div class="form-check form-switch">
                            <input class="form-check-input" type="radio" ${(e==divisi?"checked":"")} role="switch" name="divisi" value="${e}">
                            <label class="form-check-label">${e}</label>
                        </div>`;
            })

            html += `</div>
                            <div class="form-floating position-relative mb-2">
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


                                <div class="d-flex gap-2 mt-3">
                                    <div class="flex-grow-1">
                                        <button class="btn btn-outline-warning tambah_barang" style="width: 100%;"><i class="fa-solid fa-box-open"></i> TAMBAH BARANG</button>
                                    </div>
                                    <div><button class="btn btn-outline-info pembayaran" data-ket="bayar" style="width: 115px;"><i class="fa-solid fa-arrow-up-from-bracket"></i> NEXT</button></div>
                                </div>
                            </div>
                        </div>`;
            $(".main").html(html);
        },
        Bayar: function() {
            let data = {
                order: "Bayar",
                divisions
            };
            post_jwt(data)
                .then(res => {
                    let data = res.data;
                    bayar_n_hutang = data.data;
                    let met = [...metodes, "Hutang", "Wl"];

                    let html = `<div class="rounded border border-warning text-light p-2 rounded mb-2 text-center">`;
                    met.forEach(e => {
                        html += `${e}: <span class="fw-bold">${angka(data[e])}</span> || `;
                    })

                    let total = parseInt((data['Cash'] + data['Qris'] + data['Tap']) - (data['Hutang'] + data['Wl']));
                    html += `<span class="fw-bold text-warning">TOTAL: [ ${(total<0?"- ":"")+ angka(total)} ]</span>`;
                    html += `</div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control bg-dark text-light cari_card" placeholder="Cari...">
                            <label>Cari</label>
                        </div>`;
                    html += `<div style="max-height: 500px; overflow-y: auto;">`;
                    data.data.forEach((e, i) => {
                        html += `<div class="card text-bg-dark mb-3" data-menu="${e.identitas.nama}">
                          <div class="card-header">${(i+1)}. ${e.identitas.nama}</div>
                          <div class="card-body d-flex justify-content-between ps-4">
                              <div class="text-secondary"><small>${time_php_to_js(e.identitas.tgl,"d/m/Y H:i")} - ${e.identitas.no_nota} - <span class="${(e.identitas.no_nota.length==12?"text-secondary":"text-warning fw-bold")}">${angka(e.total)}</span></small></div>
                              <div>`;
                        if (e.identitas.no_nota.length == 12) {
                            html += `<button class="btn btn-sm btn-secondary detail_data" data-col="no_nota" data-filter="${e.identitas.no_nota}"><i class="fa-solid fa-circle-info"></i> Selesai</button>`;
                        } else {
                            html += `<button class="btn btn-sm btn-secondary detail_data" data-col="no_nota" data-filter="${e.identitas.no_nota}"><i class="fa-solid fa-circle-info"></i> Detail</button>
                                  <button class="btn btn-sm btn-success mx-1 detail_data" data-order="tambah"  data-col="no_nota" data-filter="${e.identitas.no_nota}"><i class="fa-solid fa-cart-plus"></i> Pesanan</button>
                                  <button class="btn btn-sm btn-light pembayaran" data-ket="bayar transaksi" data-user_id="${e.identitas.user_id}" data-no_nota="${e.identitas.no_nota}"><i class="fa-solid fa-arrow-up-right-from-square"></i> Bayar</button>`;

                        }
                        html += `</div>
                          </div>
                      </div>`;

                    })
                    html += `</div>`;
                    $(".main").html(html);
                })
                .catch(err => {
                    message("400", err.message);
                });


        },
        Hutang: function(tahun = undefined, bulan = undefined) {
            let data = {
                order: "Data hutang",
                divisions
            };

            post_jwt(data)
                .then(res => {
                    let data = res.data;
                    bayar_n_hutang = data.data;

                    let html = `<div class="rounded border border-warning text-warning fw-bold p-2 rounded mb-2 text-center">${angka(data.total)}</div>
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control bg-dark text-light cari_card" placeholder="Cari...">
                            <label>Cari</label>
                        </div>`;
                    html += `<div style="max-height: 500px; overflow-y: auto;">`;
                    data.data.forEach((e, i) => {
                        html += `<div class="card text-bg-dark mb-3" data-menu="${e.identitas.nama}">
                          <div class="card-header">${(i+1)}. ${e.identitas.nama}</div>
                          <div class="card-body d-flex justify-content-between ps-4">
                              <div class="text-secondary"><small class="text-warning fw-bold">${angka(e.total)}</small></div>
                              <div>
                                  <button class="btn btn-sm btn-secondary detail_data"  data-col="user_id" data-filter="${e.identitas.user_id}"><i class="fa-solid fa-circle-info"></i> Detail</button>
                                  <button class="btn btn-sm btn-success mx-1 btn_wa" data-wa="${e.identitas.wa}" data-user_id="${e.identitas.user_id}"><i class="fa-brands fa-whatsapp"></i> Whatsapp</button>
                                  <button class="btn btn-sm btn-light pembayaran" data-ket="bayar hutang user" data-user_id="${e.identitas.user_id}"><i class="fa-solid fa-arrow-up-right-from-square"></i> Bayar</button>
                              </div>
                          </div>
                      </div>`;

                    })
                    html += `</div>`;
                    $(".main").html(html);
                })
                .catch(err => {
                    message("400", err.message);
                });
        },
        update_qty: function(data, message) {

            let html = `<div class="text-center bg-dark p-3">
                <p style="font-size:12px">${message}</p>
                <p style="font-size:18px">${data.barang}</p>
                <small class="text-danger msg_stok"></small>
                <input class="form-control bg-dark text-light form-control-sm text-center mb-3 val_qty angka" type="text" value="${angka(data.qty)}">
              <div class="mb-4">
                <button class="cancel_qty btn btn-sm btn-secondary">Cancel</button>
                <button class="save_qty btn btn-sm btn-light" data-divisi="${data.divisi}" data-id="${(is_tambah_pesanan && data.is_update!=="new"?data.barang_id:data.id)}">Simpan</button>
              </div>
            </div>`;

            return html;
        },
        games: function() {
            let html = `<div class="container text-center"><div class="row g-3">`;
            barangs[divisi].forEach(e => {
                if (e.is_over == 0) {

                    html += ` <div class="col-6 game" data-id="${e.id}" data-qty="${e.qty}" data-divisi="${divisi}" data-status="${e.status}" data-no_nota="${e.no_nota}" data-nama="${e.nama}" data-user="${e.user}" data-metode="${e.metode}" data-roleplay="${e.roleplay}">
                                    <div class="bg-light opacity-75 p-3 fs-4 rounded d-flex justify-content-between">
                                        <div>
                                            <div class="text-dark text-start">${e.nama}</div>`;
                    if (e.metode == "Wl") {
                        html += `<div style="font-size:15px;" class="text-start ${'text-secondary'}">${e.ket} - ${e.room} - ${(e.roleplay=="Normal"?e.qty+ " Jam":(e.roleplay=="Paket"?"Paket "+e.qty:e.roleplay))}<span data-id="${e.transaksi_id}" class="text-dark meja fw-bold">[WL - ${e.user} - ${time_php_to_js(e.start,"H:i")}]</span></div>`;
                    } else {
                        html += `<div style="font-size:15px;" class="text-start ${(e.roleplay==""?'text-secondary opacity-75':'text-success')}">${e.ket} - ${e.room}${(e.roleplay !==""?" - "+e.roleplay:"")}${(e.waktu !==""?' <span data-id="'+e.transaksi_id+'" class="meja fw-bold">- ['+e.waktu+'] ['+angka(e.biaya)+']</span>':"")}</div>`;
                    }
                    html += `</div>
                                        <div class="d-flex gap-1">
                                            <button style="width:30px" class="btn btn-sm"><i class="fa-regular ${(e.status==1?"text-danger":"text-secondary")} fa-lightbulb fs-4"></i></button>
                                            <button style="width:30px" class="btn btn-sm"><i class="fa-regular ${(e.metode =="Wl"?"text-danger":"text-secondary")} fa-clock fs-4"></i></button>
                                            <button style="width:30px" class="btn btn-sm"><i class="fa-solid ${(e.metode =="Hutang"?"text-danger":"text-secondary")} fa-hand-holding-dollar fs-4"></i></button>
                                        </div>
                                    </div>
                                </div>
                        `;

                }
            })
            html += `</div></div>`;
            return html;
        },
        confirm: function(msg, order, divisi, id, roleplay, qty, no_nota) {

            let html = `<div class="text-center rounded border border-secondary bg-dark p-3">
                    <p style="font-size:16px" class="msg_conform">${msg}</p>
                    <div class="mb-3 body_execute_game">
                        <button class="cancel_qty btn btn-sm btn-secondary me-2">Cancel</button>`;

            if (order == "lampu") {
                html += `<button class="execute_game btn btn-sm btn-light me-3" data-id="${id}" data-order="${order}" data-divisi="${divisi}">Matikan Lampu</button>`;

            } else if (order == "lampu n hutang" || order == "bayar") {
                if (roleplay !== "Open" && roleplay !== "Paket") {
                    html += `<button class="execute_game btn btn-sm btn-light me-3" data-id="${id}" data-order="jam" data-qty="${qty}" data-divisi="${divisi}">Jam</button>`;
                }
                html += `<button class="execute_game btn btn-sm btn-light me-3" data-id="${id}" data-order="pesanan" data-divisi="${divisi}" data-no_nota="${no_nota}">Pesanan</button>`;
                html += `<button class="pembayaran btn btn-sm btn-danger" data-ket="bayar transaksi" data-id="${id}" data-no_nota="${no_nota}" data-order="bayar" data-divisi="${divisi}">Bayar</button>`;
                if (role == "Root") {
                    html += `<button class="execute_game btn btn-sm btn-danger ms-3" data-id="${id}" data-no_nota="${no_nota}" data-order="tetap hutang" data-divisi="${divisi}">Hutang</button>`;
                }
            } else if (order == "booked") {
                html += `<button class="execute_game btn btn-sm btn-light me-3" data-id="${id}" data-no_nota="${no_nota}" data-order="delete wl" data-divisi="${divisi}">Delete</button>`;
                html += `<button class="execute_game btn btn-sm btn-danger" data-id="${id}" data-no_nota="${no_nota}" data-order="${order}" data-divisi="${divisi}">Play</button>`;
            } else {
                html += `<button class="execute_game btn btn-sm btn-light me-3" data-id="${id}" data-order="wl" data-divisi="${divisi}">Waiting list</button>`;
                html += `<button class="execute_game btn btn-sm btn-danger" data-id="${id}" data-order="${order}" data-divisi="${divisi}">Play</button>`;
            }

            html += `</div><div class="detail_order mb-4"></div>
                </div>`;

            return html;
        },
        penghutang: (nama, hutang, id) => {
            let html = `<div class="rounded bg-danger mb-2 p-2">
                        <h6 class="text-center">PENGHUTANG</h6>
                        <input type="hidden" class="form-control mb-2 id_hutang" value="${id}">
                        <input type="text" class="form-control mb-2 nama_hutang" value="${nama}">
                        <input type="text" class="form-control jml_hutang" value="${angka(hutang)}">
                    </div>`;

            return html;
        },
        update_waktu: (order = undefined) => {
            if (order !== undefined) {
                update_waktu.forEach(e => {
                    if (e.roleplay !== "Open" && e.waktu == "00:00") {
                        let msg = `${e.divisi} ${e.barang} waktunya sudah habis!`;

                        // tampilkan pesan (misalnya di console atau alert)
                        message('400', msg, 'toast');

                        // jalankan notif.mp3
                        let audio = new Audio("/notif_game_over.mp3");
                        // audio.play();

                    }
                })
                return;
            }
            const mejaElements = document.querySelectorAll('.row .meja');

            let hasil = [];

            if (mejaElements.length > 0) {
                mejaElements.forEach(el => {
                    hasil.push(el.getAttribute('data-id'));
                });
            }
            if (hasil.length == 0) {
                return;
            }

            let data = {
                order: "Update waktu",
                datas: hasil,
                divisions
            }

            post("home/encode_jwt", {
                data
            }).then(req => {
                if (req.status == "200") {
                    fetchData(`playground/${req.data}`).then(res => {
                        update_waktu = res.data;
                        bayar_n_hutang = res.data2;
                        update_waktu.forEach(item => {
                            let el = document.querySelector(`.meja[data-id="${item.id}"]`);
                            if (el) {
                                // parsing waktu ke detik
                                let [h, m] = item.waktu.split(":").map(Number);
                                let totalMinutes = (Math.abs(h) * 60) + m;
                                if (item.metode == "Wl") {
                                    el.textContent = `- [${item.nama}] [${time_php_to_js(item.start,"H:i")}]`;
                                } else {
                                    if (totalMinutes >= 0) {
                                        el.textContent = `- [${item.waktu}] [${angka(item.biaya)}]`;
                                    }

                                }
                                el.dataset.barang_id = item.barang_id;
                                el.dataset.no_nota = item.no_nota;
                                // reset class dulu
                                el.classList.remove("text-warning", "text-success");

                                if (item.roleplay !== "Open") {
                                    // logika style
                                    if (totalMinutes <= 5 && totalMinutes > 1) {
                                        el.classList.add("text-warning");
                                    } else if (totalMinutes <= 1) {
                                        el.classList.add("text-danger");
                                        el.classList.remove("text-warning");
                                        el.classList.remove("text-dark");
                                    }

                                }
                            }
                        });


                    })
                } else {
                    message(req.status, req.message);
                }
            })


        }
    }

    let show = () => {
        let data = {
            order: "Show",
            id: 0,
            kategori: "Metode",
            format: "array",
            tabel: 'transaksi',
            divisi
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`playground/${req.data}`).then(res => {

                    if (res.status == "200") {
                        barangs = res.data
                        metodes = res.data2;
                        divisions = res.data3;
                        let menu_arr = [{
                            menu: 'Kasir',
                            icon: '<i class="fa-solid fa-cash-register"></i>'
                        }, {
                            menu: 'Bayar',
                            icon: '<i class="fa-solid fa-calendar-day"></i>'
                        }, {
                            menu: "Hutang",
                            icon: '<i class="fa-solid fa-hand-holding-dollar"></i>'
                        }];
                        let html = `<ul class="nav nav-pills mb-3 justify-content-center gap-2 mb-3" id="menu">`;
                        menu_arr.forEach((e, i) => {
                            html += `<li class="nav-item menus">
                            <a class="nav-link d-flex flex-column align-items-center justify-content-center rounded-circle ${(e.menu=="Kasir"?'bg-light text-dark':'bg-secondary text-light')}"
                                href="#" data-target="${e.menu}" style="width:80px; height:80px;">
                                ${e.icon}
                                <div>${e.menu.toUpperCase()}</div>

                            </a>
                        </li>`;
                        })
                        html += `</ul>`;

                        $(".menu").html(html);
                        menu_active("Kasir", "Kasir");
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
    }, 220);


    const f_diskons = () => {
        // Ambil option terpilih
        const selectedOption = $('select[name="durasi"] option:selected');
        const durasi = selectedOption.val();
        const result = {
            value: durasi,
            diskon: parseInt(selectedOption.data("diskon")) || 0,
            harga: parseInt(selectedOption.data("harga")) || 0,
        };

        let exp = durasi.split(" ");

        let roleplay = (exp.length == 1 && durasi == "Open" ? "Open" : (exp.length == 1 && durasi !== "Open" ? "Normal" : "Paket"));
        let qty = (exp.length == 3 ? parseInt(exp[1]) : (durasi == "Open" ? 0 : parseInt(result.value)));
        // Diskon pelajar/girls
        const selectedDiskon = $('input[name="diskons"]:checked');
        const diskonPelajarGirls = selectedDiskon.length ? {
            type: selectedDiskon.data("diskon"),
            value: parseInt(selectedDiskon.val())
        } : null;

        // Helper untuk bikin object diskon
        const makeDiskon = (nama, diskon = 0, id = null) => ({
            nama,
            diskon,
            id
        });

        // Paket
        const paket = durasi.startsWith("Paket") ?
            makeDiskon(durasi, result.diskon) :
            makeDiskon("Paket");

        // Weekdays
        const $weekdays = $('input[name="is_weekdays"]');
        const isWeekdays = $weekdays.is(':checked') ?
            makeDiskon("Weekdays", parseInt($weekdays.val()), $weekdays.data("id")) :
            makeDiskon("Weekdays");


        // Pelajar & Girls
        let isPelajar, isGirls;
        if (diskonPelajarGirls) {
            if (diskonPelajarGirls.type === "is_pelajar") {
                isPelajar = makeDiskon("Pelajar", diskonPelajarGirls.value);
                isGirls = makeDiskon("Girls");
            } else {
                isGirls = makeDiskon("Girls", diskonPelajarGirls.value);
                isPelajar = makeDiskon("Pelajar");
            }
        } else {
            const girlsVal = $('input[name="is_girls"]').is(':checked') ?
                parseInt($('input[name="is_girls"]').val()) :
                0;
            isGirls = makeDiskon("Girls", girlsVal);
            isPelajar = makeDiskon("Pelajar");
        }

        const diskons = [paket, isWeekdays, isPelajar, isGirls];
        let totalDiskon = 0;

        let desc_diskons = [];
        let total_isgirls = 0;
        let total_ispelajar = 0;
        let total_isweekdays = 0;

        diskons.forEach(e => {
            if (e.diskon > 0) {

                desc_diskons.push(e.nama);
            }
        })
        if (roleplay == "Normal") {
            let new_diskon = (isWeekdays.diskon > 0 ? isWeekdays.diskon : 0) * qty;
            total_isweekdays = new_diskon;
            totalDiskon = new_diskon;
            if (isGirls.diskon > 0) {
                totalDiskon = new_diskon + (isWeekdays.diskon > 0 ? result.harga - isWeekdays.diskon : result.harga);
                total_isgirls = (isWeekdays.diskon > 0 ? result.harga - isWeekdays.diskon : result.harga);
            } else if (isPelajar.diskon > 0) {
                totalDiskon = new_diskon + (isPelajar.diskon * qty);
                total_ispelajar = isPelajar.diskon * qty;
            }
        }

        if (roleplay == "Paket") {
            totalDiskon += paket.diskon + (isWeekdays.diskon > 0 ? 10000 : 0);
        }
        if (roleplay == "Open") {
            let html = `<div class="text-center text-warning p-1">TOTAL DISKON: ${angka(isWeekdays.diskon)}</div>`;
            if (roleplay == "Open" && isWeekdays.diskon > 0) {
                html += `<div class="d-flex justify-content-between">
                        <div>Weekdays</div>`;
                html += `<div>${angka(isWeekdays.diskon)}</div>`;
                html += `</div>`;

            }
            html += `</div>`;
            $(".detail_order").html(html);
        } else {

            if (totalDiskon > 0) {
                let html = `<div class="text-center text-warning p-1">TOTAL DISKON: ${angka(totalDiskon)}</div>`;
                diskons.forEach(e => {
                    if (e.diskon > 0 && roleplay == "Normal") {
                        html += `<div class="d-flex justify-content-between">
                        <div>${e.nama}</div>`;
                        if (e.nama == "Weekdays") {
                            html += `<div>${angka(total_isweekdays)}</div>`;
                        } else if (e.nama == "Pelajar") {
                            html += `<div>${angka(total_ispelajar)}</div>`;
                        } else if (e.nama == "Girls") {
                            html += `<div>${angka(total_isgirls)}</div>`;
                        } else {
                            html += `<div>${angka(e.diskon)}</div>`;
                        }

                        html += `</div>`;
                    } else if (e.diskon > 0) {
                        html += `<div class="d-flex justify-content-between">
                            <div>${e.nama}</div>`;
                        html += `<div>${angka(e.diskon)}</div>`;
                        html += `</div>`;
                    }
                })

                $(".detail_order").html(html);
            } else {
                $(".detail_order").html("");

            }
        }




        let res = {
            diskon: totalDiskon,
            qty,
            durasi: roleplay,
            desc_diskons: desc_diskons.join(",")
        }

        return res;
    }

    const play_game = (id, div, order, dp = 0) => {
        let diskons = f_diskons();
        diskons.diskon = diskons.diskon;

        const found = barangs[div].find(e => e.id == id);
        if (!found) return;

        // clone dulu biar tidak mengubah data asli
        const meja_selected = {
            ...found
        };

        meja_selected['barang'] = meja_selected['nama'];
        meja_selected['divisi'] = div;
        meja_selected['qty'] = diskons.qty;
        meja_selected['diskon'] = diskons.diskon;
        meja_selected['roleplay'] = diskons.durasi;
        meja_selected['total'] = (meja_selected.roleplay == "Open" ? 0 : (parseInt(meja_selected.harga) * parseInt(meja_selected.qty)));
        meja_selected['biaya'] = (meja_selected.roleplay == "Open" ? 0 : meja_selected.total - meja_selected.diskon);
        meja_selected['is_update'] = "new";
        meja_selected['jenis'] = div;
        meja_selected['barang_id'] = meja_selected.id;
        meja_selected['tipe'] = "Mix";
        meja_selected['link'] = "";
        meja_selected['desc_diskons'] = diskons.desc_diskons;

        delete meja_selected.diskons;
        datas.push(meja_selected);
    }

    const menu_active = (target, those = undefined) => {

        $(".menus a").removeClass("bg-light text-dark").addClass("bg-secondary text-light");

        // tambahkan active ke menu yang diklik
        if (those.length < 10) {
            $('.menus a[data-target="Kasir"]').removeClass("bg-secondary text-light").addClass("bg-light text-dark");
        } else {
            $(those).removeClass("bg-secondary text-light").addClass("bg-light text-dark");

        }

        // sembunyikan semua div
        $(".content-section").hide();

        // tampilkan div terkait
        $(target).show();
        obj_html[target]();
        // let html = obj_html[target]();
        // $(".main").html(html);
    }


    $(document).on('click', '.menus a', function(e) {
        const target = $(this).data("target");
        e.preventDefault();
        menu_active(target, this);
    });

    $(document).on('change', 'input[name="divisi"]', function(e) {
        e.preventDefault();
        let val = $(this).val();
        divisi = val;

        clear_input_transaksi();

        if (divisi == "Ps" || divisi == "Billiard") {
            $(".cari_barang").attr("readonly", true);
            let judul = (divisi == "Billiard" ? '<i class="fa-solid fa-bowling-ball"></i> ' + divisi : '<i class="fa-brands fa-playstation"></i> ' + divisi);
            let html = build_html(judul, "offcanvas");
            html += obj_html.games();
            $(".body_canvas").html(html);
            canvas.show();
            setTimeout(() => {
                obj_html.update_waktu();
            }, 200);
        } else {
            $(".cari_barang").attr("readonly", false);
        }
        $(".cari_barang").focus();

    });

    $(document).on('keyup', '.cari_barang', function(e) {
        e.preventDefault();
        let text = $(this).val();
        // filter data berdasarkan barang
        if (text == "") {
            $(".body_list_barang").html("");
            return;
        } else {
            let hasil = barangs[divisi].filter(item =>
                item.jenis !== "Kulakan" && item.barang.toLowerCase().includes(text.toLowerCase())
            );
            // tampilkan hasil
            let html = "";
            hasil.forEach(e => {
                html += `<div class="select_barang" data-id="${e.id}" >
                        <div class="d-flex justify-content-between ${(e.link !=="" && e.tipe=="Mix"?"text-warning":"")}">
                            <span>${e.barang}</span>
                            <span class="text-muted">${angka(e.harga)} [${angka(e.qty)}]</span>
                        </div>
                    </div>`;
            });
            $(".body_list_barang").html(html);
        }
    });

    $(document).on('click', '.select_barang', function(e) {
        e.preventDefault();
        let id = $(this).data("id");

        // cek apakah barang sudah ada di pesanan / datas
        let barang_exist = {};
        datas.forEach(ex => {
            if ((is_tambah_pesanan && ex.is_update !== "new" ? ex.barang_id : ex.id) == id && ex.divisi == divisi) {
                barang_exist = ex;
            }

        })


        barang_exist['divisi'] = divisi;

        // cek apakah stok 0
        if (barang_exist.id === undefined) {
            const val = barangs[divisi].find(e => e.id == id);
            const newVal = structuredClone(val); // ES2021+
            newVal.is_update = 'new';

            if (newVal.qty == 0 && newVal.tipe == "Count") {
                clear_input_transaksi();
                message("400", "Stok 0");
                return;
            }
            if (newVal.tipe == "Mix" && newVal.link !== "") {
                let exp = newVal.link.split(",");
                let stok = true;
                barangs[divisi].forEach(e => {
                    if (exp.includes(e.id) && e.qty == 0) {
                        clear_input_transaksi();
                        message("400", `Stok ${e.barang} 0`);
                        stok = false;
                        return;
                    }
                })

                if (!stok) {
                    return;
                }
            }

            newVal.qty = 1;
            newVal['divisi'] = divisi;
            $(".harga").val(angka(newVal.harga));
            $(".total").val(angka(newVal.harga * 1));
            $(".biaya").val(angka(newVal.harga * 1));
            $(".cari_barang").val(newVal.barang);

            $('.body_list_barang').html("");
            barang_selected = newVal;
        } else {

            let html = obj_html.update_qty(barang_exist, "BARANG SUDAH ADA, UBAH QTY?");
            $(".message").html(html);
            $(".message").show();
            $(".body_list_barang").html("");
            clear_input_transaksi();
            return;

        }
    });


    $(document).on('click', '.tambah_barang', function(e) {
        e.preventDefault();

        if (!barang_selected?.id) {
            message("400", "Barang belum dipilih");
            return $(".cari_barang").focus();
        }

        const cb = cari_biaya();
        if (cb.diskon > cb.harga * cb.qty) {
            message("400", "Diskon over");
            return blink('diskon');
        }


        let item = 0;
        barangs[divisi].forEach(e => {
            if (e.id == barang_selected.id) {
                item = e;
            }
        })


        if (item.tipe == "Count" && parseInt(item.qty) < cb.qty) {
            message("400", "Stok kurang. Max: " + angka(item.qty));
            blink('qty');
            return;
        }
        if (item.tipe == "Mix" && item.link !== "") {
            let exp = item.link.split(",");
            let stok = true;
            barangs[divisi].forEach(e => {
                if (exp.includes(e.id) && cb.qty > parseInt(e.qty)) {
                    message("400", `Stok ${e.barang} kurang. Max: ${angka(e.qty)}`);
                    stok = false;
                    return;
                }
            })
            if (!stok) {
                return;
            }
        }

        Object.assign(barang_selected, {
            is_update: "new",
            harga: cb.harga,
            qty: cb.qty,
            total: cb.harga * cb.qty,
            diskon: cb.diskon,
            biaya: cb.harga * cb.qty - cb.diskon
        });

        datas.push(barang_selected);
        $(".list_items").html(list_items("playground"));
        $(".super_total").val(angka(super_total().biaya));
        $(".cari_barang").focus();
        clear_input_transaksi('hapus');
    });

    $(document).on('click', '.delete_item', function(e) {
        e.preventDefault();
        let id = $(this).data("barang_id");
        let div = $(this).data("divisi");

        datas = datas.filter(e => !(e.id == id && e.divisi == div));

        $(".list_items").html(list_items("playground"));
        $(".super_total").val(angka(super_total().biaya));
        $(".cari_barang").focus();
    });

    $(document).on('click', '.update_qty', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let div = $(this).data("divisi");
        let data_update = {};

        datas.forEach(e => {
            if ((is_tambah_pesanan && e.is_update !== "new" ? e.barang_id : e.id) == id && e.divisi == div) {
                data_update = e;
            }
        })


        if (data_update.id === undefined) {
            message("400", "Barang tidak ada");
            return;
        }

        $(".message").html(obj_html.update_qty(data_update, "UPDATE QTY"));
        $(".message").show();
    });


    $(document).on('click', '.save_qty', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let div = $(this).data("divisi");
        let qty = parseInt(str_replace(".", "", $(".val_qty").val()));

        let temp_datas = [];

        let is_stok = true;

        let qty_barang = 0; // qty di db

        barangs[div].forEach(e => {
            if (e.id == id) {
                qty_barang = parseInt(e.qty);
            }
        })

        // data pesanan / datas
        let stok = true;

        datas.forEach(e => {
            const matchId = (is_tambah_pesanan && e.is_update !== "new" ? e.barang_id : e.id) == id && e.divisi == div;

            let max = (is_tambah_pesanan && e.is_update !== "new" ? (parseInt(e.qty) + qty_barang) : qty_barang);

            if (matchId) {
                if (e.tipe == "Count") {
                    if (qty > max) {
                        $(".msg_stok").html(`<div class="fs-6">Stok ${e.barang} kurang...[max: ${angka(max)}]</div>`);
                        stok = false;
                        return;
                    }
                } else if (e.tipe == "Mix" && e.link !== "") {
                    let exp = e.link.split(",");
                    barangs[divisi].forEach(ex => {
                        max = (is_tambah_pesanan && e.is_update !== "new" ? (parseInt(ex.qty) + qty_barang) : parseInt(ex.qty));

                        if (exp.includes(ex.id) && qty > max) {
                            $(".msg_stok").html(`<div class="fs-6">Stok ${ex.barang} kurang...[max: ${angka(max)}]</div>`);
                            stok = false;
                            return;
                        }
                    })
                }
                e['qty'] = qty;
                e['total'] = qty * e.harga;
                e['biaya'] = (qty * e.harga) - e.diskon;
                e['is_update'] = (e.is_update == "false" ? "true" : e.is_update);
                temp_datas.push(e);

            } else {
                temp_datas.push(e);

            }
            if (!stok) {
                return;
            }

        });

        if (!stok) return;

        datas = temp_datas;

        $(".list_items").html(list_items("playground"));
        $(".super_total").val(angka(super_total().biaya));

        $(".message").html("");
        $(".message").hide();
        $(".cari_barang").focus();
    });

    $(document).on('click', '.cancel_qty', function(e) {
        e.preventDefault();
        let order = $(this).data('order');
        $(".message").html("");
        $(".message").hide();
        $(".cari_barang").focus();

        if (order == "wl") {
            datas = [];
            user_hutang = {};

        }
    });

    $(document).on('click', '.game', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let div = $(this).data("divisi");
        let status = $(this).data("status");
        let user = $(this).data("user")
        let meja = $(this).data("nama")
        let metode = $(this).data("metode");
        let no_nota = $(this).data("no_nota");
        let roleplay = $(this).data("roleplay");
        let qty = $(this).data("qty");


        if (status == "1") {
            if (metode == "Hutang") {
                $(".message").html(obj_html.confirm("Lampu masih menyala. Tindakan?", "lampu n hutang", div, id, roleplay, qty, no_nota));
            } else {
                $(".message").html(obj_html.confirm("Lampu masih menyala. Matikan Lampu?", "lampu", div, id, roleplay, qty, no_nota));
            }
        } else if (metode == "Wl") {
            $(".message").html(obj_html.confirm("Sudah dibooking " + user + ", hapus booking?", "booked", div, id, roleplay, qty, no_nota));
        } else if (metode == "Hutang") {
            $(".message").html(obj_html.confirm("Pembayaran belum diproses. Tindakan?", "bayar", div, id, roleplay, qty, no_nota));
        } else {
            let meja_selected = barangs[div].filter(e => e.id == id)[0];
            let html = `<div>Yakin main ${div} di ${meja}?</div>`;
            html += `<div class="input-group input-group-sm my-3">
                        <label class="input-group-text fs-5">Durasi</label>
                        <select class="form-select fs-5 f_diskon" name="durasi" data-id="${id}" data-divisi="${div}">
                        <option value="Open" selected>Open</option>`;
            meja_selected.diskons.forEach(e => {
                let exp = e.nama.split(" ");
                if (exp[0] == "Paket") {
                    html += `<option data-harga="${meja_selected.harga}" data-diskon="${e.diskon}" value="${e.nama}">${e.nama}</option>`;
                }
            })

            for (let i = 1; i < 11; i++) {
                html += `<option data-harga="${meja_selected.harga}" value="${i}">${i} Jam</option>`;
            }
            html += `</select>
                    </div>
                    <div class="d-flex justify-content-center gap-3">`;
            meja_selected.diskons.forEach(e => {
                let exp = e.nama.split(" ");
                if (exp[0] == "Paket") {
                    return;
                }

                if (e.nama == "Weekdays") {
                    html += `<div class="form-check form-switch">
                                   <input class="form-check-input f_diskon" disabled name="is_weekdays" ${(e.is_weekdays==false?"":"checked")} data-id="${e.id}" value="${e.diskon}" type="checkbox" role="switch">
                                   <label class="form-check-label">${e.nama}</label>
                               </div>`;
                }
            })
            html += `<div class="body_diskons d-flex justify-content-center gap-3"></div></div>`;
            $(".message").html(obj_html.confirm(html, "main", div, id));
        }

        $(".message").show();
    });

    $(document).on('change', 'select[name="durasi"]', function(e) {
        e.preventDefault();
        let val = $(this).val();
        let id = $(this).data('id');
        let div = $(this).data('divisi');
        let meja_selected = barangs[div].filter(e => e.id == id)[0] || null;
        let is_pelajar = meja_selected.diskons[0].is_pelajar;

        let exp = val.split(" ");
        // val = `${exp[0]} ${exp[1]} Jam`;

        if (exp[0] == "Paket") {
            $(".body_diskons").html("");
        } else {
            let html = ``;
            meja_selected.diskons.forEach(e => {
                if (is_pelajar == true) {
                    if (exp.length == 1 && val !== "Open") {
                        if (parseInt(val) > 1) {
                            if (e.nama == "Pelajar" || e.nama == "Girls") {
                                html += `<div class="form-check form-switch">
                                       <input class="form-check-input f_diskon" name="diskons" data-diskon="${(e.nama=="Pelajar"?"is_pelajar":"is_girls")}" value="${e.diskon}" type="radio" role="switch">
                                       <label class="form-check-label">${e.nama}</label>
                                   </div>`;
                            }
                        } else {
                            if (e.nama == "Pelajar") {
                                html += `<div class="form-check form-switch">
                                       <input class="form-check-input f_diskon" name="diskons" data-diskon="is_pelajar" value="${e.diskon}" type="radio" role="switch">
                                       <label class="form-check-label">${e.nama}</label>
                                   </div>`;
                            }

                        }
                    }
                } else {
                    if (exp.length == 1 && val !== "Open") {
                        if (parseInt(val) > 1) {
                            if (e.nama == "Girls") {
                                html += `<div class="form-check form-switch">
                                               <input class="form-check-input f_diskon" name="is_girls" data-diskon="${e.nama}" value="${e.diskon}" type="checkbox" role="switch">
                                               <label class="form-check-label">${e.nama}</label>
                                           </div>`;

                            }

                        }

                    }
                }

                $(".body_diskons").html(html);
            })
        }
    });
    $(document).on('change', '.f_diskon', function(e) {
        e.preventDefault();
        f_diskons();
    });

    $(document).on('click', '.execute_game', function(e) {
        e.preventDefault();

        const id = $(this).data("id");
        const div = $(this).data("divisi");
        const order = $(this).data("order");
        const qty = $(this).data("qty");
        const no_nota = $(this).data("no_nota");

        if (order == "main") {
            play_game(id, div, order);
            $(".super_total").val(angka(super_total().biaya));
            canvas.hide();
            $(".message").html("");
            $(".message").hide();

            $(".list_items").html(list_items('playground'));
        } else if (order == "pesanan") {

            bayar_n_hutang.forEach(e => {
                if (e.identitas.no_nota == no_nota) {
                    datas = e.data.map(item => ({
                        ...item,
                        is_update: "false"
                    }));
                    user_hutang['nama'] = e.identitas.nama;
                    user_hutang['user_id'] = e.identitas.user_id;
                    return;
                }
            })
            is_tambah_pesanan = true;
            menu_active("Kasir", "Kasir");
            $(".message").hide();
            canvas.hide();
            $(".super_total").val(angka(super_total().biaya));

        } else if (order == "jam") {
            $(".body_execute_game").html("");
            $(".msg_conform").html("Tambah berapa jam?");
            let html = '';
            html += `<select class="form-select form-select-sm mb-3" name="tambah_jam">`;
            for (let i = 1; i < 8; i++) {
                html += `<option value="${i}">${i} Jam</option>`;
            }
            html += `</select>
                    <button class="cancel_qty btn btn-secondary me-2" data-id="${id}">Cancel</button><button data-id="${id}" data-divisi="${div}" data-ket="${order}" class="btn btn-primary tambah_jam">Save</button>`;
            $(".detail_order").html(html);

        } else if (order == "lampu") {
            let data = {
                order,
                id,
                divisions
            };
            post_jwt(data)
                .then(res => {
                    message(res.status, res.message);
                    if (res.status == "200") {
                        reset('');
                        barangs = res.data;
                        let judul = (div == "Billiard" ? '<i class="fa-solid fa-bowling-ball"></i> ' + div : '<i class="fa-brands fa-playstation"></i> ' + div);
                        let html = build_html(judul, "offcanvas");
                        html += obj_html.games();
                        $(".body_canvas").html(html);
                    }
                })
                .catch(err => {
                    message("400", err.message);
                });
        } else if (order == "wl") {
            let html = build_html("Waiting List", "modal");
            html += `<div class="container">
            <div class="bg-dark rounded mb-3">
                            <div class="form-floating mb-3">
                                <input type="number" min="0" max="23" class="form-control bg-dark text-light nama_user" placeholder="Nama" required>
                                <label>Nama</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="number" min="0" max="60" class="form-control bg-dark text-light wa_user" placeholder="No. W.a" required>
                                <label>No. W.a</label>
                            </div> 
                            <div class="d-grid">
                                <button class="btn btn-secondary btn_simpan_user">SIMPAN</button>
                            </div>
                        </div>
                        <hr>
                        <div class="form-floating position-relative">
                            <input type="text" class="form-control bg-dark text-light border-warning cari_user" data-order="wl" placeholder="Cari...">
                            <label class="text-secondary">Cari Nama</label>
                            <div class="bg-dark text-light body_list_hasil"></div>
                        </div></div>`;


            $(".body_modal_static").html(html);
            modal_static.show();
            $(".message").hide();

            myModalStatic.addEventListener('hidden.bs.modal', function() {
                if (user_hutang.id === undefined) {
                    $(".detail_order").html('<span class="text-danger text-light bg-danger p-1 rounded fw-bold">User Wl belum dimasukkan</span>')
                } else {
                    let html = '<span>Waiting list: ' + user_hutang.nama + '</span>';
                    html += `<div class="d-flex justify-content-center mb-3 gap-2">
                                <div class="input-group">
                                    <span class="input-group-text">Jam</span>
                                    <input type="text" class="form-control target_jam" placeholder="Jam" value="<?= date("H"); ?>">
                                </div>
                                <div class="input-group">
                                    <span class="input-group-text">Menit</span>
                                    <input type="text" class="form-control target_menit" placeholder="Menit" value="<?= date("i"); ?>">
                                </div>
                            </div>
                            <div class="input-group mb-3">
                                    <span class="input-group-text">Dp</span>
                                    <input type="text" class="form-control target_dp angka" placeholder="Dp">
                                </div>
                            <button class="cancel_qty btn btn-secondary me-2" data-order="hutang">Cancel</button><button data-id="${id}" data-divisi="${div}" data-ket="${order}" class="btn btn-primary btn_simpan_transaksi">Save Wl</button>`;
                    $(".detail_order").html("");
                    $(".body_execute_game").html(html);
                }
                $(".message").show();
            });


        } else if (order == "booked" || order == "delete wl" || order == "tetap hutang") {
            let transaksi = {};
            bayar_n_hutang.forEach(e => {
                if (e.identitas.no_nota == no_nota) {
                    e.data.forEach(x => {
                        if (x.divisi == div && x.barang_id == id) {
                            transaksi = x;
                            return;
                        }
                    })
                }
            })
            let data = {
                order,
                id: transaksi.id
            };
            post_jwt(data)
                .then(res => {
                    message(res.status, res.message);
                    setTimeout(() => {
                        location.reload();
                    }, 800);
                })
                .catch(err => {
                    message("400", err.message);
                });
        } else {
            $(".detail_order").html("");
        }



    });

    $(document).on('click', '.tambah_jam', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let div = $(this).data("divisi");
        let jam = $('select[name="tambah_jam"]').val();

        let transaksi_id = $('.meja[data-barang_id="' + id + '"]').data('id');

        if (transaksi_id == undefined) {
            message("400", "Transaksi id 0");
            return;
        }

        let data = {
            order: "jam",
            id: transaksi_id,
            jam,
            divisions
        };

        post_jwt(data)
            .then(res => {
                message(res.status, res.message);
                if (res.status == "200") {
                    reset('');
                    barangs = res.data;
                    let judul = (div == "Billiard" ? '<i class="fa-solid fa-bowling-ball"></i> ' + div : '<i class="fa-brands fa-playstation"></i> ' + div);
                    let html = build_html(judul, "offcanvas");
                    html += obj_html.games();
                    $(".body_canvas").html(html);
                    $(".message").html("");
                    $(".message").hide();

                }
            })
            .catch(err => {
                message("400", err.message);
            });

    });
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
            order: "Cari User",
            text,
            id: 0,
            filters: ["Admin", "Karyawan"],
            tabel: "user",
            is_data: "karyawan"
        };

        if (order == "hutang" || order == "wl") {
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

        if (order == "wl") {
            user_hutang = {
                id,
                nama
            }
            modal_static.hide();
            return;
        }

        const existing = $('.nama_hutang');

        if (existing.length === 0) {
            user_hutang = {
                id,
                nama
            }

            $('.before_penghutang').after(obj_html.penghutang(nama, hutang, id));
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

    });

    $(document).on('click', '.close_cari_user', function(e) {
        e.preventDefault();
        $(".body_modal_static").html("");
        modal_static.hide();
    });

    // detail data bayar n hutang
    $(document).on('click', '.detail_data', function(e) {
        e.preventDefault();
        let filter = $(this).data("filter");
        let col = $(this).data("col");
        let order = $(this).data("order");


        let data = [];
        let total = 0;
        let temp_user_penghutang = {};
        bayar_n_hutang.forEach(e => {
            if (e.identitas[col] == filter) {
                data = e.data;
                total = e.total;
                temp_user_penghutang['nama'] = e.identitas.nama;
                temp_user_penghutang['user_id'] = e.identitas.user_id;
                return;
            }
        })

        if (order == "tambah") {
            datas = data.map(item => ({
                ...item,
                is_update: "false"
            }));
            user_hutang = temp_user_penghutang;
            is_tambah_pesanan = true;
            menu_active("Kasir", "Kasir");
            $(".super_total").val(angka(super_total().biaya));
            return;
        }
        let html = build_html("DETAIL", "modal");
        html += '<div class="container">';
        html += tabel_detail_hutang(data, total);
        $(".body_modal").html(html);
        modal.show();

    });

    setInterval(() => {
        obj_html.update_waktu('notif');
    }, 3000);
</script>



<?= $this->endSection() ?>