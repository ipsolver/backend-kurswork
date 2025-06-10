<?php $this->Title = 'Додавання жанру'; ?>

<h2>Додавання жанру</h2>

<form method="post">

 <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?=$error_message; ?>
    </div>
    <?php endif; ?>


    <div class="mb-3">
        <label for="name" class="form-label">Назва жанру</label>
        <input type="text" name="name" id="name" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success">Додати</button>
    <a href="/crystal/genres/index" class="btn btn-secondary">Скасувати</a>
</form>
