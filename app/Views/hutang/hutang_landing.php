<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>

<div class="input-group input-group-sm mb-2">
    <input type="text" class="form-control bg-dark text-light border-secondary cari_card" placeholder="Cari..." aria-label="Recipient's username" aria-describedby="button-addon2">
</div>



<?php foreach ($data as $k => $i): ?>
    <div class="card text-bg-dark mb-3" data-menu="<?= $i['nama']; ?>">
        <div class="card-header"><?= ($k + 1) . ". " . $i['nama']; ?></div>
        <div class="card-body d-flex justify-content-between ps-4">
            <div class="text-secondary"><small><?= angka($i['total_biaya']); ?></small></div>
            <div>
                <button class="btn btn-sm btn-secondary btn_detail" data-total_biaya="<?= $i['total_biaya']; ?>" data-nama="<?= $i['nama']; ?>" data-user_id="<?= $i['user_id']; ?>"><i class="fa-solid fa-circle-info"></i></button>
                <button class="btn btn-sm btn-success mx-1 btn_wa" data-total_biaya="<?= $i['total_biaya']; ?>" data-user_id="<?= $i['user_id']; ?>"><i class="fa-brands fa-whatsapp"></i></button>
                <button class="btn btn-sm btn-light btn_kasir" data-user_id="<?= $i['user_id']; ?>" data-nama="<?= $i['nama']; ?>"><i class="fa-solid fa-arrow-up-right-from-square"></i> Bayar</button>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script>
    let detail = (data, total_biaya) => {
        let html = `<div class="bg-secondary fw-bold p-2 mb-2">TOTAL: ${angka(total_biaya)}</div>
                        <table class="table table-dark table-bordered" style="font-size: 10px;">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Tgl</th>
                                    <th class="text-center">Barang</th>
                                    <th class="text-center">Harga</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Diskon</th>
                                    <th class="text-center">Biaya</th>
                                </tr>
                            </thead>
                            <tbody>`;
        data.forEach((e, i) => {
            html += `<tr>
                        <td class="text-center">${(i+1)}</td>
                        <td class="text-center">${time_php_to_js(e.tgl)}</td>
                        <td class="text-start">${e.barang}</td>
                        <td class="text-end">${angka(e.harga)}</td>
                        <td class="text-end">${angka(e.qty)}</td>
                        <td class="text-end">${angka(e.diskon)}</td>
                        <td class="text-end">${angka(e.biaya)}</td>
                    </tr>`;
        });
        html += `</tbody>
                        </table>`;

        return html;
    }
    $(document).on("click", ".btn_detail", function(e) {
        e.preventDefault();
        let user_id = $(this).data("user_id");
        let total_biaya = $(this).data("total_biaya");
        let nama = $(this).data("nama");
        post("hutang/detail", {
            user_id
        }).then(res => {
            let html = build_html(nama, "offcanvas");
            html += detail(res.data, total_biaya);
            $(".body_canvas").html(html);
            loading("close");
            canvas.show();
        })
    })

    $(document).on('click', '.btn_wa', function(e) {
        e.preventDefault();
        let user_id = $(this).data("user_id");
        let total_biaya = $(this).data("total_biaya");

        post("hutang/wa", {
            user_id
        }).then(res => {
            if (res.status == "200") {
                let no_hp = "62";
                no_hp += res.data2.wa.substring(1);

                let text = "_Assalamualaikum Wr. Wb._%0a";
                text += "Yth. *" + res.data2.nama + '*%0a%0a';
                text += 'Tagihan Anda di 9CLEAN:%0a%0a';
                text += '*No. -- Tgl -- Barang -- Harga -- Qty -- Total -- Diskon -- Biaya*%0a'

                let x = 1;
                res.data.forEach((e, i) => {
                    text += (x++) + '. ' + time_php_to_js(e.tgl) + ' - ' + e.barang + ' - ' + angka(e.harga) + ' - ' + angka(e.qty) + ' - ' + angka(e.total) + ' - ' + angka(e.diskon) + ' - ' + angka(e.biaya) + '%0a';

                })
                text += '%0a';
                text += "*TOTAL: " + angka(total_biaya) + "*%0a%0a";
                text += "*_Mohon segera dibayar njihhh..._*%0a";
                text += "_Wassalamualaikum Wr. Wb._%0a%0a";
                text += 'Petugas%0a%0a';
                text += '<?= user()['nama']; ?>';
                text += "%0a%0a";
                text += "_(*)Pesan ini dikirim oleh sistem, jadi mohon maklum dan ampun tersinggung njih._";
                text += "%0a%0a";
                // text += "Info lebih lengkap klik: %0a%0a";
                // text += jwt;
                loading("close");

                // let url = "https://api.whatsapp.com/send/?phone=" + no_hp + "&text=" + text;
                let url = "whatsapp://send/?phone=" + no_hp + "&text=" + text;

                location.href = url;
            }
        })


    });

    const kasir = (super_total, user_id) => {
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
                    

                    <div class="d-grid gap-2 mt-4">
                        <button class="btn btn-info btn_bayar" data-user_id="${user_id}" style="width:100%"><i class="fa-solid fa-arrow-right-to-bracket"></i> BAYAR</button>
                    </div>
                </div>`;

        return html;
    }

    let super_total = {};
    $(document).on('click', '.btn_kasir', function(e) {
        e.preventDefault();
        let user_id = $(this).data("user_id");
        let nama = $(this).data("nama");

        post("hutang/kasir", {
            user_id
        }).then(res => {
            super_total = res.data;
            let html = build_html(nama.toUpperCase(), "offcanvas");
            html += kasir(res.data, user_id);

            $(".body_canvas").html(html);
            loading("close");
            canvas.show();

            $('#main_canvas').on('shown.bs.offcanvas', function() {
                $('.uang_pembayaran').trigger('focus').select();
            });

        })



    });

    const blink = (cls, duration = 2000, interval = 300) => {
        let el = $("." + cls);
        let isOn = false;

        const blinkInterval = setInterval(() => {
            el.toggleClass("bg-success bg-danger");
            isOn = !isOn;
        }, interval);

        // Hentikan blinking setelah `duration` ms
        setTimeout(() => {
            clearInterval(blinkInterval);
            el.removeClass("bg-danger").addClass("bg-success"); // Reset ke awal
        }, duration);
    };

    $(document).on('click', '.btn_bayar', function(e) {
        e.preventDefault();
        let user_id = $(this).data("user_id");
        let uang = $(".uang_pembayaran").val();
        uang = (uang == "" ? "0" : uang);
        uang = angka_to_int(uang);

        if (uang < super_total.biaya) {
            message("400", "Uang kurang");
            blink("uang_pembayaran");
            loading("close");
            return;
        }

        post("hutang/bayar", {
            user_id,
            uang,
            biaya: super_total.biaya
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
</script>
<?= $this->endSection() ?>