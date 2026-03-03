<?php
require_once __DIR__ . '/includes/seo.php';

$seo = [
    'title' => 'Deployments | DJM Apps Laboratory',
    'description' => 'Deployments area of DJM Apps Laboratory for release history, deployment status, and rollback planning.',
    'canonical' => 'https://djm-apps.com/deployments.php',
    'path' => '/deployments.php',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?= render_seo_tags($seo); ?>
    <link rel="stylesheet" href="/styles.css?v=<?= filemtime(__DIR__ . '/styles.css'); ?>">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <span class="brand-dot"></span>
            <span class="brand-title">DJM Apps</span>
        </div>
        <nav class="sidebar-nav" aria-label="Primary navigation">
            <a href="/index.php" class="nav-item">Dashboard</a>
            <a href="/projects.php" class="nav-item">Projects</a>
            <a href="/deployments.php" class="nav-item active" aria-current="page">Deployments</a>
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
                <h1 class="page-title">Deployments</h1>
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
            <section class="projects-section" aria-label="Deployments coming soon">
                <div class="section-header">
                    <h2>Deployments Module</h2>
                </div>
                <div class="projects-grid">
                    <article class="card project-card placeholder-card">
                        <h3>Coming Soon</h3>
                    </article>
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
