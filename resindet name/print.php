<?php
include '../connect.php';  // <-- must define $conn = mysqli_connect(...)

// ------------------- GET & VALIDATE ID -------------------
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger text-center'>Invalid or missing resident ID.</div>";
    exit;
}

$id = (int)$_GET['id'];

// ------------------- FETCH RESIDENT DATA -------------------
$sql = "SELECT * FROM residents WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    echo "<div class='alert alert-danger'>Database error: " . mysqli_error($conn) . "</div>";
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$resident = mysqli_fetch_assoc($result);

if (!$resident) {
    echo "<div class='alert alert-danger text-center'>Resident not found.</div>";
    exit;
}

// Close statement
mysqli_stmt_close($stmt);

// ------------------- GENERATE QR CODE DATA -------------------
$qrData = "Resident ID: " . $resident['id'] . "\n";
$qrData .= "Name: " . $resident['full_name'] . "\n";
$qrData .= "Phone: " . $resident['phone_number'] . "\n";
$qrData .= "Emergency: " . $resident['emergeny_contact_name'] . " (" . $resident['emergency_contact_phone'] . ")";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ID Card - <?php echo htmlspecialchars($resident['full_name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- QR Code Library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card-container {
            width: 720px;
            margin: 40px auto;
            display: flex;
            flex-direction: row;
            gap: 20px;
            page-break-inside: avoid;
        }

        .id-card {
            width: 340px;
            height: 220px;
            background-color: white;
            border: 2px solid #007bff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-size: 13px;
        }

        .photo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .title-section {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .title-text {
            font-weight: bold;
            font-size: 14px;
            line-height: 1.3;
            color: #003366;
        }

        .label {
            font-weight: 600;
            color: #000;
        }

        .info-line {
            margin-bottom: 4px;
            line-height: 1.4;
        }

        .qr-code {
            width: 60px;
            height: 60px;
            border: 1px solid #ddd;
            padding: 2px;
            background: white;
        }

        .id-card h6 {
            color: #003366;
            margin: 0;
            font-size: 14px;
        }

        @media print {
            body { background: white; }
            .card-container { margin: 10px auto; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="card-container">
    <!-- FRONT SIDE -->
    <div class="id-card">
        <div class="title-section">
            <img src="<?php echo htmlspecialchars($resident['photo'] ?? 'default-photo.jpg'); ?>" 
                 alt="Photo" class="photo" onerror="this.src='default-photo.jpg'">
            <div class="title-text">
                Jimma Zone<br>
                Ifa Boru Kebele<br>
                <strong>Resident Citizen Card</strong>
            </div>
        </div>
        <div>
            <div class="info-line"><span class="label">Full Name:</span> <?php echo htmlspecialchars($resident['full_name']); ?></div>
            <div class="info-line"><span class="label">Gender:</span> <?php echo htmlspecialchars($resident['gender']); ?></div>
            <div class="info-line"><span class="label">Age:</span> <?php echo htmlspecialchars($resident['age']); ?></div>
            <div class="info-line"><span class="label">Nationality:</span> ETHIOPIA</div>
            <div class="info-line"><span class="label">Region:</span> OROMIA</div>
        </div>
    </div>

    <!-- BACK SIDE -->
    <div class="id-card">
        <div class="text-center mb-2">
            <h6>Emergency & Card Info</h6>
        </div>
        <div class="info-line"><span class="label">Emergency Contact:</span> <?php echo htmlspecialchars($resident['emergeny_contact_name']); ?></div>
        <div class="info-line"><span class="label">Phone:</span> <?php echo htmlspecialchars($resident['emergency_contact_phone']); ?></div>
        <div class="info-line"><span class="label">Issued:</span> <?php echo date('Y-m-d', strtotime($resident['move_in_date'])); ?></div>
        <div class="info-line"><span class="label">Expires:</span> <?php echo date('Y-m-d', strtotime('+5 years', strtotime($resident['move_in_date']))); ?></div>
        <div class="d-flex justify-content-between align-items-end mt-3">
            <div id="qrcode" class="qr-code"></div>
            <small class="text-muted">Keep card with you.</small>
        </div>
    </div>
</div>

<!-- Print & Cancel Buttons -->
<div class="d-flex justify-content-center mt-4 no-print">
    <button onclick="window.print()" class="btn btn-primary me-2">Print ID Card</button>
    <a href="resident_management.php" class="btn btn-secondary">Back to List</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const qrData = `<?php echo addslashes($qrData); ?>`;
        new QRCode(document.getElementById("qrcode"), {
            text: qrData,
            width: 60,
            height: 60,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    });
</script>

</body>
</html>

<?php
// Close connection
mysqli_close($conn);
?>