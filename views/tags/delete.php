<?php $this->Title = 'Видалення тегу'; ?>

<h2>Видалення тегу</h2>

<p>Ви впевнені, що хочете видалити тег <strong><?= htmlspecialchars($this->tag['name']) ?></strong>?</p>

<form method="post">
    <button type="submit" class="btn btn-danger">Так, видалити</button>
    <a href="/crystal/tags/index" class="btn btn-secondary">Скасувати</a>
</form>
