<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>

<div class="text-center fw-bold mb-3" style="font-size: 12px;">
    <div>WELCOME</div>
    -[<span class="bg-dark"><?= strtoupper(user()['nama']); ?></span>]-
</div>

<?php if (count(session('dbs')) > 1 && session('db') !== ''): ?>
    <div class="border rounded border-secondary text-center p-3 mb-2 main">
        <h6>PILIH UNIT</h6>

        <select class="form-select  bg-dark text-secondary mb-2" name="db">
            <?php if (session('db') == ''): ?>
                <option selected>Pilih...</option>
            <?php endif; ?>
            <?php foreach (session('dbs') as $i): ?>
                <option value="<?= $i; ?>" <?= (session('db') !== '' && $i == session('db') ? 'selected' : '') ?>><?= upper_first($i); ?></option>
            <?php endforeach; ?>
        </select>


        <?php if (user()['role'] == "Root" && session()->has('lokasi') && count($data) > 0): ?>
            <div class="body_lokasi" style="font-size:12px">
                <?php foreach ($data as $i): ?>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" <?= (session('lokasi') == $i['value'] ? "checked" : "") ?> type="radio" name="lokasi" value="<?= $i['value']; ?>">
                        <label class="form-check-label"><?= $i['value']; ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="body_lokasi" style="font-size:12px"></div>
        <?php endif; ?>

        <div class="d-grid">
            <button type="button" class="submit btn btn-secondary">Save</button>
        </div>
    </div>
<?php endif; ?>

<div class="border rounded border-secondary p-3 menu"></div>



