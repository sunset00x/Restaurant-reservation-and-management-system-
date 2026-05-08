<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    mysqli_query($conn, "DELETE FROM menu_items WHERE id = $id");
    header("Location: login.php");
}

$result = mysqli_query($conn, "SELECT * FROM menu_items ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; }
        nav { background: #333; padding: 15px; text-align: center; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
        .container { max-width: 1000px; margin: 30px auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        img { width: 50px; height: 50px; object-fit: cover; }
        .logout { float: right; color: #e74c3c !important; }
    </style>
</head>
<body>

<nav>
    <a href="index.php">View Menu</a>
    <a href="add_item.php">Add Item</a>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_reservation.php">Reservations</a>
    <a href="logout.php" class="logout">Logout</a>
</nav>

<div class="container">
    <h2>Manage Menu</h2>
    <table>
        <tr>
            <th>Image</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><img src="uploads/<?php echo $row['image_path']; ?>"></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['category']; ?></td>
            <td>$<?php echo number_format($row['price'], 2); ?></td>
            <td><a href="admin_dashboard.php?delete_id=<?php echo $row['id']; ?>" style="color:red;">Delete</a></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>