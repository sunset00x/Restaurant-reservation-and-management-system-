<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$items_per_page = 8;
$offset = ($page - 1) * $items_per_page;
$search_safe = mysqli_real_escape_string($conn, $search);
$search_sql = '';
if ($search !== '') {
    $search_sql = "WHERE name LIKE '%$search_safe%' OR category LIKE '%$search_safe%' OR description LIKE '%$search_safe%'";
}

if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM menu_items WHERE id = $id");

    $redirectUrl = 'admin_dashboard.php';
    if ($search !== '') {
        $redirectUrl .= '?search=' . urlencode($search);
    }
    header("Location: $redirectUrl");
    exit();
}

$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM menu_items $search_sql");
$countRow = mysqli_fetch_assoc($countResult);
$totalItems = (int)$countRow['total'];
$totalPages = max(1, ceil($totalItems / $items_per_page));

$result = mysqli_query($conn, "SELECT * FROM menu_items $search_sql ORDER BY id DESC LIMIT $offset, $items_per_page");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        :root {
            --bg: #eef2ff;
            --surface: #ffffff;
            --accent: #2563eb;
            --accent-soft: #dbeafe;
            --text: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --danger: #dc2626;
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: Inter, system-ui, sans-serif; background: linear-gradient(180deg, #eff6ff 0%, var(--bg) 100%); color: var(--text); }
        nav { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; padding: 20px 32px; background: #1e293b; color: white; }
        .brand { font-size: 1.2rem; font-weight: 700; }
        .nav-links { display: flex; flex-wrap: wrap; gap: 12px; }
        .nav-links a { color: white; text-decoration: none; padding: 10px 16px; border-radius: 999px; background: rgba(255,255,255,0.08); transition: background 0.2s ease; }
        .nav-links a:hover { background: rgba(255,255,255,0.18); }
        .logout { background: #ef4444 !important; }
        .page { width: min(1180px, 100%); margin: 24px auto; padding: 0 20px; }
        .hero { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 20px; align-items: center; margin-bottom: 24px; }
        .hero-title { max-width: 680px; }
        .hero-title h1 { margin: 0; font-size: clamp(2rem, 3vw, 2.8rem); letter-spacing: -0.02em; }
        .hero-title p { margin: 14px 0 0; color: var(--muted); line-height: 1.75; }
        .search-panel { flex: 1 1 320px; display: grid; gap: 10px; }
        .search-panel form { display: flex; gap: 10px; }
        .search-panel input { width: 100%; padding: 14px 16px; border: 1px solid var(--border); border-radius: 14px; font-size: 0.95rem; }
        .search-panel button { border: none; background: var(--accent); color: white; padding: 14px 20px; border-radius: 14px; font-weight: 700; cursor: pointer; transition: background 0.2s ease; }
        .search-panel button:hover { background: #1d4ed8; }
        .panel { background: var(--surface); border: 1px solid var(--border); border-radius: 28px; padding: 26px; box-shadow: 0 30px 80px rgba(15, 23, 42, 0.08); }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 18px; margin-bottom: 22px; }
        .stat { background: #eff6ff; border: 1px solid rgba(59, 130, 246, 0.18); border-radius: 20px; padding: 18px 20px; }
        .stat small { color: var(--muted); }
        .stat strong { display: block; margin-top: 10px; font-size: 2rem; color: var(--accent); }
        table { width: 100%; border-collapse: separate; border-spacing: 0 14px; }
        th, td { text-align: left; padding: 18px 20px; }
        th { color: var(--muted); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; }
        td { background: #f8fafc; border: 1px solid #e2e8f0; }
        tr td:first-child { border-top-left-radius: 16px; border-bottom-left-radius: 16px; }
        tr td:last-child { border-top-right-radius: 16px; border-bottom-right-radius: 16px; }
        .food-row { display: flex; align-items: center; gap: 16px; }
        .food-image { width: 70px; height: 70px; object-fit: cover; border-radius: 20px; border: 1px solid var(--border); }
        .food-info { display: grid; gap: 6px; }
        .food-name { font-weight: 700; font-size: 1rem; margin: 0; }
        .food-desc { color: var(--muted); font-size: 0.95rem; }
        .badge { display: inline-flex; align-items: center; padding: 8px 12px; border-radius: 999px; background: rgba(37, 99, 235, 0.1); color: #1d4ed8; font-size: 0.85rem; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .actions a { text-decoration: none; padding: 10px 16px; border-radius: 14px; font-weight: 700; transition: transform 0.2s ease; }
        .actions .edit { background: #2563eb; color: white; }
        .actions .delete { background: rgba(248, 113, 113, 0.14); color: #b91c1c; }
        .actions a:hover { transform: translateY(-1px); }
        .pagination { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; margin-top: 22px; }
        .pagination a, .pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 44px; padding: 10px 14px; border-radius: 12px; border: 1px solid transparent; background: white; color: var(--text); text-decoration: none; }
        .pagination a:hover { border-color: #cbd5e1; }
        .pagination .active { background: var(--accent); color: white; border-color: var(--accent); }
        .pagination .disabled { opacity: 0.45; pointer-events: none; }
        @media (max-width: 820px) {
            nav { flex-direction: column; align-items: stretch; }
            .hero { flex-direction: column; }
            .search-panel form { flex-direction: column; }
            th, td { padding: 16px 12px; }
        }
    </style>
</head>
<body>

<nav>
    <div class="brand">budhigangaROCKS Admin</div>
    <div class="nav-links">
        <a href="index.php">View Menu</a>
        <a href="add_item.php">Add Item</a>
        <a href="admin_categories.php">Categories</a>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="admin_reservation.php">Reservations</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</nav>

<div class="page">
    <div class="hero">
        <div class="hero-title">
            <h1>Hello ADMIN</h1>
     
        </div>
        <div class="search-panel">
            <form method="GET" action="admin_dashboard.php">
                <input type="text" name="search" placeholder="Search food by name, category, or description" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="stats">
            <div class="stat">
                <small>Total menu items</small>
                <strong><?php echo $totalItems; ?></strong>
            </div>
            <div class="stat">
                <small>Current page</small>
                <strong><?php echo $page; ?> / <?php echo $totalPages; ?></strong>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Food</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <div class="food-row">
                                    <img class="food-image" src="uploads/<?php echo htmlspecialchars($row['image_path']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                    <div class="food-info">
                                        <p class="food-name"><?php echo htmlspecialchars($row['name']); ?></p>
                                        <p class="food-desc"><?php echo htmlspecialchars(strlen($row['description']) > 60 ? substr($row['description'], 0, 60) . '...' : $row['description']); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td>$<?php echo number_format($row['price'], 2); ?></td>
                            <td>
                                <div class="actions">
                                    <a href="edit_item.php?id=<?php echo $row['id']; ?>" class="edit">Edit</a>
                                    <a href="admin_dashboard.php?delete_id=<?php echo $row['id']; ?><?php echo $search !== '' ? '&search=' . urlencode($search) : ''; ?>" class="delete" onclick="return confirm('Delete this menu item?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 32px; color: var(--muted);">No menu items found matching your search.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php $queryParam = $search !== '' ? '&search=' . urlencode($search) : ''; ?>
            <a class="<?php echo $page <= 1 ? 'disabled' : ''; ?>" href="<?php echo $page > 1 ? 'admin_dashboard.php?page=' . ($page - 1) . $queryParam : '#'; ?>">Previous</a>
            <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                if ($start > 1) {
                    echo '<a href="admin_dashboard.php?page=1' . $queryParam . '">1</a>';
                    if ($start > 2) echo '<span>...</span>';
                }
                for ($i = $start; $i <= $end; $i++):
            ?>
                <a class="<?php echo $i === $page ? 'active' : ''; ?>" href="admin_dashboard.php?page=<?php echo $i . $queryParam; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            <?php
                if ($end < $totalPages) {
                    if ($end < $totalPages - 1) echo '<span>...</span>';
                    echo '<a href="admin_dashboard.php?page=' . $totalPages . $queryParam . '">' . $totalPages . '</a>';
                }
            ?>
            <a class="<?php echo $page >= $totalPages ? 'disabled' : ''; ?>" href="<?php echo $page < $totalPages ? 'admin_dashboard.php?page=' . ($page + 1) . $queryParam : '#'; ?>">Next</a>
        </div>
    </div>
</div>

</body>
</html>