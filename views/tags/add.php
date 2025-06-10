<?php $this->Title = 'Додавання тегу'; ?>

<h2>Додавання нового тегу</h2>

<?php if (!empty($this->errorMessages)): ?>
    <div class="alert alert-danger">
        <ul>
            <?php foreach ($this->errorMessages as $message): ?>
                <li><?= htmlspecialchars($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post">

 <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?=$error_message; ?>
    </div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="tagName" class="form-label">Назва тегу</label>
        <input type="text" class="form-control" id="tagName" name="name" required>
    </div>
    <button type="submit" class="btn btn-primary">Зберегти</button>
    <a href="/crystal/tags/index" class="btn btn-secondary">Скасувати</a>
</form>
