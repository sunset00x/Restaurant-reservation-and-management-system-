<?php
session_start();
include 'db.php'; // Ensure your db.php connection is correct

$error = "";

// --- 1. LOGIN LOGIC ---
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === "staff" && $password === "pass123") {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Invalid admin credentials.";
    }   
}

// --- 2. LOGOUT LOGIC ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
// --- 3. DELETE LOGIC ---
if (isset($_SESSION['admin_logged_in']) && isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM reservations WHERE id = $id");
    header("Location: admin_reservation.php?status=deleted");
    exit();
}

// --- 4. FETCH DATA ---
$result = mysqli_query($conn, "SELECT * FROM reservations ORDER BY STR_TO_DATE(res_date, '%Y-%m-%d') DESC, res_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>budhigangaROCKS | Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --accent: #e67e22; --dark: #2c3e50; --bg: #f4f7f6; }
        body { font-family: 'Montserrat', sans-serif; background: var(--bg); margin: 0; color: #333; }
        
        /* Login Screen Styles */
        .login-container { height: 100vh; display: flex; justify-content: center; align-items: center; background: var(--dark); }
        .login-box { background: white; padding: 40px; border-radius: 12px; width: 320px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-orange { background: var(--accent); color: white; border: none; padding: 12px; width: 100%; border-radius: 6px; cursor: pointer; font-weight: bold; }

        /* Dashboard Styles */
        .admin-nav { background: white; padding: 15px 5%; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .main-container { max-width: 1100px; margin: 40px auto; padding: 0 20px; }
        .stats-bar { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.03); }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        th { background: var(--dark); color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        .btn-del { color: #e74c3c; text-decoration: none; font-weight: bold; }
        .status-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['admin_logged_in'])): ?>
    <div class="login-container">
        <div class="login-box">
            <h2 style="font-family: serif; margin-bottom: 20px;">budhigangaROCKS</h2>
            <?php if($error) echo "<p style='color:red; font-size:0.8rem;'>$error</p>"; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login" class="btn-orange">Enter Admin Panel</button>
            </form>
            <p style="font-size: 0.7rem; color: #888; margin-top: 20px;">Secured Admin Access Only</p>
        </div>
    </div>

<?php else: ?>
    <nav class="admin-nav">
        <h2 style="margin:0; color:var(--dark);">Reservation Manager</h2>
        <div>
            <a href="index.php" style="margin-right:20px; text-decoration:none; color:#666;">View Site</a>
            <a href="index.php" style="color:var(--accent); font-weight:bold; text-decoration:none;">Logout</a>
        </div>
    </nav>

    <div class="main-container">
        <?php if(isset($_GET['status'])) echo "<div class='status-msg'>Reservation successfully removed.</div>"; ?>

        <div class="stats-bar">
            <div class="stat-card">
                <h3 style="margin:0; font-size:0.8rem; color:#888;">TOTAL BOOKINGS</h3>
                <p style="font-size:2rem; margin:10px 0; font-weight:bold;"><?php echo mysqli_num_rows($result); ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Schedule</th>
                    <th>Guest Information</th>
                    <th>Party Size</th>
                    <th>Table</th>
                    <th>Special Request</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td>
                            <strong><?php echo date('M d, Y', strtotime($row['res_date'])); ?></strong><br>
                            <span style="color:#777;"><?php echo date('h:i A', strtotime($row['res_time'])); ?></span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['guest_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($row['guest_email']); ?></small>
                        </td>
                        <td><span style="background:#e1f5fe; color:#0288d1; padding:4px 8px; border-radius:4px; font-weight:bold;"><?php echo $row['guest_count']; ?> Guests</span></td>
                        <td><?php echo !empty($row['table_number']) ? htmlspecialchars($row['table_number']) : 'N/A'; ?></td>
                        <td><i style="color:#888; font-size:0.8rem;"><?php echo $row['special_request'] ? htmlspecialchars($row['special_request']) : 'None'; ?></i></td>
                        <td>
                            <a href="admin_reservation.php?delete=<?php echo $row['id']; ?>" class="btn-del" onclick="return confirm('Cancel this booking?')">Cancel</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:40px; color:#999;">No reservations found yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

</body>
</html>