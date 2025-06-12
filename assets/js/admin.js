let currentTable = null;
let originalData = [];
let allOrders = [];

let sortConfig = { field: null, direction: null };
const sortableFields = {
    id: 'number',
    tarif: 'number',
    cost: 'number',
    created_at: 'date',
    published_at: 'date',
    discount: 'number',
    price: 'number'
};



categoryMap = window._categoryMap;
genreMap = window._genreMap;
glassMap = window._glassMap;
gtMap = window._gtMap;

async function getTableData(tableName) {

    if (['Items', 'News', 'Logs'].includes(tableName))
        document.getElementById('addButton').style.display = 'none';
    else
        document.getElementById('addButton').style.display = 'inline-block';


    currentTable = tableName;

    let response = await fetch(`/crystal/admin/getTable?tableName=${tableName}`);
    let data = await response.json();
    if (!Array.isArray(data))
        return;

    originalData = data;

    /////////////////////////////////////////////////////////////////////////////////////////
    let categoryResponse = await fetch(`/crystal/admin/searchOptions?table=categories`);
    let catData = await categoryResponse.json();

    let genreResponse = await fetch(`/crystal/admin/searchOptions?table=genres`);
    let genData = await genreResponse.json();

    let gtResponse = await fetch(`/crystal/admin/searchOptions?table=glass_types`);
    let gtData = await gtResponse.json();

    let glassResponse = await fetch(`/crystal/admin/searchOptions?table=glass`);
    let glassData = await glassResponse.json();
    /////////////////////////////////////////////////////////////////////////////////////////

    let categoryMap = Object.fromEntries(catData.map(c => [c.id, c.name]));
    let genreMap = Object.fromEntries(genData.map(g => [g.id, g.name]));
    let glassMap = Object.fromEntries(glassData.map(g => [g.id, g.name]));
    let gtMap = Object.fromEntries(gtData.map(t => [t.id, t.name]));

    window._categoryMap = categoryMap;
    window._genreMap = genreMap;
    window._glassMap = glassMap;
    window._gtMap = gtMap;


    document.querySelectorAll('#sidebar button').forEach(btn => btn.classList.remove('active'));
    document.querySelector(`#sidebar button[onclick="getTableData('${tableName}')"]`)?.classList.add('active');

    let container = document.getElementById('info');
    container.innerHTML = '';

    // Панель пошуку
    let searchPanel = document.createElement('div');
    searchPanel.id = 'searchPanel';

    let searchFieldSelect = document.createElement('select');
    Object.keys(data[0]).forEach(key => {
        let option = document.createElement('option');
        option.value = key;
        option.textContent = key;
        searchFieldSelect.appendChild(option);
    });

    let searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Пошук...';

    let searchButton = document.createElement('button');
    searchButton.innerText = 'Знайти';
    searchButton.className = "btn btn-primary";
    searchButton.onclick = () => {
        let filtered = filterTable(searchFieldSelect.value, searchInput.value, { categoryMap, genreMap, glassMap, gtMap });
        renderTable(filtered, currentTable, { categoryMap, genreMap, glassMap, gtMap });
    };


    let resetButton = document.createElement('button');
    resetButton.innerText = 'Скинути пошук';
    resetButton.className = 'btn btn-secondary ms-2';
    resetButton.onclick = () => {
        searchInput.value = '';
        searchFieldSelect.value = 'id';
        renderTable(originalData, currentTable, { categoryMap, genreMap, glassMap, gtMap });
    };

    searchPanel.appendChild(searchFieldSelect);
    searchPanel.appendChild(searchInput);
    searchPanel.appendChild(searchButton);
    searchPanel.appendChild(resetButton);
    container.appendChild(searchPanel);

    /////////////////////////////////////////////// Панель видалення логів
    if (tableName === 'Logs') {

        // Кнопка Статистика
        let statsButton = document.createElement('button');
        statsButton.innerText = 'Статистика';
        statsButton.className = 'btn btn-info';
        statsButton.onclick = openLogsStatsModal;

        searchPanel.appendChild(statsButton);


        let logDeletePanel = document.createElement('div');
        logDeletePanel.id = 'logDeletePanel';
        logDeletePanel.style.margin = '15px 0';

        let dateInput = document.createElement('input');
        dateInput.type = 'date';
        dateInput.className = 'form-control d-inline-block me-2';
        dateInput.style.width = '200px';

        let deleteLogsButton = document.createElement('button');
        deleteLogsButton.innerText = 'Видалити логи старше';
        deleteLogsButton.className = 'btn btn-danger';
        deleteLogsButton.onclick = async () => {
            if (!dateInput.value) {
                alert('Оберіть дату!');
                return;
            }

            if (!confirm(`Справді видалити логи до ${dateInput.value}?`))
                return;

            let res = await fetch('/crystal/admin/deleteOldLogs', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ date: dateInput.value })
            });

            let result = await res.json();
            if (result.success) {
                alert('Старі логи успішно видалено!');
                getTableData('Logs');
            }
            else
                alert(result.message || 'Помилка при видаленні логів');
        };


        logDeletePanel.appendChild(deleteLogsButton);
        logDeletePanel.appendChild(dateInput);
        container.appendChild(logDeletePanel);



    }
    ////////////////////////

    // renderTable(data, tableName);
    renderTable(data, tableName, { categoryMap, genreMap, glassMap, gtMap });
    sessionStorage.setItem('selectedTable', tableName);
}

