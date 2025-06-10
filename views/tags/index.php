<?php $this->Title = 'Список тегів'; ?>

<h2>Список тегів</h2>

<a href="/crystal/tags/add" class="btn btn-success mb-3">Додати тег</a>

<?php if (empty($tags)): ?>
    <p>Теги відсутні</p>
<?php else: ?>
<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Назва тегу</th>
            <th>Дії</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($tags as $tag): ?>
        <tr>
            <td><?= htmlspecialchars($tag['id']) ?></td>
            <td><?= htmlspecialchars($tag['name']) ?></td>
            <td>
                <a href="/crystal/tags/edit/<?= $tag['id'] ?>" class="btn btn-sm btn-primary">Редагувати</a>
                <button class="btn btn-sm btn-danger btn-delete-tag" data-id="<?= $tag['id'] ?>">Видалити</button>
</td>

        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>




<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.btn-delete-tag').forEach(button => {
    button.addEventListener('click', function () {
      if (!confirm('Ви впевнені, що хочете видалити цей тег?')) return;

      const tagId = this.dataset.id;
      const row = this.closest('tr');

      fetch('/crystal/tags/delete', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: tagId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success)
          row.remove();
        else
          alert('Помилка при видаленні тегу');
      })
      .catch(() => alert('Серверна помилка при видаленні тегу'));
    });
  });
});
</script>
