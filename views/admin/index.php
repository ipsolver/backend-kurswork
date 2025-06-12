<h2>Адмін-панель</h2>

<style>
.order-cards-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}
.order-card {
    border: 1px solid #ccc;
    padding: 10px;
    width: 250px;
    border-radius: 8px;
}
.order-img-small {
    width: 100%;
    height: auto;
    max-height: 150px;
    object-fit: cover;
    margin-bottom: 10px;
}
.modal {
    position: fixed;
    z-index: 1000;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
}
.modal-content {
    background: #fff;
    margin: 10% auto;
    padding: 20px;
    width: 60%;
    top: -100px;
    max-width: 500px;
    border-radius: 10px;
}
.close {
    float: right;
    font-size: 24px;
    cursor: pointer;
}
.discount-badge {
    background-color: #ff6347;
    color: white;
    font-weight: bold;
    display: inline-block;
    padding: 4px 8px;
    border-radius: 8px;
    margin: 8px 0;
    font-size: 14px;
}


</style>

<div id="admin-page">
<div class="admin-wrapper">
  <div id="sidebar">
    <button onclick = "loadOrders()">Заявки</button>
    <?php foreach ($models as $model): ?>
      <button onclick="getTableData('<?= $model ?>')"><?= ucfirst($model) ?></button>
    <?php endforeach; ?>
  </div>

  <div id="content-panel">
      <div style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
      <a id="addButton" class="btn btn-success" href="#">Додати</a>
    </div>
    <div id="info-panel">
      <div id="info">Оберіть таблицю...</div>
    </div>
  </div>
</div>
    </div>

<div id="orderModal" class="modal" style="display: none;">
  <div class="modal-content">
    <div id="orderModalBody"></div>
  </div>
</div>

<div id="glassAddModal" class="glass-modal">
  <div class="glass-modal-content">
    <h3>Додати скло</h3>
    <input type="text" id="glass_name" placeholder="Назва">
    <select id="glass_type_select"></select>
    <input type="number" id="glass_length" placeholder="Довжина (см)">
    <input type="number" id="glass_width" placeholder="Ширина (см)">
    <input type="number" id="glass_thickness" placeholder="Товщина (мм)">
    <input type="number" id="glass_cost" placeholder="Вартість">
    <button class="btn btn-primary" onclick="submitGlass()">Додати</button>
    <button class="btn btn-danger" onclick="closeGlassModal()">Скасувати</button>
  </div>
</div>

<div class="modal stats-modal" id="logsStatsModal" tabindex="-1" style="display:none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Статистика логів</h5>
        <button type="button" class="btn-close" onclick="closeLogsStatsModal()"></button>
      </div>
      <div class="modal-body">
        <label>Показати за останні днів:</label>
        <input type="number" id="logsStatsDays" class="form-control" value="7" min="1" max="365">
        <button class="btn btn-primary mt-2" onclick="loadLogsStats()">Показати</button>
        <button class="btn btn-secondary mt-2" onclick="location.href='/crystal/admin/stats'">Звітність</button>
        <div id="logsStatsChart" class="mt-3"></div>
        <div id="logsMethodChart" class="mb-4"></div>
      </div>
    </div>
  </div>
</div>


<script src="/crystal/assets/js/admin.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
