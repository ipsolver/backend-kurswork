<?php $this->Title = empty($isUnpublished) ? 'Картини' : 'Неопубліковані картини'; ?>

<h2><?= empty($isUnpublished) ? 'Картини' : 'Неопубліковані картини' ?></h2>

<style>
a{
    color: black;
}
.like-badge {
  display: inline-block;
  background-color: #ffc0cb;
  color: #333;
  font-size: 14px;
  padding: 4px 8px;
  border-radius: 20px;
  font-weight: bold;
  margin-top: 5px;
  box-shadow: 0 0 5px rgba(0,0,0,0.1);
  user-select: none;
}
</style>

<?php if($role == "admin"): ?>
  <div class="action-buttons mb-4">
    <a href="/crystal/items/add"><button class="btn btn-success">Додати картину</button></a>

    <?php if (!empty($isUnpublished)): ?>
      <a href="/crystal/items/"><button class="btn btn-primary">Опубліковані товари</button></a>
    <?php else: ?>    
    <a href="/crystal/items/unpublished" class="unpublished-btn-wrapper">
  <button class="btn btn-primary position-relative">
    Неопубліковані товари
    <span class="badge-unpublished"><?= $unpublishedCount ?></span>
  </button>
</a>

    
    
      <?php endif ?>
  </div>
<?php endif ?>

<?php include("filter.php"); ?>




<div class="row row-cols-1 row-cols-md-2 g-4 h-300" id="itemsContainer">
  <?php if (!empty($isUnpublished)): ?>

  <script src="/crystal/assets/js/unpublishedFilter.js"></script>
  
  <?php endif ?>
</div>



<?php if ($totalPages > 1): ?>
<nav class="mt-4">
  <ul class="pagination justify-content-center">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
        <a class="page-link" href="<?= !empty($isUnpublished) ? "/crystal/items/unpublished/$i" : "/crystal/items/index/$i" ?>">
          <?= $i ?>
        </a>
      </li>
    <?php endfor ?>
  </ul>
</nav>
<?php endif ?>



<?php if (empty($isUnpublished)): ?>
    <?php include("itemsFilter.php"); ?>
<?php endif ?>
