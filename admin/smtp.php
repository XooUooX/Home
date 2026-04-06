<?php
require_once '../functions.php';
requireLogin();

$title = 'SMTP设置';
$activeMenu = 'smtp';

$success = '';
$error = '';

// 获取当前SMTP配置
$stmt = db()->query("SELECT * FROM smtp WHERE id = 1");
$smtp = $stmt->fetch();

if (!$smtp) {
    // 如果没有配置，创建默认配置
    db()->exec("INSERT INTO smtp (id, host, port, encryption) VALUES (1, '', 587, 'tls')");
    $stmt = db()->query("SELECT * FROM smtp WHERE id = 1");
    $smtp = $stmt->fetch();
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'save') {
        $host = trim($_POST['host'] ?? '');
        $port = intval($_POST['port'] ?? 587);
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $encryption = $_POST['encryption'] ?? 'tls';
        $from_email = trim($_POST['from_email'] ?? '');
        $from_name = trim($_POST['from_name'] ?? '');
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        $stmt = db()->prepare("
            UPDATE smtp 
            SET host = ?, port = ?, username = ?, password = ?, 
                encryption = ?, from_email = ?, from_name = ?, enabled = ?
            WHERE id = 1
        ");
        
        if ($stmt->execute([$host, $port, $username, $password, $encryption, $from_email, $from_name, $enabled])) {
            // PRG模式：保存成功后重定向
            header('Location: smtp.php?msg=success');
            exit;
        } else {
            $error = '保存失败';
        }
    } elseif ($action === 'test') {
        // 发送测试邮件
        $test_email = trim($_POST['test_email'] ?? '');
        if (!filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
            $error = '请输入有效的测试邮箱地址';
        } else {
            $result = sendTestEmail($test_email);
            if ($result) {
                header('Location: smtp.php?msg=email_sent');
                exit;
            } else {
                header('Location: smtp.php?msg=email_failed');
                exit;
            }
        }
    }
}

// 检查URL参数
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'success':
            $success = 'SMTP设置已保存';
            break;
        case 'email_sent':
            $success = '测试邮件已发送，请检查收件箱';
            break;
        case 'email_failed':
            $error = '测试邮件发送失败，请检查SMTP配置';
            break;
    }
}

// 发送测试邮件函数
function sendTestEmail($toEmail) {
    $smtp = getSMTPConfig();
    
    if (!$smtp || empty($smtp['host']) || empty($smtp['username'])) {
        return false;
    }
    
    $subject = 'SMTP测试邮件';
    $body = '<h2>SMTP配置测试</h2><p>如果您收到此邮件，说明SMTP配置正确！</p><p>发送时间：' . date('Y-m-d H:i:s') . '</p>';
    
    // 使用PHPMailer发送
    require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';
    require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp['username'];
        $mail->Password = $smtp['password'];
        $mail->SMTPSecure = $smtp['encryption'] === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = intval($smtp['port']);
        
        $from_email = $smtp['from_email'] ?: $smtp['username'];
        $from_name = $smtp['from_name'] ?: '个人主页';
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($toEmail);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->CharSet = 'UTF-8';
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Test email error: {$mail->ErrorInfo}");
        return false;
    }
}

// 使用SMTP发送邮件（通过PHPMailer）
function sendEmailWithSMTP($toEmail, $subject, $body, $smtp) {
    $host = $smtp['host'];
    $port = $smtp['port'];
    $username = $smtp['username'];
    $password = $smtp['password'];
    $encryption = $smtp['encryption'];
    $from_email = $smtp['from_email'] ?: $username;
    $from_name = $smtp['from_name'] ?: '个人主页';
    
    if (empty($host) || empty($username) || empty($password)) {
        return false;
    }
    
    // 如果启用了SMTP，使用PHPMailer
    if ($smtp['enabled']) {
        require_once __DIR__ . '/../lib/PHPMailer/PHPMailer.php';
        require_once __DIR__ . '/../lib/PHPMailer/SMTP.php';
        require_once __DIR__ . '/../lib/PHPMailer/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = true;
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->SMTPSecure = $encryption === 'ssl' ? PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = intval($port);
            
            $mail->setFrom($from_email, $from_name);
            $mail->addAddress($toEmail);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->CharSet = 'UTF-8';
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("PHPMailer Error: {$mail->ErrorInfo}");
            return false;
        }
    } else {
        // 使用原生mail函数
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$from_name} <{$from_email}>\r\n";
        
        return mail($toEmail, $subject, $body, $headers);
    }
}


