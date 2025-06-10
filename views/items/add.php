<?php $this->Title = 'Додати картину'; ?>

<h2>Додати нову картину</h2>

<form method="POST" enctype="multipart/form-data" class="mb-4">

 <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?=$error_message; ?>
    </div>
    <?php endif; ?>


  <div class="row mb-2">
    <div class="col">
      <label>Назва</label>
      <input value="<?=$this->controller->post->title ?>" type="text" name="title" class="form-control" required>
    </div>
    <div class="col">
      <label>Код</label>
      <input value="<?=$this->controller->post->code ?>" type="text" name="code" class="form-control" required>
    </div>
  </div>

  <div class="mb-2">
    <label>Опис</label>
    <textarea name="description" class="form-control" rows="3"></textarea>
  </div>

  <div class="row mb-2">
    <div class="col">
      <label>Категорія</label>
      <select name="category_id" class="form-select" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="col">
      <label>Жанр</label>
      <select name="genre_id" class="form-select" required>
        <?php foreach ($genres as $genre): ?>
          <option value="<?= $genre['id'] ?>"><?= htmlspecialchars($genre['name']) ?></option>
        <?php endforeach ?>
      </select>
    </div>
  </div>

  <div class="row mb-2">
    <div class="col">
      <label>Тариф (базова ціна)</label>
      <input value="<?=$this->controller->post->tarif ?>" type="number" step="0.01" name="tarif" class="form-control" required>
    </div>
    <div class="col">
      <label>Знижка (%)</label>
      <input value="<?=$this->controller->post->discount ?>" type="number" name="discount" class="form-control" value="0">
    </div>
  </div>

  <div class="row mb-2">
    <div class="col">
      <label>Тип скла</label>
      <select id="glassTypeSelect" class="form-select">
        <option value="">— Оберіть тип скла —</option>
        <?php foreach ($glassTypes as $type): ?>
          <option value="<?= $type['id'] ?>"><?= htmlspecialchars($type['name']) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="col">
      <label>Скло</label>
      <select name="glass_id" id="glassSelect" class="form-select">
        <option value="">— Оберіть скло —</option>
        <?php foreach ($glasses as $glass): ?>
          <option value="<?= $glass['id'] ?>" data-type="<?= $glass['glass_type'] ?>"
            data-info="<?= $glass['length_cm'] ?>×<?= $glass['width_cm'] ?> см, товщина: <?= $glass['thickness_mm'] ?> мм, вартість: <?= $glass['cost'] ?>">
            <?= htmlspecialchars($glass['name']) ?>
          </option>
        <?php endforeach ?>
      </select>
    </div>
  </div>

  <div id="glassInfo" class="mb-3 text-muted" style="display:none;"></div>

  <div class="mb-3">
    <label>Фото</label>
    <input type="file" name="images[]" class="form-control" multiple accept="image/png, image/jpeg">
    <small class="text-muted">Можна завантажити до 4 зображень. Оберіть яке буде основним</small>
  </div>

<div id="imagePreviewContainer" class="d-flex gap-2 flex-wrap mt-2"></div>
<input type="hidden" name="main_image_index" id="mainImageIndex" value="0">



  <div class="mb-3">
    <label>Дата публікації</label>
    <input value="<?=$this->controller->post->published_at ?>" type="datetime-local" name="published_at" class="form-control">
  </div>

  <button type="submit" class="btn btn-success">Зберегти</button>
  <a href="/crystal/items" class="btn btn-secondary">Скасувати</a>
</form>

<script>
document.getElementById('glassTypeSelect')?.addEventListener('change', function () 
{
  let selectedType = this.value;
  let glassSelect = document.getElementById('glassSelect');
  let options = glassSelect.querySelectorAll('option');
  glassSelect.value = '';

  options.forEach(option => {
    if (!option.value) 
        return;
    option.hidden = option.dataset.type !== selectedType;
  });
});

document.getElementById('glassSelect')?.addEventListener('change', function () 
{
  let info = this.selectedOptions[0]?.dataset.info || '';
  let infoBox = document.getElementById('glassInfo');
  if (info) 
  {
    infoBox.textContent = "Обране скло: " + info;
    infoBox.style.display = 'block';
  } 
  else
    infoBox.style.display = 'none';
});

document.querySelector('input[name="images[]"]').addEventListener('change', function () 
{
  let container = document.getElementById('imagePreviewContainer');
  let mainInput = document.getElementById('mainImageIndex');
  container.innerHTML = '';

  const files = this.files;
  if (!files || files.length === 0) 
    return;

  Array.from(files).forEach((file, index) => {
    const reader = new FileReader();
    reader.onload = function (e) 
    {
      let img = document.createElement('img');
      img.src = e.target.result;
      img.className = 'preview-thumb';
      img.dataset.index = index;
      img.title = 'Натисніть, щоб обрати головне зображення';

      img.addEventListener('click', () => {
        document.querySelectorAll('.preview-thumb').forEach(i => i.classList.remove('main-pic'));
        img.classList.add('main-pic');
        mainInput.value = index;
      });

      // По замовчуванню перше — головне
      if (index === 0)
        img.classList.add('main-pic');

      container.appendChild(img);
    };
    reader.readAsDataURL(file);
  });
});
</script>
