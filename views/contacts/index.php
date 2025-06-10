<?php $this->Title = 'Контактні дані'; ?>

<h2 class="cont-page-title">Контактні дані</h2>

<?php if($role == "admin"): ?>
<a href="/crystal/contacts/add" class="add-contact-btn">Додати</a>
<?php endif ?>

<?php if (empty($contacts)): ?>
    <p class="empty-msg">Контакти ще не додані</p>
<?php else: ?>
    <div class="contacts-grid">
        <?php foreach ($contacts as $contact): ?>
            <div class="contact-card" style="background: <?= htmlspecialchars($contact['color_bg']) ?>; color: <?= htmlspecialchars($contact['color_text']) ?>;">
                <div class="cont-card-header">
                    <h3><?= $contact['title'] ?></h3>
                    <?php if($role == "admin"): ?>
                    <div class="cont-card-actions">

                        <a href="#" 
                        title="Редагувати" 
                        class="cont-edit-btn"
                        data-id="<?= $contact['id'] ?>"
                        data-title="<?= htmlspecialchars($contact['title'], ENT_QUOTES) ?>"
                        data-content="<?= htmlspecialchars($contact['content'], ENT_QUOTES) ?>"
                        data-bg="<?= htmlspecialchars($contact['color_bg']) ?>"
                        data-text="<?= htmlspecialchars($contact['color_text']) ?>">
                        <img src="/crystal/assets/img/edit_btn.png" alt="Редагувати">
                        </a>
                          
                        
                        <button type="button" class="cont-delete-btn" data-id="<?= $contact['id'] ?>" title="Видалити">
                            <img src="/crystal/assets/img/delete_btn.png" alt="Видалити">
                        </button>
                    </div>
                    <?php endif ?>
                </div>
                <div class="cont-card-content">
                    <?= $contact['content'] ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<div id="contactModal" class="cont-modal">
  <div class="cont-modal-content">
    <h3>Додати контакт</h3>
    <form id="addContactForm">
      <input type="text" name="title" placeholder="Заголовок" required>
      <textarea name="content" id = "contenting" placeholder="Контент" required></textarea>
      <label>Колір фону: <input type="color" name="color_bg" value="#ffffff"></label>
      <label>Колір тексту: <input type="color" name="color_text" value="#000000"></label>
      <div class="cont-modal-actions">
        <button type="submit">Додати</button>
        <button type="button" id="closeModal">Скасувати</button>
      </div>
    </form>
  </div>
</div>

<script  src = "/crystal/assets/ckeditor/ckeditor.js"></script>
<script>
    CKEDITOR.replace('contenting');
</script>
<script src = "/crystal/assets/js/contacts.js"></script>

