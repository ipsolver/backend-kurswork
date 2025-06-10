<?php $this->Title = 'Підтвердження видалення товару'; ?>

<h2>Видалити товар</h2>

<div class="card">
  <div class="card-body">
    <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
    <p class="card-text"><?= htmlspecialchars($item['description']) ?></p>

    <img src="<?= $item['image'] ?: '/crystal/assets/img/default-item.png' ?>" 
         class="img-fluid" style="max-height: 200px;" alt="Зображення товару">
    
    <hr>
    <p>Ви справді бажаєте видалити цей товар?</p>

    <form method="post">
        <input type = "text" value = "1" name = "confirm" hidden>
        <button type="submit" class="btn btn-danger">Так, видалити</button>
        <a href="/crystal/items/index" class="btn btn-secondary">Скасувати</a>
    </form>
  </div>
</div>
