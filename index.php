<?php
$serverTime = date("Y-m-d H:i:s");
$uptime = shell_exec("uptime -p");
$load = sys_getloadavg();

$memRaw = shell_exec("free -m");
$memLines = explode("\n", trim($memRaw));
$memParts = preg_split('/\s+/', $memLines[1]);

$totalMem = (int)$memParts[1];
$usedMem  = (int)$memParts[2];
$freeMem  = (int)$memParts[3];

$memPercent = round(($usedMem / $totalMem) * 100, 1);

$diskRaw = shell_exec("df -h /");
$diskLines = explode("\n", trim($diskRaw));
$diskParts = preg_split('/\s+/', $diskLines[1]);

$diskTotal = $diskParts[1];
$diskUsed  = $diskParts[2];
$diskFree  = $diskParts[3];
$diskPercent = str_replace('%', '', $diskParts[4]);

?>

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
        <div class="stat-value">
            <?php echo round($load[0],2) . " / " . round($load[1],2) . " / " . round($load[2],2); ?> 
        </div>

        <div class="stat-label">Memory Usage</div>
        <div class="stat-value">
            <?php echo "$usedMem MB / $totalMem MB ($memPercent%)"; ?>
        </div>
        <div class="bar">
            <div class="bar-fill" style="width: <?php echo $memPercent; ?>%"></div>
        </div>
        

        <div class="stat-label">Disk Usage</div>
        <div class="stat-value">
            <?php echo "$diskUsed / $diskTotal ($diskPercent%)"; ?>
        </div>
        <div class="bar">
            <div class="bar-fill" style="width: <?php echo $diskPercent; ?>%"></div>
        </div>
    </div>

    <div class="footer">
        Experimental Infrastructure Lab • LAMP Stack
    </div>
</div>
</body>
</html>
