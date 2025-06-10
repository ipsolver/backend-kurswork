document.getElementById('likeBtn')?.addEventListener('click', async () => {
  const res = await fetch('/crystal/items/toggleLike', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({item_id: $item.id})
  });

  if (!res.ok) 
    return;
  const data = await res.json();

   if (!data.success) 
   {
      showToast(data.message || 'Помилка');
      return;
  }

  const btn = document.getElementById('likeBtn');
  const countEl = document.getElementById('likeCount');
  countEl.textContent = data.count;
  
  btn.classList.toggle('btn-danger', data.liked);
  btn.classList.toggle('btn-outline-danger', !data.liked);
});

function showToast(message) 
{
  let toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.add('show');

  setTimeout(() => {
    toast.classList.remove('show');
  }, 3000);
}


document.getElementById('previewImage')?.addEventListener('click', () => {
  const modal = document.getElementById('imageModal');
  const modalImg = document.getElementById('modalImage');
  modalImg.src = document.getElementById('previewImage').src;
  modal.style.display = 'flex';
});

document.getElementById('imageModal')?.addEventListener('click', (e) => {
  if (e.target.id === 'imageModal')
    e.currentTarget.style.display = 'none';
});