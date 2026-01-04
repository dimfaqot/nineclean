<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>

<div class="main"></div>

<script>
    loading();
    let datas = [];
    let show = () => {

        let data = {
            order: "Show",
            id: 0,
            'tabel': 'profile'
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`profile/${req.data}`).then(res => {
                    loading("close");
                    datas = res.data2;
                    if (res.status == "200") {
                        let html = `
                                <div class="form-floating mb-2">
                                    <input type="text" class="form-control bg-dark text-light border border-warning" name="nama" value="${res.data.nama}" placeholder="Nama" required>
                                    <label class="text-secondary">Nama</label>
                                </div>
                                <div class="form-floating mb-2">
                                    <input type="text" class="form-control bg-dark text-light border border-warning" name="pendiri" value="${res.data.pendiri}" placeholder="Pendiri" required>
                                    <label class="text-secondary">Pendiri</label>
                                </div>
                                <div class="form-floating mb-2">
                                    <input type="text" class="form-control bg-dark text-light border border-warning" name="manager" value="${res.data.manager}" placeholder="Manager" required>
                                    <label class="text-secondary">Manager</label>
                                </div>
                                <div class="form-floating mb-2">
                                    <input type="date" class="form-control bg-dark text-light border border-warning" name="tgl_berdiri" value="${time_php_to_js(res.data.tgl_berdiri, "Y-m-d")}" required>
                                    <label class="text-secondary">Tgl. Berdiri</label>
                                </div>
                                <div class="form-floating mb-2">
                                    <input type="text" class="form-control bg-dark text-light border border-warning" name="sub_unit" value="${res.data.sub_unit}" placeholder="Sub Unit">
                                    <label class="text-secondary">Sub Unit</label>
                                </div>
                                <div class="form-floating mb-3 detail" style="cursor: pointer;">
                                    <input type="text" class="form-control bg-dark text-light border border-secondary detail" value="${angka(datas.total)}" readonly>
                                    <label class="text-secondary">Jml. Modal</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control bg-dark text-light border border-warning" name="modal_asal" value="${res.data.modal_asal}" placeholder="Asal Modal">
                                    <label class="text-secondary">Asal Modal</label>
                                </div>

                                <div class="d-grid">
                                    <button type="button" class="btn btn-lg btn-secondary submit">Save</button>
                                </div>
                                    `;

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

    $(document).on('click', '.detail', function(e) {
        e.preventDefault();

        let html = build_html("DETAIL MODAL", "offcanvas");
        html += `
                                <h6 class="text-warning">TOTAL: ${angka(datas.total)}</h6>
                                <input class="form-control form-control-sm bg-dark text-light cari mb-2" placeholder="Cari">
                                <table class="table table-sm table-dark" style="font-size:12px">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center">Tgl</th>
                                            <th class="text-center">Barang</th>
                                            <th class="text-center">qty</th>
                                            <th class="text-center">Biaya</th>
                                        </tr>
                                    </thead>
                                    <tbody class="tabel_search">`;
        datas.data.forEach((e, i) => {
            html += `<tr>
                                            <th scope="row">${(i+1)}</th>
                                            <td>${time_php_to_js(e.tgl)}</td>
                                            <td class="text-start">${e.barang}</td>
                                            <td>${angka(e.qty)}</td>
                                            <td class="text-end">${angka(e.biaya)}</td>
                                        </tr>`;
        })
        html += `</tbody>
                                </table>`;
        $(".body_canvas").html(html);
        canvas.show();

    });

    $(document).on('click', '.submit', function(e) {
        e.preventDefault();
        if (!cek_form()) {
            return;
        }
        loading();
        let order = 'Edit';
        let id = $(this).data("id");

        let data = {
            order,
            id,
            nama: $('input[name="nama"]').val(),
            pendiri: $('input[name="pendiri"]').val(),
            manager: $('input[name="manager"]').val(),
            tgl_berdiri: $('input[name="tgl_berdiri"]').val(),
            sub_unit: $('input[name="sub_unit"]').val(),
            modal_asal: $('input[name="modal_asal"]').val(),
            tabel: "profile"
        };

        post("home/encode_jwt", {
            data
        }).then(req => {
            if (req.status == "200") {
                fetchData(`profile/${req.data}`).then(res => {
                    loading("close");
                    message(res.status, res.message);
                    if (res.status == "200") {
                        setTimeout(() => {
                            location.reload();
                        }, 1000);

                    }
                })
            } else {
                loading("close");
                message(res.status, res.message);
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