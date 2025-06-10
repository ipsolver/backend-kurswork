<?php $this->Title = $newsItem['title']; ?>

<div class="news-view">
    <h1><?= htmlspecialchars($newsItem['title']) ?></h1>

    <?php if (!empty($newsItem['image'])): ?>
        <img src="<?= htmlspecialchars($newsItem['image']) ?>" alt="<?= htmlspecialchars($newsItem['title']) ?>">
    <?php endif; ?>
        
    <div class="news-content">
        <?= $newsItem['content'] ?>
    </div>

        <?php if (!empty($newsItem['tags'])): ?>
    <div style="margin-top: 15px;">
        <?php foreach ($newsItem['tags'] as $tag): ?>
            <span class="badge badge-tag"><?= $tag['name'] ?></span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>



    <div class="news-date">
        Дата публікації: 
    <?php
        $dt = new DateTime($newsItem['created_at']);
        $dt->modify('+3 hours');
        echo $dt->format('d.m.Y H:i');
    ?>
    </div>


    <div class="back-btn">
        <a href="/crystal/news/index" class="btn btn-outline-primary">Назад до списку новин</a>
    </div>
</div>
