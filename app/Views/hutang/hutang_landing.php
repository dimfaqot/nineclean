<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>

<div class="main"></div>

<script>
    let datases = [];
    let datas = [];
    let metodes = [];
    let user_hutang = {};
    loading();
    let show = () => {
        let data = {
            order: "Show",
            id: 0,
            tabel: 'transaksi',
            filter: "by user",
            kategori: "Metode",
            format: "array"
        };
        post("home/encode_jwt", {
            data
        }).then(req => {
            fetchData(`hutang/${req.data}`).then(res => {
                loading("close");
                datases = res.data.data;
                metodes = res.data.sub_menu;
                let html = `
                <div class="input-group input-group-sm mb-2">
                    <input type="text" class="form-control bg-dark text-light border-secondary cari_card" placeholder="Cari..." aria-label="Recipient's username" aria-describedby="button-addon2">
                </div>
                <h6 class="text-warning">TOTAL: ${angka(res.data.total)}</h6>`;
                datases.forEach((e, i) => {
                    html +=
                        `
                        <div class="card text-bg-dark mb-3" data-menu="${e.nama}">
                            <div class="card-header">${(i+1)}. ${e.nama}</div>
                            <div class="card-body d-flex justify-content-between ps-4">
                                <div class="text-secondary"><small>${angka(e.biaya)}</small></div>
                                <div>
                                    <button class="btn btn-sm btn-secondary detail_hutang" data-user_id="${e.user_id}"><i class="fa-solid fa-circle-info"></i></button>
                                    <button class="btn btn-sm btn-success mx-1 btn_wa" data-user_id="${e.user_id}"><i class="fa-brands fa-whatsapp"></i></button>
                                    <button class="btn btn-sm btn-light pembayaran" data-ket="bayar hutang" data-user_id="${e.user_id}"><i class="fa-solid fa-arrow-up-right-from-square"></i> Bayar</button>
                                </div>
                            </div>
                        </div>
                        `;
                })
                $(".main").html(html);
            })
        })
    }

    setTimeout(() => {
        show();
    }, 120);
</script>
<?= $this->endSection() ?>