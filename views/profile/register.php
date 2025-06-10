<?php
/** @var string $error_message Повідомлення про помилку*/
$this->Title = 'Реєстрація';
?>
<h1>Реєстрація</h1>
<form method="POST" action="" enctype="multipart/form-data">
    <?php if (!empty($error_message)): ?>
    <div class="alert alert-danger" role="alert">
        <?=$error_message; ?>
    </div>
    <?php endif; ?>
    <div class="mb-3">
    <label for="InputFirstName" class="form-label">Ім'я *</label>
    <input value="<?=$this->controller->post->first_name ?>" name="first_name" type="text" class="form-control" id="InputFirstName" required>
  </div> 
  <div class="mb-3">
    <label for="InputLastName" class="form-label">Прізвище *</label>
    <input value="<?=$this->controller->post->last_name ?>" name="last_name" type="text" class="form-control" id="InputLastName" required>
  </div>
  <div class="mb-3">
    <label for="InputLogin" class="form-label">Логін *</label>
    <input value="<?=$this->controller->post->username ?>" name="username" type="text" class="form-control" id="InputLogin" required>
  </div>
<div class="mb-3">
    <label for="InputPhone" class="form-label">Номер телефону *</label>
    <input value="<?= $this->controller->post->phone ?>" name="phone" type="text" class="form-control" id="InputPhone" required>
</div>
    </div>
    <div class="mb-3">
        <label for="InputProfilePic" class="form-label">Аватарка</label>
        <input name="profile_picture" type="file" class="form-control" id="InputProfilePic" accept="image/jpeg, image/png">
    </div>

  <div class="mb-3">
    <label for="InputPassword" class="form-label">Пароль *</label>
    <input name="password" type="password" class="form-control" id="InputPassword" required>
  </div>
  <div class="mb-3">
    <label for="InputPassword2" class="form-label">Пароль (ще раз) *</label>
    <input name="password2" type="password" class="form-control" id="InputPassword2" required>
  </div>
  <button type="submit" class="btn btn-primary">Зареєструватися</button>
</form>
<p>Уже зареєстровані?<a href="/crystal/profile/login"> Вхід</a></p>