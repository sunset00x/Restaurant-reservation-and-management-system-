<?php
include 'db.php'; // Ensure your database connection is here

$tables = [
    'T1' => ['label' => 'Table 1 — 2 seats', 'capacity' => 2],
    'T2' => ['label' => 'Table 2 — 2 seats', 'capacity' => 2],
    'T3' => ['label' => 'Table 3 — 4 seats', 'capacity' => 4],
    'T4' => ['label' => 'Table 4 — 4 seats', 'capacity' => 4],
    'T5' => ['label' => 'Table 5 — 6 seats', 'capacity' => 6],
    'T6' => ['label' => 'Table 6 — 6 seats', 'capacity' => 6],
    'T7' => ['label' => 'Table 7 — 8 seats', 'capacity' => 8],
    'T8' => ['label' => 'Table 8 — 10 seats', 'capacity' => 10],
];

$message = "";
$error = "";
$showForm = true;

$selectedTable = "";
$reservedTables = [];
$guestName = "";
$guestEmail = "";
$guestCount = 2;
$resDate = "";
$resTime = "";
$specialRequest = "";

function ensureTableColumnExists($conn) {
    $result = mysqli_query($conn, "SHOW COLUMNS FROM reservations LIKE 'table_number'");
    if ($result && mysqli_num_rows($result) === 0) {
        mysqli_query($conn, "ALTER TABLE reservations ADD COLUMN table_number VARCHAR(20) NULL");
    }
}

function getReservedTables($conn, $date, $time) {
    $reserved = [];
    if (!$date || !$time) {
        return $reserved;
    }
    $date = mysqli_real_escape_string($conn, $date);
    $time = mysqli_real_escape_string($conn, $time);

    $sql = "SELECT table_number FROM reservations WHERE res_date = '$date' AND res_time = '$time'";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (!empty($row['table_number'])) {
                $reserved[] = $row['table_number'];
            }
        }
    }
    return $reserved;
}

ensureTableColumnExists($conn);

