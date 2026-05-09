<?php
$host = "localhost";
$user = "root"; 
$pass = ""; 
$dbname = "restaurant_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

function ensureMenuItemsCategoryColumn($conn) {
    $columnResult = mysqli_query($conn, "SHOW COLUMNS FROM menu_items LIKE 'category'");
    if ($columnResult && mysqli_num_rows($columnResult) > 0) {
        $column = mysqli_fetch_assoc($columnResult);
        $type = strtolower($column['Type']);
        if (str_starts_with($type, 'enum(') || str_starts_with($type, 'set(')) {
            mysqli_query($conn, "ALTER TABLE menu_items MODIFY category VARCHAR(100) NOT NULL DEFAULT ''");
        }
    } else {
        mysqli_query($conn, "ALTER TABLE menu_items ADD COLUMN category VARCHAR(100) NOT NULL DEFAULT '' AFTER description");
    }
}

ensureMenuItemsCategoryColumn($conn);
?>