function renderTable(data, tableName, maps = {}) {

    let container = document.getElementById('info');
    let oldTable = container.querySelector('table');
    if (oldTable)
        oldTable.remove();

    console.log(data);

    let table = document.createElement('table');
    table.className = 'admin-table';

    let thead = document.createElement('thead');
    let headerRow = document.createElement('tr');

    /////////////////////////////////////////////////////////////

    Object.keys(data[0]).forEach(key => {
        let th = document.createElement('th');
        th.textContent = key;

        if (Object.keys(sortableFields).includes(key)) {
            th.classList.add('sortable');

            th.style.cursor = 'pointer';
            th.onclick = () => {
                if (sortConfig.field === key) {
                    // toggle
                    if (sortConfig.direction === 'asc')
                        sortConfig.direction = 'desc';
                    else if (sortConfig.direction === 'desc')
                        sortConfig = { field: null, direction: null };
                    else
                        sortConfig.direction = 'asc';
                }
                else
                    sortConfig = { field: key, direction: 'asc' };


                let sorted = [...data];
                if (sortConfig.field && sortConfig.direction) {
                    sorted.sort((a, b) => {
                        let valA = a[sortConfig.field];
                        let valB = b[sortConfig.field];

                        let type = sortableFields[sortConfig.field];

                        if (type === 'number') {
                            valA = parseFloat(valA);
                            valB = parseFloat(valB);
                        }
                        else if (type === 'date') {
                            valA = new Date(valA);
                            valB = new Date(valB);
                        }
                        else if (type === 'string') {
                            valA = valA?.toString().toLowerCase() ?? '';
                            valB = valB?.toString().toLowerCase() ?? '';
                        }

                        if (valA < valB)
                            return sortConfig.direction === 'asc' ? -1 : 1;
                        if (valA > valB)
                            return sortConfig.direction === 'asc' ? 1 : -1;

                        return 0;
                    });

                }

                renderTable(sorted, tableName, maps);
            };

            if (sortConfig.field === key) {
                let arrow = sortConfig.direction === 'asc' ? ' ↑' :
                    sortConfig.direction === 'desc' ? ' ↓' : '';
                th.textContent = key + arrow;
                th.style.backgroundColor = '#e0f0ff';
            }
        }

        headerRow.appendChild(th);
    });



    //////////////////////////////////////////////////////////////
    headerRow.appendChild(document.createElement('th'));
    thead.appendChild(headerRow);
    table.appendChild(thead);

    let tbody = document.createElement('tbody');
    data.forEach(row => {
        let tr = document.createElement('tr');
        Object.entries(row).forEach(([key, value]) => {
            let td = document.createElement('td');

            let input;
            if (key === 'role' && tableName === 'Users') {
                input = document.createElement('select');

                const isSelf = row.username === 'vader';

                ['user', 'admin'].forEach(optionVal => {
                    if (isSelf && optionVal === 'user') return;

                    let option = document.createElement('option');
                    option.value = optionVal;
                    option.textContent = optionVal;
                    if (value === optionVal)
                        option.selected = true;
                    input.appendChild(option);
                });

                if (isSelf)
                    input.disabled = true;
            }

            else if (
                (["Items", "Glass"].includes(tableName)) &&
                (["category_id", "genre_id", "glass_id", "glass_type"].includes(key))) {
                input = document.createElement('select');

                let map;
                if (key === 'category_id') map = maps.categoryMap;
                if (key === 'genre_id') map = maps.genreMap;
                if (key === 'glass_id') map = maps.glassMap;
                if (key === 'glass_type') map = maps.gtMap;

                if (map) {
                    let nullOption = document.createElement('option');
                    nullOption.value = '';
                    nullOption.textContent = '-- не вказано --';
                    if (value === '' || value === null || value === undefined) nullOption.selected = true;
                    input.appendChild(nullOption);

                    for (let [id, name] of Object.entries(map)) {
                        let option = document.createElement('option');
                        option.value = id;
                        option.textContent = name;
                        if (String(value) === String(id)) option.selected = true;
                        input.appendChild(option);
                    }
                }
            }

            else {
                input = document.createElement('input');
                input.value = value;
            }

            input.className = `${tableName}_${row.id}`;
            input.dataset.field = key;

            if (tableName === 'Logs') {
                input.disabled = true;
            }
            else {
                input.addEventListener('input', () => {
                    tr.classList.add('unsaved');
                });
            }


            td.appendChild(input);
            tr.appendChild(td);
        });

        let tdActions = document.createElement('td');

        let btnSave = document.createElement('button');
        btnSave.innerText = 'Зберегти';
        btnSave.className = 'btn btn-success';
        btnSave.disabled = (tableName === 'Logs');
        if (tableName !== 'Logs')
            btnSave.onclick = () => saveRow(tableName, row.id);

        tdActions.appendChild(btnSave);

        if (!(tableName === 'Users' && row.username === 'vader')) {
            let btnDelete = document.createElement('button');
            btnDelete.innerText = 'Видалити';
            btnDelete.className = 'btn btn-danger';
            btnDelete.onclick = () => deleteRow(tableName, row.id);
            tdActions.appendChild(btnDelete);
        }

        tr.appendChild(tdActions);
        tbody.appendChild(tr);
    });

    table.appendChild(tbody);
    container.appendChild(table);
}

