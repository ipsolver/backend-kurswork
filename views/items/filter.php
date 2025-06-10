<div class="accordion mb-4" id="filterAccordion">
  <div class="accordion-item">
    <h2 class="accordion-header" id="headingOne">
      <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
        Фільтр
      </button>
    </h2>
    <div id="filterCollapse" class="accordion-collapse collapse">
      <div class="accordion-body">
        <form id="filterForm">
          <input type="text" name="title" class="form-control mb-2" placeholder="Назва товару">
          <input type="text" name="code" class="form-control mb-2" placeholder="Код товару">

          <select name="category" class="form-select mb-2">
            <option value="all">Всі категорії</option>
            <?php
            foreach ($categories as $category)
                echo "<option value=\"{$category['id']}\">{$category['name']}</option>";
            ?>
          </select>

          <select name="genre" class="form-select mb-2">
            <option value="all">Всі жанри</option>
            <?php foreach ($genres as $genre): ?>
              <option value="<?= $genre['id'] ?>"><?= $genre['name'] ?></option>
            <?php endforeach; ?>
          </select>


          <select name="sort" class="form-select mb-2">
            <option value="default">Сортування</option>
            <option value="price_asc">Ціна ↑</option>
            <option value="price_desc">Ціна ↓</option>
          </select>

        </form>        
        <button type="button" class="btn btn-secondary w-100 mt-2" id="resetFilters">Скинути фільтри</button>
      </div>
    </div>
  </div>
</div>