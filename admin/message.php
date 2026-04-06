<?php
require_once '../functions.php';
requireLogin();

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 20;

$action = $_POST['action'] ?? '';
$id = intval($_POST['id'] ?? 0);
$replyContent = $_POST['reply_content'] ?? '';

$success = '';
$error = '';

if ($action === 'reply' && $id && $replyContent) {
    if (replyMessage($id, $replyContent)) {
        header('Location: message.php?msg=reply_sent');
        exit;
    } else {
        header('Location: message.php?msg=reply_failed');
        exit;
    }
} elseif ($action === 'delete' && $id) {
    if (deleteMessage($id)) {
        header('Location: message.php?msg=deleted');
        exit;
    } else {
        header('Location: message.php?msg=delete_failed');
        exit;
    }
} elseif ($action === 'mark_read' && $id) {
    markMessageAsRead($id);
    header('Location: message.php?msg=marked_read');
    exit;
}

// 检查URL参数
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'reply_sent': $success = '回复已发送，邮件通知已发送给访客'; break;
        case 'reply_failed': $error = '回复失败'; break;
        case 'deleted': $success = '留言已删除'; break;
        case 'delete_failed': $error = '删除失败'; break;
        case 'marked_read': $success = '留言已标记为已读'; break;
    }
}

$messages = getAllMessages($page, $perPage);
$total = getMessageCount();
$unread = getUnreadMessageCount();
$totalPages = ceil($total / $perPage);

$title = '留言管理';
$activeMenu = 'message';

include 'header.php';
?>

<h1 class="page-title">
    <span>留言管理</span>
    <?php if ($unread > 0): ?>
    <span class="badge" style="background: #ff3b30; color: white; font-size: 12px; padding: 2px 8px; border-radius: 10px; margin-left: 8px;"><?php echo $unread; ?> 未读</span>
    <?php endif; ?>
</h1>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>
<?php if ($error): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title">留言列表 (共 <?php echo $total; ?> 条)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th>访客信息</th>
                <th style="width: 45%;">留言内容</th>
                <th>时间</th>
                <th>状态</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $msg): ?>
            <tr class="<?php echo $msg['status'] === 'unread' ? 'unread-row' : ''; ?>">
                <td><?php echo $msg['id']; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($msg['nickname']); ?></strong><br>
                    <small style="color: var(--gray-500);"><?php echo htmlspecialchars($msg['email']); ?></small>
                </td>
                <td>
                    <div class="message-content"><?php echo nl2br(htmlspecialchars($msg['content'])); ?></div>
                    <?php if ($msg['reply_content']): ?>
                    <div class="reply-box" style="margin-top: 12px; padding: 12px; background: #f0f9ff; border-left: 3px solid #34c759; border-radius: 6px;">
                        <strong style="color: #34c759;">回复：</strong>
                        <?php echo nl2br(htmlspecialchars($msg['reply_content'])); ?>
                        <small style="color: var(--gray-400); display: block; margin-top: 4px;">
                            <?php echo $msg['reply_time']; ?>
                        </small>
                    </div>
                    <?php endif; ?>
                </td>
                <td>
                    <?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?>
                </td>
                <td>
                    <?php if ($msg['status'] === 'unread'): ?>
                    <span class="status-badge" style="background: #ff3b30;">未读</span>
                    <?php elseif ($msg['status'] === 'read'): ?>
                    <span class="status-badge" style="background: #ff9500;">已读</span>
                    <?php else: ?>
                    <span class="status-badge status-active">已回复</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <?php if ($msg['status'] === 'unread'): ?>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="btn btn-small btn-secondary">标为已读</button>
                        </form>
                        <?php endif; ?>
                        <?php if (!$msg['reply_content']): ?>
                        <button onclick="openReplyModal(<?php echo $msg['id']; ?>, '<?php echo htmlspecialchars($msg['nickname'], ENT_QUOTES); ?>')" class="btn btn-small btn-primary">回复</button>
                        <?php endif; ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('确定删除这条留言？')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                            <button type="submit" class="btn btn-small btn-danger">删除</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 8px;">
        <?php if ($page > 1): ?>
        <a href="?page=<?php echo $page - 1; ?>" class="btn btn-small btn-secondary">上一页</a>
        <?php endif; ?>
        <span style="line-height: 32px; color: var(--gray-500);">第 <?php echo $page; ?> / <?php echo $totalPages; ?> 页</span>
        <?php if ($page < $totalPages): ?>
        <a href="?page=<?php echo $page + 1; ?>" class="btn btn-small btn-secondary">下一页</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- 回复弹窗 -->
<div id="replyModal" class="modal" style="display:none;">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h3>回复留言 - <span id="replyToName"></span></h3>
            <span class="close" onclick="closeReplyModal()">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" value="reply">
            <input type="hidden" name="id" id="replyMessageId">
            <div class="form-group">
                <label>回复内容</label>
                <textarea name="reply_content" rows="5" required placeholder="请输入回复内容..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">发送回复</button>
        </form>
    </div>
</div>

<style>
.unread-row {
    background: #fff5f5;
}
.unread-row td {
    font-weight: 500;
}
.message-content {
    max-height: 100px;
    overflow-y: auto;
    line-height: 1.6;
}
</style>

<script>
function openReplyModal(id, name) {
    document.getElementById('replyMessageId').value = id;
    document.getElementById('replyToName').textContent = name;
    document.getElementById('replyModal').style.display = 'block';
}

function closeReplyModal() {
    document.getElementById('replyModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.id === 'replyModal') {
        closeReplyModal();
    }
}
</script>

<?php include 'footer.php'; ?>
