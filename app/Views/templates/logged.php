<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $judul; ?></title>
    <!-- Bootstrap 5.2 CSS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= base_url('files/logos/' . session('db')); ?>.png" sizes="16x16">
    <link href="<?= base_url(); ?>fontawesome/css/all.css" rel="stylesheet">
    <style>
        #overlay-loading {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            backdrop-filter: blur(0.8px);
            background-color: rgba(0, 0, 0, 0.4);
            /* semi-transparan gelap */
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: all;
            overflow: hidden;
        }

        body.loading {
            overflow: hidden;
            pointer-events: none;
        }

        .list_barang,
        .list_hasil,
        .select_barang {
            padding: 0.5rem 1rem;
            border-bottom: 1px solid #888888ff;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .list_barang:hover,
        .list_hasil:hover,
        .list_hasil:hover {
            background-color: #474747ff;
        }
    </style>

    <script>
        function angka(a, prefix) {
            let angka = a.toString();
            let number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
        }
        const loading = (order = undefined) => {

            if (order === undefined) {
                let html = `
                <div id="overlay-loading">
                <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">Loading...</span>
                </div>
                </div>`;
                $(".loading").html(html);
                $(".loading").fadeIn();
            } else {
                $(".loading").hide();
            }
        }
        const blink = (cls, last_bg = 'bg-dark', duration = 2000, interval = 300) => {
            let el = $("." + cls);
            let isOn = false;

            const blinkInterval = setInterval(() => {
                el.toggleClass("bg-dark bg-danger");
                isOn = !isOn;
            }, interval);

            // Hentikan blinking setelah `duration` ms
            setTimeout(() => {
                clearInterval(blinkInterval);
                el.removeClass("bg-danger").addClass(last_bg); // Reset ke awal
            }, duration);
        };
        const api = "https://api.walisongosragen.com/";
    </script>

</head>

<body style="background-color: #2A2A2A;" class="text-light">

    <div class="container" style="margin-top: 80px;">
        <?php if (!(menu()['controller'] == "transaksi" && session('db') == "playground")): ?>
            <?= view("templates/navbar"); ?>
        <?php endif; ?>

        <?php if (session('db') == "playground" && menu()['controller'] == "transaksi"): ?>
            <div class="bg-white message pt-4" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; border-radius: 0.5rem;width:30%;display:none">

            </div>

        <?php else: ?>
            <div class="bg-white pt-4 message" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; border-radius: 0.5rem;width:80%;display:none">

            </div>

        <?php endif; ?>
        <!-- Container untuk toast -->
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
            <div id="myToast" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body body_toast">

                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>



        <div class="loading" style="display: none;"></div>

        <?= $this->renderSection('content') ?>
        <!-- modal -->

        <div class="modal fade" id="main_modal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-dark text-light border border-secondary body_modal pt-3 pb-4">

                </div>
            </div>
        </div>
        <div class="modal fade" id="main_modal_static" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content bg-dark text-light border border-secondary body_modal_static pt-3 pb-4">

                </div>
            </div>
        </div>

        <div class="offcanvas offcanvas-bottom bg-dark text-light" style="--bs-offcanvas-height: 100vh;" tabindex="-1" id="main_canvas" aria-labelledby="offcanvasBottomLabel">
            <div class="container text-center mt-3 body_canvas">

            </div>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        //upper_first message build_html cek_form today pembayaran cari_biaya super_total clear_input_transaksi btn_simpan_transaksi detail_hutang tabel_detail_hutang btn_wa
        //table .data .filter cek_stok list_items tambah_pesanan is_tambah_pesanan
        const toastEl = document.getElementById('myToast');
        const toast = new bootstrap.Toast(toastEl);

        let controller = "<?= url(); ?>";
        let db = "<?= session('db'); ?>";
        let role = "<?= user()['role']; ?>";
        let myModal = document.getElementById('main_modal');
        let modal = bootstrap.Modal.getOrCreateInstance(myModal);

        let myModalStatic = document.getElementById('main_modal_static');
        let modal_static = bootstrap.Modal.getOrCreateInstance(myModalStatic);

        let myOffcanvas = document.getElementById('main_canvas')
        let canvas = new bootstrap.Offcanvas(myOffcanvas);
        // canvas.show();

        function upper_first(str) {
            if (!str) return "";
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        async function fetchData(url) {
            try {
                const response = await fetch(api + url);
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                const data = await response.json();
                return data;
            } catch (err) {
                console.error('Error fetching data:', err);
                return null;
            }
        }



        async function post(url = '', data = {}, order = undefined) {
            if (order !== undefined) {
                loading();
            }
            const response = await fetch("<?= base_url(); ?>" + url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            });
            return response.json(); // parses JSON response into native JavaScript objects
        }


        // message
        const message = (status, message, id, tabel, is_reload, is_show, kategori) => {
            let html = ``;
            if (id == "toast") {
                if (status == "200") {
                    $("#myToast").removeClass("text-bg-danger").addClass("text-bg-primary");
                } else {
                    $("#myToast").removeClass("text-bg-primary").addClass("text-bg-danger");
                }
                $(".body_toast").html(message);

                toast.show();
            } else {
                if (status == "200") {
                    html = `
                <div class="text-center text-success">
                    <div class="mb-1"><i class="fa-solid fa-circle-check text-success" style="font-size: 30px;"></i></div>
                    <p style="font-size:12px">${message}</p>
                </div>`;
                } else if (status == "400") {
                    html = `
                <div class="text-center text-danger">
                    <div class="mb-1"><i class="fa-solid fa-circle-xmark text-danger" style="font-size: 30px;"></i></div>
                   <p style="font-size:20px">${message}</p>
                </div>`;

                } else if (status = "confirm") {
                    html += ` 
                <div class="text-center text-danger pb-4">
                    <p style="font-size:12px">${message}</p>
                    <button class="btn btn-sm btn-secondary me-1 cancel_confirm">Batal</button>
                    <button class="btn btn-sm btn-danger btn_delete" data-id="${id}" data-tabel="${tabel}" data-kategori="${kategori}" data-is_reload="${is_reload}" data-is_show="${is_show}" style="width: 50px;">Ya</button>
                </div>`;
                }

                $(".message").html(html);
                $(".message").fadeIn();

                if (status !== "confirm") {
                    setTimeout(() => {
                        $(".message").fadeOut();
                    }, (status === "200" ? 800 : 1400));

                }
            }


        }

        $(document).on('click', '.cancel_confirm', function(e) {
            e.preventDefault();
            $(".message").hide();
            $(".message").html("");
        });

        $(document).on('click', '.btn_delete', function(e) {
            e.preventDefault();
            let id = $(this).data("id");
            let is_reload = $(this).data("is_reload");
            let tabel = $(this).data("tabel");
            let is_show = $(this).data("is_show");
            let kategori = $(this).data("kategori");
            let data = {
                order: "Delete",
                id,
                tabel,
                kategori,
                format: "array",
                admin: role
            };

            post("home/encode_jwt", {
                data
            }).then(req => {
                if (req.status == "200") {
                    fetchData(`${(kategori=="Inv"?"inv":tabel)}/${req.data}`).then(res => {
                        loading("close");
                        message(res.status, res.message);
                        if (is_reload == "reload" && res.status == "200") {
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }

                        if (is_show == "show") {
                            datas = res.data;
                            show();
                        }
                    })
                }
            })
        });

        $(document).on('keyup', '.cari_card', function(e) {
            e.preventDefault();
            let value = $(this).val().toLowerCase();
            $('.card').filter(function() {
                $(this).toggle($(this).data("menu").toLowerCase().indexOf(value) > -1);
            });

        });

        $(document).on('keyup', '.cari', function(e) {
            e.preventDefault();
            let value = $(this).val().toLowerCase();
            $('.tabel_search tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });

        });

        const elem_arr = {
            button: (body) => `<a href="#" class="text-danger text-center fs-5 mb-3" data-bs-dismiss="${body}">
                    <i class="fa-solid fa-circle-xmark"></i>
                </a>`,
            judul: (text) => `<div class="text-center text-secondary fs-5 mb-2">- ${text} -</div>`,
            garis: () => `<hr style="width:30%; margin: auto;"><div class="mb-3"></div>`,
        };


        const build_html = (text, body = "modal", arr = ["button", "judul", "garis"]) => {
            let html = "";
            arr.forEach(e => {
                if (e == "judul") {
                    html += elem_arr[e](text);
                } else if (e == "button") {
                    html += elem_arr[e](body);
                } else {
                    html += elem_arr[e](body);
                }
            })
            return html;
        }


        $(document).on('click', '.delete', function(e) {
            e.preventDefault();
            let alert = $(this).data("message");
            let id = $(this).data("id");
            let tabel = $(this).data("tabel");
            let is_reload = $(this).data("is_reload");
            let is_show = $(this).data("is_show");
            let kategori = $(this).data("kategori");


            message("confirm", alert, id, tabel, is_reload, is_show, kategori);
        });


        function cek_form() {
            let isValid = true;

            $('input, select').each(function() {
                const $el = $(this);
                const isRequired = $el.prop('required');
                const value = $el.val();

                if (isRequired && (!value || value.trim() === '')) {
                    message("400", `Input "${$el.attr('name') || $el.attr('id') || 'tanpa nama'}" wajib diisi.`);
                    $el.focus();
                    isValid = false;
                    return false; // keluar dari each
                }
            });

            return isValid;

        }


        $(document).on('keyup', '.angka', function(e) {
            e.preventDefault();
            let value = $(this).val();
            $(this).val(angka(value));
        });

        function angka_to_int(nominalString) {
            // Hapus semua karakter non-digit (kecuali minus jika perlu)
            const angkaBersih = nominalString.replace(/[^0-9]/g, '');
            return parseInt(angkaBersih, 10);
        }

        function str_replace(search, replace, subject) {
            // pastikan search dan replace berupa array
            var searchArr = [].concat(search);
            var replaceArr = [].concat(replace);

            var result = subject;

            searchArr.forEach(function(s, i) {
                var r = replaceArr[i] !== undefined ? replaceArr[i] : "";
                // gunakan regex global untuk mengganti semua kemunculan
                var regex = new RegExp(s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'), 'g');
                result = result.replace(regex, r);
            });

            return result;
        }



        const time_php_to_js = (date, format = "d/m/Y") => {
            const d = new Date(date * 1000);

            const map = {
                d: d.getDate().toString().padStart(2, '0'),
                m: (d.getMonth() + 1).toString().padStart(2, '0'),
                Y: d.getFullYear(),
                H: d.getHours().toString().padStart(2, '0'),
                i: d.getMinutes().toString().padStart(2, '0'),
                s: d.getSeconds().toString().padStart(2, '0')
            };

            let result = '';
            for (let i = 0; i < format.length; i++) {
                const char = format[i];
                result += map[char] ?? char; // kalau bukan placeholder, langsung ditambahkan
            }

            return result;
        };

        function today(now = new Date(), format = "d") {
            const hour = now.getHours();
            let customDate = new Date(now);

            if (hour < 7) {
                customDate.setDate(customDate.getDate() - 1);
            }

            const year = customDate.getFullYear();
            const month = String(customDate.getMonth() + 1).padStart(2, "0");
            const day = String(customDate.getDate()).padStart(2, "0");

            return format
                .replace("d", day)
                .replace("m", month)
                .replace("Y", year);
        }

        function cekWaktu(jam, menit, cls) {
            // pastikan string agar bisa cek panjang
            jam = jam.toString();
            menit = menit.toString();
            let msg;
            // validasi format: harus 2 digit
            if (!/^\d{2}$/.test(jam) || !/^\d{2}$/.test(menit)) {
                msg = `<span class="text-danger">Format jam/menit harus 2 digit (contoh 01, 09, 23)</span>`;
                $("." + cls).html(msg);
                return;
            }

            // konversi ke integer
            const jamInt = parseInt(jam, 10);
            const menitInt = parseInt(menit, 10);

            // validasi range jam 00–23 dan menit 00–59
            if (jamInt < 0 || jamInt > 23 || menitInt < 0 || menitInt > 59) {
                msg = `<span class="text-danger">Format waktu tidak valid (jam 00–23, menit 00–59</span>`;
                $("." + cls).html(msg);
                return;
            }

            // ambil waktu sekarang
            const now = new Date();

            // buat target waktu hari ini
            const target = new Date();
            target.setHours(jamInt);
            target.setMinutes(menitInt);
            target.setSeconds(0);
            target.setMilliseconds(0);

            // cek apakah sudah berlalu
            if (target.getTime() < now.getTime()) {
                msg = `<span class="text-danger">Error: jam sudah berlalu</span>`;
                $("." + cls).html(msg);
                return;
            }

            // kembalikan Unix timestamp (detik)
            return Math.floor(target.getTime() / 1000);
        }






        // PEMBAYARAN
        $(document).on('click', '.pembayaran', function(e) {
            e.preventDefault();
            let user_id = $(this).data("user_id");
            let no_nota = $(this).data("no_nota");
            let ket = $(this).data("ket");
            let order = $(this).data("order");
            let is_open = $(this).data("is_open");



            if (ket == "hutang" || ket == "bayar hutang") {
                let data = [];
                datases.forEach(e => {
                    if (order == "kasir") {
                        if (e.no_nota == no_nota) {
                            data = e;
                        }

                    } else {
                        if (e.user_id == user_id) {
                            data = e;
                        }

                    }
                })

                datas = data.data;

            }
            if (ket == "bayar hutang user") {
                bayar_n_hutang.forEach(e => {
                    if (e.identitas.user_id == user_id) {
                        datas = e.data;
                        return
                    }
                })

            }
            if (ket == "bayar transaksi") {
                bayar_n_hutang.forEach(e => {
                    if (e.identitas.no_nota == no_nota) {
                        datas = e.data;
                        return;
                    }
                })

            }

            if (datas.length == 0) {
                message("400", "Barang kosong");
                return;
            }

            let html = build_html("TRANSAKSI", "offcanvas");
            html += pembayaran(super_total(), ket);

            $(".body_canvas").html(html);
            canvas.show();

            $('#main_canvas').on('shown.bs.offcanvas', function() {
                $('.uang_pembayaran').trigger('focus').select();
            });

            if (typeof is_tambah_pesanan !== "undefined" && is_tambah_pesanan === true && ket !== "bayar transaksi") {

                $(".btn_simpan_transaksi")
                    .attr("data-ket", "update pesanan")
                    .html('<i class="fa-solid fa-arrow-right-to-bracket"></i> UPDATE');

                // sembunyikan tombol hutang dan body_metode
                $(".hutang").hide();
                $(".body_metode").hide();

            } else {
                if (is_open === "Open") {
                    $(".btn_simpan_transaksi").hide();
                    $(".body_metode").hide();
                } else {
                    $(".btn_simpan_transaksi")
                        .attr("data-ket", ket) // gunakan variabel ket semula
                        .html('<i class="fa-solid fa-arrow-right-to-bracket"></i> BAYAR');
                    if (ket == "bayar hutang user") {

                        $(".hutang").hide();
                    } else {
                        $(".hutang").show();

                    }
                    $(".body_metode").show();

                }

            }

            if (ket == "hutang" || ket == "bayar hutang" || ket == "bayar transaksi") {
                $(".message").html("");
                $(".message").hide();
                setTimeout(() => {
                    $('.hutang').closest('div').remove();
                }, 200);

            }

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

        const super_total = () => {
            let total = 0;
            let diskon = 0;
            let biaya = 0;
            datas.forEach(e => {
                total += parseInt(e.total);
                diskon += parseInt(e.diskon);
                biaya += parseInt(e.biaya);
            })

            let res = {
                total,
                diskon,
                biaya
            }
            return res;
        }

        const pembayaran = (super_total, ket) => {
            let html = ``;
            html += `<div class="border border-secondary rounded p-3">
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" style="width: 100px;">SUB TOTAL</span>
                        <input type="text" class="form-control" readonly value="${angka(super_total.total)}">
                    </div>
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text" style="width: 100px;">DISKON</span>
                        <input type="text" class="form-control" readonly  value="${angka(super_total.diskon)}">
                    </div>
                    <div class="input-group input-group-sm mb-3 before_penghutang">
                        <span class="input-group-text" style="width: 100px;">TOTAL</span>
                        <input type="text" class="form-control" readonly value="${angka(super_total.biaya)}">
                    </div>
                   
                    <h6 class="text-center">UANG PEMBAYARAN</h6>
                    <input class="form-control form-control-lg text-light text-center border border-light border-3 bg-success uang_pembayaran angka" value="${angka(super_total.biaya)}" value="0" type="text">
                    
                    <div class="mt-3 body_metode">`;
            metodes.forEach(e => {
                html += `<div class="form-check form-check-inline">
                            <input class="form-check-input" ${(e=="Cash"?"checked":"")} type="radio" name="metode" value="${e}">
                            <label class="form-check-label">${e}</label>
                        </div>`;

            })

            html += `</div>

                    <div class="d-flex gap-2 mt-3 before_hutang">
                        <div class="flex-grow-1">
                             <button class="btn btn-info btn_simpan_transaksi" data-biaya="${super_total.biaya}" data-ket="${ket}" style="width:100%"><i class="fa-solid fa-arrow-right-to-bracket"></i> BAYAR</button>
                        </div>
                        <div>
                            <button class="btn btn-outline-info hutang" data-ket="hutang" style="width: 115px;"><i class="fa-solid fa-face-frown"></i> HUTANG</button>
                        </div>
                    </div>

                </div>`;

            return html;
        }


        const clear_input_transaksi = (order = undefined) => {
            $(".cari_barang").val("");
            $(".harga").val("0");
            $(".qty").val("1");
            $(".diskon").val("0");
            $(".total").val("0");
            $(".biaya").val("0");
            $(".body_list_barang ").html("");
            if (order !== undefined) {
                barang_selected = {};
            }
        }

        $(document).on('click', '.btn_simpan_transaksi', function(e) {
            e.preventDefault();
            let metode = $('input[name="metode"]:checked').val();
            let biaya = '';
            let uang = '';
            let ket = $(this).data("ket");

            if (is_tambah_pesanan === true) {
                if ("no_nota" in datas[0] == false) {
                    message("400", "No. nota not found");
                    return;
                }
            }
            if (ket == "hutang" || ket == "wl") {
                if (user_hutang.id === undefined) {
                    message("400", "Data penghutang kosong");
                    return;
                }
                metode = upper_first(ket);

                if (ket == "wl") {
                    let id = $(this).data('id');
                    let div = $(this).data('divisi');
                    let jam = $(".target_jam").val();
                    let menit = $(".target_menit").val();
                    let dp = angka_to_int($(".target_dp").val());
                    if (dp == "" || dp == "0") {
                        $(".detail_order").html(`<span class="text-danger">Dp wajib diisi</span>`);
                        return;
                    }
                    let wkt = cekWaktu(jam, menit, 'detail_order');
                    if (wkt === undefined) {
                        return;
                    }
                    play_game(id, div, ket, dp);
                    datas[0]['start'] = wkt;
                    datas[0]['dp'] = dp;
                    datas[0]['metode'] = "Wl";
                }


            } else {
                biaya = parseInt($(this).data("biaya"));
                uang = $(".uang_pembayaran").val();
                uang = (uang == "" ? "0" : uang);
                uang = angka_to_int(uang);

                if (uang < biaya) {
                    message("400", "Uang kurang");
                    blink("uang_pembayaran", 'bg-success');
                    loading("close");
                    return;
                }
            }


            if (datas.length == 0) {
                message("400", "Data transaksi kosong");
                return;
            }


            let data = {
                datas,
                metode,
                ket,
                uang,
                no_nota: ("no_nota" in datas[0] ? datas[0].no_nota : ""),
                tgl: (typeof tgl_tambah_pesanan == "undefined" ? "" : tgl_tambah_pesanan),
                penghutang: user_hutang,
                order: "Transaksi",
                tabel: 'transaksi',
                id: 0
            }

            if (db == "playground") {
                data['divisions'] = divisions;
                if (data.ket == "wl") {
                    data.ket = "hutang";
                }
            }
            if (ket == "bayar hutang user") {
                data['order'] = ket;
            }
            console.log(data);
            return;
            post("home/encode_jwt", {
                data
            }).then(req => {
                if (req.status == "200") {
                    fetchData(`${(db=="playground"?"playground":"transaksi")}/${req.data}`).then(res => {
                        loading("close");
                        message(res.status, res.message);
                        if (ket == "bayar" || ket == "bayar hutang" || ket == "bayar transaksi" || ket == "hutang" || ket == "bayar hutang user") {
                            let jdl = (ket == "hutang" ? "HUTANG" : "INVOICE");
                            setTimeout(() => {

                                let html = build_html(jdl, "modal", ["judul", "garis"]);
                                if (ket == "hutang") {
                                    html += res.data;
                                } else {
                                    html += `<iframe id="nota_frame" src="${res.data}" style="border: none; width: 100%; height: 600px;"></iframe>`;
                                    html += `
                                                <div class="d-grid mt-5">
                                                    <button class="btn btn-secondary selesai">Selesai</button>
                                                </div>
                                            `;

                                }

                                $(".body_modal_static").html(html);
                                modal_static.show();

                            }, 1200);

                        } else {
                            location.reload();
                        }
                    })
                } else {
                    loading("close");
                    message(req.status, req.message);
                }
            })

        });

        $(document).on('click', '.selesai', function(e) {
            e.preventDefault();
            location.reload();
        });

        // HUTANG

        let tabel_detail_hutang = (data, total_biaya) => {
            let html = `<div class="border border-warning rounded fw-bold text-center text-warning p-2 mb-2">TOTAL: ${angka(total_biaya)}</div>
                        <div style="max-height: 600px;overflow-y: auto;">
                        <table class="table table-dark table-bordered" style="font-size: 16px;">
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
                        <td class="text-end">${angka(e.harga)}</td>`;
                if (e.divisi == "Ps" || e.divisi == "Billiard") {
                    if (e.roleplay == "Open" && e.is_over == 0) {
                        html += `<td class="text-end">${e.waktu}</td>`;
                    } else {
                        html += `<td class="${(e.roleplay=="Paket"?"text-warning":"")} text-end">${angka(e.qty)}</td>`;
                    }
                } else {
                    html += `<td class="text-end">${angka(e.qty)}</td>`;
                }
                html += `<td class="text-end">${angka(e.diskon)}</td>
                        <td class="text-end">${angka(e.biaya)}</td>
                    </tr>`;
            });
            html += `</tbody>
                        </table></div>`;

            return html;
        }

        $(document).on("click", ".detail_hutang", function(e) {
            e.preventDefault();
            let user_id = $(this).data("user_id");
            let no_nota = $(this).data("no_nota");
            let total_biaya = $(this).data("total_biaya");
            let nama = $(this).data("nama");
            let order = $(this).data("order");

            let data = [];
            datases.forEach(e => {
                if (order == "kasir") {
                    if (e.no_nota == no_nota) {
                        data = e;
                    }
                } else {
                    if (e.user_id == user_id) {
                        data = e;
                    }

                }
            })

            let html = build_html(data.nama, "modal");
            html += '<div class="container">';
            html += tabel_detail_hutang(data.data, data.biaya);
            if (controller == "home") {
                html += ` <div class="text-center">
                                    <button class="btn btn-sm btn-success mx-1 btn_wa" data-user_id="${data.user_id}"><i class="fa-brands fa-whatsapp"></i> W.a</button>`;
                if (controller == "home" && role == "Admin") {
                    html += `<button class="btn btn-sm btn-light pembayaran" data-ket="bayar hutang" data-user_id="${data.user_id}"><i class="fa-solid fa-arrow-up-right-from-square"></i> Bayar</button>`;
                }
                html += `</div></div>`;
            }
            $(".body_modal").html(html);
            loading("close");
            modal.show();
        })

        $(document).on('click', '.btn_wa', function(e) {
            e.preventDefault();
            let user_id = $(this).data("user_id");
            let data = [];
            datases.forEach(e => {
                if (e.user_id == user_id) {
                    data = e;
                }
            })


            let no_hp = "62";
            no_hp += data.wa.substring(1);

            let text = "_Assalamualaikum Wr. Wb._%0a";
            text += "Yth. *" + data.nama + '*%0a%0a';
            text += 'Tagihan Anda di Aguseh:%0a%0a';
            text += '*No. -- Tgl -- Barang -- Harga -- Qty -- Total -- Diskon -- Biaya*%0a'

            let x = 1;
            data.data.forEach((e, i) => {
                text += (x++) + '. ' + time_php_to_js(e.tgl) + ' - ' + e.barang + ' - ' + angka(e.harga) + ' - ' + angka(e.qty) + ' - ' + angka(e.total) + ' - ' + angka(e.diskon) + ' - ' + angka(e.biaya) + '%0a';

            })
            text += '%0a';
            text += "*TOTAL: " + angka(data.biaya) + "*%0a%0a";
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


        });

        // DATA LIST
        const table = (data, total, sub_menu, order, jenis = "All", tahuns, bulans, tahun, bulan) => {

            let html = '';
            if (controller !== "home") {
                html += `<div class="d-flex justify-content-center gap-2 mb-2">
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
            </div>`;
            }
            if (order !== 'hutang') {
                html += `
            <div class="d-flex flex-wrap gap-2">
            <div class="form-check form-switch" style="font-size:12px">
                        <input class="form-check-input filter" data-jenis="All" data-order="${order}" type="radio" role="switch" name="sub_menu" ${(jenis=="All"?"checked":"")}>
                        <label class="form-check-label">All</label>
                </div>`;
                sub_menu.forEach(e => {
                    html += `<div class="form-check form-switch" style="font-size:12px">
                        <input class="form-check-input filter" data-jenis="${e}" data-order="${order}" type="radio" role="switch" name="sub_menu" ${(e==jenis?"checked":"")}>
                        <label class="form-check-label">${e}</label>
                        </div>`;

                })
                html += `</div>`;
                if (controller === 'home') {
                    html += `<hr>`;
                }
            }
            html += `<div class="input-group input-group-sm mb-2">
                <input type="text" class="form-control bg-dark text-light border-secondary cari" placeholder="Cari...">
            </div>`;
            if (order == "laporan") {
                html += `<div class="d-grid my-2"><a href="" data-order="${order}" class="btn btn-sm btn-secondary cetak" data-jenis="${jenis}"><i class="fa-solid fa-file-pdf"></i> CETAK</a></div>`;
                if (jenis == "Tahunan" && role == "Root") {
                    html += `<div class="d-grid my-2"><a href="" class="btn btn-sm btn-success backup"><i class="fa-solid fa-database"></i> BACKUP</a></div>`;

                }
                if (jenis == "All") {
                    html += `
                    <div>Masuk: ${angka(data.transaksi.total)}</div>
                    <div>Keluar: ${angka(data.pengeluaran.total)}</div>
                    <div style="font-size:12px" class="fw-bold text-warning">TOTAL: ${(data.transaksi.total-data.pengeluaran.total<0?"-":"")+angka(data.transaksi.total-data.pengeluaran.total)}</div>
                `;
                } else {
                    html += `<div style="font-size:12px" class="fw-bold text-warning">TOTAL: ${(total.transaksi-total.pengeluaran<0?"-":"")+angka(total.transaksi-total.pengeluaran)}</div>`;
                }

            } else {
                html += `<div style="font-size:12px" class="fw-bold text-warning">TOTAL: ${angka(total)}</div>`;
            }
            if (order == "laporan") {
                if (jenis !== "All") {
                    html += `<table class="table table-sm table-dark table-bordered" style="font-size:12px">
                                <thead>
                                    <tr>
                                        <th class="text-center">${(jenis=="All"?"Tgl":jenis.slice(0,-2))}</th>
                                        <th class="text-center">Masuk</th>
                                        <th class="text-center">Keluar</th>
                                        <th class="text-center">Saldo</th>
                                    </tr>
                                </thead>
                                <tbody class="tabel_search">`;
                    data.forEach((e, i) => {
                        html += `<tr>
                                        <td class="${(jenis=="All" || jenis=="Tahunan"?"text-center":"")}">${e.tgl}</td>
                                        <td class="text-end">${angka(e.masuk)}</td>
                                        <td class="text-end">${angka(e.keluar)}</td>
                                        <td class="text-end">${(e.masuk-e.keluar<0?"-":"")}${angka(e.masuk-e.keluar)}</td>
                                    </tr>`;
                    })
                    html += `</tbody>
                            </table>`;

                }

            } else {
                html += `<table class="table table-sm table-dark table-bordered" style="font-size:12px">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>`;
                if (order == "hutang") {
                    html += `<th class="text-center">Nama</th>`;
                } else {
                    html += `
                 <th class="text-center">Tgl</th>
                <th class="text-center">Barang</th>
                <th class="text-center">Qty</th>
                <th class="text-center">Diskon</th>`;

                }
                html += `<th class="text-center">Biaya</th>`;
                if (controller == "transaksi") {
                    html += `<th class="text-center">Nota</th>`;
                }
                html += `</tr>
                </thead>
                <tbody class="tabel_search">`;
                data.forEach((e, i) => {
                    html += `<tr ${(e.metode=="Hutang"?"class='text-warning'":"")}>
                                    <td scope="row">${(i+1)}</td>`
                    if (order == "hutang") {
                        html += `<td>${e.nama}</td>`;

                    } else {

                        html += ` <td>${(order=="hutang"?"-":time_php_to_js(e.tgl,"d"))}</td>
                            <td class="text-start">${e.barang}</td>
                            <td>${angka(e.qty)}</td>
                                <td>${angka(e.diskon)}</td>`;
                    }
                    if (role == "Root" || role == "Advisor") {
                        html += `<td class="text-end">${(order=="hutang"?`<a href="" class="detail_hutang text-warning" data-nama="${e.nama}" data-user_id="${e.user_id}" data-total_biaya="${e.biaya}">${angka(e.biaya)}</a>`:angka(e.biaya))}</td>`;
                    } else {
                        html += `<td class="text-end">${(e.jenis=="Bisyaroh"?"-":(order=="hutang"?`<a href="" class="detail_hutang text-warning" data-nama="${e.nama}" data-user_id="${e.user_id}" data-total_biaya="${e.biaya}">${angka(e.biaya)}</a>`:angka(e.biaya)))}</td>`;

                    }
                    if (controller == "transaksi") {
                        html += `<td>${(e.metode=="Hutang"?'<i class="fa-solid fa-hand text-secondary"></i>':`<a class="text-light" style="text-decoration:none;" href="${api}cetak/nota/<?= session('db'); ?>/${e.no_nota}" target="_blank"><i class="fa-solid fa-file-pdf"></i></a>`)}</td>`;
                    }
                    html += `</tr>`;
                })
                html += `</tbody>
                        </table>`;

            }


            return html;

        }

        $(document).on('click', '.data', function(e) {
            e.preventDefault();
            loading();

            let tahun = ($(".tahun").val() == undefined || $(".tahun").val() == "" ? "<?= date('Y'); ?>" : $(".tahun").val());
            let bulan = ($(".bulan").val() == undefined || $(".bulan").val() == "" ? "<?= date('n'); ?>" : $(".bulan").val());
            let order = $(this).data("order");
            let kategori = $(this).data("kategori");

            if (controller == "home") {
                home_menu(order, tahun, bulan);
            }


            let data = {
                order: order,
                tahun,
                bulan,
                jenis: "All",
                id: 0,
                tabel: (order == "inv" ? "pengeluaran" : order),
                format: "array",
                kategori
            };

            post("home/encode_jwt", {
                data
            }).then(req => {
                if (req.status == "200") {
                    fetchData(`home/${req.data}`).then(res => {
                        datases = res.data;
                        metodes = res.data3;
                        loading("close");

                        let html = table(res.data, res.data2, res.data3, order, data.jenis, tahuns, bulans, tahun, bulan);

                        if (controller == "home") {
                            $(".body_detail").html(html);

                        } else {
                            let html2 = build_html("Data", "offcanvas");
                            html2 += html;
                            $(".body_canvas").html(html2);
                            if ($('.tahun').length > 0) {
                                canvas.show();
                            }

                        }
                    })
                } else {
                    loading("close");
                    message(req.status, req.message);
                }
            })

        });


        $(document).on('change', '.filter', function(e) {
            e.preventDefault();
            loading();
            let tahun = ($(".tahun").val() == undefined || $(".tahun").val() == "" ? "<?= date('Y'); ?>" : $(".tahun").val());
            let bulan = ($(".bulan").val() == undefined || $(".bulan").val() == "" ? "<?= date('n'); ?>" : $(".bulan").val());
            let order = $(this).data("order");
            let jenis = $(this).data("jenis");

            let data = {
                order: order,
                tahun,
                bulan,
                jenis,
                format: "array",
                kategori: (order == "transaksi" || order == "pengeluaran" ? "Kantin" : "Inv"),
                id: 0,
                tabel: order
            };
            post("home/encode_jwt", {
                data
            }).then(req => {
                if (req.status == "200") {
                    fetchData(`home/${req.data}`).then(res => {
                        loading("close");
                        let html = table(res.data, res.data2, res.data3, order, data.jenis, tahuns, bulans, tahun, bulan);
                        if (controller == "home") {
                            $(".body_detail").html(html);

                        } else {
                            let html2 = build_html("Data", "offcanvas");
                            html2 += html;
                            $(".body_canvas").html(html2);
                            if ($('.tahun').length > 0) {
                                canvas.show();
                            }

                        }
                    })
                } else {
                    loading("close");
                    message(req.status, req.message);
                }
            })


        });

        // CEK STOK
        let cek_stok = (barang, qty, cls = undefined) => {

            let qty_awal = 0;
            data_awal_pesanan.forEach(e => {
                if ((is_tambah_pesanan ? barang.barang_id : barang.id) == e.id) {
                    qty_awal = e.qty;
                }

            })

            let res = true;
            // cek stok
            if (barang.tipe == "Count") {
                barangs.forEach(e => {
                    if (e.id == (is_tambah_pesanan && barang.is_update !== "new" ? barang.barang_id : barang.id)) {

                        let max = (is_tambah_pesanan ? (parseInt(qty_awal) + parseInt(e.qty)) : e.qty);
                        if (qty > max) {
                            let msg = `Stok ${e.barang} kurang...[max: ${max}]`;
                            if (cls === undefined ? message("400", msg) : $(cls).text(msg));
                            res = false;
                            return;
                        }

                    }
                })
            } else if (barang.link !== "" && barang.tipe == "Mix") {
                let exp = barang.link.split(",");
                barangs.forEach(e => {
                    exp.forEach(i => {
                        if (e.id == i) {

                            let max = (is_tambah_pesanan ? (parseInt(qty_awal) + parseInt(e.qty)) : e.qty);

                            if ((qty === undefined ? 1 : qty) > max) {

                                let msg = `Stok ${e.barang} kurang...[max: ${max}]`;
                                if (cls == undefined ? message("400", msg) : $(cls).text(msg));
                                res = false;
                                return;

                            }
                        }

                    })
                })

            }

            if (!res) {
                blink("val_qty");
            }
            return res;

        }


        const list_items = (order = undefined) => {

            let html = "";
            let is_open = "";
            datas.forEach((e, i) => {
                html += `<tr class="${(e.is_update=="true"?"bg-dark":(is_tambah_pesanan===true&&e.is_update=="new"?"bg-dark":""))}">
                <td class="text-center">${(i+1)}</td>`;
                if (db == "playground") {
                    if (e.roleplay == "Open" && is_open !== "Open") {
                        is_open = e.roleplay;
                    }
                    html += `<td>${e.divisi}</td>
                    <td>${e.barang}</td>`;
                    html += `<td class="text-end">${angka(e.biaya)}</td>`;
                    if (e.divisi == "Ps" || e.divisi == "Billiard") {
                        if (e.roleplay == "Open") {
                            html += `<td class="text-end">${e.waktu}</td>`;
                        } else {
                            html += `<td class="${(e.roleplay=="Paket"?"text-warning":"update_qty")} text-end" data-id="${(is_tambah_pesanan && e.is_update!=="new"?e.barang_id:e.id)}" data-divisi="${e.divisi}">${angka(e.qty)}</td>`;
                        }
                    } else {
                        html += `<td class="update_qty text-end" data-id="${(is_tambah_pesanan && e.is_update!=="new"?e.barang_id:e.id)}" data-divisi="${e.divisi}">${angka(e.qty)}</td>`;
                    }
                    html += `<td class="text-end">${angka(e.diskon)}</td>`;
                    html += `<td class="text-end">${angka(e.biaya)}</td>`;
                } else {

                    html += `<td>${e.barang}</td>
                    <td class="text-end">${angka(e.biaya)}</td>
                    <td class="update_qty text-end" data-id="${(is_tambah_pesanan && e.is_update!=="new"?e.barang_id:e.id)}">${angka(e.qty)}</td>`;
                }
                if (e.is_update == "new") {
                    html += `<td class="text-center"><a href="" class="text-danger delete_item" data-barang_id="${e.id}" data-divisi="${e.divisi}" style="text-decoration:none"><i class="fa-solid fa-circle-xmark"></i></a></td>`;
                } else {
                    html += `<td class="text-center"><i class="fa-solid fa-hand"></i></td>`;

                }
                html += `</tr>`;
            })

            if (is_open == "Open") {
                $(".pembayaran").attr("data-is_open", is_open);
            }
            return html;
        }


        $(document).on('click', '.tambah_pesanan', function(e) {
            e.preventDefault();
            clear_input_transaksi('hapus');
            $(".pencuci").html("");
            canvas.hide();
            let order = $(this).data("order");
            no_nota = $(this).data("no_nota");
            tgl_tambah_pesanan = $(this).data("tgl_tambah_pesanan");
            let ket = $(this).data("ket");

            user_hutang['user_id'] = $(this).data("user_id");
            user_hutang['nama'] = $(this).data("nama");

            let temp_data = [];
            datases.forEach(e => {
                if (e.no_nota == no_nota) {
                    temp_data = e;
                }
            })


            let temp_datas = [];
            temp_data.data.forEach(e => {
                data_awal = {
                    id: e.barang_id,
                    qty: e.qty
                };

                data_awal_pesanan.push(data_awal);
                e['is_update'] = "false";
                temp_datas.push(e);
            })
            datas = temp_datas;

            is_tambah_pesanan = true;

            $(".list_items").html(list_items());
            $(".super_total").val(angka(super_total().biaya));

        });
        <?php if (session()->getFlashdata('gagal')) : ?>
            let msg = "<?= session()->getFlashdata('gagal'); ?>";
            message("400", msg);
        <?php endif; ?>
        <?php if (session()->getFlashdata('sukses')) : ?>
            let msg = "<?= session()->getFlashdata('sukses'); ?>";
            message("200", msg);
        <?php endif; ?>
    </script>
</body>

</html>