<?php
require_once '../functions.php';
requireLogin();

$categories = getSkillCategories();
$skills = getSkills();
$success = '';
$error = '';

// 处理分类表单
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // 分类操作
    if ($action === 'add_category') {
        $stmt = db()->prepare("INSERT INTO category (name, sort_order) VALUES (?, ?)");
        if ($stmt->execute([$_POST['name'], intval($_POST['sort_order'] ?? 0)])) {
            header('Location: skill.php?msg=cat_added');
            exit;
        } else {
            $error = '添加失败';
        }
    } elseif ($action === 'edit_category') {
        $stmt = db()->prepare("UPDATE category SET name = ?, sort_order = ? WHERE id = ?");
        if ($stmt->execute([$_POST['name'], intval($_POST['sort_order'] ?? 0), $_POST['id']])) {
            header('Location: skill.php?msg=cat_updated');
            exit;
        } else {
            $error = '更新失败';
        }
    } elseif ($action === 'delete_category') {
        $stmt = db()->prepare("SELECT COUNT(*) as count FROM skill WHERE category_id = ?");
        $stmt->execute([$_POST['id']]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            header('Location: skill.php?error=cat_not_empty');
            exit;
        } else {
            $stmt = db()->prepare("DELETE FROM category WHERE id = ?");
            if ($stmt->execute([$_POST['id']])) {
                header('Location: skill.php?msg=cat_deleted');
                exit;
            } else {
                $error = '删除失败';
            }
        }
    }
    // 技能操作
    elseif ($action === 'add_skill') {
        $stmt = db()->prepare("INSERT INTO skill (category_id, name, icon, sort_order) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$_POST['category_id'], $_POST['name'], $_POST['icon'], intval($_POST['sort_order'] ?? 0)])) {
            header('Location: skill.php?msg=skill_added');
            exit;
        } else {
            $error = '添加失败';
        }
    } elseif ($action === 'edit_skill') {
        $stmt = db()->prepare("UPDATE skill SET category_id = ?, name = ?, icon = ?, sort_order = ? WHERE id = ?");
        if ($stmt->execute([$_POST['category_id'], $_POST['name'], $_POST['icon'], intval($_POST['sort_order'] ?? 0), $_POST['id']])) {
            header('Location: skill.php?msg=skill_updated');
            exit;
        } else {
            $error = '更新失败';
        }
    } elseif ($action === 'delete_skill') {
        $stmt = db()->prepare("DELETE FROM skill WHERE id = ?");
        if ($stmt->execute([$_POST['id']])) {
            header('Location: skill.php?msg=skill_deleted');
            exit;
        } else {
            $error = '删除失败';
        }
    }
}

// 检查URL参数
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'cat_added': $success = '分类已添加'; break;
        case 'cat_updated': $success = '分类已更新'; break;
        case 'cat_deleted': $success = '分类已删除'; break;
        case 'skill_added': $success = '技能已添加'; break;
        case 'skill_updated': $success = '技能已更新'; break;
        case 'skill_deleted': $success = '技能已删除'; break;
    }
}
if (isset($_GET['error']) && $_GET['error'] === 'cat_not_empty') {
    $error = '该分类下还有技能，请先删除技能';
}

// 按分类分组技能
$skillsByCat = [];
foreach ($skills as $skill) {
    $skillsByCat[$skill['category_name']][] = $skill;
}

$activeMenu = 'skill';
$title = '技能管理';
include 'header.php';
?>

<div class="page-header">
    <h2>技能管理</h2>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- 分类管理卡片 -->
<div class="card">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
        <span>分类管理</span>
        <button class="btn btn-secondary" onclick="openCategoryModal('add')">添加分类</button>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th>名称</th>
                <th>排序</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                <td><?php echo $cat['sort_order']; ?></td>
                <td>
                    <button onclick="editCategory(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>', <?php echo $cat['sort_order']; ?>)" class="btn btn-small btn-secondary">编辑</button>
                    <form method="POST" style="display:inline;" onsubmit="return confirm('确定删除? 请先确保分类下没有技能')">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                        <button type="submit" class="btn btn-small btn-danger">删除</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- 技能管理卡片 -->