include 'header.php';
?>

<h1 class="page-title">SMTP设置</h1>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title">邮件服务器配置</div>
    <form method="POST" action="">
        <input type="hidden" name="action" value="save">
        
        <div class="form-row">
            <div class="form-group">
                <label>SMTP服务器 *</label>
                <input type="text" name="host" value="<?php echo htmlspecialchars($smtp['host']); ?>" placeholder="如: smtp.gmail.com" required>
            </div>
            <div class="form-group">
                <label>端口 *</label>
                <input type="number" name="port" value="<?php echo $smtp['port']; ?>" placeholder="587" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>用户名 *</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($smtp['username']); ?>" placeholder="邮箱账号" required>
            </div>
            <div class="form-group">
                <label>密码 *</label>
                <input type="password" name="password" value="<?php echo htmlspecialchars($smtp['password']); ?>" placeholder="邮箱密码或授权码" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>加密方式</label>
                <select name="encryption">
                    <option value="tls" <?php echo $smtp['encryption'] === 'tls' ? 'selected' : ''; ?>>TLS</option>
                    <option value="ssl" <?php echo $smtp['encryption'] === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                    <option value="none" <?php echo $smtp['encryption'] === 'none' ? 'selected' : ''; ?>>无</option>
                </select>
            </div>
            <div class="form-group">
                <label>发件人邮箱</label>
                <input type="email" name="from_email" value="<?php echo htmlspecialchars($smtp['from_email']); ?>" placeholder="默认使用用户名">
            </div>
        </div>
        
        <div class="form-group">
            <label>发件人名称</label>
            <input type="text" name="from_name" value="<?php echo htmlspecialchars($smtp['from_name']); ?>" placeholder="如: 个人主页">
        </div>
        
        <div class="form-group">
            <label>启用SMTP发送</label>
            <div class="toggle-switch">
                <input type="checkbox" name="enabled" id="smtp_enabled" <?php echo $smtp['enabled'] ? 'checked' : ''; ?>>
                <label for="smtp_enabled" class="toggle-checkbox"></label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary">保存设置</button>
    </form>
</div>

<div class="card">
    <div class="card-title">发送测试邮件</div>
    <form method="POST" action="">
        <input type="hidden" name="action" value="test">
        <div class="form-group">
            <label>测试邮箱地址</label>
            <input type="email" name="test_email" placeholder="输入接收测试邮件的邮箱" required>
        </div>
        <button type="submit" class="btn btn-secondary">发送测试邮件</button>
    </form>
</div>

<div class="card" style="background: #f8f9fa;">
    <div class="card-title">常见SMTP服务器配置</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>邮箱服务商</th>
                <th>SMTP服务器</th>
                <th>端口</th>
                <th>加密</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gmail</td>
                <td>smtp.gmail.com</td>
                <td>587</td>
                <td>TLS</td>
            </tr>
            <tr>
                <td>QQ邮箱</td>
                <td>smtp.qq.com</td>
                <td>587</td>
                <td>TLS</td>
            </tr>
            <tr>
                <td>163邮箱</td>
                <td>smtp.163.com</td>
                <td>587</td>
                <td>TLS</td>
            </tr>
            <tr>
                <td>Outlook</td>
                <td>smtp.office365.com</td>
                <td>587</td>
                <td>TLS</td>
            </tr>
        </tbody>
    </table>
    <p style="margin-top: 16px; font-size: 13px; color: var(--gray-500);">
        提示：部分邮箱需要使用授权码代替密码登录，请前往邮箱设置页面开启SMTP服务并获取授权码。
    </p>
</div>

<?php include 'footer.php'; ?>
