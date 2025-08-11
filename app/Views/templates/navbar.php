  <div class="fixed-top">
      <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
          <div class="container">
              <a class="navbar-brand" href="<?= base_url(); ?>">
                  <img src="<?= base_url(); ?>logo.png" alt="Logo" width="35">
              </a>
              <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                  <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarNavDropdown">
                  <ul class="navbar-nav">
                      <?php foreach (menus() as $i): ?>
                          <?php if (count($i['data']) == 1): ?>
                              <li class="nav-item">
                                  <a class="nav-link <?= (menu()['controller'] == $i['data'][0]['controller'] ? "active" : ""); ?>" aria-current="page" href="<?= base_url($i['data'][0]['controller']); ?>"><i class="<?= $i['data'][0]['icon']; ?>"></i> <?= $i['data'][0]['menu']; ?></a>
                              </li>
                          <?php else: ?>
                              <li class="nav-item dropdown">
                                  <a class="nav-link dropdown-toggle <?= ((in_array(menu()['controller'], $i['menus'])) ? "text-white" : ""); ?>"" href=" #" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                      <i class="<?= $i['data'][0]['icon']; ?>"> </i> <?= $i['grup']; ?>
                                  </a>
                                  <ul class="dropdown-menu bg-dark">
                                      <?php foreach ($i['data'] as $d): ?>
                                          <li><a class="dropdown-item text-secondary <?= (menu()['controller'] == $d['controller'] ? "active" : ""); ?>" href="<?= base_url($d['controller']); ?>"><i class="<?= $d['icon']; ?>"></i> <?= $d['menu']; ?></a></li>
                                      <?php endforeach; ?>
                                  </ul>

                              </li>

                          <?php endif; ?>

                      <?php endforeach; ?>

                  </ul>
              </div>
          </div>
      </nav>
  </div>