/////////////////////////////////////////////////////////////////////// Статистика логів
function openLogsStatsModal() {
    document.getElementById('logsStatsModal').style.display = 'block';
}

function closeLogsStatsModal() {
    document.getElementById('logsStatsModal').style.display = 'none';
}

async function loadLogsStats() {
    let days = document.getElementById('logsStatsDays').value;
    let res = await fetch(`/crystal/admin/logStats?days=${days}`);
    let stats = await res.json();

    if (!stats || !stats.data || !stats.methods) {
        alert('Не вдалося завантажити статистику');
        return;
    }

    renderLogsChart(stats.data);
    renderMethodChart(stats.methods);
}

let methodChartInstance = null;

function renderMethodChart(methodData) {
    let ctxId = 'logsMethodChartCanvas';
    let container = document.getElementById('logsMethodChart');
    container.innerHTML = `<canvas id="${ctxId}"></canvas>`;

    let ctx = document.getElementById(ctxId).getContext('2d');

    if (methodChartInstance)
        methodChartInstance.destroy();

    methodChartInstance = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: Object.keys(methodData),
            datasets: [{
                data: Object.values(methodData),
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'],
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            return `${label}: ${value} запитів`;
                        }
                    }
                }
            }
        }
    });
}

let logsChartInstance = null;

