document.getElementById('image')?.addEventListener('change', function () 
{
  const file = this.files[0];
  const preview = document.getElementById('imagePreview');
  const container = document.getElementById('imagePreviewContainer');

  if (file && file.type.startsWith('image/')) 
    {
    const reader = new FileReader();
    reader.onload = function (e) 
    {
      preview.src = e.target.result;
      container.style.display = 'block';
    };
    reader.readAsDataURL(file);
  } 
  else 
  {
    container.style.display = 'none';
    preview.src = '';
  }
});
