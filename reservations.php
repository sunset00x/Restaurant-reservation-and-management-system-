<?php
include 'db.php'; // Ensure your database connection is here

$message = "";
$showForm = true;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize Data for Security
    $name = mysqli_real_escape_string($conn, $_POST['guest_name']);
    $email = mysqli_real_escape_string($conn, $_POST['guest_email']);
    $count = (int)$_POST['guest_count'];
    $date = mysqli_real_escape_string($conn, $_POST['res_date']);
    $time = mysqli_real_escape_string($conn, $_POST['res_time']);
    $request = mysqli_real_escape_string($conn, $_POST['special_request']);
    
    // 2. Generate a Unique Booking ID
    $booking_id = "BGR-" . strtoupper(substr(md5(time() . $name), 0, 6));

    // 3. Database Insertion
    $sql = "INSERT INTO reservations (guest_name, guest_email, guest_count, res_date, res_time, special_request) 
            VALUES ('$name', '$email', $count, '$date', '$time', '$request')";

    if (mysqli_query($conn, $sql)) {
        $showForm = false; // Hide the form on success
        
        // 4. The Virtual Email Receipt Layout
        $message = "
        <div class='email-receipt'>
            <div class='email-header'>
                <h2>budhigangaROCKS</h2>
                <p>Reservation Confirmed</p>
            </div>
            <div class='email-body'>
                <p>Hello <strong>$name</strong>,</p>
                <p>Your table is officially reserved! We've sent a copy of this confirmation to <strong>$email</strong>.</p>
                
                <div class='confirmation-details'>
                    <p><strong>Booking ID:</strong> <span class='id-text'>$booking_id</span></p>
                    <p><strong>Date:</strong> " . date('l, M d, Y', strtotime($date)) . "</p>
                    <p><strong>Time:</strong> " . date('h:i A', strtotime($time)) . "</p>
                    <p><strong>Guests:</strong> $count People</p>
                </div>

                <div class='request-box'>
                    <strong>Special Request:</strong><br>
                    " . ($request ? htmlspecialchars($request) : 'None') . "
                </div>

                <hr>
                <p style='font-size: 0.8rem; color: #777; text-align: center;'>
                    Please show this Booking ID to our staff upon arrival.
                </p>
                
                <div class='action-buttons'>
                    <button onclick='window.print()' class='btn-secondary'>Print Receipt</button>
                    <a href='index.php' class='btn-primary'>Return to Home</a>
                </div>
            </div>
        </div>";
    } else {
        // Display database error to help you debug (like in image_693a74.png)
        $message = "<div class='error-alert'><strong>System Error:</strong> " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reserve a Table | budhigangaROCKS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root { --accent: #e67e22; --dark: #2c3e50; --light: #f4f7f6; }
        body { font-family: 'Montserrat', sans-serif; background: var(--light); margin: 0; padding: 20px; }
        
        .container { max-width: 600px; margin: 40px auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        h1 { text-align: center; color: var(--dark); margin-bottom: 30px; }
        
        /* Form Styles */
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: bold; font-size: 0.85rem; color: #555; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: inherit; }
        .btn-submit { background: var(--dark); color: white; border: none; padding: 15px; width: 100%; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s; }
        .btn-submit:hover { background: var(--accent); }

        /* Email Receipt Styles */
        .email-receipt { border: 1px solid #eee; border-radius: 10px; overflow: hidden; animation: slideIn 0.5s ease-out; }
        .email-header { background: var(--dark); color: white; padding: 20px; text-align: center; }
        .email-body { padding: 30px; line-height: 1.6; }
        .confirmation-details { background: #fdf2e9; border: 2px dashed var(--accent); padding: 15px; border-radius: 8px; margin: 20px 0; }
        .id-text { color: var(--accent); font-weight: bold; font-size: 1.2rem; }
        .request-box { font-size: 0.85rem; color: #666; background: #f9f9f9; padding: 10px; border-radius: 5px; }
        .action-buttons { margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .btn-primary { background: var(--accent); color: white; padding: 12px; border-radius: 5px; text-decoration: none; text-align: center; font-weight: bold; }
        .btn-secondary { background: #eee; border: none; padding: 12px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        
        .error-alert { background: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; border: 1px solid #f87171; text-align: center; }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Print-only styles */
        @media print {
            .btn-primary, .btn-secondary { display: none; }
            body { background: white; }
            .container { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <?php echo $message; ?>

    <?php if ($showForm): ?>
        <h1>Reserve Table</h1>
        <form method="POST">
            <div class="form-group">
                <label>Your Name</label>
                <input type="text" name="guest_name" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="guest_email" required placeholder="john@example.com">
            </div>
            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Guests</label>
                    <select name="guest_count">
                        <option value="2">2 People</option>
                        <option value="4">4 People</option>
                        <option value="6">6 People</option>
                        <option value="8">Large Group (8+)</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Time</label>
                    <input type="time" name="res_time" required>
                </div>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="res_date" id="res_date" required>
            </div>
            <div class="form-group">
                <label>Special Requests</label>
                <textarea name="special_request" rows="3" placeholder="Birthday, window seat, or food allergies..."></textarea>
            </div>
            <button type="submit" class="btn-submit">CONFIRM RESERVATION</button>
        </form>
    <?php endif; ?>
</div>

<script>
    // Prevent booking past dates
    document.getElementById('res_date').min = new Date().toISOString().split("T")[0];
</script>

</body>
</html>