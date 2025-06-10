document.querySelectorAll('.cont-delete-btn').forEach(addDeleteHandler);
document.querySelectorAll('.cont-edit-btn').forEach(addEditHandler);

const modal = document.getElementById('contactModal');
const form = document.getElementById('addContactForm');

document.querySelector('.add-contact-btn')?.addEventListener('click', e => {
  e.preventDefault();
  form.reset();
  delete form.dataset.editing;
  modal.querySelector('h3').textContent = 'Додати контакт';
  form.querySelector('button[type="submit"]').textContent = 'Додати';
  CKEDITOR.instances['contenting']?.setData('');
  modal.style.display = 'flex';
});

document.getElementById('closeModal')?.addEventListener('click', () => {
  modal.style.display = 'none';
});

form.addEventListener('submit', async e => {
  e.preventDefault();
  CKEDITOR.instances['contenting']?.updateElement();

  const data = {
    title: form.title.value.trim(),
    content: form.content.value.trim(),
    color_bg: form.color_bg.value,
    color_text: form.color_text.value
  };

  if (!data.title || !data.content) 
  {
    alert('Заповніть усі поля');
    return;
  }

  const isEdit = !!form.dataset.editing;
  const url = isEdit ? '/crystal/contacts/edit' : '/crystal/contacts/add';
  
  if (isEdit) 
    data.id = form.dataset.editing;

  const res = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });

  const result = await res.json();
  if (!result.success) 
  {
    alert(result.message || 'Помилка при збереженні');
    return;
  }

  const contact = result.contact;
  const html = `
    <div class="contact-card" style="background: ${contact.color_bg}; color: ${contact.color_text};">
      <div class="cont-card-header">
        <h3>${contact.title}</h3>
        <div class="cont-card-actions">
          <a href="#" 
             class="cont-edit-btn" 
             data-id="${contact.id}" 
             data-title="${contact.title.replace(/"/g, '&quot;')}" 
             data-content="${contact.content.replace(/"/g, '&quot;')}" 
             data-bg="${contact.color_bg}" 
             data-text="${contact.color_text}">
            <img src="/crystal/assets/img/edit_btn.png" alt="Редагувати">
          </a>
          <button type="button" class="cont-delete-btn" data-id="${contact.id}" title="Видалити">
            <img src="/crystal/assets/img/delete_btn.png" alt="Видалити">
          </button>
        </div>
      </div>
      <div class="cont-card-content">${contact.content}</div>
    </div>
  `;

  const grid = document.querySelector('.contacts-grid');

  if (isEdit) 
  {
    const oldCard = document.querySelector(`.cont-edit-btn[data-id="${contact.id}"]`)?.closest('.contact-card');
    if (oldCard) 
      {
      const temp = document.createElement('div');
      temp.innerHTML = html;
      const newCard = temp.firstElementChild;
      oldCard.replaceWith(newCard);

      newCard.querySelector('.cont-delete-btn') && addDeleteHandler(newCard.querySelector('.cont-delete-btn'));
      newCard.querySelector('.cont-edit-btn') && addEditHandler(newCard.querySelector('.cont-edit-btn'));
    }
  } 

  else 
  {
    const temp = document.createElement('div');
    temp.innerHTML = html;
    const newCard = temp.firstElementChild;
    grid?.prepend(newCard);
    newCard.querySelector('.cont-delete-btn') && addDeleteHandler(newCard.querySelector('.cont-delete-btn'));
    newCard.querySelector('.cont-edit-btn') && addEditHandler(newCard.querySelector('.cont-edit-btn'));
  }

  form.reset();
  CKEDITOR.instances['contenting']?.setData('');
  delete form.dataset.editing;
  modal.style.display = 'none';
});

function addDeleteHandler(button) {
  button.addEventListener('click', () => {
    if (!confirm('Видалити цей контакт?')) 
      return;

    const id = button.dataset.id;

    fetch('/crystal/contacts/delete', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success)
          button.closest('.contact-card')?.remove();
        else
          alert('Помилка при видаленні контакту');
      })
      .catch(() => alert('Невідома помилка'));
  });
}

function addEditHandler(button) {
  button.addEventListener('click', e => {
    e.preventDefault();
    form.dataset.editing = button.dataset.id;
    form.title.value = button.dataset.title;
    form.color_bg.value = button.dataset.bg;
    form.color_text.value = button.dataset.text;
    CKEDITOR.instances['contenting']?.setData(button.dataset.content);

    modal.querySelector('h3').textContent = 'Редагувати контакт';
    form.querySelector('button[type="submit"]').textContent = 'Зберегти';
    modal.style.display = 'flex';
  });
}
