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
    
    // Check if user exists with email or kebele_id and is an admin
    $sql = "SELECT id, first_name, last_name, email, kebele_id, password, status, role 
            FROM users 
            WHERE (email = '$username' OR kebele_id = '$username') AND role = 'admin'";
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
                $_SESSION['role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                
                $login_message = "Admin login successful! Redirecting...";
                $message_type = "success";
                
                // Redirect to admin dashboard after 5 seconds
                header("refresh:5; url=../dashboard/user_management.php");
            } else {
                $login_message = "Your account is not active. Please contact administration.";
                $message_type = "error";
            }
        } else {
            $login_message = "Invalid password!";
            $message_type = "error";
        }
    } else {
        $login_message = "No admin account found with this email or Kebele ID!";
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
  <title>Admin Login | Ifa Bula Kebele</title>
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
      background: linear-gradient(135deg, #1f2937, #374151);
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
      color: #374151;
    }
    
    .loading-text {
      color: white;
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 10px;
      text-align: center;
    }
    
    .loading-subtext {
      color: rgba(255, 255, 255, 0.8);
      font-size: 16px;
      margin-bottom: 30px;
      text-align: center;
    }
    
    .loading-admin-badge {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 20px;
      border: 1px solid rgba(255, 255, 255, 0.3);
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
    
    .security-indicator {
      color: rgba(255, 255, 255, 0.7);
      font-size: 12px;
      margin-top: 5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }
  </style>
</head>
<body class="bg-gradient-to-b from-gray-50 to-gray-200 text-gray-900 min-h-screen flex flex-col font-sans">

  <!-- Loading Overlay -->
  <div id="loadingOverlay" class="loading-overlay">
    <div class="loading-logo">
      <div class="loading-logo-inner">K</div>
    </div>
    <div class="loading-text">Ifa Bula Kebele</div>
    <div class="loading-admin-badge">Admin Portal</div>
    <div class="loading-subtext">Initializing admin dashboard...</div>
    <div class="loading-spinner"></div>
    <div class="loading-progress">
      <div id="progressBar" class="loading-progress-bar"></div>
    </div>
    <div id="countdown" class="countdown">Redirecting in 5 seconds...</div>
    <div class="security-indicator">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
      </svg>
      Secure Admin Session
    </div>
  </div>

  <!-- Navigation Bar -->
  <nav class="sticky top-0 z-50 w-full flex justify-between items-center py-5 px-10 bg-white/80 backdrop-blur-md shadow-md">
    <div class="flex items-center space-x-3">
      <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Example_logo.png" alt="Logo" class="w-10 h-10 rounded-full">
      <span class="font-extrabold text-2xl text-gray-800">Ifa Bula Kebele Admin</span>
    </div>
    <ul class="hidden md:flex space-x-8 text-lg font-medium">
      <li><a href="../index.php" class="hover:text-gray-600 transition">Home</a></li>
      <li><a href="#" class="text-gray-800 font-bold border-b-2 border-gray-800">Admin Login</a></li>
    </ul>
    <button class="md:hidden text-gray-800 focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
      </svg>
    </button>
  </nav>

  <!-- Admin Login Form Section -->
  <section class="flex-grow flex items-center justify-center py-16 px-6">
    <div class="w-full max-w-md">
      <div class="bg-white p-10 rounded-2xl shadow-2xl border-4 border-gray-300">
        <div class="text-center mb-8">
          <h1 class="text-4xl font-extrabold text-gray-800 mb-2">Admin Portal</h1>
          <p class="text-gray-600">Log in to manage kebele services</p>
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

        <form method="POST" action="" class="space-y-6" id="adminLoginForm">
          <div>
            <label class="block text-gray-700 font-medium mb-2">Admin Email / Kebele ID</label>
            <input 
              type="text" 
              name="username"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-600 focus:border-transparent transition" 
              placeholder="Enter your admin email or ID" 
              value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
              required>
          </div>

          <div>
            <label class="block text-gray-700 font-medium mb-2">Password</label>
            <input 
              type="password" 
              name="password"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-600 focus:border-transparent transition" 
              placeholder="Enter your password" 
              required>
          </div>

          <div class="flex items-center justify-between text-sm">
            <label class="flex items-center space-x-2">
              <input type="checkbox" name="remember_me" class="rounded text-gray-600 focus:ring-gray-600">
              <span class="text-gray-600">Remember me</span>
            </label>
            <a href="forgot_password.php" class="text-gray-600 hover:underline">Forgot password?</a>
          </div>

          <button type="submit" 
            class="w-full bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 rounded-full shadow-lg transition transform hover:scale-105">
            Admin Log In
          </button>
        </form>

        <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
          <div class="flex items-center gap-2 text-sm text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
            <span>This portal is restricted to authorized administrators only.</span>
          </div>
        </div>

        <p class="text-center text-gray-600 mt-6">
          Not an admin? 
          <a href="../login.php" class="text-gray-600 font-semibold hover:underline">Go to User Login</a>
        </p>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-800 text-white text-center py-6">
    <p class="text-sm">&copy; 2025 Ifa Bula Kebele Admin. All rights reserved.</p>
  </footer>

  <script>
    // Show loading animation on successful admin login
    document.addEventListener('DOMContentLoaded', function() {
      const adminLoginForm = document.getElementById('adminLoginForm');
      const loadingOverlay = document.getElementById('loadingOverlay');
      const progressBar = document.getElementById('progressBar');
      const countdownElement = document.getElementById('countdown');
      
      <?php if ($message_type === 'success'): ?>
        // Show loading overlay for successful admin login
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
      
      // Add security warning for admin form
      adminLoginForm.addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]').value;
        if (password.length < 6) {
          if (!confirm('Warning: Admin passwords should be strong. Continue with this password?')) {
            e.preventDefault();
          }
        }
      });
    });
  </script>

</body>
</html>