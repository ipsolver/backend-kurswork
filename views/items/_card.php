<div class="col">
  <a href="/crystal/items/view?id=<?= $item['id'] ?>">
    <div class="card item-card shadow-sm">
      <img src="<?= $item['image'] ?: '/crystal/assets/img/default-item.png' ?>"
        class="card-img-top item-card-img" alt="Зображення товару">
      
      <div class="card-body">
        <h4 class="card-title">
            <?= htmlspecialchars($item['title']) ?>

        <?php if ($item['discount'] > 0): ?>
            <span class="discount-badge">-<?= $item['discount'] ?>%</span>
        <?php endif ?>
        </h4>
        <small>Товарний код: <?=$item['code']?></small>
        <p class="card-text"><b>Жанр:</b> <?= htmlspecialchars($item['genre_name']) ?></p>
        <p class="card-text"><b>Категорія:</b> <?= htmlspecialchars($item['category_name']) ?></p>
        <p class="card-text">
          <b>Ціна:</b> 
          <?= number_format($item['price'], 2, '.', ' ') ?> грн
          <?php if ($item['discount'] > 0): ?>
            <small class="text-muted ms-2"><s><?= number_format($item['tarif'], 2, '.', ' ') ?> грн</s></small>
          <?php endif ?>
        </p>
        
        <p>Дата публікації: <?=$item['published_at'] ?></p>
        <?php if ($role == "manager" || $role == "admin"): ?>
          <a href = "/crystal/items/edit?id=<?= $item['id']?>"><button class = "btn btn-primary">Редагувати</button></a>
          <a href = "/crystal/items/delete?id=<?= $item['id']?>"><button class = "btn btn-danger">Видалити</button></a>
          <a href = "/crystal/items/public?id=<?= $item['id']?>"><button class = "btn btn-success">Опублікувати зараз</button></a>
        <?php endif ?>
      </div>

    </div>
   </a>       
  </div>