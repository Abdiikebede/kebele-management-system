<?php
$conn = new mysqli("localhost", "root", "", "kebele_db");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$full_name = $_POST['full_name'];
$dob = $_POST['dob'];
$address = $_POST['address'];
$origin = $_POST['origin'];
$go_letter = isset($_POST['go_letter']) ? $_POST['go_letter'] : null;
$parent_id = isset($_POST['parent_id']) ? $_POST['parent_id'] : null;

$sql = "INSERT INTO id_requests (full_name, dob, address, origin, go_letter, parent_id, status)
        VALUES (?, ?, ?, ?, ?, ?, 'pending')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $full_name, $dob, $address, $origin, $go_letter, $parent_id);

if ($stmt->execute()) {
  echo "New ID request submitted successfully!";
} else {
  echo "Error: " . $stmt->error;
}

$conn->close();
?>
