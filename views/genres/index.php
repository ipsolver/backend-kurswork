<?php $this->Title = 'Список жанрів'; ?>

<h2>Список жанрів</h2>

<a href="/crystal/genres/add" class="btn btn-success mb-3">Додати жанр</a>

<?php if (empty($genres)): ?>
    <p>Жанри відсутні</p>
<?php else: ?>
<table class="table table-striped" id="categoriesTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Назва жанру</th>
            <th>Дії</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($genres as $genre): ?>
        <tr data-id="<?= $genre['id'] ?>">
            <td><?= htmlspecialchars($genre['id']) ?></td>
            <td><?= htmlspecialchars($genre['name']) ?></td>
            <td>
                <a href="/crystal/genres/edit/<?= $genre['id'] ?>" class="btn btn-sm btn-primary">Редагувати</a>
                <button class="btn btn-sm btn-danger" onclick="deleteGenre(<?= $genre['id'] ?>, <?= $genre['item_count'] ?>)">Видалити</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
function deleteGenre(id, count) 
{
    if (!confirm(`Ви впевнені, що хочете видалити цей жанр? Це видалить ${count} картин(и)`)) 
        return;

    fetch('/crystal/genres/delete', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) 
            document.querySelector(`tr[data-id='${id}']`).remove();
        else
            alert('Не вдалося видалити жанр');
    });
}
</script>
<?php endif; ?>
