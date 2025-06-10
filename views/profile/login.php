<?php
/** @var string $error_message Повідомлення про помилку*/
$this->Title = 'Login';
?>
<h1>Вхід</h1>
<form method="POST" action="">
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger" role="alert">
        <?=$error_message; ?>
    </div>
    <?php endif; ?> 
  <div class="mb-3">
    <label for="InputLogin" class="form-label">Логін</label>
    <input name="username" type="text" class="form-control" id="InputLogin">
  </div>
  <div class="mb-3">
    <label for="InputPassword" class="form-label">Пароль</label>
    <input name="password" type="password" class="form-control" id="InputPassword">
  </div>
  <button type="submit" class="btn btn-primary">Увійти</button>
</form>
<p>Ще не зареєстровані?<a href="/crystal/profile/register"> Зареєструватися</a></p>
<div style="margin-bottom: 145px"></div>