function renderLogsChart(data) {
    let ctxId = 'logsStatsChartCanvas';
    let container = document.getElementById('logsStatsChart');
    container.innerHTML = `<canvas id="${ctxId}"></canvas>`;

    let ctx = document.getElementById(ctxId).getContext('2d');

    let labels = Object.keys(data);
    let values = Object.values(data);

    if (logsChartInstance)
        logsChartInstance.destroy();

    logsChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Кількість запитів',
                data: values,
                backgroundColor: '#4e73df',
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

////////////////////////////////////////////////////////////////////// filter
function filterTable(field, query, { categoryMap, genreMap, glassMap, gtMap }) {
    query = query.trim();
    if (!query) return originalData;

    query = query.toLowerCase();

    const maps = {
        category_id: categoryMap,
        genre_id: genreMap,
        glass_id: glassMap,
        glass_type: gtMap,
        glass_type_id: gtMap
    };

    return originalData.filter(row => {
        let val = row[field];
        if (maps[field]) {
            val = maps[field][val] || '';
        }
        return String(val ?? '').toLowerCase().includes(query);
    });
}




async function deleteRow(tableName, id) {
    if (!confirm('Ви справді хочете видалити цей запис?'))
        return;

    let res = await fetch('/crystal/admin/deleteRow', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ table: tableName, id: id })
    });

    let result = await res.json();
    if (result.success) {
        alert('Успішно видалено!');
        getTableData(tableName);
    }
    else
        alert(result.message || 'Помилка при видаленні');
}

async function saveRow(tableName, id) {
    let inputs = document.querySelectorAll(`.${tableName}_${id}`);
    let fields = {};

    inputs.forEach(input => {
        let field = input.dataset.field;
        fields[field] = input.value;
    });

    let res = await fetch('/crystal/admin/updateRow', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ table: tableName, id: id, fields: fields })
    });

    let result = await res.json();

    if (result.success) {
        alert('Зміни збережено!');
        document.querySelector(`.${tableName}_${id}`)?.closest('tr')?.classList.remove('unsaved');
        getTableData(tableName);
    }
    else
        alert(result.message || 'Помилка при збереженні');
}

document.getElementById('addButton').addEventListener('click', () => {
    if (!currentTable) {
        alert('Спочатку оберіть таблицю!');
        return;
    }

    if (currentTable === 'Users') {
        addEmptyUserRow();
    } else if (currentTable === 'Glass') {
        openGlassAddModal();
    }
    else if (['Tags', 'Categories', 'Genres', 'GlassTypes'].includes(currentTable)) {
        addSimpleNameEntry(currentTable);
    }
    else {
        window.location.href = `/crystal/${currentTable.toLowerCase()}/add`;
    }
});

/////////////////////////////////////////////////////  Glass add
async function openGlassAddModal() {
    document.getElementById('glass_name').value = '';
    document.getElementById('glass_length').value = '';
    document.getElementById('glass_width').value = '';
    document.getElementById('glass_thickness').value = '';
    document.getElementById('glass_cost').value = '';

    let res = await fetch('/crystal/admin/getGlassTypes');
    let types = await res.json();

    let select = document.getElementById('glass_type_select');
    select.innerHTML = '';
    types.forEach(type => {
        let opt = document.createElement('option');
        opt.value = type.id;
        opt.textContent = type.name;
        select.appendChild(opt);
    });

    document.getElementById('glassAddModal').style.display = 'block';
}

function closeGlassModal() {
    document.getElementById('glassAddModal').style.display = 'none';
}

async function submitGlass() {
    let name = document.getElementById('glass_name').value.trim();
    let glass_type = document.getElementById('glass_type_select').value;
    let length_cm = parseFloat(document.getElementById('glass_length').value);
    let width_cm = parseFloat(document.getElementById('glass_width').value);
    let thickness_mm = parseInt(document.getElementById('glass_thickness').value);
    let cost = parseFloat(document.getElementById('glass_cost').value);

    if (!name || !glass_type || !length_cm || !width_cm || !thickness_mm || !cost) {
        alert("Будь ласка, заповніть усі поля коректно");
        return;
    }

    let data = { name, glass_type, length_cm, width_cm, thickness_mm, cost };

    let res = await fetch('/crystal/admin/addGlass', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    });

    let result = await res.json();

    if (result.success) {
        alert('Скло додано!');
        closeGlassModal();
        getTableData('Glass');
    } else {
        alert(result.message || 'Помилка при додаванні скла');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    let glassModal = document.getElementById('glassAddModal');
    let glassModalContent = document.querySelector('#glassAddModal .glass-modal-content');

    glassModal.addEventListener('click', function (event) {
        if (!glassModalContent.contains(event.target)) {
            closeGlassModal();
        }
    });
    let savedTable = sessionStorage.getItem('selectedTable');
    if (savedTable)
        getTableData(savedTable);

});


