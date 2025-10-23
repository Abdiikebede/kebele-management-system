<?php
// Include database connection
require_once '../db_connection.php';

$registration_message = "";
$message_type = ""; // success or error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $kebele_id = $conn->real_escape_string($_POST['kebele_id']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        $registration_message = "Error: Passwords do not match!";
        $message_type = "error";
    } else {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if email already exists
        $check_email_sql = "SELECT id FROM users WHERE email = '$email'";
        $result = $conn->query($check_email_sql);
        
        if ($result->num_rows > 0) {
            $registration_message = "Error: Email already registered!";
            $message_type = "error";
        } else {
            // Insert new user
            $sql = "INSERT INTO users (first_name, last_name, kebele_id, email, phone_number, password) 
                    VALUES ('$first_name', '$last_name', '$kebele_id', '$email', '$phone_number', '$hashed_password')";
            
            if ($conn->query($sql) === TRUE) {
                $registration_message = "Registration successful! You can now log in.";
                $message_type = "success";
                
                // Clear form fields after successful registration
                $_POST = array();
            } else {
                $registration_message = "Error: " . $conn->error;
                $message_type = "error";
            }
        }
    }
    
    // Close connection when done
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register | Ifa Bula Kebele</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    html { scroll-behavior: smooth; }
  </style>
</head>
<body class="bg-gradient-to-b from-blue-50 to-white text-gray-900 min-h-screen flex flex-col font-sans">

  <!-- Navigation Bar -->
  <nav class="sticky top-0 z-50 w-full flex justify-between items-center py-5 px-10 bg-white/80 backdrop-blur-md shadow-md">
    <div class="flex items-center space-x-3">
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Example_logo.png" alt="Logo" class="w-10 h-10 rounded-full">
      <span class="font-extrabold text-2xl text-blue-700">Ifa Bula Kebele</span>
    </div>
    <ul class="hidden md:flex space-x-8 text-lg font-medium">
      <li><a href="../index.php" class="hover:text-blue-600 transition">Home</a></li>
      <li><a href="./login.php" class="hover:text-blue-600 transition">Log In</a></li>
      <li><a href="#" class="text-green-700 font-bold border-b-2 border-green-700">Register</a></li>
    </ul>
    <button class="md:hidden text-gray-800 focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </nav>

  <!-- Register Form Section -->
  <section class="flex-grow flex items-center justify-center py-16 px-6">
    <div class="w-full max-w-lg">
      <div class="bg-white p-10 rounded-2xl shadow-2xl border-4 border-green-100">
        <div class="text-center mb-8">
          <h1 class="text-4xl font-extrabold text-green-700 mb-2">Create Account</h1>
          <p class="text-gray-600">Join Ifa Bula Kebele digital services</p>
        </div>

        <!-- Registration Message Display -->
        <?php if (!empty($registration_message)): ?>
          <div class="<?php echo $message_type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?> px-4 py-3 rounded mb-6 text-center">
            <?php echo $registration_message; ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="block text-gray-700 font-medium mb-2">First Name</label>
              <input type="text" name="first_name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                     placeholder="John" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
            </div>
            <div>
              <label class="block text-gray-700 font-medium mb-2">Last Name</label>
              <input type="text" name="last_name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                     placeholder="Doe" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
            </div>
          </div>

          <div>
            <label class="block text-gray-700 font-medium mb-2">Kebele ID (if available)</label>
            <input type="text" name="kebele_id" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                   placeholder="KB-XXXX-XXXX" value="<?php echo isset($_POST['kebele_id']) ? htmlspecialchars($_POST['kebele_id']) : ''; ?>">
          </div>

          <div>
            <label class="block text-gray-700 font-medium mb-2">Email Address</label>
            <input type="email" name="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                   placeholder="you@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
          </div>

          <div>
            <label class="block text-gray-700 font-medium mb-2">Phone Number</label>
            <input type="tel" name="phone_number" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                   placeholder="+251 9XX XXX XXX" value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>" required>
          </div>

          <div>
            <label class="block text-gray-700 font-medium mb-2">Password</label>
            <input type="password" name="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                   placeholder="Create a strong password" required>
          </div>

          <div>
            <label class="block text-gray-700 font-medium mb-2">Confirm Password</label>
            <input type="password" name="confirm_password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent" 
                   placeholder="Repeat your password" required>
          </div>

          <div class="flex items-center space-x-2">
            <input type="checkbox" name="terms" class="rounded text-green-600 focus:ring-green-500" required>
            <label class="text-sm text-gray-600">
              I agree to the <a href="#" class="text-green-600 underline">Terms of Service</a> and <a href="#" class="text-green-600 underline">Privacy Policy</a>
            </label>
          </div>

          <button type="submit" 
            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-full shadow-lg transition transform hover:scale-105">
            Create Account
          </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
          Already have an account? 
          <a href="login.php" class="text-green-600 font-semibold hover:underline">Log in here</a>
        </p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-blue-700 text-white text-center py-6">
    <p class="text-sm">&copy; 2025 Ifa Bula Kebele. All rights reserved.</p>
  </footer>

</body>
</html>