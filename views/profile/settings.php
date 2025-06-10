<?php
$this->Title = 'Налаштування';

use models\Users;

$currentUser = Users::getCurrentUser();
?>

<h1 class="mb-4">Налаштування профілю</h1>
<div class="d-flex justify-content-center">
<form method="POST" action="" enctype="multipart/form-data" class="card p-4 shadow-sm" style="max-width: 1000px;">
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?=$error_message; ?>
        </div>
    <?php endif; ?>

    <div class="mb-3 text-center">
        <img src="<?= $currentUser['profile_picture_url'] ?>" alt="avatar" width="100" height="100" class="rounded-circle shadow mb-2">
        <input type="file" name="profile_picture" class="form-control mt-2" accept="image/*">
    </div>

    <div class="mb-3">
        <label class="form-label">Ім'я</label>
        <input type="text" name="first_name" class="form-control" value="<?= $currentUser['first_name']?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Прізвище</label>
        <input type="text" name="last_name" class="form-control" value="<?= $currentUser['last_name'] ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Логін</label>
        <input type="text" name="username" class="form-control" value="<?= $currentUser['username'] ?>" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Номер телефону</label>
        <input type="text" name="phone" class="form-control" value="<?= $currentUser['phone']?>" required>
    </div>

    <button class="btn btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#changePassword">
        🔐 Змінити пароль
    </button>
    <div class="collapse mt-3" id="changePassword">
    <div class="mb-3">
            <label>Старий пароль</label>
            <input type="password" name="password" class="form-control">
        </div>
        <div class="mb-3">
            <label>Новий пароль</label>
            <input type="password" name="new_password" class="form-control">
        </div>
        <div class="mb-3">
            <label>Підтвердіть пароль</label>
            <input type="password" name="new_password2" class="form-control">
        </div>
    </div>


    <button type="submit" class="btn btn-primary">Зберегти зміни</button>
    <br><a style="text-align: center" href="/crystal/profile">Повернутися</a>
</form>
</div>
