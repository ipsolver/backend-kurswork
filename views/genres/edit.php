<?php $this->Title = 'Редагування жанру'; ?>

<h2>Редагування жанру</h2>

<form method="post">

 <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?=$error_message; ?>
    </div>
    <?php endif; ?>


    <div class="mb-3">
        <label for="genName" class="form-label">Назва жанру</label>
        <input type="text" class="form-control" id="genName" name="name" value="<?= htmlspecialchars($genre['name']) ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Зберегти</button>
    <a href="/crystal/genres/index" class="btn btn-secondary">Скасувати</a>
</form>
