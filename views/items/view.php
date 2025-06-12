<?php
use models\Users;
?>

<style>
.item-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin: 2rem auto 1rem;
  max-width: 1000px;
}

.item-main-image img {
  width: 100%;
  max-height: 400px;
  object-fit: contain;
  border-radius: 10px;
  cursor: zoom-in;
  transition: transform 0.3s ease;
}

.item-attributes, .item-description {
  font-size: 1rem;
  line-height: 1.5;


  max-width: 100%;
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: pre-line;
  padding: 1rem;
}

.item-bottom {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  max-width: 1000px;
  margin: 1rem auto;
  margin-top: -90px;
}

.image-slider {
  display: flex;
  margin-top: 40px;
  gap: 10px;
  overflow-x: auto;
  height: 230px;
}

.image-slider img {
  height: 200px;
  border-radius: 6px;
  cursor: pointer;
  transition: transform 0.3s ease;
}

.image-slider img:hover {
  transform: scale(1.1);
}

.bottom-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1000px;
  margin: 1rem auto 3rem;
}

.like-btn {
  border: none;
  background: transparent;
  font-size: 1.5rem;
  color: red;
  cursor: pointer;
  padding: 0.4rem 1rem;
  border-radius: 8px;
  transition: background 0.2s ease;
}

.like-btn.active-like {
  background-color: #f8d7da;
}

.return-btn {
  padding: 0.5rem 1rem;
  background: #e0e0e0;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.modal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0;
  top: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0,0,0,0.8);
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.3s ease;
}

.modal img {
  max-width: 90%;
  max-height: 90%;
  border-radius: 10px;
  animation: zoomIn 0.3s ease;
}

@keyframes zoomIn {
  from { transform: scale(0.8); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
</style>

<h2 align="center"><?= htmlspecialchars($item['title']) ?></h2>

<div class="item-container">
  <div class="item-main-image">
    <img id="previewImage" src="<?= $item['main_image']['path'] ?? '/crystal/assets/img/default-item.png' ?>" alt="Головне фото">
  </div>

  <div class="item-attributes">
    <p><strong>Код:</strong> <?= htmlspecialchars($item['code']) ?></p>
    <p><strong>Жанр:</strong> <?= htmlspecialchars($item['genre_name']) ?></p>
    <p><strong>Категорія:</strong> <?= htmlspecialchars($item['category_name']) ?></p>
    <?php if (!empty($item['glass'])): ?>
      <p><strong>Скло:</strong> <?= htmlspecialchars($item['glass_type_name']) ?> — 
      <?= htmlspecialchars($item['glass']['name']) ?><br>
      <strong>Розміри:</strong> <?= $item['glass']['length_cm'] ?>×<?= $item['glass']['width_cm'] ?> см, 
      <strong>Товщина:</strong> <?= $item['glass']['thickness_mm'] ?> мм</p>
    <?php endif ?>
    <p><strong>Ціна:</strong> <?= number_format($item['price'], 2, '.', ' ') ?> грн</p>
    <?php if ($item['discount'] > 0): ?>
      <p><small><s><?= number_format($item['tarif'], 2, '.', ' ') ?> грн</s> — знижка <?= $item['discount'] ?>%</small></p>
    <?php endif ?>

<!--  Заявки -->

<?php if (Users::isUserLogged()): ?>
  <?php if (Users::getCurrentUser()['role'] != "admin"): ?>
    <div class="order-actions">
      <a class="btn btn-outline-success" 
         href="/crystal/orders/add?from_item=<?= $item['id'] ?>">Взяти за основу для заявки</a>
      <a class="btn btn-outline-primary" 
         href="/crystal/orders/add">Особиста заявка</a>
    </div>
  <?php endif ?>
<?php else: ?>
  <p style="text-align: center; margin-top: -25%;">
    <em>Щоб подати заявку художнику, потрібно <a href="/crystal/profile/login">авторизуватись</a></em>
  </p>
<?php endif ?>




  </div>
</div>



<div class="item-bottom">
  <div class="item-description">
    <strong>Опис:</strong><br>
    <?= nl2br(htmlspecialchars($item['description'])) ?>
  </div>

  <?php if (!empty($item['images'])): ?>
    <div class="image-slider">
      <?php foreach ($item['images'] as $img): ?>
        <img src="<?= $img['path'] ?>" alt="Додаткове фото" onclick="showZoom('<?= $img['path'] ?>')">
      <?php endforeach ?>
    </div>
  <?php endif ?>
</div>

<div class="bottom-actions">
  <a href="/crystal/items" class="return-btn">Повернутися</a>
  <button id="likeBtn" class="like-btn <?= $item['is_liked'] ? 'active-like' : '' ?>">
    ❤️ <span id="likeCount"><?= $item['likes_count'] ?></span>
  </button>
</div>

<div id="imageModal" class="modal">
  <img id="modalImage" src="">
</div>

<div id="toast" class="notliked-toast"></div>

<script>
document.getElementById('likeBtn')?.addEventListener('click', async () => {
  let res = await fetch('/crystal/items/toggleLike', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ item_id: <?= $item['id'] ?> })
  });

  if (!res.ok) 
    return;

  let data = await res.json();
  if (!data.success) 
  {
    showToast(data.message || 'Помилка');
    return;
  }

  let btn = document.getElementById('likeBtn');
  let countEl = document.getElementById('likeCount');
  countEl.textContent = data.count;
  
  btn.classList.toggle('active-like', data.liked);
  // btn.classList.toggle('btn-danger', data.liked);
  // btn.classList.toggle('btn-outline-danger', !data.liked);
});

function showToast(message) {
  let toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.add('show');
  setTimeout(() => toast.classList.remove('show'), 3000);
}

function showZoom(src) {
  let modal = document.getElementById('imageModal');
  let modalImg = document.getElementById('modalImage');
  modalImg.src = src;
  modal.style.display = 'flex';
}

document.getElementById('previewImage')?.addEventListener('click', () => {
  showZoom(document.getElementById('previewImage').src);
});

document.getElementById('imageModal')?.addEventListener('click', (e) => {
  if (e.target.id === 'imageModal') e.currentTarget.style.display = 'none';
});

</script>
