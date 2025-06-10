<?php $this->Title = 'Редагувати товар'; ?>

<h2>Редагувати товар</h2>

<form method="POST" enctype="multipart/form-data" class="mb-4">

<?php if (!empty($error_message)): ?>
  <div class="alert alert-danger"><?= $error_message ?></div>
<?php endif; ?>

<div class="row mb-2">
  <div class="col">
    <label>Назва</label>
    <input value="<?= htmlspecialchars($item['title']) ?>" type="text" name="title" class="form-control" required>
  </div>
  <div class="col">
    <label>Код</label>
    <input value="<?= htmlspecialchars($item['code']) ?>" type="text" name="code" class="form-control" required>
  </div>
</div>

<div class="mb-2">
  <label>Опис</label>
  <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($item['description']) ?></textarea>
</div>

<div class="row mb-2">
  <div class="col">
    <label>Категорія</label>
    <select name="category_id" class="form-select" required>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>" <?= $item['category_id'] == $cat['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($cat['name']) ?>
        </option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="col">
    <label>Жанр</label>
    <select name="genre_id" class="form-select" required>
      <?php foreach ($genres as $genre): ?>
        <option value="<?= $genre['id'] ?>" <?= $item['genre_id'] == $genre['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($genre['name']) ?>
        </option>
      <?php endforeach ?>
    </select>
  </div>
</div>

<div class="row mb-2">
  <div class="col">
    <label>Тариф (базова ціна)</label>
    <input value="<?= $item['tarif'] ?>" type="number" step="0.01" name="tarif" class="form-control" required>
  </div>
  <div class="col">
    <label>Знижка (%)</label>
    <input value="<?= $item['discount'] ?>" type="number" name="discount" class="form-control">
  </div>
</div>

<div class="row mb-2">
  <div class="col">
    <label>Тип скла</label>
    <select id="glassTypeSelect" class="form-select">
      <option value="">— Оберіть тип скла —</option>
      <?php foreach ($glassTypes as $type): ?>
        <option value="<?= $type['id'] ?>" <?= isset($item['glass']) && $item['glass']['glass_type'] == $type['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($type['name']) ?>
        </option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="col">
    <label>Скло</label>
    <select name="glass_id" id="glassSelect" class="form-select">
      <option value="">— Оберіть скло —</option>
      <?php foreach ($glasses as $glass): ?>
        <option value="<?= $glass['id'] ?>" 
                data-type="<?= $glass['glass_type'] ?>" 
                data-info="<?= $glass['length_cm'] ?>×<?= $glass['width_cm'] ?> см, товщина: <?= $glass['thickness_mm'] ?> мм"
                <?= $item['glass_id'] == $glass['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($glass['name']) ?>
        </option>
      <?php endforeach ?>
    </select>
  </div>
</div>

<div id="glassInfo" class="mb-3 text-muted" style="<?= isset($item['glass']) ? '' : 'display:none;' ?>">
  <?php if (isset($item['glass'])): ?>
    Обране скло: <?= $item['glass']['length_cm'] ?>×<?= $item['glass']['width_cm'] ?> см, товщина: <?= $item['glass']['thickness_mm'] ?> мм
  <?php endif ?>
</div>

<div class="mb-3">
  <label>Завантажити нові фото</label>
  <input type="file" name="images[]" class="form-control" multiple accept="image/png, image/jpeg">
  <small class="text-muted">Максимум 4 зображення. Виберіть яке стане головним</small>
</div>

<?php if (!empty($item['images'])): ?>
  <div class="mb-3">
    <label>Поточні зображення:</label>
    <div class="d-flex gap-2 flex-wrap">
      <?php foreach ($item['images'] as $index => $img): ?>
        <div>
          <img src="<?= $img['path'] ?>" 
               class="preview-thumb <?= $img['is_main'] ? 'main-pic' : '' ?>" 
               data-index="<?= $index ?>" 
               style="max-height: 100px; border: 2px solid <?= $img['is_main'] ? '#e83e8c' : '#ccc' ?>; padding: 3px; cursor: pointer;">
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<div id="imagePreviewContainer" class="d-flex gap-2 flex-wrap mt-2"></div>
<input type="hidden" name="main_image_index" id="mainImageIndex" value="<?= array_search(1, array_column($item['images'] ?? [], 'is_main'), true) ?: 0 ?>">

<div class="mb-3">
  <label>Дата публікації</label>
  <input type="datetime-local" name="published_at" class="form-control"
         value="<?= date('Y-m-d\TH:i', strtotime($item['published_at'])) ?>">
</div>

<button type="submit" class="btn btn-primary">Оновити</button>
<a href="/crystal/items" class="btn btn-secondary">Назад</a>

</form>

<script>
document.getElementById('glassTypeSelect')?.addEventListener('change', function () {
  const selectedType = this.value;
  const glassSelect = document.getElementById('glassSelect');
  const options = glassSelect.querySelectorAll('option');
  glassSelect.value = '';

  options.forEach(option => {
    if (!option.value) return;
    option.hidden = option.dataset.type !== selectedType;
  });
});

document.getElementById('glassSelect')?.addEventListener('change', function () {
  const info = this.selectedOptions[0]?.dataset.info || '';
  const infoBox = document.getElementById('glassInfo');
  infoBox.style.display = info ? 'block' : 'none';
  infoBox.textContent = info ? 'Обране скло: ' + info : '';
});

document.querySelector('input[name="images[]"]')?.addEventListener('change', function () {
  const container = document.getElementById('imagePreviewContainer');
  const mainInput = document.getElementById('mainImageIndex');
  container.innerHTML = '';

  const files = this.files;
  if (!files || files.length === 0) return;

  if (files.length > 4) {
    alert('Можна вибрати максимум 4 зображення!');
    this.value = '';
    return;
  }

  Array.from(files).forEach((file, index) => {
    if (!file.type.startsWith('image/')) return;

    const reader = new FileReader();
    reader.onload = function (e) {
      const img = document.createElement('img');
      img.src = e.target.result;
      img.className = 'preview-thumb';
      img.dataset.index = index;
      img.title = 'Натисніть, щоб обрати головне зображення';

      img.addEventListener('click', () => {
        document.querySelectorAll('.preview-thumb').forEach(i => i.classList.remove('main-pic'));
        img.classList.add('main-pic');
        mainInput.value = index;
      });

      if (index === 0) img.classList.add('main-pic');

      img.style.maxHeight = '100px';
      img.style.border = '2px solid #ccc';
      img.style.padding = '3px';
      img.style.cursor = 'pointer';

      container.appendChild(img);
    };
    reader.readAsDataURL(file);
  });
});

document.querySelectorAll('.preview-thumb[data-index]').forEach((img, index) => {
  img.addEventListener('click', () => {
    document.querySelectorAll('.preview-thumb').forEach(i => i.classList.remove('main-pic'));
    img.classList.add('main-pic');
    document.getElementById('mainImageIndex').value = index;
  });
});
</script>
