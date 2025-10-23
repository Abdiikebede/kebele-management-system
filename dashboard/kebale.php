<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'kebele_management';
$username = 'root'; // Replace with your MySQL username
$password = ''; // Replace with your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get current user's data
$user_id = $_SESSION['user_id'] ?? null;
$user_email = $_SESSION['email'] ?? '';

// Fetch user-specific card data
$cardData = [
    'My Birth Requests' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE father_id = '$user_id' OR mother_id = '$user_id'")->fetchColumn(),
    'My ID Requests' => (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE email = '$user_email'")->fetchColumn(),
    'My Marriage Requests' => (int)$pdo->query("SELECT COUNT(*) FROM marriage_requests WHERE husband_id = '$user_id' OR wife_id = '$user_id'")->fetchColumn(),
    'Pending Requests' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE (father_id = '$user_id' OR mother_id = '$user_id') AND status = 'pending'")->fetchColumn() +
                         (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE email = '$user_email' AND status = 'pending'")->fetchColumn() +
                         (int)$pdo->query("SELECT COUNT(*) FROM marriage_requests WHERE (husband_id = '$user_id' OR wife_id = '$user_id') AND status = 'pending'")->fetchColumn(),
];

// Fetch user's request status data for charts
$userRequestData = [
    'Pending' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE (father_id = '$user_id' OR mother_id = '$user_id') AND status = 'pending'")->fetchColumn() +
                (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE email = '$user_email' AND status = 'pending'")->fetchColumn() +
                (int)$pdo->query("SELECT COUNT(*) FROM marriage_requests WHERE (husband_id = '$user_id' OR wife_id = '$user_id') AND status = 'pending'")->fetchColumn(),
    'Approved' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE (father_id = '$user_id' OR mother_id = '$user_id') AND status = 'approved'")->fetchColumn() +
                 (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE email = '$user_email' AND status = 'approved'")->fetchColumn() +
                 (int)$pdo->query("SELECT COUNT(*) FROM marriage_requests WHERE (husband_id = '$user_id' OR wife_id = '$user_id') AND status = 'approved'")->fetchColumn(),
    'Rejected' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE (father_id = '$user_id' OR mother_id = '$user_id') AND status = 'rejected'")->fetchColumn() +
                 (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE email = '$user_email' AND status = 'rejected'")->fetchColumn() +
                 (int)$pdo->query("SELECT COUNT(*) FROM marriage_requests WHERE (husband_id = '$user_id' OR wife_id = '$user_id') AND status = 'rejected'")->fetchColumn(),
];

// Fetch request type distribution for user
$requestTypeData = [
    'Birth Certificates' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE father_id = '$user_id' OR mother_id = '$user_id'")->fetchColumn(),
    'ID Requests' => (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE email = '$user_email'")->fetchColumn(),
    'Marriage Certificates' => (int)$pdo->query("SELECT COUNT(*) FROM marriage_requests WHERE husband_id = '$user_id' OR wife_id = '$user_id'")->fetchColumn(),
];

// Fetch recent activity (last 7 days)
$weeklyData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $birthCount = (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE (father_id = '$user_id' OR mother_id = '$user_id') AND DATE(created_at) = '$date'")->fetchColumn();
    $idCount = (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE email = '$user_email' AND DATE(created_at) = '$date'")->fetchColumn();
    $marriageCount = (int)$pdo->query("SELECT COUNT(*) FROM marriage_requests WHERE (husband_id = '$user_id' OR wife_id = '$user_id') AND DATE(created_at) = '$date'")->fetchColumn();
    
    $weeklyData[$date] = [
        'birth' => $birthCount,
        'id' => $idCount,
        'marriage' => $marriageCount,
        'total' => $birthCount + $idCount + $marriageCount
    ];
}

// Fetch user's recent requests for tables
$recentRequests = $pdo->query("
    SELECT 'Birth' AS type, br.id, br.status, br.created_at, 'Birth Certificate' AS description
    FROM birth_requests br 
    WHERE br.father_id = '$user_id' OR br.mother_id = '$user_id'
    UNION
    SELECT 'ID' AS type, nir.id, nir.status, nir.created_at, 'New ID Application' AS description
    FROM new_id_requests nir 
    WHERE nir.email = '$user_email'
    UNION
    SELECT 'Marriage' AS type, mr.id, mr.status, mr.created_at, 'Marriage Certificate' AS description
    FROM marriage_requests mr 
    WHERE mr.husband_id = '$user_id' OR mr.wife_id = '$user_id'
    ORDER BY created_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

$pendingUserRequests = $pdo->query("
    SELECT 'Birth' AS type, br.id, br.status, br.created_at, 'Birth Certificate' AS description
    FROM birth_requests br 
    WHERE (br.father_id = '$user_id' OR br.mother_id = '$user_id') AND br.status = 'pending'
    UNION
    SELECT 'ID' AS type, nir.id, nir.status, nir.created_at, 'New ID Application' AS description
    FROM new_id_requests nir 
    WHERE nir.email = '$user_email' AND nir.status = 'pending'
    UNION
    SELECT 'Marriage' AS type, mr.id, mr.status, mr.created_at, 'Marriage Certificate' AS description
    FROM marriage_requests mr 
    WHERE (mr.husband_id = '$user_id' OR mr.wife_id = '$user_id') AND mr.status = 'pending'
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>User Dashboard | Ifa Bula Kebele</title>

  <!-- Fonts & icons -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

  <!-- Tailwind (v2.2.19 as used earlier) -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <!-- ApexCharts -->
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

  <style>
    :root{
      --green-start: #0f766e;
      --green-mid: #059669;
      --green-end: #10b981;
      --card-bg: #ffffff;
      --muted: #6b7280;
    }
    [data-theme="dark"] {
      --card-bg: #0b1220;
      --muted: #9ca3af;
      background-color: #0b1220;
      color: #e6eef6;
    }
    body {
      font-family: 'Montserrat', sans-serif;
      background: #f3f4f6;
      color: #0f172a;
    }
    /* Container that matches image rounded outer card */
    .app-frame {
      max-width: 1250px;
      margin: 28px auto;
      border-radius: 18px;
      background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(246,249,246,0.98));
      box-shadow: 0 10px 30px rgba(2,6,23,0.12);
      overflow: hidden;
      display: grid;
      grid-template-columns: 260px 1fr;
      min-height: 78vh;
    }

    /* Sidebar */
    .app-sidebar {
      background: #ffffff;
      padding: 26px 18px;
      border-right: 1px solid rgba(15,23,42,0.04);
    }
    .logo {
      display:flex;
      align-items:center;
      gap:10px;
      font-weight:700;
      color:var(--green-start);
    }
    .nav-link {
      display:flex;
      align-items:center;
      gap:12px;
      padding:10px 12px;
      border-radius:10px;
      color:#0f172a;
      margin-bottom:6px;
      font-weight:600;
      transition: background .15s, transform .06s;
    }
    .nav-link:hover { background: rgba(16,185,129,0.06); transform: translateY(-1px); }

    /* Main area */
    .main {
      padding: 22px;
      overflow:auto;
    }

    /* Top header row inside main */
    .top-row {
      display:flex;
      justify-content:space-between;
      align-items:center;
      margin-bottom:18px;
    }

    /* Cards like image: gradient primary card for counts */
    .stat-card {
      border-radius:14px;
      padding:18px;
      background:var(--card-bg);
      box-shadow: 0 6px 20px rgba(2,6,23,0.06);
      transition: transform .2s, box-shadow .2s;
      cursor:pointer;
    }
    .stat-card:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(2,6,23,0.12); }

    .stat-primary {
      background: linear-gradient(135deg, var(--green-start), var(--green-end));
      color: #fff;
    }
    .stat-primary h4 { color: rgba(255,255,255,0.95); }
    .stat-primary p { color: rgba(255,255,255,0.92); font-size: 22px; font-weight:700; }

    /* grid areas */
    .cards-grid {
      display:grid;
      grid-template-columns: repeat(4, 1fr);
      gap:16px;
      margin-bottom:18px;
    }
    @media (max-width:1100px) { .app-frame { grid-template-columns: 1fr; } .cards-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width:640px) { .cards-grid { grid-template-columns: 1fr; } }

    .charts-grid {
      display:grid;
      grid-template-columns: 2fr 1fr;
      gap:16px;
      margin-top:8px;
    }
    @media (max-width:900px) { .charts-grid { grid-template-columns: 1fr; } }

    /* small utilities */
    .pill {
      padding:8px 12px;
      border-radius:999px;
      background: rgba(16,185,129,0.12);
      color: #065f46;
      font-weight:600;
      font-size:13px;
    }

    /* table styles */
    .requests-table {
      border-radius:12px;
      overflow:hidden;
      box-shadow: 0 8px 24px rgba(2,6,23,0.06);
      background:var(--card-bg);
      margin-top:14px;
    }
    .requests-table table {
      width:100%;
      border-collapse:collapse;
    }
    .requests-table thead th {
      background: linear-gradient(90deg, rgba(15,23,42,0.06), rgba(15,23,42,0.02));
      padding:12px 16px;
      font-weight:700;
      text-align:left;
      font-size:13px;
    }
    .requests-table tbody td {
      padding:12px 16px;
      border-top:1px solid rgba(15,23,42,0.04);
      font-size:13px;
    }

    .small-muted { color: var(--muted); font-size:13px; }

    /* toggle icons */
    .icon-btn { display:inline-flex; align-items:center; justify-content:center; width:40px; height:40px; border-radius:10px; background:rgba(15,23,42,0.03); }
    
    /* Status badges */
    .status-pending { background: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .status-approved { background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
    .status-rejected { background: #fee2e2; color: #dc2626; padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: 600; }
  </style>
</head>
<body data-theme="light">
  <div class="app-frame">

    <!-- SIDEBAR -->
    <aside class="app-sidebar">
      <div class="logo mb-6">
        <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,var(--green-start),var(--green-end));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800">K</div>
        <div>
          <div style="font-size:14px">Ifa Bula</div>
          <div style="font-size:12px;color:var(--muted)">User Dashboard</div>
        </div>
      </div>

      <nav class="mb-6">
        <a href="#" class="nav-link"><span class="material-icons-outlined">dashboard</span> Dashboard</a>
        <a href="#" class="nav-link"><span class="material-icons-outlined">person</span> My Profile</a>
        <div class="mt-2">
          <div class="text-xs font-semibold text-gray-500 uppercase mb-2">My Requests</div>
          <a href="../requests/request_new_id.php" class="nav-link"><span class="material-icons-outlined">add_card</span> New ID Request</a>
          <a href="../requests/request_update_id.php" class="nav-link"><span class="material-icons-outlined">edit</span> Update ID</a>
          <a href="../requests/birth_certificate.php" class="nav-link"><span class="material-icons-outlined">child_care</span> Birth Certificate</a>
          <a href="../requests/merriage_req.php" class="nav-link"><span class="material-icons-outlined">favorite</span> Marriage Certificate</a>
        </div>
        <div class="mt-2">
          <div class="text-xs font-semibold text-gray-500 uppercase mb-2">History</div>
          <a href="#" class="nav-link"><span class="material-icons-outlined">history</span> Request History</a>
          <a href="./display_announcement.php" class="nav-link"><span class="material-icons-outlined">description</span> Announcement
        </a>
        </div>
      </nav>

      <div class="mt-auto pt-6">
        <a href="./logout.php" class="nav-link" style="justify-content:flex-start"><span class="material-icons-outlined">logout</span> Logout</a>
        <div class="text-sm small-muted mt-6">Logged in as <strong style="color:var(--green-start)"><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></strong></div>
      </div>
    </aside>

    <!-- MAIN -->
    <section class="main">

      <div class="top-row">
        <div>
          <h1 class="text-2xl font-semibold">My Dashboard</h1>
          <div class="small-muted">Track and manage your certificate & ID requests</div>
        </div>

        <div class="flex items-center gap-3">
          <button id="themeBtn" class="icon-btn" title="Toggle theme" onclick="toggleTheme()">
            <span class="material-icons-outlined">brightness_6</span>
          </button>
          <div style="display:flex;flex-direction:column;align-items:flex-end">
            <div style="font-weight:700"><?php echo htmlspecialchars($_SESSION['first_name'] ?? 'User') . ' ' . htmlspecialchars($_SESSION['last_name'] ?? ''); ?></div>
            <div class="small-muted" style="font-size:12px"><?php echo htmlspecialchars($_SESSION['email'] ?? 'user@email.com'); ?></div>
          </div>
        </div>
      </div>

      <!-- STAT CARDS -->
      <div class="cards-grid">
        <!-- Primary card showing total requests -->
        <div class="stat-card stat-primary" onclick="toggleTable('recent-requests')">
          <div class="flex justify-between items-start">
            <div>
              <h4 class="text-sm font-semibold">Total My Requests</h4>
              <p class="mt-4 text-3xl"><?php echo array_sum($cardData); ?></p>
              <div class="mt-2 small-muted">All your service requests</div>
            </div>
            <div class="pill">Overview</div>
          </div>
        </div>

        <!-- Individual cards -->
        <?php foreach ($cardData as $title => $value): ?>
          <?php
            $key = strtolower(str_replace(' ', '-', $title));
            $accent = $title === 'Pending Requests' ? 'bg-yellow-50 text-yellow-600' : 
                     ($title === 'My Birth Requests' ? 'bg-blue-50 text-blue-600' : 
                     ($title === 'My Marriage Requests' ? 'bg-pink-50 text-pink-600' : 'bg-green-50 text-green-600'));
            $icon = $title === 'Pending Requests' ? 'hourglass_top' : 
                   ($title === 'My Birth Requests' ? 'child_care' : 
                   ($title === 'My Marriage Requests' ? 'favorite' : 'badge'));
          ?>
          <div class="stat-card" onclick="toggleTable('<?php echo $key; ?>')">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-sm font-semibold"><?php echo htmlspecialchars($title); ?></h4>
                <p class="mt-3 text-2xl font-bold"><?php echo htmlspecialchars($value); ?></p>
                <div class="small-muted mt-1">Click to view details</div>
              </div>
              <div class="text-right">
                <div class="rounded-full w-12 h-12 flex items-center justify-center <?php echo $accent; ?>">
                  <span class="material-icons-outlined"><?php echo $icon; ?></span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
A
      <!-- Tables (toggleable) -->
      <div id="tablesWrap">
        <!-- Recent requests -->
        <div id="recent-requests" class="requests-table" style="display:none;">
          <div style="padding:16px 18px;display:flex;justify-content:space-between;align-items:center">
            <div>
              <div class="text-lg font-semibold">Recent Requests</div>
              <div class="small-muted">Your most recent service requests</div>
            </div>
            <div class="small-muted">Last updated: <?php echo date('Y-m-d H:i:s'); ?></div>
          </div>
          <div style="overflow:auto;">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Type</th>
                  <th>Description</th>
                  <th>Status</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentRequests)): ?>
                  <tr><td colspan="5" class="text-center small-muted">No recent requests found.</td></tr>
                <?php else: ?>
                  <?php foreach ($recentRequests as $request): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($request['id']); ?></td>
                      <td><?php echo htmlspecialchars($request['type']); ?></td>
                      <td><?php echo htmlspecialchars($request['description']); ?></td>
                      <td>
                        <span class="status-<?php echo htmlspecialchars($request['status']); ?>">
                          <?php echo htmlspecialchars(ucfirst($request['status'])); ?>
                        </span>
                      </td>
                      <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($request['created_at']))); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pending requests -->
        <div id="pending-requests" class="requests-table" style="display:none;">
          <div style="padding:14px 18px;display:flex;justify-content:space-between;align-items:center">
            <div>
              <div class="text-lg font-semibold">Pending Requests</div>
              <div class="small-muted">Your requests awaiting approval</div>
            </div>
            <div class="pill"><?php echo htmlspecialchars($cardData['Pending Requests']); ?> pending</div>
          </div>
          <div style="overflow:auto;">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Type</th>
                  <th>Description</th>
                  <th>Status</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($pendingUserRequests)): ?>
                  <tr><td colspan="5" class="text-center small-muted">No pending requests found.</td></tr>
                <?php else: ?>
                  <?php foreach ($pendingUserRequests as $request): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($request['id']); ?></td>
                      <td><?php echo htmlspecialchars($request['type']); ?></td>
                      <td><?php echo htmlspecialchars($request['description']); ?></td>
                      <td><span class="status-pending">Pending</span></td>
                      <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($request['created_at']))); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- Charts area -->
      <div class="charts-grid mt-6">
        <div class="stat-card">
          <div class="flex items-center justify-between">
            <div>
              <div class="text-lg font-semibold">My Request Activity (Last 7 Days)</div>
              <div class="small-muted">Daily request activity across all service types</div>
            </div>
            <div class="pill">Activity</div>
          </div>
          <div id="trend-line-chart" style="height:340px;margin-top:12px;"></div>
        </div>

        <div>
          <div class="stat-card mb-4">
            <div class="text-md font-semibold">Request Status</div>
            <div id="status-pie-chart" style="height:200px;margin-top:10px;"></div>
          </div>
          <div class="stat-card">
            <div class="text-md font-semibold">Request Types</div>
            <div id="type-pie-chart" style="height:200px;margin-top:10px;"></div>
          </div>
        </div>
      </div>

    </section>
  </div>

<script>
  // Theme toggle
  function toggleTheme() {
    const body = document.body;
    const current = body.getAttribute('data-theme') || 'light';
    body.setAttribute('data-theme', current === 'dark' ? 'light' : 'dark');
  }

  // Table toggling
  function hideAllTables() {
    document.querySelectorAll('#tablesWrap > .requests-table').forEach(el => el.style.display = 'none');
  }

  function toggleTable(id) {
    hideAllTables();
    const el = document.getElementById(id);
    if (el) {
      el.style.display = 'block';
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  // Charting: pass PHP arrays to JS
  const dashboardData = {
    userRequests: <?php echo json_encode(array_values($userRequestData)); ?>,
    requestTypes: <?php echo json_encode(array_values($requestTypeData)); ?>,
    weeklyData: <?php echo json_encode($weeklyData); ?>,
    statusLabels: ['Pending', 'Approved', 'Rejected'],
    typeLabels: ['Birth Certificates', 'ID Requests', 'Marriage Certificates']
  };

  const colors = {
    status: ['#f59e0b', '#10b981', '#ef4444'],
    types: ['#3b82f6', '#8b5cf6', '#ec4899']
  };

  // Line chart for trends
  function createTrendLineChart() {
    const dates = Object.keys(dashboardData.weeklyData);
    const birthData = dates.map(date => dashboardData.weeklyData[date].birth);
    const idData = dates.map(date => dashboardData.weeklyData[date].id);
    const marriageData = dates.map(date => dashboardData.weeklyData[date].marriage);

    const options = {
      chart: {
        type: 'line',
        height: '100%',
        toolbar: { show: true }
      },
      series: [
        {
          name: 'Birth Certificates',
          data: birthData
        },
        {
          name: 'ID Requests',
          data: idData
        },
        {
          name: 'Marriage Certificates',
          data: marriageData
        }
      ],
      xaxis: {
        categories: dates.map(date => new Date(date).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})),
        labels: { style: { colors: '#6b7280' } }
      },
      yaxis: {
        title: { text: 'Number of Requests' },
        labels: { style: { colors: '#6b7280' } }
      },
      colors: colors.types,
      stroke: {
        width: 3,
        curve: 'smooth'
      },
      markers: {
        size: 5
      },
      legend: {
        position: 'top'
      },
      grid: {
        borderColor: '#e5e7eb'
      }
    };

    const chart = new ApexCharts(document.querySelector('#trend-line-chart'), options);
    chart.render();
  }

  // Pie chart for status
  function createStatusPieChart() {
    const options = {
      chart: {
        type: 'pie',
        height: '100%',
        toolbar: { show: false }
      },
      series: dashboardData.userRequests,
      labels: dashboardData.statusLabels,
      colors: colors.status,
      legend: {
        position: 'bottom'
      },
      dataLabels: {
        enabled: true,
        formatter: function(val) {
          return Math.round(val) + '%'
        }
      }
    };

    const chart = new ApexCharts(document.querySelector('#status-pie-chart'), options);
    chart.render();
  }

  // Pie chart for request types
  function createTypePieChart() {
    const options = {
      chart: {
        type: 'pie',
        height: '100%',
        toolbar: { show: false }
      },
      series: dashboardData.requestTypes,
      labels: dashboardData.typeLabels,
      colors: colors.types,
      legend: {
        position: 'bottom'
      },
      dataLabels: {
        enabled: true,
        formatter: function(val) {
          return Math.round(val) + '%'
        }
      }
    };

    const chart = new ApexCharts(document.querySelector('#type-pie-chart'), options);
    chart.render();
  }

  // Initialize all charts
  createTrendLineChart();
  createStatusPieChart();
  createTypePieChart();

  // Show recent requests by default
  toggleTable('recent-requests');
</script>
</body>
</html>