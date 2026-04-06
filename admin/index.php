<?php
require_once '../functions.php';
requireLogin();

$profile = getProfile();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $data = [
            'name' => $_POST['name'] ?? '',
            'avatar' => $_POST['avatar'] ?? '',
            'bio' => $_POST['bio'] ?? '',
            'status_online' => isset($_POST['status_online']) ? 1 : 0,
            'icp' => $_POST['icp'] ?? '',
            'copyright_year_start' => intval($_POST['copyright_year_start'] ?? date('Y')),
            'background_url' => $_POST['background_url'] ?? ''
        ];
        
        if (updateProfile($data)) {
            header('Location: index.php?msg=success');
            exit;
        } else {
            $error = '更新失败';
        }
    }
}

// 检查URL参数
if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $success = '个人资料已更新';
}

$title = '个人资料';
$activeMenu = 'index';
include 'header.php';
?>

<div class="page-header">
    <h2>个人资料</h2>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title">基本信息</div>
    <form method="POST" action="">
        <input type="hidden" name="action" value="update_profile">
        
        <div class="form-row">
            <div class="form-group">
                <label for="name">显示名称</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="avatar">头像 URL</label>
                <input type="text" id="avatar" name="avatar" value="<?php echo htmlspecialchars($profile['avatar'] ?? ''); ?>">
                <?php if ($profile['avatar']): ?>
                <img src="<?php echo htmlspecialchars($profile['avatar']); ?>" style="width: 60px; height: 60px; border-radius: 50%; margin-top: 8px; object-fit: cover;" alt="头像预览">
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="bio">个人简介</label>
            <textarea id="bio" name="bio" rows="3"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="icp">ICP 备案号</label>
                <input type="text" id="icp" name="icp" value="<?php echo htmlspecialchars($profile['icp'] ?? ''); ?>" placeholder="如: 京ICP备12345678号">
            </div>
            <div class="form-group">
                <label for="copyright_year_start">版权起始年份</label>
                <input type="number" id="copyright_year_start" name="copyright_year_start" value="<?php echo intval($profile['copyright_year_start'] ?? date('Y')); ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="background_url">背景动画 URL (iframe)</label>
            <input type="text" id="background_url" name="background_url" value="<?php echo htmlspecialchars($profile['background_url'] ?? ''); ?>" placeholder="https://example.com/background.html">
        </div>

        <div class="form-group">
            <label>显示在线状态 (绿色指示灯)</label>
            <div class="toggle-switch">
                <input type="checkbox" name="status_online" id="status_online" <?php echo ($profile['status_online'] ?? 0) ? 'checked' : ''; ?>>
                <label for="status_online" class="toggle-checkbox"></label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">保存修改</button>
    </form>
</div>

<?php include 'footer.php'; ?>
