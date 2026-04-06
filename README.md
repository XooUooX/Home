# 个人主页

一款功能完善的 PHP 个人主页/作品集展示系统，支持响应式布局、后台管理、留言板、技能展示、项目展示等功能。

## 功能特性

### 前端功能
- **响应式布局**：适配桌面、平板、手机等多种设备
- **个人资料展示**：头像、姓名、简介、在线状态
- **社交链接**：支持图标链接，自动识别邮箱、图片链接
- **技能展示**：按分类展示技术栈，支持图标
- **项目展示**：卡片式布局，支持封面图、外链、弹窗预览
- **留言板**：访客可留言，支持邮件通知管理员
- **图片弹窗**：点击图片可放大查看
- **滚动动画**：页面元素滚动渐入效果
- **PRG 模式**：表单提交后重定向，防止重复提交

### 后台管理功能
- **个人资料管理**：修改头像、姓名、简介、背景图、ICP备案等
- **社交链接管理**：增删改查，支持排序、启用/禁用
- **技能管理**：分类管理 + 技能管理，支持图标、排序
- **项目管理**：增删改查，支持封面图、外链、显示/隐藏
- **留言管理**：查看、回复、删除、标记已读，支持邮件回复
- **功能开关**：控制首页各模块显示/隐藏（社交链接、留言板、技能、项目）
- **SEO 设置**：网站标题、关键词、描述
- **SMTP 设置**：配置邮件发送，支持留言通知和回复通知
- **密码修改**：管理员密码修改

## 技术栈

- **后端**：PHP 7.1+，MySQL
- **数据库**：MySQL 5.7+
- **前端**：原生 HTML5 + CSS3 + JavaScript
- **邮件**：PHPMailer 库
- **无框架依赖**：轻量级，易于部署

## 目录结构

```
├── admin/                  # 后台管理目录
│   ├── header.php         # 后台公共头部（导航菜单）
│   ├── footer.php         # 后台公共底部
│   ├── login.php          # 登录页面
│   ├── index.php          # 个人资料管理
│   ├── link.php           # 社交链接管理
│   ├── skill.php          # 技能管理（分类+技能）
│   ├── project.php        # 项目管理
│   ├── message.php        # 留言管理
│   ├── toggle.php         # 功能开关
│   ├── seo.php            # SEO设置
│   ├── smtp.php           # SMTP设置
│   ├── password.php       # 密码修改
│   └── static/            # 后台静态资源
│       ├── css/
│       └── js/
├── lib/                    # 第三方库
│   └── PHPMailer/         # PHPMailer 邮件库
├── img/                    # 图片资源目录
├── static/                 # 前台静态资源
│   ├── css/
│   └── js/
├── update/                 # 更新脚本目录
├── config.php             # 数据库配置文件
├── db.php                 # 数据库连接类
├── functions.php          # 公共函数库
├── index.php              # 网站首页
├── database.sql           # 数据库结构（带示例数据）
├── install.sql            # 安装SQL（初始数据）
└── README.md              # 本说明文档
```

## 安装部署

### 环境要求
- PHP 7.4 或更高版本
- MySQL 5.7 或更高版本 / MariaDB 10.3+
- Apache/Nginx 服务器
- 启用 PDO 扩展

### 安装步骤

1. **上传文件**
将项目文件上传到网站根目录

2. **配置数据库**
   编辑 `config.php`：
   ```php
   return [
       'host' => 'localhost',
       'database' => 'root',
       'username' => 'root',
       'password' => 'password',
   ];
   ```

3. **访问后台**
   - 后台地址：`http://域名/admin/login.php`
   - 默认账号：`admin`
   - 默认密码：`admin123`
   - **首次登录后请立即修改密码**



## 数据库结构

### 表清单

| 表名 | 说明 |
|------|------|
| `admin` | 管理员账号 |
| `config` | 个人资料 |
| `link` | 社交链接 |
| `category` | 技能分类 |
| `skill` | 技能列表 |
| `project` | 项目/作品 |
| `message` | 留言板 |
| `toggle` | 功能开关 |
| `seo` | SEO 配置 |
| `smtp` | SMTP 配置 |


## 使用说明

### 后台管理

1. **个人资料**
   - 修改姓名、头像、简介
   - 设置在线状态（显示绿色小圆点）
   - 配置 ICP 备案号
   - 设置版权起始年份
   - 上传背景图或使用渐变背景
