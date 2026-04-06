<?php
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (login($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>登录 - 管理后台</title>
    <style>
        :root {
            --bg-color: #f5f5f7;
            --card-bg: #ffffff;
            --primary-color: #4a90d9;
            --primary-hover: #3a7bc8;
            --text-primary: #1d1d1f;
            --text-secondary: #86868b;
            --text-muted: #a1a1a6;
            --border-color: #e5e5e7;
            --error-color: #ff6b6b;
            --hover-bg: #f5f5f7;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 0 20px;
        }

        .login-card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 600;
            font-size: 24px;
            margin: 0 auto 16px;
        }

        .login-header h1 {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 217, 0.1);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 8px;
        }

        .btn-login:hover {
            background: var(--primary-hover);
        }

        .error-message {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid rgba(255, 107, 107, 0.2);
            color: #dc2626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: var(--primary-color);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 32px 24px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">P</div>
                <h1>管理后台</h1>
                <p>请输入管理员账号登录</p>
            </div>

            <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">登录</button>
            </form>

            <a href="../" class="back-link">← 返回首页</a>
        </div>
    </div>
</body>
</html>

