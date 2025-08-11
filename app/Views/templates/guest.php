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

    <link href="<?= base_url(); ?>fontawesome/css/all.css" rel="stylesheet">
</head>

<body style="background-color: #2A2A2A;" class="text-light">

    <div class="container" style="margin-top: 80px;">
        <?= view("templates/navbar"); ?>
        <div class="bg-white opacity-75 px-5 pt-4 message" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; border-radius: 0.5rem;display:none">

        </div>

        <?= $this->renderSection('content') ?>
        <!-- modal -->

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>


    <script>
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