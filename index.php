<?php
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
$categoryList = mysqli_query($conn, "SELECT name FROM categories ORDER BY name");

// Get search and category filters from the URL
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

// Build the dynamic SQL query
$query = "SELECT * FROM menu_items WHERE 1=1";
if ($search != '') {
    $query .= " AND name LIKE '%$search%'";
}
if ($category != '') {
    $query .= " AND TRIM(LOWER(category)) = LOWER(TRIM('$category'))";
}
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>budhigangaROCKS</title>
    <!-- Google Fonts for a premium feel -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #d35400; /* Burnt Orange */
            --dark: #1a1a1a;
            --text: #333;
            --bg: #fdfdfd;
            --card-bg: #ffffff;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg);
            color: var(--text);
            scroll-behavior: smooth;
        }

        /* --- Modern Sticky Navbar --- */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: var(--dark);
            text-decoration: none;
            font-weight: 700;
        }

        .search-bar {
            display: flex;
            background: #f1f1f1;
            border-radius: 50px;
            padding: 5px 15px;
            width: 300px;
            transition: 0.3s;
        }

        .search-bar:focus-within {
            box-shadow: 0 0 0 2px var(--accent);
            background: #fff;
        }

        .search-bar input {
            border: none;
            background: transparent;
            outline: none;
            padding: 8px;
            flex: 1;
        }

        .search-bar button {
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 700;
            color: var(--accent);
        }

        /* --- Hero Section --- */
        .hero {
            height: 50vh;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #fff;
            text-align: center;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            margin: 0;
            letter-spacing: 2px;
        }

        /* --- Navigation / Filter Section --- */
        .menu-nav {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin: 3rem 0;
            flex-wrap: wrap;
        }

        .nav-pill {
            text-decoration: none;
            color: var(--text);
            font-weight: 700;
            padding: 12px 30px;
            border-radius: 50px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            transition: 0.3s ease;
            border: 1px solid #eee;
        }

        .nav-pill:hover, .nav-pill.active {
            background: var(--accent);
            color: #fff;
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(211, 84, 0, 0.3);
        }

        /* --- Menu Grid Layout --- */
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 0 20px 80px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2.5rem;
        }

        .food-card {
            background: var(--card-bg);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .food-card:hover {
            transform: translateY(-10px);
        }

        .food-img {
            width: 100%;
            height: 220px;
            object-fit: cover;
        }

        .food-info {
            padding: 1.5rem;
        }

        .category-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--accent);
            letter-spacing: 2px;
            font-weight: 700;
        }

        .food-info h3 {
            margin: 0.5rem 0;
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
        }

        .food-info p {
            font-size: 0.9rem;
            color: #777;
            line-height: 1.6;
        }

        .price-tag {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
        }

        .price {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--dark);
        }

        .btn-view {
            background: var(--dark);
            color: #fff;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar">
    <a href="index.php" class="logo">budhigangaROCKS</a>
    
    <div class="search-bar">
        <form action="index.php" method="GET" style="display:flex; width:100%;">
            <input type="text" name="search" placeholder="Find a dish..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">SEARCH</button>
        </form>
    </div>
</nav>

<!-- Hero Visual -->
<section class="hero">
    <h1>Experience Flavor</h1>
    <p>jati aayo teti aauna manlagne</p>
    
</section>

<div class="container">
    <!-- Menu Navbar / Filter Pills -->
    <div class="menu-nav">
        <a href="index.php" class="nav-pill <?php echo $category == '' ? 'active' : ''; ?>">All</a>
        <?php if ($categoryList && mysqli_num_rows($categoryList) > 0): ?>
            <?php while ($cat = mysqli_fetch_assoc($categoryList)): ?>
                <a href="index.php?category=<?php echo urlencode($cat['name']); ?>" class="nav-pill <?php echo $category == $cat['name'] ? 'active' : ''; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <!-- The Menu Grid -->
    <div class="grid">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
            <div class="food-card">
                <img src="uploads/<?php echo $row['image_path']; ?>" class="food-img" alt="Menu Item">
                <div class="food-info">
                    <span class="category-label"><?php echo $row['category']; ?></span>
                    <h3><?php echo $row['name']; ?></h3>
                    <p><?php echo $row['description']; ?></p>
                    <div class="price-tag">
                        <span class="price">$<?php echo number_format($row['price'], 2); ?></span>
                        <a href="#" class="btn-view">Order Now</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 100px 0;">
                <h2>No dishes found matching your criteria.</h2>
                <a href="index.php" style="color:var(--accent);">Clear all filters</a>
            </div>
        <?php endif; ?>
    </div>
</div>




<style>
    /* --- Modern Footer Styling --- */
    .site-footer {
        background-color: var(--dark);
        color: #ecf0f1;
        padding: 70px 5% 30px;
        margin-top: 50px;
    }

    .footer-container {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 40px;
    }

    .footer-logo {
        font-family: 'Playfair Display', serif;
        font-size: 2rem;
        color: var(--accent);
        margin-bottom: 20px;
        display: block;
        text-decoration: none;
    }

    .footer-section h4 {
        color: var(--white);
        margin-bottom: 25px;
        font-size: 1.1rem;
        position: relative;
    }

    .footer-section h4::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: -10px;
        width: 40px;
        height: 2px;
        background: var(--accent);
    }

    .footer-section ul {
        list-style: none;
        padding: 0;
    }

    .footer-section ul li {
        margin-bottom: 12px;
    }

    .footer-section ul li a {
        color: #bdc3c7;
        text-decoration: none;
        transition: 0.3s;
        font-size: 0.9rem;
    }

    .footer-section ul li a:hover {
        color: var(--accent);
        padding-left: 5px;
    }

    .social-links {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .social-icon {
        width: 35px;
        height: 35px;
        background: rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: white;
        text-decoration: none;
        transition: 0.3s;
    }

    .social-icon:hover {
        background: var(--accent);
        transform: translateY(-3px);
    }

    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-top: 50px;
        padding-top: 30px;
        text-align: center;
        font-size: 0.85rem;
        color: #7f8c8d;
    }

    /* Form within footer */
    .footer-newsletter input {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        padding: 10px;
        color: white;
        border-radius: 4px;
        width: 100%;
        margin-bottom: 10px;
    }

    .btn-footer {
        background: var(--accent);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        width: 100%;
    }