/////////////////////////////////////////////////////////////////// Вікно prompt для додавання  name
async function addSimpleNameEntry(table) {
    let name = prompt(`Введіть назву для ${table}:`);
    if (!name || !name.trim())
        return;

    let res = await fetch('/crystal/admin/addSimple', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ table, name })
    });

    let result = await res.json();

    if (result.success) {
        alert('Додано!');
        getTableData(table);
    }
    else
        alert(result.message || 'Помилка при додаванні');
}
/////////////////////////////////////////////////////////////////////

function addEmptyUserRow() {
    let table = document.querySelector('.admin-table');
    let thead = table.querySelector('thead');
    let tbody = table.querySelector('tbody');
    let headerFields = Array.from(thead.querySelectorAll('th'))
        .map(th => th.textContent)
        .filter(text => text !== '');

    let newRow = document.createElement('tr');
    let generatedId = Date.now();

    headerFields.forEach(field => {
        let td = document.createElement('td');

        let input;
        if (field === 'role') {
            input = document.createElement('select');
            ['user', 'admin'].forEach(val => {
                let option = document.createElement('option');
                option.value = val;
                option.textContent = val;
                input.appendChild(option);
            });
        }
        else {
            input = document.createElement('input');
            input.value = '';
        }



        input.className = `Users_${generatedId}`;
        input.dataset.field = field;

        input.addEventListener('input', () => {
            newRow.classList.add('unsaved');
        });

        td.appendChild(input);
        newRow.appendChild(td);
    });

    let tdActions = document.createElement('td');

    let btnSave = document.createElement('button');
    btnSave.innerText = 'Зберегти';
    btnSave.className = 'btn btn-success';
    btnSave.onclick = () => saveNewUserRow(generatedId, headerFields);
    tdActions.appendChild(btnSave);

    let btnCancel = document.createElement('button');
    btnCancel.innerText = 'Скасувати';
    btnCancel.className = 'btn btn-danger';
    btnCancel.onclick = () => newRow.remove();
    tdActions.appendChild(btnCancel);

    newRow.appendChild(tdActions);
    tbody.insertBefore(newRow, tbody.firstChild);
}

async function saveNewUserRow(tempId, fields) {
    let inputs = document.querySelectorAll(`.Users_${tempId}`);
    let newUser = {};

    inputs.forEach(input => {
        let field = input.dataset.field;
        newUser[field] = input.value;
    });

    let res = await fetch('/crystal/admin/adduser', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newUser)
    });

    let result = await res.json();

    if (result.success) {
        alert('Користувача додано!');
        getTableData('Users');
    }
    else
        alert(result.message || 'Помилка при додаванні');
}

async function loadOrders() {
    document.getElementById('addButton').style.display = 'none';
    currentTable = null;
    document.querySelectorAll('#sidebar button').forEach(btn => btn.classList.remove('active'));
    document.querySelector('#sidebar button[onclick="loadOrders()"]')?.classList.add('active');

    let response = await fetch('/crystal/admin/getOrders');
    let data = await response.json();
    allOrders = data.orders || [];

    let container = document.getElementById('info');
    container.innerHTML = '';

    if (!allOrders.length) {
        container.innerHTML = '<p>Немає замовлень</p>';
        return;
    }

    let statusFilterWrapper = document.createElement('div');
    statusFilterWrapper.style.marginBottom = '15px';

    let statusSelect = document.createElement('select');
    statusSelect.id = 'statusFilter';
    statusSelect.className = 'form-select';
    ['Усі', 'Обробка', 'Прийнято', 'Готово', 'Відхилено', 'Прострочені'].forEach(status => {
        let opt = document.createElement('option');
        opt.value = status;
        opt.textContent = status;
        statusSelect.appendChild(opt);
    });

    statusSelect.addEventListener('change', () => {
        let selectedStatus = statusSelect.value;
        let filtered;
        if (selectedStatus === 'Усі') {
            filtered = allOrders;
        } else if (selectedStatus === 'Прострочені') {
            const now = new Date();
            filtered = allOrders.filter(order =>
                order.status === 'Прийнято' &&
                new Date(order.deadline) < now
            );
        } else {
            filtered = allOrders.filter(order => order.status === selectedStatus);
        }
        renderOrderCards(filtered);
    });

    statusFilterWrapper.appendChild(statusSelect);
    container.appendChild(statusFilterWrapper);

    renderOrderCards(allOrders);
}

