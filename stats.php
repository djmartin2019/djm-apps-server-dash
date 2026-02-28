<?php
header('Content-Type: application/json');

$serverTime = date("Y-m-d H:i:s");
$uptime     = shell_exec("uptime -p");
$load       = sys_getloadavg();

$memRaw     = shell_exec("free -m");
$memLines   = explode("\n", trim($memRaw));
$memParts   = preg_split('/\s+/', $memLines[1]);

$totalMem   = (int)$memParts[1];
$usedMem    = (int)$memParts[2];
$memPercent = round(($usedMem / $totalMem) * 100, 1);

$diskRaw    = shell_exec("df -h /");
$diskLines  = explode("\n", trim($diskRaw));
$diskParts  = preg_split('/\s+/', $diskLines[1]);

$diskTotal  = $diskParts[1];
$diskUsed   = $diskParts[2];
$diskPercent = str_replace('%', '', $diskParts[4]);

echo json_encode ([
    "cpu"   => [
        "one"       => round($load[0], 2),
        "five"      => round($load[1], 2),
        "fifteen"   => round($load[2], 2)
    ],
    "memory" => [
        "used"      => $usedMem,
        "total"     => $totalMem,
        "percent"   => $memPercent
    ],
    "disk"  => [
        "used"      => $diskUsed,
        "total"     => $diskTotal,
        "percent"   => $diskPercent
    ],
    "time"  => $serverTime
]);