</style>

<footer class="site-footer">
    <div class="footer-container">
        <!-- Brand Section -->
        <div class="footer-section">
            <a href="index.php" class="footer-logo">budhigangaROCKS</a>
            <p style="line-height: 1.6; color: #bdc3c7; font-size: 0.9rem;">
               mitho pani sarai nai mitho - PRIMEMINISTER BALEN SHAH
        </p>
            <div class="social-links">
                <a href="#" class="social-icon">FB</a>
                <a href="#" class="social-icon">IG</a>
                <a href="#" class="social-icon">TW</a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-section">
            <h4>Explore</h4>
            <ul>
                <li><a href="index.php">Our Menu</a></li>
                <li><a href="reservations.php">Reservations</a></li>
                <li><a href="about.php">Our Story</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </div>




        
        <!-- Working Hours -->
        <div class="footer-section">
            <h4>Opening Hours</h4>
            <ul style="color: #bdc3c7; font-size: 0.9rem;">
                <li>Mon - Fri: 09:00 AM - 10:00 PM</li>
                <li>Sat - Sun: 10:00 AM - 11:00 PM</li>
                <li style="margin-top: 10px; color: var(--accent);">Happy Hours: Every Friday</li>
            </ul>
        </div>

        <!-- Newsletter -->
        <div class="footer-section">
            <h4>Newsletter</h4>
            <div class="footer-newsletter">
                <p style="font-size: 0.85rem; color: #bdc3c7; margin-bottom: 15px;">Get 10% off your next meal!</p>
                <form>
                    <input type="email" placeholder="Your Email Address">
                    <button type="submit" class="btn-footer">Subscribe</button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2026 budhigangaROCKS. All Rights Reserved. | <a href="login.php" style="color: #7f8c8d; text-decoration: none;">Admin Access</a></p>
    </div>
</footer>
</body>
</html>