function renderOrderCards(orderList) {
    let container = document.getElementById('info');

    let oldWrapper = document.querySelector('.order-cards-wrapper');
    if (oldWrapper)
        oldWrapper.remove();

    let wrapper = document.createElement('div');
    wrapper.className = 'order-cards-wrapper';

    orderList.forEach(order => {
        let card = document.createElement('div');
        card.className = 'order-card';

        card.innerHTML = `
            <img src="${order.image}" class="order-img-small" alt="Зображення">
            <div><strong>Статус:</strong> ${order.status}</div>
            <div><strong>Дата:</strong> ${order.created_at}</div>
            <button class="btn btn-primary" onclick='showOrderModal(${JSON.stringify(order)})'>Детальніше</button>
        `;

        if (order.status === 'Обробка') {
            let acceptBtn = document.createElement('button');
            acceptBtn.className = 'btn btn-success';
            acceptBtn.innerText = 'Прийняти';
            acceptBtn.onclick = () => updateOrderStatus(order.id, 'Прийнято');

            let rejectBtn = document.createElement('button');
            rejectBtn.className = 'btn btn-danger';
            rejectBtn.innerText = 'Відхилити';
            rejectBtn.onclick = () => updateOrderStatus(order.id, 'Відхилено');

            card.appendChild(acceptBtn);
            card.appendChild(rejectBtn);
        }

        if (order.status === 'Прийнято') {
            let doneBtn = document.createElement('button');
            doneBtn.className = 'btn btn-success';
            doneBtn.innerText = 'Готово';
            doneBtn.onclick = () => updateOrderStatus(order.id, 'Готово');

            let rejectBtn = document.createElement('button');
            rejectBtn.className = 'btn btn-danger';
            rejectBtn.innerText = 'Відхилити';
            rejectBtn.onclick = () => updateOrderStatus(order.id, 'Відхилено');

            card.appendChild(doneBtn);
            card.appendChild(rejectBtn);
        }
        if (order.discount && order.discount > 0) {
            let badge = document.createElement('div');
            badge.className = 'discount-badge';
            badge.innerText = `Знижка: ${order.discount}%`;
            card.appendChild(badge);
        }

        wrapper.appendChild(card);
    });

    container.appendChild(wrapper);
}


async function updateOrderStatus(id, status) {
    let res = await fetch('/crystal/orders/updateStatus', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, status })
    });

    let result = await res.json();
    if (result.success)
        loadOrders();
    else
        alert(result.message || 'Помилка при оновленні статусу');
}

function showOrderModal(order) {
    let modalBody = document.getElementById('orderModalBody');

    let html = `
        <img src="${order.image}" style="max-width: 100%; height: auto;"><br><br>
        <strong>Імʼя:</strong> ${order.user_fullname}<br>
        <strong>Телефон:</strong> ${order.user_phone}<br>
        ${order.category_name ? `<strong>Категорія:</strong> ${order.category_name}<br>` : ''}
        ${order.genre_name ? `<strong>Жанр:</strong> ${order.genre_name}<br>` : ''}
    `;

    if (order.item_name || order.item_code) {
        html += `<strong>Товар:</strong> `;
        if (order.item_name) html += `${order.item_name}`;
        if (order.item_code) html += ` (${order.item_code})`;
        html += `<br>`;
    }

    html += `
        <strong>Опис:</strong><br>
        <p>${order.description}</p>
        <strong>Статус:</strong> ${order.status}<br>
        <strong>Створено:</strong> ${order.created_at}<br>
        <strong>На коли:</strong> ${order.deadline}<br>
    `;

    if (order.discount && order.discount > 0) {
        html += `<div class="discount-badge">Знижка: ${order.discount}%</div><br>`;
    }

    if (order.accepted_at) {
        html += `<strong>Прийнято:</strong> ${order.accepted_at}<br>`;
    }

    if (order.completed_at) {
        html += `<strong>Готово:</strong> ${order.completed_at}<br>`;
    }

    modalBody.innerHTML = html;
    document.getElementById('orderModal').style.display = 'block';
}

document.getElementById('orderModal').addEventListener('click', function (event) {
    if (event.target === this) {
        closeOrderModal();
    }
});


function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
}
