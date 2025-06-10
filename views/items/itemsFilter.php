<script>
const filterForm = document.getElementById('filterForm');
const inputs = filterForm.querySelectorAll('input, select');
const container = document.getElementById('itemsContainer');
const paginationContainer = document.querySelector('.pagination');

let currentPage = 1;

function debounce(fn, delay = 300)
{
  let timeout;
  return function (...args) 
  {
    clearTimeout(timeout);
    timeout = setTimeout(() => fn.apply(this, args), delay);
  };
}

function saveFiltersToStorage() 
{
  const data = {};
  inputs.forEach(input => data[input.name] = input.value);
  data.page = currentPage;
  sessionStorage.setItem('itemsFilters', JSON.stringify(data));
}

function loadFiltersFromStorage() 
{
  const saved = sessionStorage.getItem('itemsFilters');
  if (!saved) 
    return;
  const data = JSON.parse(saved);

  inputs.forEach(input => {
    if (data[input.name]) {
      input.value = data[input.name];
    }
  });

  currentPage = parseInt(data.page) || 1;
}

async function fetchItems(page = 1) 
{
  currentPage = page;
  const formData = new FormData(filterForm);
  formData.append('page', page);
  

  saveFiltersToStorage();

  const response = await fetch('/crystal/items/filter', {
    method: 'POST',
    body: formData
  });

  const data = await response.json();
  const items = data.items;
  const totalPages = data.totalPages;

  container.innerHTML = '';

  if (items.length === 0) 
  {
    container.innerHTML = '<div class="col"><p>Нічого не знайдено</p></div>';
    paginationContainer.innerHTML = '';
    return;
  }

  for (const item of items) 
  {
    const discount = item.discount > 0 ? `<span class="discount-badge">-${item.discount}%</span>` : '';
    const oldPrice = item.discount > 0 ? `<small class="text-muted ms-2"><s>${item.tarif} грн</s></small>` : '';

    container.innerHTML += `
      <div class="col" onclick = "location.href='/crystal/items/view?id=${item.id}'">
        <div class="card item-card shadow-sm">
          <img src="${item.image || '/crystal/assets/img/default-item.png'}" class="card-img-top item-card-img" alt="Зображення товару">
          <div class="card-body">
              <div class="like-badge">
                ❤️ ${item.likes_count}
              </div>

            <h4 class="card-title">${item.title}${discount}</h4>
            <small>Товарний код: ${item.code}</small>
            <p class="card-text"><b>Жанр:</b> ${item.genre_name}</p>
            <p class="card-text"><b>Категорія:</b> ${item.category_name}</p>
            <p class="card-text"><b>Ціна:</b> ${item.price} грн ${oldPrice}</p>
          <?php if ($role == "manager" || $role == "admin"): ?>
          <div class="action-buttons">
          <a href = "/crystal/items/edit?id=${item.id}"><button class = "btn btn-primary">Редагувати</button></a>
          <button class="btn btn-danger btn-delete" data-id="${item.id}">Видалити</button>
          </div>
          <?php endif ?></div>
          
        </div>
        
      </div>
    `;
  }
  container.querySelectorAll('.btn-delete').forEach(button => {
  button.addEventListener('click', (e) => {
    e.stopPropagation();
    e.preventDefault();
    let id = button.dataset.id;
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
    let active = (i === currentPage) ? 'active' : '';
    let li = document.createElement('li');
    li.className = `page-item ${active}`;
    li.innerHTML = `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
    paginationContainer.appendChild(li);
  }

  paginationContainer.querySelectorAll('a.page-link').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      const page = parseInt(e.target.dataset.page);
      fetchItems(page);
    });
  });
}

const debouncedFetch = debounce(() => fetchItems(1), 400);

inputs.forEach(input => input.addEventListener('input', debouncedFetch));

document.addEventListener('DOMContentLoaded', () => {
  loadFiltersFromStorage();
  fetchItems(currentPage);
});

//скидання фільтрів і пошуковиків
document.getElementById('resetFilters').addEventListener('click', () => {
  filterForm.reset();
  sessionStorage.removeItem('itemsFilters');
  currentPage = 1;

  fetchItems(1);
});

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
        await fetchItems(currentPage);
    } 
    else
        alert(result.message || "Помилка при видаленні");
}


</script>