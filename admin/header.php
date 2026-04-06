<?php
/**
 * 后台管理公共头部 - 重构版
 */
require_once __DIR__ . '/../functions.php';
requireLogin();

$menuItems = [
    ['section' => '内容管理'],
    ['id' => 'index', 'url' => 'index.php', 'icon' => 'user', 'title' => '个人资料'],
    ['id' => 'link', 'url' => 'link.php', 'icon' => 'link', 'title' => '社交链接'],
    ['id' => 'message', 'url' => 'message.php', 'icon' => 'message', 'title' => '留言管理'],
    ['id' => 'skill', 'url' => 'skill.php', 'icon' => 'code', 'title' => '技能管理'],
    ['id' => 'project', 'url' => 'project.php', 'icon' => 'folder', 'title' => '项目展示'],
    ['section' => '系统设置'],
    ['id' => 'toggle', 'url' => 'toggle.php', 'icon' => 'toggle', 'title' => '功能开关'],
    ['id' => 'seo', 'url' => 'seo.php', 'icon' => 'settings', 'title' => 'SEO设置'],
    ['id' => 'smtp', 'url' => 'smtp.php', 'icon' => 'mail', 'title' => 'SMTP设置'],
    ['id' => 'password', 'url' => 'password.php', 'icon' => 'lock', 'title' => '修改密码'],
];

$username = $_SESSION['admin_username'] ?? 'Admin';
$userInitial = strtoupper(substr($username, 0, 1));
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - 后台管理</title>
    <link rel="stylesheet" href="static/css/index.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="user-card">
                    <div class="user-avatar"><?php echo $userInitial; ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($username); ?></div>
                    </div>
                    <svg class="refresh-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" onclick="location.reload()">
                        <path d="M23 4v6h-6M1 20v-6h6M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>
                    </svg>
                </div>
            </div>
            <nav class="sidebar-nav">
                <?php foreach ($menuItems as $item): ?>
                    <?php if (isset($item['section'])): ?>
                        <div class="nav-section">
                            <div class="nav-section-title"><?php echo $item['section']; ?></div>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo $item['url']; ?>" class="nav-link <?php echo $activeMenu === $item['id'] ? 'active' : ''; ?>">
                            <?php if ($item['icon'] === 'user'): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <?php elseif ($item['icon'] === 'code'): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
                            <?php elseif ($item['icon'] === 'link'): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                            <?php elseif ($item['icon'] === 'folder'): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                            <?php elseif ($item['icon'] === 'message'): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                            <?php elseif ($item['icon'] === 'settings'): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                            <?php elseif ($item['icon'] === 'mail'): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            <?php elseif ($item['icon'] === 'toggle'): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/><circle cx="12" cy="12" r="10" opacity="0.3"/><path d="M12 6v12M6 12h12"/></svg>
                            <?php elseif ($item['icon'] === 'lock'): ?>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                            <?php endif; ?>
                            <span><?php echo $item['title']; ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-footer">
                <a href="logout.php" class="logout-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    <span>退出登录</span>
                </a>
            </div>
        </aside>
        <main class="admin-main">
            <header class="admin-header">
                <div class="header-title">
                    <button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                    <h1><?php echo $title; ?></h1>
                </div>
            </header>
            <div class="admin-content">
