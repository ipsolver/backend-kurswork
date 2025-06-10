<?php
$this->Title = 'Видалення профілю';
?>

<h2 class="mb-4">Підтвердіть видалення профілю</h2>

<form method="post" action="" class="needs-validation" novalidate>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?=$error_message; ?>
        </div>
    <?php endif; ?>
    <div class="mb-3">
        <label for="password" class="form-label">Введіть ваш пароль:</label>
        <input type="password" class="form-control" name="password" id="password" required>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-danger">Видалити профіль</button>
        <a href="/crystal/profile/index" class="btn btn-secondary">Скасувати</a>
    </div>
</form>