自带两款动态背景
```
/static/iframe/city-day.html
/static/iframe/city-day-night.html
```
2. **社交链接**
   - 添加社交账号（GitHub、Twitter、邮箱等）
   - URL 支持三种类型：
     - 普通链接：`https://github.com/username` → 新窗口打开
     - 邮箱：`your@email.com` 或 `mailto:your@email.com` → 打开邮件客户端
     - 图片链接：`https://example.com/image.jpg` → 点击放大查看
   - 支持排序和启用/禁用

3. **技能管理**
   - 先创建分类（如：Languages & Frameworks、Tools & Environment）
   - 再添加技能，选择所属分类
   - 图标支持：Simple Icons URL 或本地图片路径

4. **项目管理**
   - 添加项目封面图
   - 外链支持：
     - 普通链接 → 新窗口打开
     - 图片链接 → 点击放大查看
     - 留空 → 点击显示封面大图
   - 支持显示/隐藏控制

5. **功能开关**
   - 控制首页各模块是否显示
   - 可单独关闭：社交链接、留言板、技能展示、项目展示

6. **留言管理**
   - 查看访客留言（显示 IP、时间）
   - 标记已读/未读
   - 回复留言（自动发送邮件通知访客）
   - 删除留言

7. **SMTP 设置**
   - 配置邮件服务器，用于：
     - 新留言通知管理员
     - 回复留言通知访客
   - 支持 SSL/TLS 加密
   - 支持测试邮件发送

### 前端特性

- **图片弹窗**：社交链接和项目卡片的图片 URL 点击后会在弹窗中放大显示
- **表单验证**：留言板有前端+后端双重验证
- **响应式**：适配各种屏幕尺寸
- **动画效果**：滚动时元素渐入动画

## 配置说明

### config.php

```php
return [
    'host' => 'localhost',           // 数据库主机
    'database' => 'index',            // 数据库名
    'username' => 'index',            // 数据库用户名
    'password' => 'password',         // 数据库密码
];
```

### 安全建议

1. **修改默认密码**：首次登录后立即修改 admin 密码
2. **数据库安全**：
   - 使用强密码
   - 限制数据库用户权限
   - 定期备份数据

## 开发说明

### 核心函数

位于 `functions.php`：

- `db()` - 获取数据库连接
- `getProfile()` - 获取个人资料
- `getSocialLinks()` - 获取社交链接
- `getSkillsByCategory()` - 获取分类后的技能
- `getProjects()` - 获取项目列表
- `isToggleEnabled($key)` - 检查功能开关状态
- `isImageUrl($url)` - 判断 URL 是否为图片
- `sendReplyEmail()` - 发送回复邮件
- `sendNewMessageNotification()` - 发送新留言通知

### PRG 模式

所有表单提交均采用 PRG（Post/Redirect/Get）模式：
1. 表单 POST 提交
2. 处理成功后重定向（header('Location: ...')）
3. 通过 URL 参数显示成功提示
4. JS 自动清除 URL 参数，防止刷新重复显示

### 弹窗逻辑

- 社交链接：URL 为图片格式时点击显示弹窗
- 项目卡片：外链为图片格式或无外链时点击显示封面弹窗
- 支持 ESC 键、点击背景、点击×按钮关闭

## 更新日志

### 主要功能迭代

- **功能开关系统**：支持控制首页各模块显示
- **邮件通知**：新留言通知管理员，回复通知访客
- **PRG 模式**：修复表单重复提交问题
- **图片弹窗**：支持社交链接和项目图片放大查看
- **响应式布局**：适配移动端
- **后台重构**：分类菜单、操作按钮优化

## 常见问题

**Q: 如何修改默认管理员账号？**
A: 登录后台后，在"修改密码"页面修改。

**Q: 如何配置邮件通知？**
A: 进入后台 SMTP 设置，填写邮件服务器信息，开启"启用邮件通知"。

**Q: 如何隐藏某个模块？**
A: 进入后台"功能开关"，关闭对应模块的开关。

**Q: 如何添加本地图片？**
A: 将图片上传到 `/static/img/` 目录，填写路径如 `/static/img/your-image.jpg`。

**Q: 支持哪些图片格式？**
A: jpg, jpeg, png, gif, webp, svg, bmp, ico

## License

MIT License

Copyright (c) 2024

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