<div class="card">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
        <span>技能列表</span>
        <button class="btn btn-primary" onclick="openSkillModal('add')">添加技能</button>
    </div>
    <?php foreach ($skillsByCat as $catName => $catSkills): ?>
    <div class="category-section">
        <div class="category-title"><?php echo htmlspecialchars($catName); ?></div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>图标</th>
                    <th>名称</th>
                    <th>排序</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($catSkills as $skill): ?>
                <tr>
                    <td><img src="<?php echo htmlspecialchars($skill['icon']); ?>" class="skill-icon" alt=""></td>
                    <td><?php echo htmlspecialchars($skill['name']); ?></td>
                    <td><?php echo $skill['sort_order']; ?></td>
                    <td>
                        <button onclick="editSkill(<?php echo $skill['id']; ?>, '<?php echo htmlspecialchars($skill['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($skill['icon'] ?? '', ENT_QUOTES); ?>', <?php echo $skill['category_id']; ?>, <?php echo $skill['sort_order']; ?>)" class="btn btn-small btn-secondary">编辑</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('确定删除?')">
                            <input type="hidden" name="action" value="delete_skill">
                            <input type="hidden" name="id" value="<?php echo $skill['id']; ?>">
                            <button type="submit" class="btn btn-small btn-danger">删除</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endforeach; ?>
</div>

<!-- 分类弹窗 -->
<div id="categoryModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="categoryModalTitle">添加分类</h3>
            <span class="close" onclick="closeCategoryModal()">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" id="categoryFormAction" value="add_category">
            <input type="hidden" name="id" id="categoryId" value="">
            <div class="form-group">
                <label>分类名称</label>
                <input type="text" name="name" id="categoryName" required>
            </div>
            <div class="form-group">
                <label>排序</label>
                <input type="number" name="sort_order" id="categoryOrder" value="0">
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
</div>

<!-- 技能弹窗 -->
<div id="skillModal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="skillModalTitle">添加技能</h3>
            <span class="close" onclick="closeSkillModal()">&times;</span>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="action" id="skillFormAction" value="add_skill">
            <input type="hidden" name="id" id="skillId" value="">
            <div class="form-group">
                <label>所属分类</label>
                <select name="category_id" id="skillCategoryId" required>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>技能名称</label>
                <input type="text" name="name" id="skillName" required>
            </div>
            <div class="form-group">
                <label>图标 URL</label>
                <input type="text" name="icon" id="skillIcon" placeholder="https://cdn.simpleicons.org/... 或 /img/icon.svg">
                <small class="form-hint">支持本地路径如 /img/icon.svg 或完整 URL</small>
            </div>
            <div class="form-group">
                <label>排序</label>
                <input type="number" name="sort_order" id="skillOrder" value="0">
            </div>
            <button type="submit" class="btn btn-primary">保存</button>
        </form>
    </div>
</div>

<script>
// 分类弹窗
function openCategoryModal(type) {
    document.getElementById('categoryModal').style.display = 'block';
    if (type === 'add') {
        document.getElementById('categoryModalTitle').textContent = '添加分类';
        document.getElementById('categoryFormAction').value = 'add_category';
        document.getElementById('categoryId').value = '';
        document.getElementById('categoryName').value = '';
        document.getElementById('categoryOrder').value = '0';
    }
}

function editCategory(id, name, order) {
    openCategoryModal('edit');
    document.getElementById('categoryModalTitle').textContent = '编辑分类';
    document.getElementById('categoryFormAction').value = 'edit_category';
    document.getElementById('categoryId').value = id;
    document.getElementById('categoryName').value = name;
    document.getElementById('categoryOrder').value = order;
}

function closeCategoryModal() {
    document.getElementById('categoryModal').style.display = 'none';
}

// 技能弹窗
function openSkillModal(type) {
    document.getElementById('skillModal').style.display = 'block';
    if (type === 'add') {
        document.getElementById('skillModalTitle').textContent = '添加技能';
        document.getElementById('skillFormAction').value = 'add_skill';
        document.getElementById('skillId').value = '';
        document.getElementById('skillName').value = '';
        document.getElementById('skillIcon').value = '';
        document.getElementById('skillOrder').value = '0';
    }
}

function editSkill(id, name, icon, catId, order) {
    openSkillModal('edit');
    document.getElementById('skillModalTitle').textContent = '编辑技能';
    document.getElementById('skillFormAction').value = 'edit_skill';
    document.getElementById('skillId').value = id;
    document.getElementById('skillName').value = name;
    document.getElementById('skillIcon').value = icon;
    document.getElementById('skillCategoryId').value = catId;
    document.getElementById('skillOrder').value = order;
}

function closeSkillModal() {
    document.getElementById('skillModal').style.display = 'none';
}

// 点击弹窗外部关闭
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

<?php include 'footer.php'; ?>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}
.page-header h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
}
.header-actions {
    display: flex;
    gap: 10px;
}
</style>
