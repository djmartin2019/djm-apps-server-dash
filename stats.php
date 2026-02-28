<?php
header('Content-Type: application/json');

function bytesToHuman(float $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $size = max($bytes, 0);
    $unitIndex = 0;

    while ($size >= 1024 && $unitIndex < count($units) - 1) {
        $size /= 1024;
        $unitIndex++;
    }

    return round($size, 1) . ' ' . $units[$unitIndex];
}

function getCpuLoad(): array
{
    $load = sys_getloadavg();
    if (!is_array($load) || count($load) < 3) {
        return ['one' => 0.0, 'five' => 0.0, 'fifteen' => 0.0];
    }

    return [
        'one' => round((float)$load[0], 2),
        'five' => round((float)$load[1], 2),
        'fifteen' => round((float)$load[2], 2),
    ];
}

function getMemoryStatsMb(): array
{
    $totalMb = 0;
    $usedMb = 0;

    if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/meminfo')) {
        $contents = (string)file_get_contents('/proc/meminfo');
        $lines = preg_split('/\r?\n/', trim($contents));
        $memInfo = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\w+):\s+(\d+)/', $line, $matches)) {
                $memInfo[$matches[1]] = (int)$matches[2]; // kB
            }
        }

        $totalKb = $memInfo['MemTotal'] ?? 0;
        $availableKb = $memInfo['MemAvailable'] ?? (
            ($memInfo['MemFree'] ?? 0) +
            ($memInfo['Buffers'] ?? 0) +
            ($memInfo['Cached'] ?? 0)
        );

        if ($totalKb > 0) {
            $usedKb = max($totalKb - $availableKb, 0);
            $totalMb = (int)round($totalKb / 1024);
            $usedMb = (int)round($usedKb / 1024);
        }
    } elseif (PHP_OS_FAMILY === 'Darwin') {
        $totalBytes = (int)trim((string)@shell_exec('sysctl -n hw.memsize 2>/dev/null'));
        $vmStatRaw = (string)@shell_exec('vm_stat 2>/dev/null');

        $pageSize = 4096;
        if (preg_match('/page size of (\d+) bytes/', $vmStatRaw, $pageMatch)) {
            $pageSize = (int)$pageMatch[1];
        }

        $freePages = 0;
        $inactivePages = 0;
        $speculativePages = 0;

        if (preg_match('/Pages free:\s+(\d+)\./', $vmStatRaw, $match)) {
            $freePages = (int)$match[1];
        }
        if (preg_match('/Pages inactive:\s+(\d+)\./', $vmStatRaw, $match)) {
            $inactivePages = (int)$match[1];
        }
        if (preg_match('/Pages speculative:\s+(\d+)\./', $vmStatRaw, $match)) {
            $speculativePages = (int)$match[1];
        }

        if ($totalBytes > 0) {
            $availableBytes = ($freePages + $inactivePages + $speculativePages) * $pageSize;
            $usedBytes = max($totalBytes - $availableBytes, 0);
            $totalMb = (int)round($totalBytes / (1024 * 1024));
            $usedMb = (int)round($usedBytes / (1024 * 1024));
        }
    }

    $percent = $totalMb > 0 ? round(($usedMb / $totalMb) * 100, 1) : 0.0;

    return [
        'used' => $usedMb,
        'total' => $totalMb,
        'percent' => $percent,
    ];
}

function getDiskStats(): array
{
    $diskPath = '/';
    $totalBytes = (float)@disk_total_space($diskPath);
    $freeBytes = (float)@disk_free_space($diskPath);

    if ($totalBytes <= 0 || $freeBytes < 0) {
        return [
            'used' => '0 B',
            'total' => '0 B',
            'percent' => 0.0,
        ];
    }

    $usedBytes = max($totalBytes - $freeBytes, 0);
    $percent = round(($usedBytes / $totalBytes) * 100, 1);

    return [
        'used' => bytesToHuman($usedBytes),
        'total' => bytesToHuman($totalBytes),
        'percent' => $percent,
    ];
}

$serverTime = date('Y-m-d H:i:s');

echo json_encode([
    'cpu' => getCpuLoad(),
    'memory' => getMemoryStatsMb(),
    'disk' => getDiskStats(),
    'time' => $serverTime,
]);
