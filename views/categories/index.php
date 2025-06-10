<?php $this->Title = 'Список категорій'; ?>

<h2>Список категорій</h2>

<a href="/crystal/categories/add" class="btn btn-success mb-3">Додати категорію</a>

<?php if (empty($categories)): ?>
    <p>Категорії відсутні</p>
<?php else: ?>
<table class="table table-striped" id="categoriesTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Назва категорії</th>
            <th>Дії</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $category): ?>
        <tr data-id="<?= $category['id'] ?>">
            <td><?= htmlspecialchars($category['id']) ?></td>
            <td><?= htmlspecialchars($category['name']) ?></td>
            <td>
                <a href="/crystal/categories/edit/<?= $category['id'] ?>" class="btn btn-sm btn-primary">Редагувати</a>
                <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?= $category['id'] ?>, <?= $category['item_count'] ?>)">Видалити</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
function deleteCategory(id, count)
{
    if (!confirm(`Ви впевнені, що хочете видалити цю категорію? Це видалить ${count} картин(и)`)) 
        return;

    fetch('/crystal/categories/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success)
            document.querySelector(`tr[data-id='${id}']`).remove();
        else
            alert('Не вдалося видалити категорію');
    });
}
</script>
<?php endif; ?>
