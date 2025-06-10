<?php $this->Title = 'Дописи'; ?>

<h2>Дописи</h2>

<style>
  .news-card-img {
    height: 190px;
    object-fit: contain;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 7px;
  }
  .card-body
  {
    background:rgb(213, 210, 210);
  }
  input{
    margin-top: 15px;
    max-width: 500px;
  }
</style>
  
  <?php if($role == "admin"): ?>
  <div class="action-buttons mb-4">
    <a href = "/crystal/news/add"><button class = "btn btn-success">Створити новину</button></a>
    <a href = "/crystal/tags/"><button class = "btn btn-primary">Теги для новин</button></a>
  </div>
  <?php endif ?>
  

  <input type="text" name="q" class="form-control" placeholder="Пошук новин за назвою..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
  
  <select id="tagSelect" class="form-select mt-2" style="max-width: 500px;">
  <option value="all">Всі теги</option>
  <?php foreach ($tags as $tag): ?>
    <option value="<?= $tag['id'] ?>"><?=$tag['name'] ?></option>
  <?php endforeach; ?>
</select>
  <br>

<div class="row row-cols-1 row-cols-md-3 g-4">
  <?php foreach ($news as $item): ?>
    <div class="col">
      <div class="card h-100 shadow-sm">
        <a href="/crystal/news/view?id=<?= $item['id'] ?>">
          <img src="<?= $item['image'] ?: '/crystal/assets/img/default-new.png' ?>"
            class="card-img-top news-card-img"
            alt="Зображення новини"
          >
        </a>
        <div class="card-body">
          <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
          <p class="card-text"><?= htmlspecialchars($item['short_text']) ?></p>

        <?php if (!empty($item['tags'])): ?>
            <p>
              <?php foreach ($item['tags'] as $tag): ?>
                <span class="badge badge-tag"><?= htmlspecialchars($tag['name']) ?></span>
              <?php endforeach ?>
            </p>
          <?php endif ?>




        <?php if ($role == "admin"): ?>
          <a href = "/crystal/news/edit?id=<?= $item['id']?>"><button class = "btn btn-primary">Редагувати</button></a>
          <a href = "/crystal/news/delete?id=<?= $item['id']?>"><button class = "btn btn-danger">Видалити</button></a>
        <?php endif ?>
        </div>
        
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-4">
  <ul class="pagination justify-content-center">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
      <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
        <a class="page-link" href="/crystal/news/index/<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>


<script>
let tagSelect = document.getElementById('tagSelect');
let searchInput = document.querySelector('input[name="q"]');
let newsContainer = document.querySelector('.row');
let paginationContainer = document.querySelector('.pagination');

async function fetchNews(query = '', page = 1) {
    let tagId = tagSelect.value;
    let res = await fetch(`/crystal/news/search?q=${encodeURIComponent(query)}&page=${page}&tag=${tagId}`);
    let data = await res.json();

    sessionStorage.setItem('newsPage', data.page);
    sessionStorage.setItem('newsSearchQuery', query);
    sessionStorage.setItem('newsSelectedTag', tagId);

    renderNews(data.news);
    renderPagination(data.totalPages, data.page, query);
}

tagSelect.addEventListener('change', () => {
    sessionStorage.setItem('newsSelectedTag', tagSelect.value);
    sessionStorage.setItem('newsPage', 1);
    fetchNews(searchInput.value.trim(), 1);
});


function renderNews(news) {
    newsContainer.innerHTML = '';
    for (const item of news) 
    {

      let tagsHtml = '';
        if (Array.isArray(item.tags)) 
        {
            tagsHtml = '<p>';
            for (const tag of item.tags)
                tagsHtml += `<span class="badge badge-tag">${tag.name}</span> `;
            tagsHtml += '</p>';
        }

        const html = `
        <div class="col">
          <div class="card h-100 shadow-sm">
            <a href="/crystal/news/view?id=${item.id}">
              <img
                src="${item.image || '/crystal/assets/img/default-new.png'}"
                class="card-img-top news-card-img"
                alt="Зображення новини"
              >
            </a>
            <div class="card-body">
              <h5 class="card-title">${item.title}</h5>
              <p class="card-text">${item.short_text}</p>

              ${tagsHtml}

              <?php if ($role == "manager" || $role == "admin"): ?>
                <a href = "/crystal/news/edit?id=${item.id}"><button class = "btn btn-primary">Редагувати</button></a>
                <button class="btn btn-danger" onclick="deleteNews(${item.id})">Видалити</button>
                <?php endif ?>
            </div>
          </div>
        </div>`;
        newsContainer.insertAdjacentHTML('beforeend', html);
    }
}

function renderPagination(totalPages, currentPage, query) 
{
    if (!paginationContainer)
        return;

    paginationContainer.innerHTML = '';
    for (let i = 1; i <= totalPages; i++) 
    {
        const active = (i === currentPage) ? 'active' : '';
        const li = document.createElement('li');
        li.className = `page-item ${active}`;
        li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
        paginationContainer.appendChild(li);
    }

    paginationContainer.querySelectorAll('a.page-link').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const page = parseInt(e.target.dataset.page);
            sessionStorage.setItem('newsPage', page);
            fetchNews(searchInput.value.trim(), page);
        });
    });
}

let searchTimeout = null;
searchInput.addEventListener('input', () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const query = searchInput.value.trim();
        sessionStorage.setItem('newsSearchQuery', query);
        sessionStorage.setItem('newsPage', 1);
        fetchNews(query, 1);
    }, 300);
});

document.addEventListener('DOMContentLoaded', () => {
    const savedQuery = sessionStorage.getItem('newsSearchQuery') || '';
    const savedPage = parseInt(sessionStorage.getItem('newsPage')) || 1;
    const savedTag = sessionStorage.getItem('newsSelectedTag') || 'all';

    tagSelect.value = savedTag;
    searchInput.value = savedQuery;
    fetchNews(savedQuery, savedPage);
});

async function deleteNews(id) 
{
    const confirmDelete = confirm("Ви дійсно хочете видалити цю новину?");
    if (!confirmDelete) 
      return;

    const res = await fetch('/crystal/news/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });

    const result = await res.json();

    if (result.success) 
    {
        alert("Новину видалено");
        fetchNews(searchInput.value.trim(), parseInt(sessionStorage.getItem('newsPage')) || 1);
    } 
    else
        alert(result.message || "Помилка при видаленні");
}

</script>

