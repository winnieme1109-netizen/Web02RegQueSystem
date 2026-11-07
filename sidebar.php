<aside class="sidebar">
    <div class="sidebar-brand"><span>🏛️ REGISTRAR</span></div>
    <ul class="sidebar-menu">
        <li><a href="admin.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
        <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">📋 Queue Management</a></li>
        <li><a href="display.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'display.php' ? 'active' : ''; ?>">📺 Call Queue</a></li>
        <li><a href="history.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'history.php' ? 'active' : ''; ?>">📁 Transactions</a></li>
    </ul>
    
    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); margin-top: auto;">
        <a href="#" onclick="confirmLogout(event)" style="display: flex; align-items: center; gap: 10px; color: #fca5a5; text-decoration: none; font-size: 14px; font-weight: 500;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            Logout
        </a>
    </div>
</aside>