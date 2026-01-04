<?= $this->extend('templates/guest') ?>

<?= $this->section('content') ?>

<p class="text-center opacity-75 mt-2 p-4 fw-bold" style="font-size: 40px;margin-top:190px;">
    <img src="https://bkw.walisongosragen.com/files/bkw.png" width="200" alt="LOGO">
</p>

<form method="post" action="<?= base_url('auth'); ?>">
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