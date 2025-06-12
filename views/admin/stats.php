<h2>Статистика логів</h2>

<div>
    <label>Показати за останніх днів: </label>
    <input type="number" id="logsStatsDays" value="7" min="1" max="90">
    <button class = "btn btn-primary" onclick="loadLogsStats()">Оновити</button>
    <button class = "btn btn-secondary" onclick="downloadPdfReport()">Завантажити звіт</button>
    <button class = "btn btn-info" onclick="location.href='/crystal/admin/index'">Повернутися</button>
</div>

<div id="logsSummary" style="margin: 10px 0;"></div>

<div style="display: flex; flex-wrap: wrap; gap: 30px;">
    <div style="flex: 1; min-width: 300px;">
        <h4>Запити по статус-кодах</h4>
        <div id="logsStatsChart"></div>
        <div id="logsStatusCounts" style="margin-top: 10px;"></div>
    </div>
    <div style="flex: 1; min-width: 300px;">
        <h4>Methods (GET, POST)</h4>
        <div id="logsMethodChart"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
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
    renderMethodSummary(stats.methods);
}

function renderMethodSummary(methods) {
    let div = document.getElementById('logsSummary');
    let total = Object.entries(methods).map(([k, v]) => `${k}: ${v}`).join(', ');
    div.innerHTML = `<strong>Methods:</strong> ${total}`;
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
    let statusListDiv = document.getElementById('logsStatusCounts');
    statusListDiv.innerHTML = '<strong>Status:</strong><br>' + labels.map(label =>
        `Count of ${label} status: ${data[label]}`).join('<br>');
}

async function downloadPdfReport() {
    let { jsPDF } = window.jspdf;
    let doc = new jsPDF();
    doc.setFont("helvetica", "normal");

    let days = document.getElementById('logsStatsDays').value || 7;
    let periodText = `Period: last ${days} days`;

    doc.setFontSize(16);
    doc.text("Log Report", 10, 10);

    doc.setFontSize(12);
    doc.text(periodText, 10, 20);

    let dateNow = new Date().toLocaleDateString('ua-UA');
    doc.text(`Generated: ${dateNow}`, 150, 10);

    let summary = document.getElementById('logsSummary').innerText.trim();
    doc.text(summary, 10, 30);

    let statusCounts = document.getElementById('logsStatusCounts').innerText.trim();
    let lines = statusCounts.split('\n');
    let y = 40;

    for (let line of lines) {
        doc.text(line, 10, y);
        y += 6;
    }

    let charts = [
        document.getElementById('logsStatsChartCanvas'),
        document.getElementById('logsMethodChartCanvas')
    ];

    for (let chart of charts) {
        await new Promise(r => setTimeout(r, 200));
        try {
            let imgData = chart.toDataURL('image/png');
            doc.addImage(imgData, 'PNG', 10, y, 180, 90);
            y += 100;
        } catch (err) {
            doc.text("Chart not available", 10, y);
            y += 10;
        }
    }

    doc.save('logs_report.pdf');
}


window.addEventListener('DOMContentLoaded', loadLogsStats);

</script>