<script>
    let datas = [];
    let datases = [];
    let metodes = [];
    let user_hutang = [];
    loading();
    <?php if (session('db') == '' && count(session('dbs')) > 0): ?>
        let html = '';
        html += `<div class="container">`;
        html += `
        <h6>PILIH UNIT</h6>
            <select class="form-select  bg-dark text-secondary mb-2" name="db">
                <?php if (session('db') == ''): ?>
                    <option selected>Pilih...</option>
                <?php endif; ?>
                <?php foreach (session('dbs') as $i): ?>
                    <option value="<?= $i; ?>" <?= (session('db') !== '' && $i == session('db') ? 'selected' : '') ?>><?= upper_first($i); ?></option>
                <?php endforeach; ?>
            </select>`;

        <?php if (user()['role'] == "Root" && session()->has('lokasi')): ?>
            html += `<div class="body_lokasi" style="font-size:12px"></div>`;
        <?php endif; ?>
        html += `<div class="d-grid">
                    <button type="button" class="btn btn-secondary submit">Save</button>
                </div>`;
        html == `</div>`;

        setTimeout(() => {
            $(".body_modal_static").html(html);
            modal_static.show();
            loading("close");
        }, 500);

    <?php else: ?>
        setTimeout(() => {
            home_menu();
        }, 500);

    <?php endif; ?>

    let tahuns = [];
    let bulans = [];
    let home_menu = (order = undefined, tahun, bulan) => {

        if (order !== undefined) {
            let html = `
                <div class="d-flex justify-content-center gap-2">
                <div class="form-floating flex-fill" style="width: 50%;">
                    <select class="form-select bg-dark text-secondary tahun">`;
            tahuns.forEach(e => {
                html += `<option ${(e.tahun==tahun?"selected":"")} value="${e.tahun}">${e.tahun}</option>`;

            })

            html += `</select>
                    <label>Tahun</label>
                </div>
                <div class="form-floating flex-fill" style="width: 50%;">
                    <select class="form-select bg-dark text-secondary bulan">`;
            bulans.forEach(e => {
                html += `<option ${(e.satuan==bulan?"selected":"")} value="${e.satuan}">${e.bulan}</option>`;

            })

            html += `</select>
                    <label>Bulan</label>
                </div>
            </div>
            <div class="d-flex justify-content-center gap-2 mt-3">`;
            html += body_menu(order);
            html += `</div>
            <div class="body_detail mt-4"></div>
        `;

            $(".menu").html(html);

            return;
        }

        let data = {
            order: "Menu",
            id: 0,
            tabel: "transaksi"
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`home/${req.data}`).then(res => {
                    tahuns = res.data;
                    bulans = res.data2;
                    let html = `
                <div class="d-flex justify-content-center gap-2">
                <div class="form-floating flex-fill" style="width: 50%;">
                    <select class="form-select bg-dark text-secondary tahun">`;
                    tahuns.forEach(e => {
                        html += `<option ${(e.tahun=="<?= date("Y"); ?>"?"selected":"")} value="${e.tahun}">${e.tahun}</option>`;

                    })

                    html += `</select>
                    <label>Tahun</label>
                </div>
                <div class="form-floating flex-fill" style="width: 50%;">
                    <select class="form-select bg-dark text-secondary bulan">`;
                    bulans.forEach(e => {
                        html += `<option ${(e.satuan=="<?= date("n"); ?>"?"selected":"")} value="${e.satuan}">${e.bulan}</option>`;

                    })

                    html += `</select>
                    <label>Bulan</label>
                </div>
            </div>
            <div class="d-flex justify-content-center gap-2 mt-3">`;
                    html += body_menu("Menu");
                    html += `</div>
            <div class="body_detail mt-4"></div>
        `;

                    $(".menu").html(html);

                    loading(false);
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })
    }


    const body_menu = (order = "") => {
        let html = `
                        <a href="" data-order="transaksi" data-kategori="Kantin" class="border ${(order=='transaksi'?"bg-success":"")} data rounded-circle border-secondary text-center p-1 ${(order=="transaksi"?"bg-success":"")}" style="width:50px;height:50px;text-decoration:none;color:white;cursor:pointer">
                            <div><i class="${(order=="transaksi"?"text-light":"text-success")} fa-solid fa-arrow-turn-down"></i></div>
                            <div style="font-size: xx-small;">Masuk</div>
                        </a>
                        <a href="" data-kategori="Inv" data-order="pengeluaran" class="border  ${(order=='pengeluaran'?"bg-success":"")} data rounded-circle border-secondary text-center p-1 ${(order=="pengeluaran"?"bg-success":"")}" style="width:50px;height:50px;text-decoration:none;color:white;cursor:pointer">
                            <div><i class="${(order=="pengeluaran"?"text-light":"text-success")} fa-solid fa-arrow-turn-up"></i></div>
                            <div style="font-size: xx-small;">Keluar</div>
                        </a>
                        <a href="" data-kategori="Metode" data-order="hutang" class="border data rounded-circle border-secondary text-center p-1 ${(order=="hutang"?"bg-success":"")}" style="width:50px;height:50px;text-decoration:none;color:white;cursor:pointer">
                            <div><i class="${(order=="hutang"?"text-light":"text-success")} fa-solid fa-spinner"></i></div>
                            <div style="font-size: xx-small;">Hutang</div>
                        </a>
                        <a href="" data-order="laporan" class="border data rounded-circle border-secondary text-center p-1 ${(order=="laporan"?"bg-success":"")}" style="width:50px;height:50px;text-decoration:none;color:white;cursor:pointer">
                            <div><i class="${(order=="laporan"?"text-light":"text-success")} fa-solid fa-arrow-trend-up"></i></div>
                            <div style="font-size: xx-small;">Laporan</div>
                        </a>
                    `;
        return html;
    }



    $(document).on('keyup', '.cari', function(e) {
        e.preventDefault();
        let value = $(this).val().toLowerCase();
        $('.tabel_search tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });

    });
    $(document).on('click', '.submit', function(e) {
        e.preventDefault();
        let db = $('select[name="db"]').val();
        let lokasi = $('input[name="lokasi"]:checked').val();

        if (role == "Root") {
            post(`home/${(lokasi===undefined?"check_db":"change_db")}`, {
                db,
                lokasi
            }).then(res => {
                if (res.status == "200") {
                    if (res.data.length > 0) {
                        let html = ``;
                        res.data.forEach((e, i) => {
                            html += `<div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="lokasi" value="${e.value}">
                                        <label class="form-check-label">${e.value}</label>
                                    </div>`;
                        })

                        $(".body_lokasi").html(html);

                    } else {
                        post("home/change_db", {
                            db
                        }).then(req => {
                            if (req.status == "200") {
                                location.reload();
                            } else {
                                loading("close");
                                message(req.status, req.message);
                            }
                        })
                    }
                } else {
                    loading("close");
                    message(req.status, req.message);
                }
            })
        } else {

            post("home/change_db", {
                db
            }).then(req => {
                if (req.status == "200") {
                    // location.reload();
                } else {
                    loading("close");
                    message(req.status, req.message);
                }
            })
        }

    });

    $(document).on('click', '.cetak', function(e) {
        e.preventDefault();
        let tahun = ($(".tahun").val() == undefined || $(".tahun").val() == "" ? "<?= date('Y'); ?>" : $(".tahun").val());
        let bulan = ($(".bulan").val() == undefined || $(".bulan").val() == "" ? "<?= date('n'); ?>" : $(".bulan").val());
        let jenis = $(this).data("jenis");

        let data = {
            order: "laporan",
            tahun,
            bulan,
            jenis,
            id: 0
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                let url = `${api}cetak/${req.data}`;
                window.open(url, '_blank');
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })

    });
    $(document).on('click', '.backup', function(e) {
        e.preventDefault();

        let tahun = ($(".tahun").val() == undefined || $(".tahun").val() == "" ? "<?= date('Y'); ?>" : $(".tahun").val());
        let bulan = ($(".bulan").val() == undefined || $(".bulan").val() == "" ? "<?= date('n'); ?>" : $(".bulan").val());
        let order = "laporan";
        let jenis = "Backup";

        let data = {
            order,
            tahun,
            bulan,
            jenis,
            id: 0
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`home/${req.data}`).then(res => {
                    loading("close");

                    let html = build_html("Backup", "modal");

                    html += `<div class="container"><h6 class="text-warning">TOTAL: ${angka(res.data2)}</h6>`;
                    html += `<table class="table table-sm table-dark" style="font-size:12px">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">Tahun</th>
                                <th class="text-center">Masuk</th>
                                <th class="text-center">Keluar</th>
                                <th class="text-center">Saldo</th>
                                <th class="text-center">Lock</th>
                            </tr>
                        </thead>
                        <tbody class="tabel_unlock">`;
                    res.data.forEach((e, i) => {
                        html += `<tr>
                                <th scope="row">${(i+1)}</th>
                                <td>${time_php_to_js(e.tahun)}</td>
                                <td class="text-start">${e.masuk}</td>
                                <td class="text-end">${angka(e.keluar)}</td>
                                <td class="text-end">${angka(e.saldo)}</td>
                                <td class="text-center"><a class="unlock text-warning" data-keep="${e.keep}" data-id="${e.id}">${(e.keep==1?'<i class="fa-solid fa-lock text-secondary"></i>':'<i class="fa-solid fa-lock-open text-success"></i>')}</a></td>
                            </tr>`;
                    })
                    html += `</tbody>
                    </table>`;
                    html += `</div>`;

                    $(".body_modal").html(html);
                    modal.show();
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })

    });
    $(document).on('click', '.unlock', function(e) {
        e.preventDefault();
        loading();
        let id = $(this).data("id");
        let keep = $(this).data("keep");

        let data = {
            order: "laporan",
            keep,
            jenis: "Unlock",
            id
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`home/${req.data}`).then(res => {
                    loading("close");
                    message(res.status, res.message);

                    let html = '';
                    res.data.forEach((e, i) => {
                        html += `<tr>
                                <th scope="row">${(i+1)}</th>
                                <td>${time_php_to_js(e.tahun)}</td>
                                <td class="text-start">${e.masuk}</td>
                                <td class="text-end">${angka(e.keluar)}</td>
                                <td class="text-end">${angka(e.saldo)}</td>
                                <td class="text-center"><a class="unlock text-warning" data-keep="${e.keep}" data-id="${e.id}">${(e.keep==1?'<i class="fa-solid fa-lock text-secondary"></i>':'<i class="fa-solid fa-lock-open text-success"></i>')}</a></td>
                            </tr>`;
                    })

                    $(".tabel_unlock").html(html);
                })
            } else {
                loading("close");
                message(req.status, req.message);
            }
        })
        // fetchData(`home/unlock/<?= session('db') ?>/${id}/${keep}`).then(res => {
        //     loading("close");
        //     message(res.status, res.message);

        //     let html = '';
        //     res.data.forEach((e, i) => {
        //         html += `<tr>
        //                         <th scope="row">${(i+1)}</th>
        //                         <td>${time_php_to_js(e.tahun)}</td>
        //                         <td class="text-start">${e.masuk}</td>
        //                         <td class="text-end">${angka(e.keluar)}</td>
        //                         <td class="text-end">${angka(e.saldo)}</td>
        //                         <td class="text-center"><a class="unlock text-warning" data-keep="${e.keep}" data-id="${e.id}">${(e.keep==1?'<i class="fa-solid fa-lock text-secondary"></i>':'<i class="fa-solid fa-lock-open text-success"></i>')}</a></td>
        //                     </tr>`;
        //     })

        //     $(".tabel_unlock").html(html);

        // })

    });
</script>
<?= $this->endSection() ?>