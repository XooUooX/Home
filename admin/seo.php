<?php
require_once __DIR__ . '/../functions.php';
requireLogin();

$success = '';
$error = '';

// 获取当前SEO设置
$stmt = db()->query("SELECT * FROM seo LIMIT 1");
$seo = $stmt->fetch();

// 如果没有数据，创建默认空数据
if (!$seo) {
    $stmt = db()->prepare("INSERT INTO seo (site_title, site_description, site_keywords, favicon) VALUES (?, ?, ?, ?)");
    $stmt->execute(['', '', '', '']);
    $seo = [
        'site_title' => '',
        'site_description' => '',
        'site_keywords' => '',
        'favicon' => ''
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_title = $_POST['site_title'] ?? '';
    $site_description = $_POST['site_description'] ?? '';
    $site_keywords = $_POST['site_keywords'] ?? '';
    $favicon = $_POST['favicon'] ?? '';

    $stmt = db()->prepare("UPDATE seo SET site_title = ?, site_description = ?, site_keywords = ?, favicon = ? WHERE id = 1");
    if ($stmt->execute([$site_title, $site_description, $site_keywords, $favicon])) {
        header('Location: seo.php?msg=success');
        exit;
    } else {
        $error = '更新失败';
    }
}

// 检查URL参数
if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $success = 'SEO设置已更新';
}

$title = 'SEO设置';
$activeMenu = 'seo';
include 'header.php';
?>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title">网站SEO设置</div>
    <form method="POST">
        <div class="form-group">
            <label for="site_title">网站标题</label>
            <input type="text" id="site_title" name="site_title" value="<?php echo htmlspecialchars($seo['site_title'] ?? ''); ?>" placeholder="我的个人主页">
        </div>

        <div class="form-group">
            <label for="site_description">网站描述</label>
            <textarea id="site_description" name="site_description" rows="2" placeholder="简短描述您的网站，用于搜索引擎展示"><?php echo htmlspecialchars($seo['site_description'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="site_keywords">关键词</label>
            <input type="text" id="site_keywords" name="site_keywords" value="<?php echo htmlspecialchars($seo['site_keywords'] ?? ''); ?>" placeholder="个人主页, 开发者, 作品集, 简历">
            <small class="form-hint">多个关键词用逗号分隔</small>
        </div>

        <div class="form-group">
            <label for="favicon">网站图标 (Favicon)</label>
            <input type="text" id="favicon" name="favicon" value="<?php echo htmlspecialchars($seo['favicon'] ?? ''); ?>" placeholder="/img/favicon.ico">
            <small class="form-hint">浏览器标签页显示的小图标</small>
        </div>

        <button type="submit" class="btn btn-primary">保存设置</button>
    </form>
</div>

<div class="card">
    <div class="card-title">搜索引擎预览</div>
    <div style="background: #f8f9fa; padding: 16px; border-radius: 8px; border: 1px solid var(--gray-200);">
        <div style="color: #1a0dab; font-size: 18px; font-weight: 400; line-height: 1.3; margin-bottom: 4px;">
            <?php echo htmlspecialchars($seo['site_title'] ?: '网站标题'); ?>
        </div>
        <div style="color: #006621; font-size: 14px; line-height: 1.4; margin-bottom: 4px;">
            example.com ›
        </div>
        <div style="color: #545454; font-size: 14px; line-height: 1.5;">
            <?php echo htmlspecialchars(mb_substr($seo['site_description'] ?: '网站描述将显示在这里...', 0, 150)); ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
