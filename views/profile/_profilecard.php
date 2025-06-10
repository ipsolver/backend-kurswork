<?php
use models\Users;
use core\helper;
$currentUser = Users::getCurrentUser();
?>

<div class="card shadow-lg border-0 rounded-4" style="background: linear-gradient(135deg, #f5f7fa, #c3cfe2);">
    <div class="card-body d-flex align-items-center p-4">
        <img src="<?= $currentUser['profile_picture'] ?>"
             alt="avatar" class="rounded-circle border border-white shadow me-4" 
             width="100" height="100">

        <div class="flex-grow-1">
            <h3 class="mb-1"><?= $currentUser['first_name'] . ' ' . $currentUser['last_name'] ?></h3>
            <p class="text-muted mb-0">@<?= $currentUser['username'] ?></p>
            <?php if (!empty($currentUser['phone'])): ?>
                <p class="mb-0">📞 <?= htmlspecialchars($currentUser['phone']) ?></p>
            <?php endif; ?>
            
            <?php if (!empty($currentUser['role'])): ?>
                <p class="mt-2"><?= $currentUser['role'] ?></p>
            <?php endif; ?>
        </div>

        <div class="ms-auto d-flex gap-2">
            <?php if($currentUser['role'] != "admin"): ?>
            <a href="/crystal/orders/" class="btn btn-outline-primary">
                Мої замовлення
            </a>
            <?php endif ?>
            <a href="/crystal/profile/settings" class="btn btn-outline-secondary">
                Налаштування
            </a>
            <?php if($currentUser['role'] != "admin"): ?>
            <a href="/crystal/profile/delete" class="btn btn-outline-danger">
                Видалити профіль
            </a>
            <?php endif ?>

        </div>
    </div>
</div>
<div style="margin-bottom: 170px"></div>