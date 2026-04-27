<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fresh Farm Egg - Home</title>
<link rel="stylesheet" href="assets/style.css">
<style>
    /* General Styles */
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f9fff7;
        color: #333;
    }

    a {
        text-decoration: none;
    }

    /* Hero Section */
    header {
        text-align: center;
        padding: 120px 20px;
        position: relative;
        color: white;
        /* Background image */
        background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('uploads/bg.png') no-repeat center center/cover;
        border-bottom: 4px solid #4CAF50;
    }

    header h1 {
        font-size: 3em;
        margin-bottom: 20px;
        font-weight: bold;
    }

    header p {
        font-size: 1.3em;
        max-width: 700px;
        margin: 0 auto 40px;
        font-weight: bold;
    }

    /* Buttons - stacked vertically */
    .btn-fill {
        display: block;
        width: 220px;
        margin: 10px auto;
        padding: 14px 0;
        border-radius: 8px;
        font-weight: bold;
        background-color: #4CAF50;
        color: white;
        text-align: center;
        font-size: 1em;
        transition: transform 0.2s, background-color 0.2s;
    }

    .btn-fill:hover {
        background-color: #45a049;
        transform: translateY(-2px);
    }

    /* Features Section */
    .features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        padding: 60px 20px;
        max-width: 1200px;
        margin: auto;
    }

    .feature-card {
        background-color: #ffffff;
        padding: 30px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 6px 12px rgba(0,0,0,0.08);
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.12);
    }

    .feature-card::before {
        content: "🥚";
        font-size: 50px;
        position: absolute;
        top: -25px;
        left: calc(50% - 25px);
        opacity: 0.1;
    }

    .feature-card h3 {
        margin-bottom: 15px;
        font-size: 1.5em;
        color: #4CAF50;
        font-weight: bold;
    }

    .feature-card p {
        font-size: 1em;
        color: #555;
    }

    /* About Section */
    .about {
        background-color: #e8f5e9;
        padding: 60px 20px;
        text-align: center;
    }

    .about h2 {
        font-size: 2em;
        margin-bottom: 30px;
        color: #2e7d32;
        font-weight: bold;
    }

    .about ul {
        list-style-type: disc;
        padding-left: 0;
        max-width: 600px;
        margin: auto;
        text-align: left;
        color: #555;
        line-height: 1.8;
    }

    /* Footer */
    footer {
        background-color: #4CAF50;
        color: #fff;
        text-align: center;
        padding: 20px 0;
        margin-top: 40px;
    }

    @media (max-width: 768px) {
        header h1 {
            font-size: 2.2em;
        }

        header p {
            font-size: 1.1em;
        }
    }
</style>
</head>
<body>

<!-- Hero Header -->
<header>
    <h1>Right Choice Fresh Farm Egg</h1>
    <p>Manage your egg deliveries, inventory, and branch performance efficiently with our smart system.</p>
    <a href="admin/login.php" class="btn-fill">Admin Login</a>
    <a href="client/login.php" class="btn-fill">User Login</a>
    <a href="client/register.php" class="btn-fill">User Register</a>
</header>

<!-- Features Section -->
<section class="features">
    <div class="feature-card">
        <h3>📦 Real-Time Inventory</h3>
        <p>Monitor stock levels for big trays, small trays, and individual eggs across all branches in real-time.</p>
    </div>
    <div class="feature-card">
        <h3>🚚 Delivery Tracking</h3>
        <p>Track daily deliveries and ensure branches receive the correct stock on time with accurate records.</p>
    </div>
    <div class="feature-card">
        <h3>👥 Client Management</h3>
        <p>Branches can register clients to access dashboards, view deliveries, and stay updated on inventory.</p>
    </div>
    <div class="feature-card">
        <h3>📈 Business Insights</h3>
        <p>Analyze inventory trends and delivery performance to make informed business decisions easily.</p>
    </div>
</section>

<!-- About Section -->
<section class="about">
    <h2>Why Choose Fresh Farm Egg?</h2>
    <ul>
        <li>Track deliveries and inventory in real-time</li>
        <li>Reduce errors in stock management</li>
        <li>Generate reports and insights for better decisions</li>
        <li>Ensure smooth communication between branches and clients</li>
    </ul>
</section>

<!-- Footer -->
<footer>
    &copy; 2026 Fresh Farm Egg. All Rights Reserved.
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>