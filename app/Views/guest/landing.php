<?= $this->extend('templates/guest') ?>

<?= $this->section('content') ?>

<p class="text-center bg-light text-dark opacity-75 mt-2 p-4 fw-bold" style="font-size: 40px;margin-top:190px;">
    <img src="<?= base_url('logo.png'); ?>" width="80" alt="LOGO">
    -[<span class="bg-dark text-light pb-1"><?= strtoupper(profile()['nama']); ?></span>]-
</p>

<form method="post" action="<?= base_url('guest/login'); ?>">
    <div class="form-floating mb-3">
        <input type="text" name="username" class="form-control bg-dark text-light" placeholder="Username" required>
        <label class="text-secondary">Username</label>
    </div>
    <div class="form-floating mb-3">
        <input type="password" name="password" class="form-control bg-dark text-light" placeholder="Password" required>
        <label class="text-secondary">Password</label>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-lg btn-secondary"><i class="fa-solid fa-arrow-up-right-from-square"></i> Login</button>
    </div>
</form>
<?= $this->endSection() ?>