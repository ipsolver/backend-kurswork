document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('itemsContainer');
  const paginationContainer = document.querySelector('.pagination');
  const filterForm = document.getElementById('filterForm');
  const inputs = filterForm.querySelectorAll('input, select');
  let currentPage = 1;

  const storageKey = 'unpublishedFilters';

  function debounce(fn, delay = 300) 
  {
    let timeout;
    return function (...args) 
    {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  const debouncedFetch = debounce(() => fetchUnpublishedItems(1), 400);
  inputs.forEach(input => input.addEventListener('input', debouncedFetch));

  function saveFiltersToStorage() 
  {
    const data = {};
    inputs.forEach(input => data[input.name] = input.value);
    data.page = currentPage;
    sessionStorage.setItem(storageKey, JSON.stringify(data));
  }

  function loadFiltersFromStorage() 
  {
    const saved = sessionStorage.getItem(storageKey);
    if (!saved) return;

    const data = JSON.parse(saved);
    inputs.forEach(input => {
      if (data[input.name] !== undefined)
        input.value = data[input.name];
    });

    currentPage = parseInt(data.page) || 1;
  }

  
async function deleteItem(id) 
{
    const confirmDelete = confirm("Ви дійсно хочете видалити цю картину?");
    if (!confirmDelete) 
      return;

    const res = await fetch('/crystal/items/deletejson', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
    });

    const result = await res.json();

    if (result.success) 
    {
        alert("Картину видалено");
        await fetchUnpublishedItems(currentPage);
    } 
    else
        alert(result.message || "Помилка при видаленні");
}

  async function fetchUnpublishedItems(page = 1) 
  {
    currentPage = page;

    const data = {};
    inputs.forEach(input => data[input.name] = input.value);
    data.page = page;

    saveFiltersToStorage();

    const response = await fetch('/crystal/items/filterUnpublished', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    });

    if (!response.ok) 
    {
      container.innerHTML = '<div class="col"><p>Помилка при завантаженні товарів</p></div>';
      paginationContainer.innerHTML = '';
      return;
    }

    const json = await response.json();
    const items = json.items;
    const totalPages = json.totalPages;

    container.innerHTML = '';

    if (items.length === 0) 
    {
      container.innerHTML = '<div class="col"><p>Нічого не знайдено</p></div>';
      if (!paginationContainer) 
        return;
      paginationContainer.innerHTML = '';
      return;
    }

    for (const item of items) 
    {
      const discount = item.discount > 0 ? `<span class="discount-badge">-${item.discount}%</span>` : '';
      const oldPrice = item.discount > 0 ? `<small class="text-muted ms-2"><s>${item.tarif} грн</s></small>` : '';

      container.innerHTML += `
        <div class="col">
          <div class="card item-card shadow-sm" data-id="${item.id}">
            <img src="${item.image || '/crystal/assets/img/default-item.png'}" class="card-img-top item-card-img" alt="Зображення товару">
            <div class="card-body">
              <h4 class="card-title">${item.title}${discount}</h4>
              <small>Товарний код: ${item.code}</small>
              <p class="card-text"><b>Жанр:</b> ${item.genre_name}</p>
              <p class="card-text"><b>Категорія:</b> ${item.category_name}</p>
              <p class="card-text"><b>Ціна:</b> ${item.price} грн ${oldPrice}</p>
              <p><b>Дата публікації:</b> ${item.published_at}</p>
              <div class="action-buttons mt-3">
                <a href="/crystal/items/edit?id=${item.id}"><button class="btn btn-primary me-2">Редагувати</button></a>
                <button class="btn btn-danger btn-delete" data-id="${item.id}">Видалити</button>
                <button class="btn btn-success publish-now-btn" data-id="${item.id}">Опублікувати зараз</button>
              </div>
            </div>
          </div>
        </div>
      `;
    }

container.addEventListener('click', (e) => {
  const card = e.target.closest('.item-card');
  if (card && !e.target.closest('.action-buttons')) {
    const id = card.dataset.id;
    window.location.href = `/crystal/items/view?id=${id}`;
  }
});




  container.querySelectorAll('.btn-delete').forEach(button => {
  button.addEventListener('click', (e) => {
    e.stopPropagation();
    e.preventDefault();
    const id = button.dataset.id;
    deleteItem(id);
  });
});



    renderPagination(totalPages, page);
  }

  function renderPagination(totalPages, currentPage) 
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
      fetchUnpublishedItems(page);
    });
  });
}


  container.addEventListener('click', async (e) => {
    if (e.target.classList.contains('publish-now-btn')) {
      e.preventDefault();
      e.stopPropagation();

      const itemId = e.target.dataset.id;

      if (!confirm('Опублікувати цей товар зараз?')) return;

      const res = await fetch('/crystal/items/publish', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: itemId })
      });

      const data = await res.json();
      if (data.success) {
        fetchUnpublishedItems(currentPage);
      } else {
        alert('Не вдалося опублікувати товар');
      }
    }
  });

  document.getElementById('resetFilters').addEventListener('click', () => {
    filterForm.reset();
    sessionStorage.removeItem(storageKey);
    currentPage = 1;
    fetchUnpublishedItems(1);
  });

  loadFiltersFromStorage();
  fetchUnpublishedItems(currentPage);
});