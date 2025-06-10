<?php
$this->Title = 'Crystal';
use models\Users;

$currentUser = Users::getCurrentUser();
?>
<div class="hero">
    <div class="typing-text">Вітаємо в Crystal!</div>
    <div class="hero-subtitle">Поглянь на світ через призму мистецтва</div>
</div>

<?php if (!empty($featuredItems)): ?>
<h2 class="slider-heading">Найпопулярніші картини</h2>
<div class="glass-slider">
  <div class="slider-track">
    <?php foreach ($featuredItems as $item): ?>
      <div class="slide">
        <a href="/crystal/items/view?id=<?= $item['id'] ?>">
          <img src="<?= $item['main_image'] ?: '/crystal/assets/img/default-new.png' ?>" alt="<?= htmlspecialchars($item['title']) ?>">
        </a>
        <div class="slide-title"><?= htmlspecialchars($item['title']) ?></div>
        <div class="slide-likes">Вподобань: ❤️ <?= $item['like_count'] ?></div>
      </div>
    <?php endforeach ?>
  </div>
</div>
<?php endif; ?>

<h2 class="landing-title" align="center">Статистика</h2>

<div class="stats-section">

  <div class="stat-circle users">
    <div class="stat-number" data-target="<?= $totalUsers ?>">0</div>
    <div class="stat-label">Користувачів</div>
  </div>
  <div class="stat-circle items">
    <div class="stat-number" data-target="<?= $totalItems - $unpublishedItems ?>">0</div>
    <div class="stat-label">Картин</div>
  </div>
  <div class="stat-circle news">
    <div class="stat-number" data-target="<?= $totalNews ?>">0</div>
    <div class="stat-label">Дописів</div>
  </div>
<div class="stat-circle orders">
    <div class="stat-number" data-target="<?= $totalOrders ?>">0</div>
    <div class="stat-label">Замовлень</div>
  </div>

</div>



<div class="landing">
  <p class="landing-subtitle">Тут знайдеш багатогранну колекцію витворів мистецтва на склі</p>
  <div>
    <h3 class="landing-title"><a href="/crystal/items"><span>До продукції </span></a></h3>
    <?php if($currentUser && $currentUser['role'] != "admin"): ?>
    <h3 class="landing-title"><a href="/crystal/orders/add"><span>Картина на замовлення </span></a></h3>
    <?php endif; ?>
  </div>
</div>

<script>
  let track = document.querySelector('.slider-track');
  let slides = document.querySelectorAll('.slide');
  let currentIndex = 0;

  let firstClone = slides[0].cloneNode(true);
  track.appendChild(firstClone);

  let totalSlides = slides.length + 1;

  function slide() 
  {
    currentIndex++;
    track.style.transition = 'transform 0.6s ease-in-out';
    track.style.transform = `translateX(-${currentIndex * 100}%)`;

    if (currentIndex === totalSlides - 1) 
    {
      setTimeout(() => {
        track.style.transition = 'none';
        currentIndex = 0;
        track.style.transform = 'translateX(0)';
      }, 600);
    }
  }

  setInterval(slide, 4000);


  document.addEventListener("DOMContentLoaded", () => {
    let counters = document.querySelectorAll(".stat-number");
    let hasAnimated = false;

    let animateCounter = (counter) => {
      let target = +counter.getAttribute("data-target");
      let count = 0;
      let duration = 3000;
      let stepTime = 50;
      let steps = duration / stepTime;
      let increment = Math.ceil(target / steps);

      let update = () => {
        count += increment;
        if (count < target) 
        {
          counter.innerText = count;
          setTimeout(update, stepTime);
        } 
        else
          counter.innerText = target;
      };

      update();
    };

    let observer = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !hasAnimated) 
        {
          counters.forEach(counter => animateCounter(counter));
          hasAnimated = true;
          observer.disconnect();
        }
      });
    }, {
      threshold: 0.5
    });

    let statsSection = document.querySelector('.stats-section');
    if (statsSection)
      observer.observe(statsSection);
  });

</script>