if (isset($_GET['availability']) && $_GET['availability'] == '1') {
    $date = isset($_GET['res_date']) ? $_GET['res_date'] : '';
    $time = isset($_GET['res_time']) ? $_GET['res_time'] : '';
    header('Content-Type: application/json');
    echo json_encode([
        'reserved' => getReservedTables($conn, $date, $time),
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize Data for Security
    $guestName = mysqli_real_escape_string($conn, $_POST['guest_name']);
    $guestEmail = mysqli_real_escape_string($conn, $_POST['guest_email']);
    $guestCount = (int)$_POST['guest_count'];
    $resDate = mysqli_real_escape_string($conn, $_POST['res_date']);
    $resTime = mysqli_real_escape_string($conn, $_POST['res_time']);
    $specialRequest = mysqli_real_escape_string($conn, $_POST['special_request']);
    $selectedTable = mysqli_real_escape_string($conn, $_POST['table_number'] ?? '');

    $reservedTables = getReservedTables($conn, $resDate, $resTime);

    if (!$selectedTable) {
        $error = "Please choose a table for your reservation.";
    } elseif (in_array($selectedTable, $reservedTables)) {
        $error = "This table is already booked for the selected date and time. Please choose another table.";
    } else {
        // 2. Generate a Unique Booking ID
        $booking_id = "BGR-" . strtoupper(substr(md5(time() . $guestName), 0, 6));

        // 3. Database Insertion
        $sql = "INSERT INTO reservations (guest_name, guest_email, guest_count, res_date, res_time, special_request, table_number) 
                VALUES ('$guestName', '$guestEmail', $guestCount, '$resDate', '$resTime', '$specialRequest', '$selectedTable')";

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
                    <p>Hello <strong>" . htmlspecialchars($guestName) . "</strong>,</p>
                    <p>Your table is officially reserved! We've sent a copy of this confirmation to <strong>" . htmlspecialchars($guestEmail) . "</strong>.</p>
                    
                    <div class='confirmation-details'>
                        <p><strong>Booking ID:</strong> <span class='id-text'>$booking_id</span></p>
                        <p><strong>Date:</strong> " . date('l, M d, Y', strtotime($resDate)) . "</p>
                        <p><strong>Time:</strong> " . date('h:i A', strtotime($resTime)) . "</p>
                        <p><strong>Guests:</strong> $guestCount People</p>
                        <p><strong>Table:</strong> " . htmlspecialchars($tables[$selectedTable]['label']) . "</p>
                    </div>

                    <div class='request-box'>
                        <strong>Special Request:</strong><br>
                        " . ($specialRequest ? htmlspecialchars($specialRequest) : 'None') . "
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
            $message = "<div class='error-alert'><strong>System Error:</strong> " . mysqli_error($conn) . "</div>";
        }
    }
}

if (!$reservedTables && $resDate && $resTime) {
    $reservedTables = getReservedTables($conn, $resDate, $resTime);
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

        .table-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; margin-top: 14px; }
        .table-card { background: #f8fafc; border: 1px solid #dbeafe; padding: 18px; border-radius: 18px; text-align: left; cursor: pointer; transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease; }
        .table-card:hover { transform: translateY(-2px); }
        .table-card.selected { border-color: #2563eb; box-shadow: 0 12px 30px rgba(37, 99, 235, 0.15); }
        .table-card.booked { background: #fef2f2; border-color: #fecaca; color: #9b1c1c; cursor: default; opacity: 0.85; }
        .table-card.booked:hover { transform: none; box-shadow: none; }
        .table-card .title { font-weight: 700; margin-bottom: 6px; }
        .table-card .capacity { display: block; margin-bottom: 10px; color: #475569; font-size: 0.95rem; }
        .table-card .status { display: inline-flex; align-items: center; gap: 8px; padding: 6px 10px; border-radius: 999px; font-size: 0.85rem; font-weight: 700; }
        .table-card.available .status { background: rgba(37, 99, 235, 0.12); color: #1d4ed8; }
        .table-card.booked .status { background: rgba(248, 113, 113, 0.12); color: #b91c1c; }
        .table-card .subtitle { font-size: 0.85rem; color: #64748b; }
        .availability-info { margin-top: 10px; font-size: 0.95rem; color: #475569; }
        .availability-info strong { color: #1d4ed8; }

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
        <?php if ($error): ?>
            <div class="error-alert"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" id="reservation-form">
            <div class="form-group">
                <label>Your Name</label>
                <input type="text" name="guest_name" required placeholder="John Doe" value="<?php echo htmlspecialchars($guestName); ?>">
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="guest_email" required placeholder="john@example.com" value="<?php echo htmlspecialchars($guestEmail); ?>">
            </div>
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div class="form-group" style="flex: 1; min-width: 180px;">
                    <label>Guests</label>
                    <select name="guest_count">
                        <option value="2" <?php echo $guestCount === 2 ? 'selected' : ''; ?>>2 People</option>
                        <option value="4" <?php echo $guestCount === 4 ? 'selected' : ''; ?>>4 People</option>
                        <option value="6" <?php echo $guestCount === 6 ? 'selected' : ''; ?>>6 People</option>
                        <option value="8" <?php echo $guestCount === 8 ? 'selected' : ''; ?>>8+ People</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1; min-width: 180px;">
                    <label>Time</label>
                    <input type="time" name="res_time" id="res_time" required value="<?php echo htmlspecialchars($resTime); ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Date</label>
                <input type="date" name="res_date" id="res_date" required value="<?php echo htmlspecialchars($resDate); ?>">
            </div>
            <div class="form-group">
                <label>Choose Your Table</label>
                <input type="hidden" name="table_number" id="table_number" value="<?php echo htmlspecialchars($selectedTable); ?>">
                <div class="table-grid" id="table-grid">
                    <?php foreach ($tables as $tableId => $tableInfo): ?>
                        <?php $occupied = ($resDate && $resTime && in_array($tableId, $reservedTables)); ?>
                        <button type="button" class="table-card <?php echo $occupied ? 'booked' : 'available'; ?> <?php echo (!$occupied && $selectedTable === $tableId) ? 'selected' : ''; ?>" data-table="<?php echo $tableId; ?>" <?php echo $occupied ? 'disabled' : ''; ?>>
                            <div class="title"><?php echo htmlspecialchars($tableInfo['label']); ?></div>
                            <div class="capacity"><?php echo $tableInfo['capacity']; ?> seats</div>
                            <div class="status"><?php echo $occupied ? 'Booked' : 'Available'; ?></div>
                            <div class="subtitle">Click to select</div>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="availability-info" id="availability-note">Choose a date and time to refresh table availability.</div>
            </div>
            <div class="form-group">
                <label>Special Requests</label>
                <textarea name="special_request" rows="3" placeholder="Birthday, window seat, or food allergies..."><?php echo htmlspecialchars($specialRequest); ?></textarea>
            </div>
            <button type="submit" class="btn-submit">CONFIRM RESERVATION</button>
        </form>
        <script>
            async function refreshTableAvailability() {
                const date = document.getElementById('res_date').value;
                const time = document.getElementById('res_time').value;
                const note = document.getElementById('availability-note');
                const tableInput = document.getElementById('table_number');
                const cards = document.querySelectorAll('.table-card');

                if (!date || !time) {
                    note.textContent = 'Choose a date and time to refresh table availability.';
                    cards.forEach(card => {
                        card.classList.remove('booked', 'selected');
                        card.classList.add('available');
                        card.disabled = true;
                        card.querySelector('.status').textContent = 'Select a date/time';
                    });
                    tableInput.value = '';
                    return;
                }

                note.textContent = 'Checking table availability...';
                const response = await fetch(`reservations.php?availability=1&res_date=${encodeURIComponent(date)}&res_time=${encodeURIComponent(time)}`);
                const data = await response.json();
                const reserved = data.reserved || [];
                let availableCount = 0;

                cards.forEach(card => {
                    const tableId = card.dataset.table;
                    const isBooked = reserved.includes(tableId);
                    const selected = tableInput.value === tableId;

                    card.disabled = isBooked;
                    card.classList.toggle('booked', isBooked);
                    card.classList.toggle('available', !isBooked);
                    card.classList.toggle('selected', !isBooked && selected);
                    card.querySelector('.status').textContent = isBooked ? 'Booked' : 'Available';
                    card.querySelector('.subtitle').textContent = isBooked ? 'Choose another table' : 'Click to select';

                    if (!isBooked) {
                        availableCount++;
                    }
                });

                if (availableCount === 0) {
                    note.innerHTML = '<strong>No tables available</strong> for this time. Please choose a different date or time.';
                    tableInput.value = '';
                } else {
                    note.innerHTML = '<strong>' + availableCount + '</strong> table(s) available. Click one to select it.';
                }
            }

            document.querySelectorAll('.table-card').forEach(card => {
                card.addEventListener('click', () => {
                    if (card.disabled) return;
                    const tableId = card.dataset.table;
                    const tableInput = document.getElementById('table_number');

                    document.querySelectorAll('.table-card.selected').forEach(selectedCard => {
                        selectedCard.classList.remove('selected');
                    });

                    card.classList.add('selected');
                    tableInput.value = tableId;
                });
            });

            document.getElementById('res_date').addEventListener('change', refreshTableAvailability);
            document.getElementById('res_time').addEventListener('change', refreshTableAvailability);
            window.addEventListener('load', refreshTableAvailability);
        </script>
    <?php endif; ?>
</div>

<script>
    // Prevent booking past dates
    document.getElementById('res_date').min = new Date().toISOString().split("T")[0];
</script>

</body>
</html>