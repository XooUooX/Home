<?php
require_once '../functions.php';
requireLogin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = $_POST['old_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = '请填写所有字段';
    } elseif ($newPassword !== $confirmPassword) {
        $error = '两次输入的新密码不一致';
    } elseif (strlen($newPassword) < 6) {
        $error = '新密码长度至少6位';
    } else {
        $result = changePassword($_SESSION['admin_id'], $oldPassword, $newPassword);
        if ($result['success']) {
            header('Location: password.php?msg=success');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

// 检查URL参数
if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $success = '密码修改成功';
}

$title = '修改密码';
$activeMenu = 'password';
include 'header.php';
?>

<div class="page-header">
    <h2>修改密码</h2>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title">修改管理员密码</div>
    <form method="POST" action="">
        <div class="form-group">
            <label for="old_password">原密码</label>
            <input type="password" id="old_password" name="old_password" required>
        </div>
        <div class="form-group">
            <label for="new_password">新密码</label>
            <input type="password" id="new_password" name="new_password" required>
            <small style="color: var(--gray-500);">密码长度至少6位</small>
        </div>
        <div class="form-group">
            <label for="confirm_password">确认新密码</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        <button type="submit" class="btn btn-primary">保存修改</button>
    </form>
</div>

<?php include 'footer.php'; ?>
