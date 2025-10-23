<?php
include 'connect.php';

// Ensure resident ID is provided
if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>Resident ID not specified.</div>";
    exit;
}

$id = $_GET['id'];
$sql = "SELECT * FROM residents WHERE id = ?";
$stmt = mysqli_stmt_init($conn);

if (mysqli_stmt_prepare($stmt, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $resident = mysqli_fetch_assoc($result);
}

if (!$resident) {
    echo "<div class='alert alert-danger'>Resident not found.</div>";
    exit;
}

// Handle update form submission
if (isset($_POST['submit'])) {
    $full_name = $_POST['full_name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $date_of_birth = $_POST['date_of_birth'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $move_in_date = $_POST['move_in_date'];
    $emergeny_contact_name = $_POST['emergeny_contact_name'];
    $emergency_contact_phone = $_POST['emergency_contact_phone'];
    $merital_status = $_POST['merital_status'];
    $educational_level = $_POST['educational_level'];
    $house_number = $_POST['house_number'];

    // Handle photo upload if a new one is provided
    if (!empty($_FILES['photo']['name'])) {
        $photo_name = $_FILES['photo']['name'];
        $photo_tmp = $_FILES['photo']['tmp_name'];
        $photo_path = "uploads/" . uniqid() . "_" . basename($photo_name);
        move_uploaded_file($photo_tmp, $photo_path);

        if (!empty($resident['photo']) && file_exists($resident['photo'])) {
            unlink($resident['photo']);
        }
    } else {
        $photo_path = $resident['photo'];
    }

    // Update query with age included
    $sql = "UPDATE residents SET
        full_name = ?,
        age = ?,
        gender = ?,
        date_of_birth = ?,
        address = ?,
        phone_number = ?,
        email = ?,
        move_in_date = ?,
        emergeny_contact_name = ?,
        emergency_contact_phone = ?,
        merital_status = ?,
        educational_level = ?,
        house_number = ?,
        photo = ?
        WHERE id = ?";

    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param(
            $stmt,
            "sissssssssssssi",
            $full_name,
            $age,
            $gender,
            $date_of_birth,
            $address,
            $phone_number,
            $email,
            $move_in_date,
            $emergeny_contact_name,
            $emergency_contact_phone,
            $merital_status,
            $educational_level,
            $house_number,
            $photo_path,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='alert alert-success'>Resident information updated successfully!</div>";

            // Refresh resident data
            $sql = "SELECT * FROM residents WHERE id = ?";
            $stmt = mysqli_stmt_init($conn);
            if (mysqli_stmt_prepare($stmt, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $resident = mysqli_fetch_assoc($result);
            }
        } else {
            echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Resident Info</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h2 class="mb-4">Edit Resident Information</h2>

        <form method="POST" action="" enctype="multipart/form-data">

            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name *</label>
                <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($resident['full_name']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="age" class="form-label">Age *</label>
                <input type="number" class="form-control" id="age" name="age" value="<?php echo htmlspecialchars($resident['age']); ?>" required min="0">
            </div>

            <div class="mb-3">
                <label for="gender" class="form-label">Gender *</label>
                <select class="form-select" id="gender" name="gender" required>
                    <option value="">Select</option>
                    <option value="Male" <?php echo ($resident['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($resident['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="date_of_birth" class="form-label">Date of Birth *</label>
                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($resident['date_of_birth']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address *</label>
                <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($resident['address']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number *</label>
                <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($resident['phone_number']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address *</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($resident['email']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="move_in_date" class="form-label">Move-in Date *</label>
                <input type="date" class="form-control" id="move_in_date" name="move_in_date" value="<?php echo htmlspecialchars($resident['move_in_date']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="emergeny_contact_name" class="form-label">Emergency Contact Name *</label>
                <input type="text" class="form-control" id="emergeny_contact_name" name="emergeny_contact_name" value="<?php echo htmlspecialchars($resident['emergeny_contact_name']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone *</label>
                <input type="tel" class="form-control" id="emergency_contact_phone" name="emergency_contact_phone" value="<?php echo htmlspecialchars($resident['emergency_contact_phone']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="merital_status" class="form-label">Marital Status *</label>
                <select class="form-select" id="merital_status" name="merital_status" required>
                    <option value="">Select</option>
                    <option value="Single" <?php echo ($resident['merital_status'] === 'Single') ? 'selected' : ''; ?>>Single</option>
                    <option value="Married" <?php echo ($resident['merital_status'] === 'Married') ? 'selected' : ''; ?>>Married</option>
                    <option value="Divorced" <?php echo ($resident['merital_status'] === 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                    <option value="Widowed" <?php echo ($resident['merital_status'] === 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="educational_level" class="form-label">Education Level *</label>
                <select class="form-select" id="educational_level" name="educational_level" required>
                    <option value="">Select</option>
                    <option value="High School" <?php echo ($resident['educational_level'] === 'High School') ? 'selected' : ''; ?>>High School</option>
                    <option value="Diploma" <?php echo ($resident['educational_level'] === 'Diploma') ? 'selected' : ''; ?>>Diploma</option>
                    <option value="Bachelor Degree" <?php echo ($resident['educational_level'] === 'Bachelor Degree') ? 'selected' : ''; ?>>Bachelor's Degree</option>
                    <option value="Master Degree" <?php echo ($resident['educational_level'] === 'Master Degree') ? 'selected' : ''; ?>>Master's Degree</option>
                    <option value="PhD" <?php echo ($resident['educational_level'] === 'PhD') ? 'selected' : ''; ?>>PhD</option>
                    <option value="Other" <?php echo ($resident['educational_level'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="house_number" class="form-label">House Number *</label>
                <input type="text" class="form-control" id="house_number" name="house_number" value="<?php echo htmlspecialchars($resident['house_number']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="photo" class="form-label">Upload Photo</label>
                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                <?php if (!empty($resident['photo'])): ?>
                    <div class="mt-2">
                        <small>Current photo:</small><br>
                        <img src="<?php echo htmlspecialchars($resident['photo']); ?>" alt="Current Photo" style="max-width: 200px;">
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" name="submit" class="btn btn-primary">Update</button>
            <a href="main.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>

</html>
