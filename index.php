<?php
require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/projects.php';

$seo = [
    'title' => 'DJM Apps Laboratory | Server Dashboard',
    'description' => 'Entry dashboard for DJM Apps Laboratory with live server health, deployed projects, and infrastructure visibility.',
    'canonical' => 'https://djm-apps.com/',
    'path' => '/',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?= render_seo_tags($seo); ?>
    <link rel="stylesheet" href="/styles.css?v=<?= filemtime(__DIR__ . '/styles.css'); ?>">
</head>
<body>
<?php $projects = get_deployed_projects(); ?>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="brand-dot"></span>
            <span class="brand-title">DJM Apps</span>
        </div>
        <nav class="sidebar-nav" aria-label="Primary navigation">
            <a href="/index.php" class="nav-item active" aria-current="page">Dashboard</a>
            <a href="/projects.php" class="nav-item">Projects</a>
            <a href="/deployments.php" class="nav-item">Deployments</a>
            <a href="/infrastructure.php" class="nav-item">Infrastructure</a>
        </nav>
    </aside>

    <div class="main-shell">
        <header class="topbar">
            <div class="topbar-left">
                <button
                    class="sidebar-toggle"
                    id="sidebar-toggle"
                    type="button"
                    aria-label="Toggle sidebar"
                    aria-controls="sidebar"
                    aria-expanded="false"
                >
                    <span class="hamburger-icon" aria-hidden="true">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
                <h1 class="page-title">Server Dashboard</h1>
            </div>
            <div class="topbar-right">
                <div class="server-status">
                    <span class="status-dot" id="server-status-dot" aria-hidden="true"></span>
                    <span id="server-status-text">Connecting...</span>
                </div>
                <time class="server-time" id="current-time" datetime="">--:--:--</time>
            </div>
        </header>

        <main class="main-content">
            <section class="stats-grid" aria-label="Server health">
                <article class="card stat-card">
                    <p class="stat-label">CPU Load (1 / 5 / 15 min)</p>
                    <p class="stat-value" id="cpu">-- / -- / --</p>
                </article>

                <article class="card stat-card">
                    <p class="stat-label">Memory Usage</p>
                    <p class="stat-value" id="memory">-- MB / -- MB (--%)</p>
                    <div class="bar" aria-label="Memory usage progress">
                        <div class="bar-fill" id="memory-bar"></div>
                    </div>
                </article>

                <article class="card stat-card">
                    <p class="stat-label">Disk Usage</p>
                    <p class="stat-value" id="disk">-- / -- (--%)</p>
                    <div class="bar" aria-label="Disk usage progress">
                        <div class="bar-fill" id="disk-bar"></div>
                    </div>
                </article>
            </section>

            <section class="projects-section" aria-label="Deployed projects">
                <div class="section-header">
                    <h2>Deployed Projects</h2>
                </div>
                <div class="projects-grid">
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $project): ?>
                            <a class="project-card-link" href="<?= htmlspecialchars($project['url']) ?>" target="_blank" rel="noopener noreferrer">
                                <article class="card project-card">
                                    <img
                                        class="project-image"
                                        src="<?= htmlspecialchars($project['image']) ?>"
                                        alt="<?= htmlspecialchars($project['name']) ?> preview image"
                                        loading="lazy"
                                        decoding="async"
                                        onerror="this.onerror=null;this.src='/assets/djm-apps.png';"
                                    >
                                    <h3><?= htmlspecialchars($project['name']) ?></h3>
                                    <p><?= htmlspecialchars($project['description']) ?></p>
                                </article>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <article class="card project-card placeholder-card">
                            <h3>Projects Coming Soon</h3>
                            <span class="btn-link disabled" aria-disabled="true">No Projects Yet</span>
                        </article>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
const bodyEl = document.body;
const sidebarToggleBtn = document.getElementById('sidebar-toggle');

function setSidebarOpenState(isOpen) {
    bodyEl.classList.toggle('sidebar-open', isOpen);
    sidebarToggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
}

sidebarToggleBtn.addEventListener('click', () => {
    bodyEl.classList.toggle('sidebar-open');
    setSidebarOpenState(bodyEl.classList.contains('sidebar-open'));
});

document.addEventListener('click', (event) => {
    if (!bodyEl.classList.contains('sidebar-open')) {
        return;
    }

    const isToggle = event.target.closest('#sidebar-toggle');
    const isInsideSidebar = event.target.closest('#sidebar');
    if (!isToggle && !isInsideSidebar) {
        setSidebarOpenState(false);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setSidebarOpenState(false);
    }
});

function updateStatusIndicator(isOnline) {
    const dot = document.getElementById('server-status-dot');
    const text = document.getElementById('server-status-text');
    dot.classList.toggle('offline', !isOnline);
    text.innerText = isOnline ? 'Online' : 'Offline';
}

function renderServerTime(value) {
    const serverTime = document.getElementById('current-time');
    const [datePart, timePart] = String(value).split(' ');
    serverTime.setAttribute('datetime', value);

    if (window.innerWidth <= 720 && datePart && timePart) {
        serverTime.innerHTML = `<span class="server-time-date">${datePart}</span><span class="server-time-clock">${timePart}</span>`;
    } else {
        serverTime.textContent = value;
    }
}

async function fetchStats() {
    try {
        const response = await fetch('/stats.php');
        if (!response.ok) {
            throw new Error(`Unexpected status: ${response.status}`);
        }
        const data = await response.json();

        const cpuEl = document.getElementById('cpu');
        const memoryEl = document.getElementById('memory');
        const memoryBarEl = document.getElementById('memory-bar');
        const diskEl = document.getElementById('disk');
        const diskBarEl = document.getElementById('disk-bar');
        if (cpuEl) {
            cpuEl.innerText = `${data.cpu.one} / ${data.cpu.five} / ${data.cpu.fifteen}`;
        }

        if (memoryEl) {
            memoryEl.innerText = `${data.memory.used} MB / ${data.memory.total} MB (${data.memory.percent}%)`;
        }
        if (memoryBarEl) {
            memoryBarEl.style.width = `${data.memory.percent}%`;
        }

        if (diskEl) {
            diskEl.innerText = `${data.disk.used} / ${data.disk.total} (${data.disk.percent}%)`;
        }
        if (diskBarEl) {
            diskBarEl.style.width = `${data.disk.percent}%`;
        }

        renderServerTime(data.time);

        updateStatusIndicator(true);
    } catch (error) {
        updateStatusIndicator(false);
        console.error('Failed to fetch status:', error);
    }
}

window.addEventListener('resize', () => {
    const currentTime = document.getElementById('current-time');
    const value = currentTime.getAttribute('datetime');
    if (value) {
        renderServerTime(value);
    }
});

fetchStats();
setInterval(fetchStats, 5000);
</script>
</body>
</html>
