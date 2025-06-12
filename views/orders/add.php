<?php
$this->Title = 'Заявка';
?>

<h1 class="mb-4 text-center">Залишити заявку</h1>

<div class="order-form-wrapper">
    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= $error_message ?></div>
        <?php endif; ?>

        <div class="mb-3">
            <label for="description" class="form-label">Опис</label>
            <textarea name="description" class="form-control" rows="4" required><?= htmlspecialchars($this->post->description ?? $preDescription ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label for="deadline" class="form-label">Дедлайн</label>
            <input type="date" name="deadline" class="form-control" required value="<?= htmlspecialchars($this->post->due_date ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="category_id" class="form-label">Категорія</label>
            <select name="category_id" class="form-select">
                <option value="">— Не вибрано —</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"
                        <?= ((isset($this->post->category_id) && $this->post->category_id == $category['id']) ? 'selected' : '')
                        || (isset($preCategoryId) && $preCategoryId == $category['id'])? 'selected' : '' ?>>
                        <?= $category['name']?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="genre_id" class="form-label">Жанр</label>
            <select name="genre_id" class="form-select">
                <option value="">— Не вибрано —</option>
                <?php foreach ($genres as $genre): ?>
                    <option value="<?= $genre['id'] ?>"
                        <?= ((isset($this->post->genre_id) && $this->post->genre_id == $genre['id']) ? 'selected' : '')
                        || (isset($preGenreId) && $preGenreId == $genre['id'])? 'selected' : '' ?>>
                        <?= $genre['name']?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="width_cm" class="form-label">Ширина (см)</label>
            <input type="number" name="width_cm" step="0.1" class="form-control"
                value="<?= htmlspecialchars($this->post->width_cm ?? $preWidth ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="height_cm" class="form-label">Висота (см)</label>
            <input type="number" name="height_cm" step="0.1" class="form-control"
                value="<?= htmlspecialchars($this->post->height_cm ?? $preHeight ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="thickness_mm" class="form-label">Товщина скла (мм)</label>
            <input type="number" name="thickness_mm" step="1" class="form-control"
                value="<?= htmlspecialchars($this->post->thickness_mm ?? $preThickness ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="glass_type" class="form-label">Тип скла</label>
            <select name="glass_type" class="form-select">
                <option value="">— Не вибрано —</option>
                <?php foreach ($glass_types as $glass): ?>
                    <option value="<?= $glass['id'] ?>"
                        <?= ((isset($this->post->glass_type) && $this->post->glass_type == $glass['id']) ? 'selected' : '')
                        || (isset($preGlassType) && $preGlassType == $glass['id']) ? 'selected' : '' ?>>
                        <?= $glass['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>


        <?php if (!empty($preImagePath)): ?>
    <div class="mt-3">
        <p class="mb-2">Зображення до картини:</p>
        <img src="<?= htmlspecialchars($preImagePath) ?>" alt="Превʼю" style="max-width: 100%; border: 1px solid #ccc;">
    </div>
    <input type="hidden" name="preImagePath" value="<?= htmlspecialchars($preImagePath) ?>">
<?php endif; ?>
    



        <div class="mb-3">
            <label for="image" class="form-label">Зображення (необов'язково)</label>
            <input type="file" name="image" id="order-image-input" class="form-control" accept="image/*">
        </div>

    <div id="image-preview" class="mt-3" style="display: none;">
        <p class="mb-2">Попередній перегляд:</p>
        <img id="preview-img" src="" alt="Превʼю зображення" style="max-width: 100%; height: auto; border: 1px solid #ccc; padding: 5px;">
    </div>

    <?php if (!empty($item_id)): ?>
        <input type="hidden" name="item_id" value="<?= $item_id ?>">
    <?php endif; ?>

        <button type="submit" class="btn btn-primary">Створити</button>
        <a href="/crystal/orders" class="btn btn-link">До моїх заявок</a>
    </form>
    <small>Це попереднє замовлення. Після відправки я звʼяжуся з вами для уточнення деталей і оплати</small>
</div>

<script src = "/crystal/assets/js/add_orders.js"></script>
