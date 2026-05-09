<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

function ensureCategoriesTableExists($conn) {
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories");
    $row = mysqli_fetch_assoc($result);
    if ((int)$row['total'] === 0) {
        $defaultCategories = ['Breakfast', 'Lunch', 'Dinner', 'Dessert', 'Drinks'];
        foreach ($defaultCategories as $name) {
            $safeName = mysqli_real_escape_string($conn, $name);
            mysqli_query($conn, "INSERT IGNORE INTO categories (name) VALUES ('$safeName')");
        }
    }
}

ensureCategoriesTableExists($conn);

$message = isset($_SESSION['category_message']) ? $_SESSION['category_message'] : '';
if (isset($_SESSION['category_message'])) {
    unset($_SESSION['category_message']);
}
$editCategory = null;

if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
    $_SESSION['category_message'] = 'Category deleted successfully.';
    header("Location: admin_categories.php");
    exit();
}

if (isset($_GET['edit_id'])) {
    $id = (int)$_GET['edit_id'];
    $result = mysqli_query($conn, "SELECT * FROM categories WHERE id = $id");
    if ($result && mysqli_num_rows($result) > 0) {
        $editCategory = mysqli_fetch_assoc($result);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') {
        $message = 'Category name cannot be empty.';
    } else {
        $safeName = mysqli_real_escape_string($conn, $name);
        if (!empty($_POST['id'])) {
            $id = (int)$_POST['id'];
            $sql = "UPDATE categories SET name = '$safeName' WHERE id = $id";
            $_SESSION['category_message'] = mysqli_query($conn, $sql) ? 'Category updated successfully.' : 'Error: ' . mysqli_error($conn);
        } else {
            $sql = "INSERT INTO categories (name) VALUES ('$safeName')";
            $_SESSION['category_message'] = mysqli_query($conn, $sql) ? 'Category added successfully.' : 'Error: ' . mysqli_error($conn);
        }
        header('Location: admin_categories.php');
        exit();
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <style>
        body { font-family: Inter, system-ui, sans-serif; background: #eef2ff; margin: 0; color: #0f172a; }
        nav { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; padding: 18px 32px; background: #1e293b; }
        nav a { color: white; text-decoration: none; padding: 10px 16px; border-radius: 999px; background: rgba(255,255,255,0.08); }
        nav a:hover { background: rgba(255,255,255,0.16); }
        .page { width: min(1100px, 100%); margin: 24px auto; padding: 0 20px; }
        .panel { background: white; border: 1px solid #e2e8f0; border-radius: 24px; padding: 30px; box-shadow: 0 30px 60px rgba(15, 23, 42, 0.08); }
        h1 { margin: 0 0 12px; font-size: clamp(2rem, 2.5vw, 2.4rem); }
        .intro { margin: 0 0 26px; color: #475569; line-height: 1.7; }
        .message { margin-bottom: 20px; padding: 16px; border-radius: 16px; background: #d1fae5; color: #065f46; }
        .form-grid { display: grid; gap: 18px; margin-bottom: 32px; }
        label { display: block; margin-bottom: 8px; font-weight: 700; }
        input[type=text] { width: 100%; padding: 14px 16px; border: 1px solid #cbd5e1; border-radius: 14px; font-size: 1rem; }
        button { border: none; background: #2563eb; color: white; padding: 14px 20px; border-radius: 14px; font-weight: 700; cursor: pointer; }
        button.secondary { background: #c7d2fe; color: #1e3a8a; }
        .table-wrapper { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 520px; }
        th, td { padding: 16px 18px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { color: #64748b; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.08em; }
        td { color: #0f172a; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .actions a { display: inline-flex; align-items: center; gap: 6px; padding: 10px 14px; border-radius: 12px; background: #eff6ff; color: #1d4ed8; text-decoration: none; font-weight: 700; }
        .actions a.delete { background: #fee2e2; color: #b91c1c; }
        .actions a:hover { opacity: 0.92; }
        @media (max-width: 760px) {
            nav { flex-direction: column; align-items: stretch; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<nav>
    <div style="font-weight:700; color:#fff;">budhigangaROCKS Admin</div>
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="add_item.php">Add Item</a>
        <a href="admin_reservation.php">Reservations</a>
    </div>
</nav>

<div class="page">
    <div class="panel">
        <h1>Manage Food Categories</h1>
        <p class="intro">Add, edit, or remove categories that are used when creating menu items.</p>

        <?php if ($message !== ''): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST" class="form-grid">
            <div>
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" placeholder="e.g. Appetizer" value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : ''; ?>" required>
                <?php if ($editCategory): ?>
                    <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                <?php endif; ?>
            </div>
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                <button type="submit"><?php echo $editCategory ? 'Update Category' : 'Add Category'; ?></button>
                <?php if ($editCategory): ?>
                    <a href="admin_categories.php" class="secondary">Cancel</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Created At</th>
                        <th style="width:220px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cat['name']); ?></td>
                            <td><?php echo htmlspecialchars($cat['created_at']); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="admin_categories.php?edit_id=<?php echo $cat['id']; ?>">Edit</a>
                                    <a href="admin_categories.php?delete_id=<?php echo $cat['id']; ?>" class="delete" onclick="return confirm('Delete this category?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
