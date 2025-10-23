<?php
$conn = new mysqli("localhost", "root", "", "kebele_db");

// Fetch residents for parent selection
$residents = $conn->query("SELECT id, full_name FROM residents");
?>
<!DOCTYPE html>
<html>
<head>
  <title>New ID Request</title>
  <style>
    .hidden { display: none; }
  </style>
  <script>
    function toggleFormPart(value) {
      document.getElementById("goLetter").classList.add("hidden");
      document.getElementById("parentSelect").classList.add("hidden");

      if (value === "other_kebele") {
        document.getElementById("goLetter").classList.remove("hidden");
      } else if (value === "this_kebele") {
        document.getElementById("parentSelect").classList.remove("hidden");
      }
    }
  </script>
</head>
<body>
  <h2>Request New ID</h2>
  <form action="submit_id_request.php" method="POST">
    <label>Full Name:</label><br>
    <input type="text" name="full_name" required><br><br>

    <label>Date of Birth:</label><br>
    <input type="date" name="dob" required><br><br>

    <label>Address:</label><br>
    <input type="text" name="address" required><br><br>

    <label>Are you from this kebele?</label><br>
    <input type="radio" name="origin" value="this_kebele" onclick="toggleFormPart(this.value)" required> Yes<br>
    <input type="radio" name="origin" value="other_kebele" onclick="toggleFormPart(this.value)"> No<br><br>

    <!-- Woreda Go Letter input -->
    <div id="goLetter" class="hidden">
      <label>Woreda Go Letter:</label><br>
      <input type="text" name="go_letter"><br><br>
    </div>

    <!-- Parent selection -->
    <div id="parentSelect" class="hidden">
      <label>Select Parent:</label><br>
      <select name="parent_id">
        <option value="">--Select--</option>
        <?php while($row = $residents->fetch_assoc()): ?>
          <option value="<?= $row['id'] ?>"><?= $row['full_name'] ?></option>
        <?php endwhile; ?>
      </select><br><br>
    </div>

    <button type="submit">Submit Request</button>
  </form>
</body>
</html>
