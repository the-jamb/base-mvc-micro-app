<?php if (Session::isLoggedIn()): ?>
    <nav class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <span class="logo-icon">🚀</span>
                <span class="logo-text">SocialAI Pro</span>
            </div>
        </div>
        <div class="sidebar-menu">
            <a href="/index.php?page=generator"
                class="menu-item <?php echo ($currentPage ?? '') === 'generator' ? 'active' : ''; ?>">
                <span class="menu-icon">✨</span>
                <span class="menu-text">Generator</span>
            </a>
            <a href="/index.php?page=history"
                class="menu-item <?php echo ($currentPage ?? '') === 'history' ? 'active' : ''; ?>">
                <span class="menu-icon">📝</span>
                <span class="menu-text">Historia</span>
            </a>
            <a href="/index.php?page=favorites"
                class="menu-item <?php echo ($currentPage ?? '') === 'favorites' ? 'active' : ''; ?>">
                <span class="menu-icon">⭐</span>
                <span class="menu-text">Ulubione</span>
            </a>
        </div>
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr(Session::get('username', 'U'), 0, 1)); ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo htmlspecialchars(Session::get('username', 'User')); ?></div>
                    <div class="user-role">Premium</div>
                </div>
            </div>
            <div class="sidebar-actions">
                <button onclick="toggleTheme()" class="btn-icon" title="Zmień motyw">
                    <span class="theme-icon">🌙</span>
                </button>
                <a href="/index.php?action=logout" class="btn-icon" title="Wyloguj">
                    <span>🚪</span>
                </a>
            </div>
        </div>
    </nav>
<?php endif; ?>