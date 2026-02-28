<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infrastructure | DJM Apps Server Dashboard</title>
    <link rel="stylesheet" href="styles.css">
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
            <a href="/deployments.php" class="nav-item">Deployments</a>
            <a href="/infrastructure.php" class="nav-item active" aria-current="page">Infrastructure</a>
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
                <h1 class="page-title">Infrastructure</h1>
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
            <section class="projects-section" aria-label="Infrastructure coming soon">
                <div class="section-header">
                    <h2>Infrastructure Tech Stack</h2>
                </div>
                <div class="tech-stack-grid">
                    <article class="card tech-card">
                        <div class="tech-logo">
                            <img class="tech-logo-image" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" alt="PHP logo" loading="lazy" decoding="async">
                        </div>
                        <h3>PHP</h3>
                        <p>PHP powers my backend and API endpoints with lightweight, server-side rendering.</p>
                    </article>

                    <article class="card tech-card">
                        <div class="tech-logo">
                            <img class="tech-logo-image" src="https://cdn.simpleicons.org/ubuntu/E95420" alt="Ubuntu logo" loading="lazy" decoding="async">
                        </div>
                        <h3>Ubuntu</h3>
                        <p>Ubuntu runs my VPS host environment with stable Linux tooling for app and service operations.</p>
                    </article>

                    <article class="card tech-card">
                        <div class="tech-logo">
                            <img class="tech-logo-image" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/apache/apache-original.svg" alt="Apache logo" loading="lazy" decoding="async">
                        </div>
                        <h3>Apache</h3>
                        <p>Apache handles my HTTP requests, virtual hosts, and reverse proxy behavior for deployed apps.</p>
                    </article>

                    <article class="card tech-card">
                        <div class="tech-logo">
                            <img class="tech-logo-image" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/postgresql/postgresql-original.svg" alt="PostgreSQL logo" loading="lazy" decoding="async">
                        </div>
                        <h3>PostgreSQL</h3>
                        <p>PostgreSQL provides my relational data storage for projects, deployment records, and infrastructure metadata.</p>
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
