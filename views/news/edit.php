<?php $this->Title = 'Редагування новини'; ?>

<h2>Редагування новини</h2>

<form method="POST" enctype="multipart/form-data">
    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?= $error_message; ?>
    </div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="title" class="form-label">Назва</label>
        <input type="text" class="form-control" id="title" name="title"
               value="<?= htmlspecialchars($newsItem['title']) ?>">
    </div>

    <div class="mb-3">
        <label for="short_text" class="form-label">Короткий опис</label>
        <textarea class="form-control" id="short_text" name="short_text" rows="2"><?= htmlspecialchars($newsItem['short_text']) ?></textarea>
    </div>

    <div class="mb-3">
        <label for="content" class="form-label">Контент</label>
        <textarea class="form-control" id="content" name="content" rows="8"><?= htmlspecialchars($newsItem['content']) ?></textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Поточне зображення:</label><br>
        <img src="<?= $newsItem['image'] ?>" style="max-width: 200px; border: 1px solid #ccc; padding: 4px;"><br><br>

        <label for="image" class="form-label">Замінити зображення:</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/png, image/jpeg">
    </div>

    <div class="mb-3" id="previewBox" style="display:none;">
        <label class="form-label">Завантажене зображення:</label><br>
        <img id="previewImage" src="" style="max-width: 200px; border: 1px dashed #999; padding: 4px;">
    </div>

    <div class="mb-3">
        <label for="tags" class="form-label">Теги</label>
        <select multiple name="tags[]" id="tags" class="form-select" style="height: 150px;">
            <?php foreach ($allTags as $tag): ?>
                <option value="<?= $tag['id'] ?>"
                    <?= in_array($tag['id'], $currentTagIds) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($tag['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small class="form-text text-muted">Утримуйте Ctrl (Cmd на Mac) для вибору кількох тегів</small>
    </div>

    <button type="submit" class="btn btn-primary">Зберегти зміни</button>
    <a href="/crystal/news/index" class="btn btn-secondary">Скасувати</a>
</form>

<script src="/crystal/assets/ckeditor/ckeditor.js"></script>
<script>
  CKEDITOR.replace('content');

  document.getElementById('image')?.addEventListener('change', function () {
    const file = this.files[0];
    const previewBox = document.getElementById('previewBox');
    const previewImage = document.getElementById('previewImage');

    if (file && file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = function (e) {
        previewImage.src = e.target.result;
        previewBox.style.display = 'block';
      };
      reader.readAsDataURL(file);
    } else {
      previewBox.style.display = 'none';
      previewImage.src = '';
    }
  });
</script>
