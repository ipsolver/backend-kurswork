function DeleteHandler() {
    document.querySelectorAll('.delete-order-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!confirm('Ви дійсно хочете видалити це замовлення?')) return;

            let id = this.dataset.id;
            fetch('/crystal/orders/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`.order-card[data-order-id="${id}"]`)?.remove();
                    } else {
                        alert('Помилка при видаленні: ' + (data.message || 'Спробуйте пізніше'));
                    }
                });
        });
    });
}

function OrderModalHandler() {
    document.querySelectorAll('.order-card').forEach(card => {
        card.addEventListener('click', function (e) {
            if (e.target.classList.contains('delete-order-btn')) return;

            let orderId = this.dataset.orderId;

            fetch('/crystal/orders/view', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: orderId })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success)
                        showOrderModal(data.order);
                    else
                        alert('Помилка: ' + (data.message || 'Не вдалося завантажити замовлення'));
                });
        });
    });
}


function loadFilteredOrders(page = 1) {
    const status = document.getElementById('status-filter').value;

    fetch('/crystal/orders/filter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status, page })
    })
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('orders-container');
            container.innerHTML = '';

            if (!Array.isArray(data.orders) || data.orders.length === 0) {
                container.innerHTML = '<p>Немає замовлень із цим статусом</p>';
            }
            else {
                data.orders.forEach(order => {
                    let desc = order.description.length > 12
                        ? order.description.substring(0, 12) + '...'
                        : order.description;

                    let imageHtml = order.image
                        ? `<img src="${order.image}" alt="зображення замовлення" class="order-image">`
                        : '';

                    let deleteBtnHtml = (order.status === 'Обробка' || order.status === 'Відхилено')
                        ? `<button class="delete-order-btn" data-id="${order.id}">Видалити</button>`
                        : '';

                    let discountBadge = '';
                    if (order.discount && order.discount > 0)
                        discountBadge = `<div class="discount-badge">Знижка: ${order.discount}%</div>`;

                    container.innerHTML += `
                        <div class="order-card" data-order-id="${order.id}">
                            ${imageHtml}
                            ${discountBadge}
                            <div class="order-details">
                                <p class="order-desc">${desc}</p>
                                <p><strong>До:</strong> ${order.deadline}</p>
                                <p><strong>Статус:</strong> ${order.status}</p>
                                <p><strong>Створено:</strong> ${order.created_at}</p>
                                ${deleteBtnHtml}
                            </div>
                        </div>
                    `;
                });
            }

            DeleteHandler();
            OrderModalHandler();
            renderPagination(data.totalPages, data.currentPage);
        });
}

function renderPagination(totalPages, currentPage) {
    let wrapper = document.querySelector('.pagination-wrapper');
    wrapper.innerHTML = '';

    if (totalPages <= 1)
        return;

    let ul = document.createElement('ul');
    ul.className = 'pagination justify-content-center';

    for (let i = 1; i <= totalPages; i++) {
        let li = document.createElement('li');
        li.className = 'page-item' + (i === currentPage ? ' active' : '');
        let a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;
        a.addEventListener('click', e => {
            e.preventDefault();
            loadFilteredOrders(i);
        });
        li.appendChild(a);
        ul.appendChild(li);
    }

    wrapper.appendChild(ul);
}

document.getElementById('status-filter').addEventListener('change', () => loadFilteredOrders(1));

document.addEventListener('DOMContentLoaded', () => {
    loadFilteredOrders(1);
});

function showOrderModal(order) {
    let modalBody = document.getElementById('orderModalBody');
    modalBody.innerHTML = `
        ${order.image ? `<img src="${order.image}" style="max-width: 100%; height: auto;"><br><br>` : ''}
        <strong>Імʼя:</strong> ${order.user_fullname}<br>
        <strong>Телефон:</strong> ${order.user_phone}<br>
        ${order.category_name ? `<strong>Категорія:</strong> ${order.category_name}<br>` : ''}
        ${order.genre_name ? `<strong>Жанр:</strong> ${order.genre_name}<br>` : ''}
        <strong>Опис:</strong><br>
        <p>${order.description}</p>
        <strong>Статус:</strong> ${order.status}<br>
        <strong>Створено:</strong> ${order.created_at}<br>
        <strong>На коли:</strong> ${order.deadline}
        ${order.discount && order.discount > 0 ? `<div class="discount-badge">Знижка: ${order.discount}%</div><br>` : ''}
        ${order.accepted_at ? `<br><strong>Прийнято:</strong> ${order.accepted_at}<br>` : ''}
        ${order.completed_at ? `<strong>Готово:</strong> ${order.completed_at}<br>` : ''}
    `;
    document.getElementById('orderModal').style.display = 'block';
}

document.getElementById('orderModal').addEventListener('click', function (event) {
    if (event.target === this)
        closeOrderModal();
});

function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
}

