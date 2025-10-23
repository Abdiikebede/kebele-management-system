<?php
session_start();
// Include database connection
require_once '../db_connection.php';

$login_message = "";
$message_type = ""; // success or error

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    // Check if user exists with email or kebele_id
    $sql = "SELECT id, first_name, last_name, email, kebele_id, password, status FROM users 
            WHERE email = '$username' OR kebele_id = '$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Check if account is active
            if ($user['status'] == 'active') {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['kebele_id'] = $user['kebele_id'];
                $_SESSION['logged_in'] = true;
                
                $login_message = "Login successful! Redirecting...";
                $message_type = "success";
                
                // Redirect to dashboard after 5 seconds
                header("refresh:5; url=../dashboard/kebale.php");
            } else {
                $login_message = "Your account is not active. Please contact administration.";
                $message_type = "error";
            }
        } else {
            $login_message = "Invalid password!";
            $message_type = "error";
        }
    } else {
        $login_message = "No account found with this email or Kebele ID!";
        $message_type = "error";
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
  <title>Login | Ifa Bula Kebele</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <style>
    html { scroll-behavior: smooth; }
    
    /* Loading Animation Styles */
    .loading-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(135deg, #0f766e, #059669);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }
    
    .loading-overlay.active {
      opacity: 1;
      visibility: visible;
    }
    
    .loading-logo {
      width: 120px;
      height: 120px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 30px;
      border: 3px solid rgba(255, 255, 255, 0.2);
      animation: pulse 2s infinite;
    }
    
    .loading-logo-inner {
      width: 80px;
      height: 80px;
      background: white;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 24px;
      color: #059669;
    }
    
    .loading-text {
      color: white;
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 20px;
      text-align: center;
    }
    
    .loading-subtext {
      color: rgba(255, 255, 255, 0.8);
      font-size: 16px;
      margin-bottom: 30px;
      text-align: center;
    }
    
    .loading-spinner {
      width: 50px;
      height: 50px;
      border: 4px solid rgba(255, 255, 255, 0.3);
      border-top: 4px solid white;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }
    
    .loading-progress {
      width: 200px;
      height: 4px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 2px;
      margin-top: 20px;
      overflow: hidden;
    }
    
    .loading-progress-bar {
      height: 100%;
      background: white;
      width: 0%;
      border-radius: 2px;
      transition: width 5s linear;
    }
    
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    
    .countdown {
      color: white;
      font-size: 14px;
      margin-top: 10px;
      font-weight: 600;
    }
  </style>
</head>
<body class="bg-gradient-to-b from-blue-50 to-white text-gray-900 min-h-screen flex flex-col font-sans">

  <!-- Loading Overlay -->
  <div id="loadingOverlay" class="loading-overlay">
    <div class="loading-logo">
      <div class="loading-logo-inner">K</div>
    </div>
    <div class="loading-text">Ifa Bula Kebele</div>
    <div class="loading-subtext">Preparing your dashboard...</div>
    <div class="loading-spinner"></div>
    <div class="loading-progress">
      <div id="progressBar" class="loading-progress-bar"></div>
    </div>
    <div id="countdown" class="countdown">Redirecting in 5 seconds...</div>
  </div>

  <!-- Navigation Bar -->
  <nav class="sticky top-0 z-50 w-full flex justify-between items-center py-5 px-10 bg-white/80 backdrop-blur-md shadow-md">
    <div class="flex items-center space-x-3">
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Example_logo.png" alt="Logo" class="w-10 h-10 rounded-full">
      <span class="font-extrabold text-2xl text-blue-700">Ifa Bula Kebele</span>
    </div>
    <ul class="hidden md:flex space-x-8 text-lg font-medium">
      <li><a href="../index.php" class="hover:text-blue-600 transition">Home</a></li>
      <li><a href="#" class="text-blue-700 font-bold border-b-2 border-blue-700">Log In</a></li>
      <li><a href="registration.php" class="hover:text-blue-600 transition">Register</a></li>
      <li><a href="admin_login.php" class="hover:text-blue-600 transition">Admin</a></li>
    </ul>
    <button class="md:hidden text-gray-800 focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </nav>

  <!-- Login Form Section -->
  <section class="flex-grow flex items-center justify-center py-16 px-6">
    <div class="w-full max-w-md">
      <div class="bg-white p-10 rounded-2xl shadow-2xl border-4 border-blue-100">
        <div class="text-center mb-8">
          <h1 class="text-4xl font-extrabold text-blue-700 mb-2">Welcome Back</h1>
          <p class="text-gray-600">Log in to access your kebele services</p>
        </div>

        <!-- Login Message Display -->
        <?php if (!empty($login_message)): ?>
          <div class="<?php echo $message_type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?> px-4 py-3 rounded mb-6 text-center">
            <?php echo $login_message; ?>
            <?php if ($message_type === 'success'): ?>
              <div class="text-sm mt-1">You will be redirected shortly...</div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6" id="loginForm">
          <div>
            <label class="block text-gray-700 font-medium mb-2">Kebele ID / Email</label>
            <input 
              type="text" 
              name="username"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
              placeholder="Enter your ID or email" 
              value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
              required>
          </div>

          <div>
            <label class="block text-gray-700 font-medium mb-2">Password</label>
            <input 
              type="password" 
              name="password"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition" 
              placeholder="Enter your password" 
              required>
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center space-x-2">
              <input type="checkbox" name="remember_me" class="rounded text-blue-600 focus:ring-blue-500">
              <span class="text-gray-600">Remember me</span>
            </label>
            <a href="forgot_password.php" class="text-blue-600 hover:underline">Forgot password?</a>
          </div>

          <button type="submit" 
            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-full shadow-lg transition transform hover:scale-105">
            Log In
          </button>
        </form>

        <p class="text-center text-gray-600 mt-6">
          Don't have an account? 
          <a href="registration.php" class="text-blue-600 font-semibold hover:underline">Register here</a>
        </p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-blue-700 text-white text-center py-6">
    <p class="text-sm">&copy; 2025 Ifa Bula Kebele. All rights reserved.</p>
  </footer>

  <script>
    // Show loading animation on successful login
    document.addEventListener('DOMContentLoaded', function() {
      const loginForm = document.getElementById('loginForm');
      const loadingOverlay = document.getElementById('loadingOverlay');
      const progressBar = document.getElementById('progressBar');
      const countdownElement = document.getElementById('countdown');
      
      <?php if ($message_type === 'success'): ?>
        // Show loading overlay for successful login
        setTimeout(() => {
          loadingOverlay.classList.add('active');
          progressBar.style.width = '100%';
          
          // Update countdown
          let seconds = 5;
          const countdownInterval = setInterval(() => {
            seconds--;
            countdownElement.textContent = `Redirecting in ${seconds} second${seconds !== 1 ? 's' : ''}...`;
            
            if (seconds <= 0) {
              clearInterval(countdownInterval);
            }
          }, 1000);
        }, 500);
      <?php endif; ?>
      
      // Prevent form resubmission on page refresh
      if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
      }
    });
  </script>

</body>
</html>