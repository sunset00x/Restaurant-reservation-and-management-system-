<?php
include 'db.php'; 

$message = "";

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $category = $_POST['category'];

    $image_name = $_FILES['image']['name'];
    $temp_name = $_FILES['image']['tmp_name'];
    $folder = "uploads/" . $image_name;

    if (!is_dir('uploads')) {
        mkdir('uploads');
    }

    $sql = "INSERT INTO menu_items (name, price, description, category, image_path) 
            VALUES ('$name', '$price', '$description', '$category', '$image_name')";

    if (mysqli_query($conn, $sql)) {
        if (move_uploaded_file($temp_name, $folder)) {
            $message = "Success: Item added to menu!";
        } else {
            $message = "Error: Image upload failed.";
        }
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Menu Item</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        nav { background: #333; padding: 15px; text-align: center; }
        nav a { color: white; margin: 0 15px; text-decoration: none; font-weight: bold; }
        .form-container { background: white; padding: 30px; border-radius: 8px; width: 350px; margin: 50px auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; }
        input, textarea, select { width: 100%; margin-bottom: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #27ae60; color: white; border: none; padding: 12px; width: 100%; border-radius: 4px; cursor: pointer; font-size: 16px; }
    </style>
</head>
<body>

<nav>
    <a href="index.php">Home</a>
    <a href="add_item.php">Add Item</a>
    <a href="admin_dashboard.php">Dashboard</a>
     <a href="admin_reservation.php">Reservations</a>
</nav>

<div class="form-container">
    <h2>Add New Item</h2>
    <?php if($message != "") echo "<p style='color:green; text-align:center;'>$message</p>"; ?>
    
    <form action="add_item.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Item Name" required>
        <input type="number" step="0.01" name="price" placeholder="Price" required>
        <textarea name="description" placeholder="Description"></textarea>
        
        <select name="category">
            <option value="Breakfast">Breakfast</option>
            <option value="Lunch">Lunch</option>
            <option value="Dinner">Dinner</option>
            <option value="Dessert">Dessert</option>
            <option value="Drinks">Drink</option>
        </select>

        <label style="font-size: 14px; color: #666;">Upload Image:</label>
        <input type="file" name="image" required>
        
        <button type="submit" name="submit">Add to Menu</button>
    </form>
</div>

</body>
</html>