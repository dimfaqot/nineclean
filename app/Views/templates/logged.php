<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $judul; ?></title>
    <!-- Bootstrap 5.2 CSS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= base_url(); ?>logo.png" sizes="16x16">
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
        .list_hasil {
            padding: 0.5rem 1rem;
            border-bottom: 1px solid #888888ff;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .list_barang:hover,
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
    </script>
    <link href="<?= base_url(); ?>fontawesome/css/all.css" rel="stylesheet">
</head>

<body style="background-color: #2A2A2A;" class="text-light">

    <div class="container" style="margin-top: 80px;">
        <?= view("templates/navbar"); ?>

        <div class="bg-white opacity-75 px-5 pt-4 message" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; border-radius: 0.5rem;display:none">

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
        let myModal = document.getElementById('main_modal');
        let modal = bootstrap.Modal.getOrCreateInstance(myModal);

        let myModalStatic = document.getElementById('main_modal_static');
        let modal_static = bootstrap.Modal.getOrCreateInstance(myModalStatic);

        let myOffcanvas = document.getElementById('main_canvas')
        let canvas = new bootstrap.Offcanvas(myOffcanvas);
        // canvas.show();

        const loading = (order = undefined) => {
            let html = `
            <div id="overlay-loading">
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`;
            $(".loading").html(html);
            if (order == undefined) {
                $(".loading").fadeIn();
            } else {
                $(".loading").hide();
            }
        }

        async function post(url = '', data = {}, order = undefined) {
            if (order == undefined) {
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


        const message = (status, message, id, tabel, is_reload) => {
            let html = ``;
            if (status == "200") {
                html = `
                <div class="text-center text-success">
                    <div class="mb-1"><i class="fa-solid fa-circle-check text-success" style="font-size: 30px;"></i></div>
                    <p style="font-size:12px">${message}</p>
                </div>`;
            } else if (status == "400") {
                html = `
                <div class="text-center text-danger">
                    <div class="mb-1"><i class="fa-solid fa-circle-check text-danger" style="font-size: 30px;"></i></div>
                   <p style="font-size:12px">${message}</p>
                </div>`;

            } else if (status = "confirm") {
                html += ` 
                <div class="text-center text-danger pb-4">
                    <p style="font-size:12px">${message}</p>
                    <button class="btn btn-sm btn-secondary me-1 cancel_confirm">Batal</button>
                    <button class="btn btn-sm btn-danger btn_delete" data-id="${id}" data-tabel="${tabel}" data-is_reload="${is_reload}" style="width: 50px;">Ya</button>
                </div>`;
            }

            $(".message").html(html);
            $(".message").fadeIn();

            if (status !== "confirm") {
                setTimeout(() => {
                    $(".message").fadeOut();
                }, 600);

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

            post("home/delete", {
                id,
                tabel
            }).then(res => {
                message(res.status, res.message);
                loading("close");
                if (is_reload == "reload" && res.status == "200") {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
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

        const elem_arr = {
            button: (body) => `<a href="#" class="text-danger text-center fs-5 mb-3" data-bs-dismiss="${body}">
                    <i class="fa-solid fa-circle-xmark"></i>
                </a>`,
            judul: (text) => `<div class="text-center text-secondary mb-2">- ${text} -</div>`,
            garis: () => `<hr style="width:30%; margin: auto;"><div class="mb-3"></div>`,
        };

        // modal
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

            message("confirm", alert, id, tabel, "reload");
        });


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