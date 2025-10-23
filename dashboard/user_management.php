<?php
session_start();

// Check if user is logged in and has admin role
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'admin') {
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

// Fetch card data (counts by status for birth and ID requests)
$cardData = [
    'Pending Requests' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE status = 'pending'")->fetchColumn() +
                         (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE status = 'pending'")->fetchColumn(),
    'Approved Requests' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE status = 'approved'")->fetchColumn() +
                          (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE status = 'approved'")->fetchColumn(),
    'Rejected Requests' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE status = 'rejected'")->fetchColumn() +
                          (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE status = 'rejected'")->fetchColumn(),
];

// Fetch chart data for birth and ID requests by status
$birthRequestData = [
    'Pending' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE status = 'pending'")->fetchColumn(),
    'Approved' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE status = 'approved'")->fetchColumn(),
    'Rejected' => (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE status = 'rejected'")->fetchColumn(),
];

$idRequestData = [
    'Pending' => (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE status = 'pending'")->fetchColumn(),
    'Approved' => (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE status = 'approved'")->fetchColumn(),
    'Rejected' => (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE status = 'rejected'")->fetchColumn(),
];

// Combined
$combinedRequestData = [
    'Pending' => $cardData['Pending Requests'],
    'Approved' => $cardData['Approved Requests'],
    'Rejected' => $cardData['Rejected Requests'],
];

// Fetch weekly data for trend analysis
$weeklyData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $birthCount = (int)$pdo->query("SELECT COUNT(*) FROM birth_requests WHERE DATE(created_at) = '$date'")->fetchColumn();
    $idCount = (int)$pdo->query("SELECT COUNT(*) FROM new_id_requests WHERE DATE(created_at) = '$date'")->fetchColumn();
    $weeklyData[$date] = [
        'birth' => $birthCount,
        'id' => $idCount,
        'total' => $birthCount + $idCount
    ];
}

// Fetch table data for pending and approved requests
$pendingRequests = $pdo->query("
    SELECT 'Birth' AS type, br.id, br.status, br.created_at, COALESCE(u.first_name, 'N/A') AS first_name, COALESCE(u.last_name, 'N/A') AS last_name
    FROM birth_requests br 
    LEFT JOIN users u ON br.father_id = u.kebele_id
    WHERE br.status = 'pending'
    UNION
    SELECT 'ID' AS type, nir.id, nir.status, nir.created_at, COALESCE(u.first_name, 'N/A') AS first_name, COALESCE(u.last_name, 'N/A') AS last_name
    FROM new_id_requests nir 
    LEFT JOIN users u ON nir.email = u.email
    WHERE nir.status = 'pending'
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

$approvedRequests = $pdo->query("
    SELECT 'Birth' AS type, br.id, br.status, br.created_at, COALESCE(u.first_name, 'N/A') AS first_name, COALESCE(u.last_name, 'N/A') AS last_name
    FROM birth_requests br 
    LEFT JOIN users u ON br.father_id = u.kebele_id
    WHERE br.status = 'approved'
    UNION
    SELECT 'ID' AS type, nir.id, nir.status, nir.created_at, COALESCE(u.first_name, 'N/A') AS first_name, COALESCE(u.last_name, 'N/A') AS last_name
    FROM new_id_requests nir 
    LEFT JOIN users u ON nir.email = u.email
    WHERE nir.status = 'approved'
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin Dashboard | Ifa Bula Kebele</title>

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
          <div style="font-size:12px;color:var(--muted)">Admin Dashboard</div>
        </div>
      </div>

      <nav class="mb-6">
        <a href="./announcement.php" class="nav-link"><span class="material-icons-outlined">dashboard</span> Announcement</a>
        <a href="../resindet name/main.php" class="nav-link"><span class="material-icons-outlined">people</span> Residents</a>
        <div class="mt-2">
          <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Requests</div>
          <a href="../view/new_id_view.php" class="nav-link"><span class="material-icons-outlined">add_card</span> New ID Requests</a>
          <a href="../view/update_id_view.php" class="nav-link"><span class="material-icons-outlined">edit</span> Update ID Requests</a>
          <a href="../view/birthview.php" class="nav-link"><span class="material-icons-outlined">child_care</span> Birth Certificate Requests</a>
          <a href="../view/merrigeview.php" class="nav-link"><span class="material-icons-outlined">favorite</span> Marriage Certificate Requests</a>
        </div>

      </nav>

      <div class="mt-auto pt-6">
        <a href="./logout.php" class="nav-link" style="justify-content:flex-start"><span class="material-icons-outlined">logout</span> Logout</a>
        <div class="text-sm small-muted mt-6">Logged in as <strong style="color:var(--green-start)"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong></div>
      </div>
    </aside>

    <!-- MAIN -->
    <section class="main">

      <div class="top-row">
        <div>
          <h1 class="text-2xl font-semibold">Dashboard</h1>
          <div class="small-muted">Plan, prioritize, and manage certificate & ID requests</div>
        </div>

        <div class="flex items-center gap-3">
          <button id="themeBtn" class="icon-btn" title="Toggle theme" onclick="toggleTheme()">
            <span class="material-icons-outlined">brightness_6</span>
          </button>
          <div style="display:flex;flex-direction:column;align-items:flex-end">
            <div style="font-weight:700">Abdi Kebede</div>
            <div class="small-muted" style="font-size:12px">Abdikebede17@gmail.com</div>
          </div>
        </div>
      </div>

      <!-- STAT CARDS -->
      <div class="cards-grid">
        <!-- Primary card showing total combined -->
        <div class="stat-card stat-primary" onclick="toggleTable('combined')">
          <div class="flex justify-between items-start">
            <div>
              <h4 class="text-sm font-semibold">Total Requests</h4>
              <p class="mt-4 text-3xl"><?php echo array_sum($cardData); ?></p>
              <div class="mt-2 small-muted">Sum of birth & ID requests</div>
            </div>
            <div class="pill">Overview</div>
          </div>
        </div>

        <!-- Individual cards -->
        <?php foreach ($cardData as $title => $value): ?>
          <?php
            $key = strtolower(str_replace(' ', '-', $title));
            $accent = $title === 'Rejected Requests' ? 'bg-red-50 text-red-600' : ($title === 'Approved Requests' ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600');
          ?>
          <div class="stat-card" onclick="toggleTable('<?php echo $key; ?>')">
            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-sm font-semibold"><?php echo htmlspecialchars($title); ?></h4>
                <p class="mt-3 text-2xl font-bold"><?php echo htmlspecialchars($value); ?></p>
                <div class="small-muted mt-1">Click to view table</div>
              </div>
              <div class="text-right">
                <div class="rounded-full w-12 h-12 flex items-center justify-center <?php echo $accent; ?>">
                  <span class="material-icons-outlined"><?php echo $title === 'Approved Requests' ? 'check_circle' : ($title === 'Pending Requests' ? 'hourglass_top' : 'cancel'); ?></span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Tables (toggleable) -->
      <div id="tablesWrap">
        <!-- Combined (shows all categories summary) -->
        <div id="combined" class="requests-table" style="display:none;">
          <div style="padding:16px 18px;display:flex;justify-content:space-between;align-items:center">
            <div>
              <div class="text-lg font-semibold">All Requests Summary</div>
              <div class="small-muted">Combined totals for birth & ID requests</div>
            </div>
            <div class="small-muted">Last updated: <?php echo date('Y-m-d H:i:s'); ?></div>
          </div>
          <div style="padding:12px 18px;">
            <div class="grid grid-cols-3 gap-4">
              <div class="p-4 rounded-lg bg-green-50">
                <div class="text-sm font-semibold">Pending</div>
                <div class="text-2xl font-bold"><?php echo htmlspecialchars($combinedRequestData['Pending']); ?></div>
              </div>
              <div class="p-4 rounded-lg bg-green-100">
                <div class="text-sm font-semibold">Approved</div>
                <div class="text-2xl font-bold"><?php echo htmlspecialchars($combinedRequestData['Approved']); ?></div>
              </div>
              <div class="p-4 rounded-lg bg-red-50">
                <div class="text-sm font-semibold">Rejected</div>
                <div class="text-2xl font-bold"><?php echo htmlspecialchars($combinedRequestData['Rejected']); ?></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending requests -->
        <div id="pending-requests" class="requests-table" style="display:none;">
          <div style="padding:14px 18px;display:flex;justify-content:space-between;align-items:center">
            <div>
              <div class="text-lg font-semibold">Pending Requests</div>
              <div class="small-muted">Requests awaiting action</div>
            </div>
            <div class="pill"><?php echo htmlspecialchars($cardData['Pending Requests']); ?> pending</div>
          </div>
          <div style="overflow:auto;">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Type</th>
                  <th>User</th>
                  <th>Status</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($pendingRequests)): ?>
                  <tr><td colspan="5" class="text-center small-muted">No pending requests found.</td></tr>
                <?php else: ?>
                  <?php foreach ($pendingRequests as $request): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($request['id']); ?></td>
                      <td><?php echo htmlspecialchars($request['type']); ?></td>
                      <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                      <td><span style="text-transform:capitalize;"><?php echo htmlspecialchars($request['status']); ?></span></td>
                      <td><?php echo htmlspecialchars(date('Y-m-d H:i:s', strtotime($request['created_at']))); ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Approved requests -->
        <div id="approved-requests" class="requests-table" style="display:none;">
          <div style="padding:14px 18px;display:flex;justify-content:space-between;align-items:center">
            <div>
              <div class="text-lg font-semibold">Approved Requests</div>
              <div class="small-muted">Requests that have been approved</div>
            </div>
            <div class="pill"><?php echo htmlspecialchars($cardData['Approved Requests']); ?> approved</div>
          </div>
          <div style="overflow:auto;">
            <table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Type</th>
                  <th>User</th>
                  <th>Status</th>
                  <th>Created At</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($approvedRequests)): ?>
                  <tr><td colspan="5" class="text-center small-muted">No approved requests found.</td></tr>
                <?php else: ?>
                  <?php foreach ($approvedRequests as $request): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($request['id']); ?></td>
                      <td><?php echo htmlspecialchars($request['type']); ?></td>
                      <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                      <td><span style="text-transform:capitalize;"><?php echo htmlspecialchars($request['status']); ?></span></td>
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
              <div class="text-lg font-semibold">Request Trends (Last 7 Days)</div>
              <div class="small-muted">Daily request volume for birth certificates and IDs</div>
            </div>
            <div class="pill">Trend Analysis</div>
          </div>
          <div id="trend-line-chart" style="height:340px;margin-top:12px;"></div>
        </div>

        <div>
          <div class="stat-card mb-4">
            <div class="text-md font-semibold">Request Status Comparison</div>
            <div id="status-bar-chart" style="height:200px;margin-top:10px;"></div>
          </div>
          <div class="stat-card">
            <div class="text-md font-semibold">Request Type Distribution</div>
            <div id="type-bar-chart" style="height:200px;margin-top:10px;"></div>
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
    const el = document.getElementById(id) || document.getElementById(id + '-requests');
    if (el) {
      el.style.display = 'block';
      // Scroll into view
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  }

  // Charting: pass PHP arrays to JS
  const dashboardData = {
    birthRequests: <?php echo json_encode(array_values($birthRequestData)); ?>,
    idRequests: <?php echo json_encode(array_values($idRequestData)); ?>,
    combinedRequests: <?php echo json_encode(array_values($combinedRequestData)); ?>,
    weeklyData: <?php echo json_encode($weeklyData); ?>,
    labels: ['Pending', 'Approved', 'Rejected']
  };

  const colors = ['#f59e0b', '#10b981', '#ef4444'];

  // Line chart for trends
  function createTrendLineChart() {
    const dates = Object.keys(dashboardData.weeklyData);
    const birthData = dates.map(date => dashboardData.weeklyData[date].birth);
    const idData = dates.map(date => dashboardData.weeklyData[date].id);
    const totalData = dates.map(date => dashboardData.weeklyData[date].total);

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
          name: 'Total Requests',
          data: totalData
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
      colors: ['#10b981', '#3b82f6', '#8b5cf6'],
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

  // Bar chart for status comparison
  function createStatusBarChart() {
    const options = {
      chart: {
        type: 'bar',
        height: '100%',
        toolbar: { show: false }
      },
      series: [
        {
          name: 'Birth Certificates',
          data: dashboardData.birthRequests
        },
        {
          name: 'ID Requests',
          data: dashboardData.idRequests
        }
      ],
      xaxis: {
        categories: dashboardData.labels
      },
      colors: ['#10b981', '#3b82f6'],
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '60%',
          borderRadius: 4
        }
      },
      dataLabels: {
        enabled: false
      },
      legend: {
        position: 'top'
      }
    };

    const chart = new ApexCharts(document.querySelector('#status-bar-chart'), options);
    chart.render();
  }

  // Bar chart for request type distribution
  function createTypeBarChart() {
    const totalBirth = dashboardData.birthRequests.reduce((a, b) => a + b, 0);
    const totalID = dashboardData.idRequests.reduce((a, b) => a + b, 0);

    const options = {
      chart: {
        type: 'bar',
        height: '100%',
        toolbar: { show: false }
      },
      series: [
        {
          name: 'Requests',
          data: [totalBirth, totalID]
        }
      ],
      xaxis: {
        categories: ['Birth Certificates', 'ID Requests']
      },
      colors: ['#10b981'],
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '50%',
          borderRadius: 6
        }
      },
      dataLabels: {
        enabled: true,
        formatter: function(val) {
          return val
        }
      }
    };

    const chart = new ApexCharts(document.querySelector('#type-bar-chart'), options);
    chart.render();
  }

  // Initialize all charts
  createTrendLineChart();
  createStatusBarChart();
  createTypeBarChart();

  // Optionally show the combined summary by default
  toggleTable('combined');
</script>
</body>
</html>