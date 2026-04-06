<?php
require_once '../functions.php';
requireLogin();

$success = '';
$error = '';

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_toggle') {
        $toggleKey = $_POST['toggle_key'] ?? '';
        $isEnabled = isset($_POST['is_enabled']) ? 1 : 0;
        
        if ($toggleKey && updateToggle($toggleKey, $isEnabled)) {
            header('Location: toggle.php?msg=updated');
            exit;
        } else {
            $error = '更新失败';
        }
    }
}

// 检查URL参数
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'updated': $success = '设置已保存'; break;
    }
}

// 获取所有功能开关
$toggles = getToggles();

$title = '功能开关';
$activeMenu = 'toggle';
include 'header.php';
?>

<div class="page-header">
    <h2>功能开关</h2>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title">首页模块显示控制</div>
    <p style="color: var(--text-secondary); margin-bottom: 20px;">
        通过以下开关控制首页各模块的显示与隐藏。
    </p>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40%;">功能模块</th>
                <th style="width: 40%;">状态</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($toggles as $toggle): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($toggle['toggle_name']); ?></strong>
                    <br>
                    <small style="color: var(--text-muted);"><?php echo htmlspecialchars($toggle['toggle_key']); ?></small>
                </td>
                <td>
                    <?php if ($toggle['is_enabled']): ?>
                    <span class="status-badge status-active">显示中</span>
                    <?php else: ?>
                    <span class="status-badge status-inactive">已隐藏</span>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="update_toggle">
                        <input type="hidden" name="toggle_key" value="<?php echo htmlspecialchars($toggle['toggle_key']); ?>">
                        <label class="toggle-switch">
                            <input type="checkbox" name="is_enabled" <?php echo $toggle['is_enabled'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <span class="toggle-checkbox"></span>
                            <span class="toggle-label"><?php echo $toggle['is_enabled'] ? '开启' : '关闭'; ?></span>
                        </label>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.data-table td {
    vertical-align: middle;
}
.toggle-switch {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}
.toggle-switch input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}
.toggle-checkbox {
    position: relative;
    width: 44px;
    height: 24px;
    background: #d1d1d6;
    border-radius: 12px;
    transition: all 0.3s ease;
    flex-shrink: 0;
}
.toggle-checkbox::before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
}
.toggle-switch input:checked + .toggle-checkbox {
    background: #34c759;
}
.toggle-switch input:checked + .toggle-checkbox::before {
    transform: translateX(20px);
}
.toggle-label {
    font-size: 14px;
    color: var(--text-secondary);
    user-select: none;
}
</style>

<?php include 'footer.php'; ?>
