<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>

<div class="input-group input-group-sm mb-2">
    <input type="text" class="form-control bg-dark text-light border-secondary cari_card" placeholder="Cari..." aria-label="Recipient's username" aria-describedby="button-addon2">
    <button class="btn btn-outline-light form_input" data-order="Add" type="button"><i class="fa-solid fa-circle-plus"></i> <?= menu()['menu']; ?></button>
</div>
<div class="main"></div>

<script>
    loading();
    let datas = [];
    let games = [];
    let rooms = [];

    let diskons = (id) => {
        let data = datas.find(item => item.id == id);

        let html = `<div class="bg-secondary fw-bold p-2 text-center mb-2">HARGA: ${angka(data.harga)}</div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control bg-dark text-light" name="nama" placeholder="Nama">
                        <label for="floatingInput">Nama</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control bg-dark text-light angka" name="diskon" placeholder="Diskon">
                        <label for="floatingInput">Diskon</label>
                    </div>

                    <div class="d-grid mb-3">
                        <button class="btn btn-secondary btn_diskon" data-harga="${data.harga}" data-id="${data.id}">Save</button>
                    </div>

                    <div class="mb-2">DISKON - ${angka(data.total_diskon)}</div>
                    <div style="max-height: 600px;overflow-y: auto;">
                        <table class="table table-dark table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Nama</th>
                                    <th class="text-center">Diskon</th>
                                </tr>
                            </thead>
                            <tbody>`;
        data.diskon.forEach((e, i) => {
            html += `<tr>
                                    <td class="text-center">${(i+1)}</td>
                                    <td class="text-start ${(e.nama !=="Weekdays"?"update":"")}" data-id="${e.id}" data-col="nama" contenteditable="${(e.nama !=="Weekdays"?"true":"false")}"">${e.nama}</td>
                                    <td class="text-end update" data-id="${e.id}" data-col="diskon" contenteditable="true">${angka(e.diskon)}</td>
                                </tr>`;
        });
        html += `</tbody>
                        </table>
                    </div>`;

        $('.body_diskons').html(html);
    }

    $(document).on('click', '.diskon', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let game = $(this).data("game");
        let judul = (game == "Billiard" ? '<i class="fa-solid fa-bowling-ball"></i> ' : '<i class="fa-brands fa-playstation"></i> ');
        judul += $(this).data("judul");

        let html = build_html(judul, "offcanvas");

        html += `<div class="container body_diskons">`;
        html += `</div>`;


        $(".body_canvas").html(html);
        canvas.show();

        diskons(id);

    });
    $(document).on('blur', '.update', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let col = $(this).data("col");
        let val = $(this).text();

        let data = {
            order: "Edit Diskon",
            id,
            col,
            val,
            tabel: "games"
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`games/${req.data}`).then(res => {
                    loading("close");
                    message(res.status, res.message);
                    datas = res.data;

                })
            } else {
                loading("close");
                message(res.status, res.message);
            }
        })

    });
    $(document).on('click', '.btn_diskon', function(e) {
        e.preventDefault();
        let id = $(this).data("id");
        let harga = $(this).data("harga");
        let nama = $('input[name="nama"]').val();
        let diskon = $('input[name="diskon"]').val();

        if (nama == "" || diskon == "") {
            message("400", "Semua harus diisi");
            return;
        }
        if (angka_to_int(diskon) > angka_to_int(harga.toString())) {
            message("400", "Diskon terlalu besar");
            return;
        }


        let data = {
            order: "Add Diskon",
            id,
            nama,
            diskon,
            harga,
            tabel: "games"
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`games/${req.data}`).then(res => {
                    loading("close");
                    message(res.status, res.message);
                    datas = res.data;

                    diskons(id);

                })
            } else {
                loading("close");
                message(res.status, res.message);
            }
        })

    });

    let main = () => {
        let html = ``;
        datas.forEach((e, i) => {
            html += `<div class="card text-bg-dark mb-3" data-menu="${e.nama}">
                                        <div class="card-header d-flex justify-content-between">
                                            <div>${(i+1)}. ${e.nama}</div>
                                            <div class="text-secondary">${(e.game=="Billiard"?'<i class="fa-solid fa-bowling-ball"></i>':'<i class="fa-brands fa-playstation"></i>')}</div>
                                        </div>
                                        <div class="card-body d-flex justify-content-between ps-4">
                                            <div class="text-secondary"><small>${angka(e.harga)} - ${e.ket}</small></div>
                                            <div>`;
            if (role == "Root") {
                html += `<button class="btn btn-sm btn-success me-2 diskon" data-game="${e.game}" data-id="${e.id}" data-judul="${e.nama} ${e.room}">Diskon</button>`;
            }
            html += `<button class="btn btn-sm btn-light me-2 form_input" data-order="Edit" data-id="${e.id}">Edit</button>
                                                <button class="btn btn-sm btn-danger delete" data-id="${e.id}" data-message="Yakin hapus data ini?" data-tabel="<?= menu()['tabel']; ?>" data-is_reload="reload">Delete</button>
                                            </div>
                                        </div>
                                    </div>`;

        })
        $(".main").html(html);
    }
    const show = (order = undefined) => {
        let data = {
            order: "Show",
            id: 0,
            kategori: "Games",
            format: "array",
            tabel: 'games',
            controller
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`games/${req.data}`).then(res => {
                    if (res.status == "200") {
                        datas = res.data;
                        games = res.data2;
                        rooms = res.data3;
                        main();
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
                        <select class="form-select bg-dark text-light border-secondary rounded" name="game" required>`;
        if (order == "Add") {
            html += `<option selected value="">Pilih Game</option>`;
        }
        games.forEach(e => {
            html += `<option ${(e==data.game?"selected":"")} value="${e}">${e}</option>`;

        })
        html += `</select>
                        <label class="text-secondary">Game</label>
                    </div>`;
        html += `<div class="form-floating mb-3">
                        <select class="form-select bg-dark text-light border-secondary rounded" name="room" required>`;
        if (order == "Add") {
            html += `<option selected value="">Pilih Room</option>`;
        }
        rooms.forEach(e => {
            html += `<option ${(e==data.room?"selected":"")} value="${e}">${e}</option>`;

        })
        html += `</select>
                        <label class="text-secondary">Room</label>
                    </div>`;
        html += `<div class="form-floating mb-3">
                        <input type="text" name="nama" ${(order=="Edit"?'value="'+data.nama+'"':"")} class="form-control bg-dark text-light" placeholder="Nama" required>
                        <label class="text-secondary">Nama</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="ket" ${(order=="Edit"?'value="'+data.ket+'"':"")} class="form-control bg-dark text-light" placeholder="Ket" required>
                        <label class="text-secondary">Ket</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="text" name="harga" ${(order=="Edit"?'value="'+angka(data.harga)+'"':"")} class="form-control bg-dark text-light angka" placeholder="Harga" required>
                        <label class="text-secondary">Harga</label>
                    </div>
                    <div class="form-floating mb-3">
                        <input type="number" name="iot_id" ${(order=="Edit"?'value="'+angka(data.harga)+'"':"")} class="form-control bg-dark text-light angka" placeholder="Iot" required>
                        <label class="text-secondary">Iot</label>
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
            game: $('select[name="game"]').val(),
            room: $('select[name="room"]').val(),
            nama: $('input[name="nama"]').val(),
            ket: $('input[name="ket"]').val(),
            iot_id: $('input[name="iot_id"]').val(),
            harga: angka_to_int($('input[name="harga"]').val()),
            tabel: "games"
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`games/${req.data}`).then(res => {
                    loading("close");
                    message(res.status, res.message);
                    if (res.status == "200") {
                        datas = res.data;
                        main();
                    }
                })
            } else {
                loading("close");
                message(res.status, res.message);
            }
        })
    });
</script>
<?= $this->endSection() ?>