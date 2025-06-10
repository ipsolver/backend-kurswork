<?php $this->Title = 'Підтвердження видалення новини'; ?>

<h2>Видалити новину</h2>

<div class="card">
  <div class="card-body">
    <h5 class="card-title"><?= htmlspecialchars($newsItem['title']) ?></h5>
    <p class="card-text"><?= htmlspecialchars($newsItem['short_text']) ?></p>

    <img src="<?= $newsItem['image'] ?: '/crystal/assets/img/default-new.png' ?>" 
         class="img-fluid" style="max-height: 200px;" alt="Зображення новини">
    
    <hr>
    <p>Ви справді бажаєте видалити цю новину?</p>

    <form method="post">
        <input type = "text" value = "1" name = "confirm" hidden>
        <button type="submit" class="btn btn-danger">Так, видалити</button>
        <a href="/crystal/news/index" class="btn btn-secondary">Скасувати</a>
    </form>
  </div>
</div>
