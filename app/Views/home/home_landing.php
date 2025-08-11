<?= $this->extend('templates/logged') ?>

<?= $this->section('content') ?>
<div class="text-center bg-danger p-4 fw-bold" style="font-size: 40px;margin-top:200px">
    <div>WELCOME</div>
    -[<span class="bg-dark pb-1"><?= strtoupper(user()['nama']); ?></span>]-
</div>
<?= $this->endSection() ?>