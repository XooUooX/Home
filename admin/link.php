<?php
require_once '../functions.php';
requireLogin();

$socialLinks = getSocialLinks();
$allLinks = db()->query("SELECT * FROM link ORDER BY sort_order")->fetchAll();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_link') {
        $stmt = db()->prepare("INSERT INTO link (name, url, icon, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$_POST['name'], $_POST['url'], $_POST['icon'], intval($_POST['sort_order'] ?? 0), isset($_POST['is_active']) ? 1 : 0])) {
            header('Location: link.php?msg=added');
            exit;
        } else {
            $error = '添加失败';
        }
    } elseif ($action === 'edit_link') {
        $stmt = db()->prepare("UPDATE link SET name = ?, url = ?, icon = ?, sort_order = ?, is_active = ? WHERE id = ?");
        if ($stmt->execute([$_POST['name'], $_POST['url'], $_POST['icon'], intval($_POST['sort_order'] ?? 0), isset($_POST['is_active']) ? 1 : 0, $_POST['id']])) {
            header('Location: link.php?msg=updated');
            exit;
        } else {
            $error = '更新失败';
        }
    } elseif ($action === 'delete_link') {
        $stmt = db()->prepare("DELETE FROM link WHERE id = ?");
        if ($stmt->execute([$_POST['id']])) {
            header('Location: link.php?msg=deleted');
            exit;
        } else {
            $error = '删除失败';
        }
    }
}

// 检查URL参数
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added': $success = '链接已添加'; break;
        case 'updated': $success = '链接已更新'; break;
        case 'deleted': $success = '链接已删除'; break;
    }
}

$title = '社交链接';
$activeMenu = 'link';
include 'header.php';
?>

<div class="page-header">
    <h2>社交链接</h2>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
        <span>链接列表</span>
        <button class="btn btn-primary" onclick="openModal('add')">添加链接</button>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>图标</th>
                <th>名称</th>
                <th>链接</th>
                <th>排序</th>
                <th>状态</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allLinks as $link): ?>
            <tr>
                <td><img src="<?php echo htmlspecialchars($link['icon']); ?>" style="width: 24px; height: 24px;" alt=""></td>
                <td><?php echo htmlspecialchars($link['name']); ?></td>
                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($link['url']); ?></td>
                <td><?php echo $link['sort_order']; ?></td>
                <td>
                    <?php if ($link['is_active']): ?>
                    <span class="status-badge status-active">启用</span>
                    <?php else: ?>
                    <span class="status-badge status-inactive">禁用</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button onclick="editLink(<?php echo $link['id']; ?>, '<?php echo htmlspecialchars($link['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($link['url'] ?? '', ENT_QUOTES); ?>', '<?php echo htmlspecialchars($link['icon'] ?? '', ENT_QUOTES); ?>', <?php echo $link['sort_order']; ?>, <?php echo $link['is_active']; ?>)" class="btn btn-small btn-secondary">编辑</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('确定删除?')">
                        <input type="hidden" name="action" value="delete_link">
                        <input type="hidden" name="id" value="<?php echo $link['id']; ?>">
                        <button type="submit" class="btn btn-small btn-danger">删除</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="linkModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">添加链接</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" id="formAction" value="add_link">
            <input type="hidden" name="id" id="linkId" value="">
            <div class="form-row">
                <div class="form-group">
                    <label>名称</label>
                    <input type="text" name="name" id="linkName" required placeholder="如: GitHub">
                </div>
                <div class="form-group">
                    <label>排序</label>
                    <input type="number" name="sort_order" id="linkOrder" value="0">
                </div>
            </div>
            <div class="form-group">
                <label>链接 URL</label>
                <input type="text" name="url" id="linkUrl" placeholder="https://github.com/yourname">
            </div>
            <div class="form-group">
                <label>图标 URL</label>
                <input type="text" name="icon" id="linkIcon" placeholder="https://example.com/image.png 或 /img/project.jpg">
                <small class="form-hint">支持本地路径如 /img/icon.svg 或完整 URL</small>
            </div>
            <div class="form-group">
                <label>启用此链接</label>
                <div class="toggle-switch">
                    <input type="checkbox" name="is_active" id="linkActive" checked>
                    <label for="linkActive" class="toggle-checkbox"></label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
</div>

<script>
function openModal(type) {
    document.getElementById('linkModal').style.display = 'block';
    if (type === 'add') {
        document.getElementById('modalTitle').textContent = '添加链接';
        document.getElementById('formAction').value = 'add_link';
        document.getElementById('linkId').value = '';
        document.getElementById('linkName').value = '';
        document.getElementById('linkUrl').value = '';
        document.getElementById('linkIcon').value = '';
        document.getElementById('linkOrder').value = '0';
        document.getElementById('linkActive').checked = true;
    }
}

function editLink(id, name, url, icon, order, active) {
    document.getElementById('linkModal').style.display = 'block';
    document.getElementById('modalTitle').textContent = '编辑链接';
    document.getElementById('formAction').value = 'edit_link';
    document.getElementById('linkId').value = id;
    document.getElementById('linkName').value = name;
    document.getElementById('linkUrl').value = url;
    document.getElementById('linkIcon').value = icon;
    document.getElementById('linkOrder').value = order;
    document.getElementById('linkActive').checked = active == 1;
}

function closeModal() {
    document.getElementById('linkModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include 'footer.php'; ?>
