<?php
session_start();
require_once 'db.php';

// 检查是否已登录
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// 要求登录
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// 登录函数
function login($username, $password) {
    $stmt = db()->prepare("SELECT id, password FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id'] = $user['id'];
        return true;
    }
    return false;
}

// 登出函数
function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}

// 获取个人资料
function getProfile() {
    $stmt = db()->query("SELECT * FROM config LIMIT 1");
    return $stmt->fetch();
}

// 更新个人资料
function updateProfile($data) {
    $fields = [];
    $values = [];
    foreach ($data as $key => $value) {
        if (in_array($key, ['name', 'avatar', 'bio', 'status_online', 'icp', 'copyright_year_start', 'background_url'])) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }
    }
    $values[] = 1;
    $sql = "UPDATE config SET " . implode(', ', $fields) . " WHERE id = ?";
    $stmt = db()->prepare($sql);
    return $stmt->execute($values);
}

// 获取所有技能分类
function getSkillCategories() {
    $stmt = db()->query("SELECT * FROM category ORDER BY sort_order");
    return $stmt->fetchAll();
}

// 获取所有技能
function getSkills() {
    $stmt = db()->query("
        SELECT s.*, c.name as category_name 
        FROM skill s 
        JOIN category c ON s.category_id = c.id 
        ORDER BY c.sort_order, s.sort_order
    ");
    return $stmt->fetchAll();
}

// 按分类获取技能
function getSkillsByCategory() {
    $categories = getSkillCategories();
    $result = [];
    foreach ($categories as $cat) {
        $stmt = db()->prepare("SELECT * FROM skill WHERE category_id = ? ORDER BY sort_order");
        $stmt->execute([$cat['id']]);
        $skills = $stmt->fetchAll();
        if (!empty($skills)) {
            $result[] = [
                'category' => $cat,
                'skills' => $skills
            ];
        }
    }
    return $result;
}

// 获取社交链接
function getSocialLinks() {
    $stmt = db()->query("SELECT * FROM link WHERE is_active = 1 ORDER BY sort_order");
    return $stmt->fetchAll();
}

// 获取所有项目
function getProjects() {
    $stmt = db()->query("SELECT * FROM project WHERE is_active = 1 ORDER BY sort_order");
    return $stmt->fetchAll();
}

// 修改密码
function changePassword($adminId, $oldPassword, $newPassword) {
    $stmt = db()->prepare("SELECT password FROM admin WHERE id = ?");
    $stmt->execute([$adminId]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($oldPassword, $user['password'])) {
        return ['success' => false, 'message' => '原密码错误'];
    }
    
    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = db()->prepare("UPDATE admin SET password = ? WHERE id = ?");
    
    if ($stmt->execute([$newPasswordHash, $adminId])) {
        return ['success' => true, 'message' => '密码修改成功'];
    }
    return ['success' => false, 'message' => '密码修改失败'];
}

// ============ 留言功能 ============

// 添加留言
function addMessage($nickname, $email, $content) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = db()->prepare("INSERT INTO message (nickname, email, content, ip) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$nickname, $email, $content, $ip]);
}

// 获取所有留言
function getAllMessages($page = 1, $perPage = 20) {
    $offset = ($page - 1) * $perPage;
    $stmt = db()->prepare("SELECT * FROM message ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, (int)$perPage, PDO::PARAM_INT);
    $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// 获取留言总数
function getMessageCount() {
    $stmt = db()->query("SELECT COUNT(*) FROM message");
    return $stmt->fetchColumn();
}

// 获取未读留言数
function getUnreadMessageCount() {
    $stmt = db()->query("SELECT COUNT(*) FROM message WHERE status = 'unread'");
    return $stmt->fetchColumn();
}

// 获取单条留言
function getMessage($id) {
    $stmt = db()->prepare("SELECT * FROM message WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// 获取SMTP配置
function getSMTPConfig() {
    $stmt = db()->query("SELECT * FROM smtp WHERE id = 1");
    return $stmt->fetch();
}

// 标记留言为已读
function markMessageAsRead($id) {
    $stmt = db()->prepare("UPDATE message SET status = 'read' WHERE id = ? AND status = 'unread'");
    return $stmt->execute([$id]);
}

// 回复留言
function replyMessage($id, $replyContent) {
    $stmt = db()->prepare("UPDATE message SET status = 'replied', reply_content = ?, reply_time = NOW() WHERE id = ?");
    if ($stmt->execute([$replyContent, $id])) {
        $message = getMessage($id);
        if ($message) {
            sendReplyEmail($message['email'], $message['nickname'], $message['content'], $replyContent);
        }
        return true;
    }
    return false;
}

// 删除留言
function deleteMessage($id) {
    $stmt = db()->prepare("DELETE FROM message WHERE id = ?");
    return $stmt->execute([$id]);
}

// 发送新留言通知邮件给管理员
function sendNewMessageNotification($nickname, $email, $content) {
    $profile = getProfile();
    $siteName = $profile['name'] ?? '个人主页';
    
    // 获取SMTP配置中的邮箱作为管理员邮箱
    $smtp = getSMTPConfig();
    $adminEmail = $smtp['from_email'] ?? $smtp['username'] ?? '';
    
    if (empty($adminEmail)) {
        return false;
    }
    
    $subject = "【{$siteName}】收到新留言";
    $contentHtml = nl2br(htmlspecialchars($content));
    
    $body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #FF9500; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
        .message-box { background: white; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #FF9500; }
        .info-row { margin: 8px 0; }
        .label { font-weight: 600; color: #666; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; }
        .btn { display: inline-block; padding: 10px 20px; background: #007AFF; color: white; text-decoration: none; border-radius: 6px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{$siteName} - 新留言通知</h2>
        </div>
        <div class="content">
            <p>您的网站收到了新留言：</p>
            
            <div class="message-box">
                <div class="info-row"><span class="label">昵称：</span>{$nickname}</div>
                <div class="info-row"><span class="label">邮箱：</span>{$email}</div>
                <div class="info-row"><span class="label">内容：</span></div>
                <div style="margin-top: 10px; padding: 10px; background: #f5f5f5; border-radius: 4px;">
                    {$contentHtml}
                </div>
            </div>
            
            <p style="margin-top: 20px;">
                <a href="{$_SERVER['HTTP_HOST']}/admin/message.php" class="btn">查看留言</a>
            </p>
        </div>
        <div class="footer">
            此邮件由系统自动发送，请勿直接回复
        </div>
    </div>
</body>
</html>
HTML;
    
    $smtp = getSMTPConfig();
    
    // 如果启用了SMTP且配置了完整信息，使用SMTP发送
    if ($smtp && $smtp['enabled'] && !empty($smtp['host']) && !empty($smtp['username']) && !empty($smtp['password'])) {
        return sendEmailViaSMTP($adminEmail, $subject, $body, $smtp, $siteName);
    }
    
    // 否则使用原生mail函数
    $from_email = $smtp['from_email'] ?? ($smtp['username'] ?? 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $from_name = $siteName;
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$from_name} <{$from_email}>\r\n";
    
    return mail($adminEmail, $subject, $body, $headers);
}

// 发送回复邮件（支持SMTP配置）
function sendReplyEmail($toEmail, $toName, $messageContent, $replyContent) {
    $profile = getProfile();
    $siteName = $profile['name'] ?? '个人主页';
    $subject = "您在 {$siteName} 的留言有了回复";
    
    $messageHtml = nl2br(htmlspecialchars($messageContent));
    $replyHtml = nl2br(htmlspecialchars($replyContent));
    
    $body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007AFF; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f8f9fa; padding: 20px; border-radius: 0 0 8px 8px; }
        .message-box { background: white; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #007AFF; }
        .reply-box { background: white; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #34C759; }
        .footer { margin-top: 20px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{$siteName}</h2>
        </div>
        <div class="content">
            <p>您好，{$toName}：</p>
            <p>您在 {$siteName} 的留言收到了回复：</p>
            
            <div class="message-box">
                <strong>您的留言：</strong><br>
                {$messageHtml}
            </div>
            
            <div class="reply-box">
                <strong>站长回复：</strong><br>
                {$replyHtml}
            </div>
            
            <p>感谢您的留言，如有任何问题欢迎再次联系！</p>
        </div>
        <div class="footer">
            此邮件由系统自动发送，请勿直接回复
        </div>
    </div>
</body>
</html>
HTML;
    
    // 获取SMTP配置
    $smtp = getSMTPConfig();
    
    // 如果启用了SMTP且配置了完整信息，使用SMTP发送
    if ($smtp && $smtp['enabled'] && !empty($smtp['host']) && !empty($smtp['username']) && !empty($smtp['password'])) {
        return sendEmailViaSMTP($toEmail, $subject, $body, $smtp, $siteName);
    }
    
    // 否则使用原生mail函数
    $from_email = $smtp['from_email'] ?? ($smtp['username'] ?? 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $from_name = $smtp['from_name'] ?? $siteName;
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$from_name} <{$from_email}>\r\n";
    
    return mail($toEmail, $subject, $body, $headers);
}

// 通过SMTP发送邮件（使用 PHPMailer）
function sendEmailViaSMTP($toEmail, $subject, $body, $smtp, $siteName) {
    // 加载 PHPMailer
    require_once __DIR__ . '/lib/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/lib/PHPMailer/SMTP.php';
    require_once __DIR__ . '/lib/PHPMailer/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // 服务器设置
        $mail->isSMTP();
        $mail->Host = $smtp['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtp['username'];
        $mail->Password = $smtp['password'];
        $mail->SMTPSecure = $smtp['encryption'] === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = intval($smtp['port']);
        
        // 发件人
        $from_email = $smtp['from_email'] ?: $smtp['username'];
        $from_name = $smtp['from_name'] ?: $siteName;
        $mail->setFrom($from_email, $from_name);
        
        // 收件人
        $mail->addAddress($toEmail);
        
        // 内容
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
}

// 获取所有功能开关
function getToggles() {
    $stmt = db()->query("SELECT * FROM toggle ORDER BY id");
    return $stmt->fetchAll();
}

// 获取单个功能开关状态
function isToggleEnabled($toggleKey) {
    $stmt = db()->prepare("SELECT is_enabled FROM toggle WHERE toggle_key = ?");
    $stmt->execute([$toggleKey]);
    $result = $stmt->fetch();
    return $result ? (bool)$result['is_enabled'] : true;
}

// 更新功能开关状态
function updateToggle($toggleKey, $isEnabled) {
    $stmt = db()->prepare("UPDATE toggle SET is_enabled = ? WHERE toggle_key = ?");
    return $stmt->execute([$isEnabled ? 1 : 0, $toggleKey]);
}

// 判断URL是否为图片
function isImageUrl($url) {
    if (empty($url)) return false;
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
    $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
    return in_array($ext, $imageExtensions);
}

// 从 URL 提取 GitHub 仓库名称 (格式: owner/repo)
function extractGithubRepo($url) {
    if (empty($url)) return null;
    
    // 匹配 github.com/owner/repo 格式
    if (preg_match('/github\.com\/([^\/]+)\/([^\/\s]+)/', $url, $matches)) {
        $owner = $matches[1];
        $repo = $matches[2];
        // 移除 .git 后缀
        $repo = preg_replace('/\.git$/', '', $repo);
        return $owner . '/' . $repo;
    }
    
    return null;
}

// 获取 GitHub 仓库信息
function fetchGithubRepoInfo($repo) {
    if (empty($repo)) return null;
    
    $apiUrl = "https://api.github.com/repos/{$repo}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: PHP-Project-Manager',
        'Accept: application/vnd.github.v3+json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if ($data) {
            return [
                'stars' => $data['stargazers_count'] ?? 0,
                'forks' => $data['forks_count'] ?? 0,
                'description' => $data['description'] ?? '',
                'updated_at' => $data['updated_at'] ?? null
            ];
        }
    }
    
    return null;
}

// 刷新所有 GitHub 项目信息（可用于定时任务）
function refreshAllGithubRepoInfo() {
    $stmt = db()->query("SELECT id, github_repo FROM project WHERE github_repo IS NOT NULL AND github_repo != ''");
    $projects = $stmt->fetchAll();
    
    foreach ($projects as $project) {
        $info = fetchGithubRepoInfo($project['github_repo']);
        if ($info) {
            $updateStmt = db()->prepare("UPDATE project SET github_stars = ?, github_forks = ?, github_updated = NOW() WHERE id = ?");
            $updateStmt->execute([$info['stars'], $info['forks'], $project['id']]);
        }
    }
    
    return count($projects);
}
