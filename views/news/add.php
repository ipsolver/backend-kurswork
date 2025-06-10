<?php $this->Title = 'Створити новину'; ?>

<h2>Створення новини</h2>


<form method="POST" enctype="multipart/form-data" class="mt-3" style="max-width: 700px;">

    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?=$error_message; ?>
    </div>
    <?php endif; ?>

    <div class="mb-3">
        <label for="title" class="form-label">Заголовок</label>
        <input value="<?=$this->controller->post->title ?>" type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
    </div>

    <div class="mb-3">
        <label for="short_text" class="form-label">Короткий опис</label>
        <textarea value="<?=$this->controller->post->short_text ?>" class="form-control" id="short_text" name="short_text" rows="2"><?= htmlspecialchars($_POST['short_text'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label for="content" class="form-label">Повний текст</label>
        <textarea value="<?=$this->controller->post->content ?>" class="form-control" id="content" name="content"  rows="8"><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
        <label for="image" class="form-label">Зображення</label>
        <input type="file" class="form-control" id="image" name="image" accept="image/jpeg, image/png">
        <div class="mt-2" id="imagePreviewContainer" style="display:none;">
            <label>Попередній перегляд:</label><br>
            <img id="imagePreview" src="" alt="Попередній перегляд" style="max-height: 200px; border: 1px solid #ccc; padding: 4px;">
        </div>
    </div>


        <?php use models\Tags; ?>
<div class="mb-3">
    <label for="tags" class="form-label">Теги</label>
    <select multiple name="tags[]" id="tags" class="form-select" style="height: 150px;">
        <?php foreach (Tags::getAll() as $tag): ?>
            <option value="<?= $tag['id'] ?>"
                <?= in_array($tag['id'], $_POST['tags'] ?? []) ? 'selected' : '' ?>>
                <?= htmlspecialchars($tag['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <small class="form-text text-muted">Для вибору кількох тегів утримуйте Ctrl (Cmd на Mac).</small>
</div>



    <button type="submit" class="btn btn-success">Створити</button>
    <a href="/crystal/news/index" class="btn btn-secondary">Скасувати</a>
</form>

<script  src = "/crystal/assets/ckeditor/ckeditor.js"></script>
<script>
    CKEDITOR.replace('content');
</script>
<script src = "/crystal/assets/js/news.js"></script>