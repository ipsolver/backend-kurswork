<?php
$this->Title = 'About';
?>
<link rel="stylesheet" href="/crystal/assets/css/style.css">
<div class="about-card">
    <div class="about-content">
        <div class="about-text">
            <h1>Привіт!</h1>
            <h2>Я — <?= $admin ?>, автор <?= $title ?></h2>
            <p><?= $description ?></p>
        </div>
        <div class="about-image">
            <img src="/chatter/assets/img/hero_me.jpg" alt="Автор Chatter">
        </div>
    </div>
    <div class="about-telegram">
        <a href="https://t.me/vader_vad" target="_blank">
            <img src="/chatter/assets/img/telegram_icon.png" alt="Telegram" />
        </a>
    </div>
    <a href = "/crystal/home">На головну</a>
</div>