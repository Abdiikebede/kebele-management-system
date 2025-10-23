<?php
include '../db_connection.php';

// Check if ID is provided
if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>No resident ID specified.</div>";
    exit;
}

$id = intval($_GET['id']);

// Delete query
$sql = "DELETE FROM new_id_request WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    // Optional: Redirect after deletion
    header("Location: main.php?deleted=1");
    exit;
} else {
    echo "<div class='alert alert-danger'>Error deleting resident: " . mysqli_error($conn) . "</div>";
}
