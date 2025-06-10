<?php $this->Title = 'Редагування категорії'; ?>

<h2>Редагування категорії</h2>

<form method="post">

 <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?=$error_message; ?>
    </div>
    <?php endif; ?>


    <div class="mb-3">
        <label for="catName" class="form-label">Назва категорії</label>
        <input type="text" class="form-control" id="catName" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Зберегти</button>
    <a href="/crystal/categories/index" class="btn btn-secondary">Скасувати</a>
</form>
