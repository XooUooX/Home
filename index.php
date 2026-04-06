<?php
require_once 'functions.php';

// 加载数据
$profile = getProfile();
$skillsByCategory = getSkillsByCategory();
$socialLinks = getSocialLinks();
$projects = getProjects();

// 加载功能开关
$showSocialLinks = isToggleEnabled('links');
$showMessages = isToggleEnabled('messages');
$showSkills = isToggleEnabled('skills');
$showProjects = isToggleEnabled('projects');

// 获取 SEO 设置
$stmt = db()->query("SELECT * FROM seo LIMIT 1");
$seo = $stmt->fetch();

// 处理留言提交
$messageSuccess = '';
$messageError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_message') {
    $nickname = trim($_POST['nickname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $content = trim($_POST['content'] ?? '');
    
    if (empty($nickname) || empty($email) || empty($content)) {
        $messageError = '请填写所有必填项';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messageError = '请输入有效的邮箱地址';
    } elseif (strlen($content) < 10) {
        $messageError = '留言内容至少需要10个字符';
    } else {
        if (addMessage($nickname, $email, $content)) {
            // 发送邮件通知管理员
            sendNewMessageNotification($nickname, $email, $content);
            
            // PRG模式：提交成功后重定向，防止重复提交
            header('Location: ' . $_SERVER['REQUEST_URI'] . '?msg=success');
            exit;
        } else {
            $messageError = '留言提交失败，请稍后重试';
        }
    }
}

// 检查URL参数中的成功消息
if (isset($_GET['msg']) && $_GET['msg'] === 'success') {
    $messageSuccess = '留言提交成功，感谢您的反馈！';
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($seo['site_title']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($seo['site_description']); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($seo['site_keywords']); ?>">
    <link rel="icon" href="<?php echo htmlspecialchars($seo['favicon']); ?>" type="image/x-icon">
    <link rel="stylesheet" href="static/css/index.css">
</head>

<body>
    <?php if (!empty($profile['background_url'])): ?>
    <iframe class="bg-fixed" src="<?php echo htmlspecialchars($profile['background_url']); ?>" frameborder="0"></iframe>
    <?php else: ?>
    <div class="bg-fixed" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);"></div>
    <?php endif; ?>

    <div class="main-container">
        <main class="layout-grid">
            <aside class="profile-card card" data-reveal data-delay="0.05">
                <div class="avatar-box" data-reveal data-delay="0.1">
                    <img src="<?php echo htmlspecialchars($profile['avatar'] ?? ''); ?>" class="avatar" alt="<?php echo htmlspecialchars($profile['name'] ?? ''); ?>">
                    <?php if ($profile['status_online'] ?? false): ?>
                    <span class="status-badge"></span>
                    <?php endif; ?>
                </div>
                <h1 class="name" data-reveal data-delay="0.18"><?php echo htmlspecialchars($profile['name'] ?? ''); ?></h1>
                <p class="bio-short" data-reveal data-delay="0.26"><?php echo nl2br(htmlspecialchars($profile['bio'] ?? '')); ?></p>
                
                <?php if ($showSocialLinks && !empty($socialLinks)): ?>
                <div class="social-links" data-reveal data-delay="0.34">
                    <?php foreach ($socialLinks as $link): ?>
                    <?php if ($link['url']): ?>
                    <?php
                    $url = $link['url'];
                    // 自动识别邮箱并添加 mailto: 协议
                    if (strpos($url, '@') !== false && strpos($url, '://') === false && strpos($url, 'mailto:') !== 0) {
                        $url = 'mailto:' . $url;
                    }
                    ?>
                    <?php if (isImageUrl($url)): ?>
                    <div class="social-link" onclick="openModal('<?php echo htmlspecialchars($url); ?>', '<?php echo htmlspecialchars($link['name']); ?>')" title="<?php echo htmlspecialchars($link['name']); ?>" style="cursor: pointer;">
                        <img src="<?php echo htmlspecialchars($link['icon']); ?>" alt="<?php echo htmlspecialchars($link['name']); ?>">
                    </div>
                    <?php else: ?>
                    <a href="<?php echo htmlspecialchars($url); ?>" class="social-link" target="_blank" rel="noopener noreferrer" title="<?php echo htmlspecialchars($link['name']); ?>">
                        <img src="<?php echo htmlspecialchars($link['icon']); ?>" alt="<?php echo htmlspecialchars($link['name']); ?>">
                    </a>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="social-link" onclick="openModal('<?php echo htmlspecialchars($link['icon']); ?>', '<?php echo htmlspecialchars($link['name']); ?>')" title="<?php echo htmlspecialchars($link['name']); ?>" style="cursor: pointer;">
                        <img src="<?php echo htmlspecialchars($link['icon']); ?>" alt="<?php echo htmlspecialchars($link['name']); ?>">
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($showMessages): ?>
                <!-- 留言板 -->
                <h2 class="section-title" style="margin-top: 32px; align-self: flex-start;" data-reveal data-delay="0.4">Message</h2>
                <div class="guestbook-form" data-reveal data-delay="0.45" style="width: 100%; padding: 16px; box-sizing: border-box;">
                    <?php if ($messageSuccess): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($messageSuccess); ?></div>
                    <?php endif; ?>
                    <?php if ($messageError): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($messageError); ?></div>
                    <?php endif; ?>
                    <form method="POST" action="" style="width: 100%;">
                        <input type="hidden" name="action" value="submit_message">
                        <div class="form-group" style="margin-bottom: 12px;">
                            <input type="text" name="nickname" required placeholder="昵称 *" maxlength="100" style="padding: 10px 12px; width: 100%; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <input type="email" name="email" required placeholder="邮箱 *" maxlength="255" style="padding: 10px 12px; width: 100%; box-sizing: border-box;">
                        </div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <textarea name="content" rows="4" required placeholder="留言内容（至少10个字符） *" minlength="10" style="padding: 10px 12px; min-height: 80px; width: 100%; box-sizing: border-box; resize: vertical;"></textarea>
                        </div>
                        <button type="submit" class="btn-submit" style="padding: 10px 20px; font-size: 0.9rem;">提交留言</button>
                    </form>
                </div>
                <?php endif; ?>
            </aside>

            <section class="card" data-reveal data-delay="0.15">
                <?php if ($showSkills): ?>
                <h2 class="section-title" data-reveal data-delay="0.22">Tech Stack</h2>

                <div class="skill-stack">
                    <?php foreach ($skillsByCategory as $index => $group): ?>
                    <div class="stack-group" data-reveal data-delay="<?php echo 0.3 + $index * 0.04; ?>">
                        <h3><?php echo htmlspecialchars($group['category']['name']); ?></h3>
                        <div class="stack-list">
                            <?php foreach ($group['skills'] as $skillIndex => $skill): ?>
                            <span class="tech-tag" data-reveal data-delay="<?php echo 0.36 + $skillIndex * 0.04; ?>">
                                <img src="<?php echo htmlspecialchars($skill['icon']); ?>" alt="<?php echo htmlspecialchars($skill['name']); ?>">
                                <?php echo htmlspecialchars($skill['name']); ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($showProjects && !empty($projects)): ?>
                <h2 class="section-title" style="margin-top: 40px;" data-reveal data-delay="0.4">Projects</h2>
                <div class="projects-grid" data-reveal data-delay="0.45">
                    <?php foreach ($projects as $project): ?>
                    <div class="project-card" data-reveal>
                        <?php if ($project['image']): ?>
                        <div class="project-image-wrapper" onclick="openModal('<?php echo htmlspecialchars($project['image']); ?>', '<?php echo htmlspecialchars($project['title']); ?>')" style="cursor: pointer;">
                            <img src="<?php echo htmlspecialchars($project['image']); ?>" alt="<?php echo htmlspecialchars($project['title']); ?>">
                        </div>
                        <?php endif; ?>
                        <?php if ($project['url'] && !isImageUrl($project['url'])): ?>
                        <a href="<?php echo htmlspecialchars($project['url']); ?>" class="project-info-link" target="_blank" rel="noopener noreferrer">
                        <?php endif; ?>
                        <div class="project-info">
                            <div class="project-title"><?php echo htmlspecialchars($project['title']); ?></div>
                            <div class="project-desc"><?php echo htmlspecialchars(mb_strimwidth($project['description'] ?? '暂无描述', 0, 60, '...')); ?></div>
                            <?php if (!empty($project['github_repo']) && $project['github_stars'] > 0): ?>
                            <div class="github-stats">
                                <span class="github-stars">
                                    <svg viewBox="0 0 24 24" fill="currentColor" width="14" height="14"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    <?php echo number_format($project['github_stars']); ?> stars
                                </span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php if ($project['url'] && !isImageUrl($project['url'])): ?></a><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <footer class="footer" data-reveal data-delay="0.2">
        <span>Copyright © <?php echo $profile['copyright_year_start'] ?? date('Y'); ?> - <?php echo date('Y'); ?></span>
        <?php if (!empty($profile['icp'])): ?>
        <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener noreferrer"><?php echo htmlspecialchars($profile['icp']); ?></a>
        <?php endif; ?>
    </footer>

    <!-- 图片弹窗 -->
    <div id="imageModal" class="modal" onclick="closeModal(event)">
        <span class="close" onclick="closeModalByButton(event)">&times;</span>
        <div class="modal-content">
            <img id="modalImage" src="" alt="">
        </div>
        <div class="modal-caption" id="modalCaption"></div>
    </div>

    <script src="static/js/index.js"></script>
</body>
</html>
