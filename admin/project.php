<?php
require_once '../functions.php';
requireLogin();

$projects = db()->query("SELECT * FROM project ORDER BY sort_order")->fetchAll();
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_project') {
        // 提取 GitHub repo 从 URL
        $githubRepo = extractGithubRepo($_POST['url'] ?? '');
        $githubInfo = $githubRepo ? fetchGithubRepoInfo($githubRepo) : null;
        
        $stmt = db()->prepare("INSERT INTO project (title, description, image, url, github_repo, github_stars, github_forks, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$_POST['title'], $_POST['description'], $_POST['image'], $_POST['url'], $githubRepo, $githubInfo['stars'] ?? 0, $githubInfo['forks'] ?? 0, intval($_POST['sort_order'] ?? 0), isset($_POST['is_active']) ? 1 : 0])) {
            header('Location: project.php?msg=added');
            exit;
        } else {
            $error = '添加失败';
        }
    } elseif ($action === 'edit_project') {
        // 提取 GitHub repo 从 URL
        $githubRepo = extractGithubRepo($_POST['url'] ?? '');
        $githubInfo = $githubRepo ? fetchGithubRepoInfo($githubRepo) : null;
        
        $stmt = db()->prepare("UPDATE project SET title = ?, description = ?, image = ?, url = ?, github_repo = ?, github_stars = ?, github_forks = ?, sort_order = ?, is_active = ? WHERE id = ?");
        if ($stmt->execute([$_POST['title'], $_POST['description'], $_POST['image'], $_POST['url'], $githubRepo, $githubInfo['stars'] ?? 0, $githubInfo['forks'] ?? 0, intval($_POST['sort_order'] ?? 0), isset($_POST['is_active']) ? 1 : 0, $_POST['id']])) {
            header('Location: project.php?msg=updated');
            exit;
        } else {
            $error = '更新失败';
        }
    } elseif ($action === 'delete_project') {
        $stmt = db()->prepare("DELETE FROM project WHERE id = ?");
        if ($stmt->execute([$_POST['id']])) {
            header('Location: project.php?msg=deleted');
            exit;
        } else {
            $error = '删除失败';
        }
    }
}

// 检查URL参数
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'added': $success = '项目已添加'; break;
        case 'updated': $success = '项目已更新'; break;
        case 'deleted': $success = '项目已删除'; break;
    }
}

$title = '项目展示';
$activeMenu = 'project';
include 'header.php';
?>

<div class="page-header">
    <h2>项目展示</h2>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
        <span>项目列表</span>
        <button class="btn btn-primary" onclick="openModal('add')">添加项目</button>
    </div>
    <div class="project-grid">
        <?php foreach ($projects as $project): ?>
        <div class="project-card" data-project-id="<?php echo $project['id']; ?>">
            <?php if ($project['image']): ?>
            <img src="<?php echo htmlspecialchars($project['image']); ?>" alt="">
            <?php endif; ?>
            <div class="project-card-content">
                <div class="project-card-title"><?php echo htmlspecialchars($project['title']); ?></div>
                <div class="project-card-desc"><?php echo htmlspecialchars(mb_substr($project['description'] ?? '', 0, 100)) . (mb_strlen($project['description'] ?? '') > 100 ? '...' : ''); ?></div>
                <div class="project-card-footer">
                    <?php if ($project['is_active']): ?>
                    <span class="status-badge status-active">显示</span>
                    <?php else: ?>
                    <span class="status-badge status-inactive">隐藏</span>
                    <?php endif; ?>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn btn-small btn-secondary" onclick="editProject(<?php echo $project['id']; ?>)">编辑</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('确定删除?')">
                            <input type="hidden" name="action" value="delete_project">
                            <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                            <button type="submit" class="btn btn-small btn-danger">删除</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// 存储所有项目数据
const projectsData = <?php echo json_encode(array_map(function($p) {
    return [
        'id' => $p['id'],
        'title' => $p['title'],
        'description' => $p['description'] ?? '',
        'image' => $p['image'] ?? '',
        'url' => $p['url'] ?? '',
        'github_repo' => $p['github_repo'] ?? '',
        'github_stars' => $p['github_stars'] ?? 0,
        'github_forks' => $p['github_forks'] ?? 0,
        'sort_order' => $p['sort_order'],
        'is_active' => $p['is_active']
    ];
}, $projects), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;

function editProject(id) {
    const project = projectsData.find(p => p.id == id);
    if (!project) return;
    
    document.getElementById('projectModal').style.display = 'block';
    document.getElementById('modalTitle').textContent = '编辑项目';
    document.getElementById('formAction').value = 'edit_project';
    document.getElementById('projectId').value = project.id;
    document.getElementById('projectTitle').value = project.title;
    document.getElementById('projectDesc').value = project.description;
    document.getElementById('projectImage').value = project.image;
    document.getElementById('projectUrl').value = project.url;
    document.getElementById('projectOrder').value = project.sort_order;
    document.getElementById('projectActive').checked = project.is_active == 1;
}

function openModal(type) {
    document.getElementById('projectModal').style.display = 'block';
    if (type === 'add') {
        document.getElementById('modalTitle').textContent = '添加项目';
        document.getElementById('formAction').value = 'add_project';
        document.getElementById('projectId').value = '';
        document.getElementById('projectTitle').value = '';
        document.getElementById('projectDesc').value = '';
        document.getElementById('projectImage').value = '';
        document.getElementById('projectUrl').value = '';
        document.getElementById('projectOrder').value = '0';
        document.getElementById('projectActive').checked = true;
    }
}

function closeModal() {
    document.getElementById('projectModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<div id="projectModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">添加项目</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" id="formAction" value="add_project">
            <input type="hidden" name="id" id="projectId" value="">
            <div class="form-group">
                <label>项目名称</label>
                <input type="text" name="title" id="projectTitle" required>
            </div>
            <div class="form-group">
                <label>项目描述</label>
                <textarea name="description" id="projectDesc" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>封面图片 URL</label>
                <input type="text" name="image" id="projectImage" placeholder="https://example.com/image.png 或 /img/project.jpg">
            </div>
            <div class="form-group">
                <label>项目链接 (留空则点击时弹窗显示图片)</label>
                <input type="text" name="url" id="projectUrl" placeholder="https://github.com/yourname/project">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>排序</label>
                    <input type="number" name="sort_order" id="projectOrder" value="0">
                </div>
                <div class="form-group">
                    <label>显示在首页</label>
                    <div class="toggle-switch">
                        <input type="checkbox" name="is_active" id="projectActive" checked>
                        <label for="projectActive" class="toggle-checkbox"></label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
