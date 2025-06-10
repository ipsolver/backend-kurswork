document.getElementById('order-image-input').addEventListener('change', function (event) {
    let file = event.target.files[0];
    let previewContainer = document.getElementById('image-preview');
    let previewImg = document.getElementById('preview-img');

    if (file && file.type.startsWith('image/')) {
        let reader = new FileReader();

        reader.onload = function (e) {
            previewImg.src = e.target.result;
            previewContainer.style.display = 'block';
        };

        reader.readAsDataURL(file);
    } else {
        previewImg.src = '';
        previewContainer.style.display = 'none';
    }
});