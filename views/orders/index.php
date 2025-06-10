<?php $this->Title = "Мої замовлення"; ?>
<style>
.modal-custom {
    display: none;
    position: fixed;
    z-index: 1050;
    padding-top: 60px;
    left: 0; top: 0; width: 100%; height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.6);
}
.modal-content-custom {
    background-color: #fff;
    margin: auto;
    padding: 20px;
    width: 90%;
    max-width: 500px;
    border-radius: 8px;
    position: relative;
}
.modal-close {
    position: absolute;
    right: 15px;
    top: 10px;
    font-size: 28px;
    cursor: pointer;
}
</style>
<h1 class="mb-4">Мої замовлення</h1>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="/crystal/orders/add"><button class="btn btn-success">Створити замовлення</button></a>

    <select id="status-filter" class="form-select w-auto">
        <option value="">— Усі статуси —</option>
        <option value="Обробка">Обробка</option>
        <option value="Прийнято">Прийнято</option>
        <option value="Відхилено">Відхилено</option>
        <option value="Готово">Готово</option>
    </select>
</div>

<div id="orders-wrapper">
    <div id="orders-container" class="orders-grid"></div>
    <div class="pagination-wrapper mt-4"></div>
</div>

<div id="orderModal" class="modal-custom">
  <div class="modal-content-custom">
    <span onclick="closeOrderModal()" class="modal-close">&times;</span>
    <div id="orderModalBody">Завантаження...</div>
  </div>
</div>

<script src="/crystal/assets/js/my_orders.js"></script>
