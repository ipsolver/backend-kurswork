<?php
/** @var string $Title */
/** @var string $Content */

use models\Users;
use models\Session;

$currentUser = Users::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet">
    <title><?= $Title ?? 'Crystal' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/crystal/assets/css/style.css">
</head>
<body>
    <header class="bg-light border-bottom mb-3">
    <nav class="navbar navbar-expand-lg navbar-light container">
    <a class="navbar-brand text-primary fw-bold" href="/crystal">Crystal</a>

    
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-3">
                    <li class="nav-item">
                        <a class="nav-link text-primary me-2" href="/crystal/items/">Картини</a>
                    </li>
                <li class="nav-item">
                    <a class="nav-link text-primary me-2" href="/crystal/news/">Дописи</a>
                </li>
                <li class="nav-item">   
                    <a class="nav-link text-primary me-2" href="/crystal/home/about">Про автора</a> 
                </li>
                <li class="nav-item">   
                    <a class="nav-link text-primary me-2" href="/crystal/contacts/">Контакти</a> 
                </li>
                <?php if($currentUser && $currentUser['role'] === 'admin'): ?>
                 <li class="nav-item">   
                    <a class="nav-link text-danger me-2" href="/crystal/admin/">Адмін</a> 
                </li>
                <?php endif; ?>
        </ul>

        <div class="ms-auto d-flex align-items-center">
            <?php if(Users::isUserLogged()): ?>
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <img src="<?= $currentUser['profile_picture_url'] ?? '/crystal/assets/img/default-avatar.png' ?>" alt="avatar" class="rounded-circle" width="32" height="32">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                    <span class="dropdown-item text-primary fw-semibold">
                        @<?= htmlspecialchars($currentUser['username']) ?>
                    </span>
                        <li><a class="dropdown-item" href="/crystal/profile">Мій профіль</a></li>
                    <?php if($currentUser['role'] != "admin"): ?>
                        <li><a class="dropdown-item" href="/crystal/orders">Мої замовлення</a></li>
                    <?php endif; ?>
                        <li><a class="dropdown-item" href="/crystal/profile/settings">Налаштування</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/crystal/profile/logout">Вийти</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a class="btn btn-primary me-2" href="/crystal/profile/login">Вхід</a>
                <a class="btn btn-primary" href="/crystal/profile/register">Реєстрація</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
    </header>

    <main class="container">
        <?= $Content ?? '' ?>
    </main>

<footer class="site-footer">
  <div class="footer-content">
    <p>&copy; 2025 Crystal. Всі права захищено</p>
  </div>
</footer>

</body>
</html>
