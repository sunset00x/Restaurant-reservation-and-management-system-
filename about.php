<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Story | budhigangaROCKS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: #d35400;
            --dark: #1a1a1a;
            --light: #fdfdfd;
            --gold: #c0a16b;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.8;
        }

        /* Hero Section */
        .story-hero {
            height: 60vh;
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            text-align: center;
            padding: 0 20px;
        }

        .story-hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            margin-bottom: 10px;
        }

        .tagline {
            font-size: 1.2rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--gold);
            font-weight: 300;
        }

        /* Content Section */
        .content-wrapper {
            max-width: 900px;
            margin: -100px auto 100px;
            background: white;
            padding: 80px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            border-radius: 4px;
            position: relative;
            z-index: 10;
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: var(--accent);
            margin: 20px auto;
        }

        .story-text {
            font-size: 1.1rem;
            color: #555;
            text-align: justify;
            margin-bottom: 30px;
        }

        .quote {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            font-size: 1.5rem;
            color: var(--dark);
            text-align: center;
            margin: 50px 0;
            padding: 20px;
            border-left: 5px solid var(--accent);
            background: #fff8f4;
        }

        .btn-back {
            display: inline-block;
            margin-top: 40px;
            padding: 12px 30px;
            background: var(--dark);
            color: white;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            transition: 0.3s;
            border-radius: 50px;
        }

        .btn-back:hover {
            background: var(--accent);
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .content-wrapper { padding: 40px 20px; margin-top: -50px; }
            .story-hero h1 { font-size: 2.5rem; }
        }
        
    </style>
</head>
<body>

    <section class="story-hero">
        <div class="tagline">budhigangaROCKS</div>
        <h1>Our Story</h1>
    </section>

    <div class="content-wrapper">
        <h2 class="section-title">MITHO pani sarai nai MITHO</h2>
        
        <p class="story-text">
            Inspired by the timeless strength of the Budhiganga river stones and the untamed spirit of local Himalayan flavors, <strong>budhigangaROCKS</strong> was born. We didn't just want to build a restaurant; we wanted to create a sanctuary where the "rock-solid" traditions of our ancestors meet the bold, vibrant energy of modern gastronomy.
        </p>

        <div class="quote">
            "BALEN SHAH ordered food from our cafe before joining RSP"
        </div>

        <p class="story-text">
            Every dish on our menu tells a tale of the landscape. From sun-ripened spices to hand-picked herbs, our ingredients are sourced from local farmers who share our reverence for quality. At <strong>budhigangaROCKS</strong>, we believe that food should be an adventure—a rhythmic harmony of textures and tastes that stays with you long after the final bite.
        </p>

        
        <div style="text-align: center;">
            <a href="index.php" class="btn-back">← EXPLORE THE MENU</a>
        </div>
    </div>

</body>
</html>