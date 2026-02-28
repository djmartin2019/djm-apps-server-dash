<!DOCTYPE html>
<html>
<head>
    <title>DJM Apps Lab</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
<div class="container">
    <h1>DJM Apps Server</h1>
    <div class="panel">
        <div class="stat-label">CPU Load (1 / 5 / 15 min)</div>
        <div class="stat-value" id="cpu"></div>

        <div class="stat-label">Memory Usage</div>
        <div class="stat-value" id="memory"></div>
        <div class="bar">
            <div class="bar-fill" id="memory-bar"></div>
        </div>
        
        <div class="stat-label">Disk Usage</div>
        <div class="stat-value" id="disk"></div>
        <div class="bar">
            <div class="bar-fill" id="disk-bar"></div>
        </div>
    </div>

    <div class="footer">
        Experimental Infrastructure Lab • LAMP Stack
    </div>
</div>
<script>
async function fetchStats() {
    try {
        const response = await fetch('/stats.php');
        const data = await response.json();

        //CPU
        document.getElementById('cpu').innerText =
            `${data.cpu.one} / ${data.cpu.five} / ${data.cpu.fifteen}`;

        // Memory
        document.getElementById('memory').innerText = 
            `${data.memory.used} MB / ${data.memory.total} MB (${data.memory.percent}%)`;

        // Disk
        document.getElementById('disk').innerText = 
            `${data.disk.used} / ${data.disk.total} (${data.disk.percent}%)`;
        document.getElementById('disk-bar').style.width = 
            `${data.disk.percent}%`;

    } catch (error) {
        console.error("Failed to fetch status: ", error);
    }
}

fetchStats();

setInterval(fetchStats, 5000);
</script>
</body>
</html>
