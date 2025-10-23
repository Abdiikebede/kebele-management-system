<?php
session_start();
require_once '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch active announcements
$current_date = date('Y-m-d H:i:s');
$announcements = [];

$sql = "SELECT a.*, u.first_name, u.last_name 
        FROM announcements a 
        LEFT JOIN users u ON a.created_by = u.id 
        WHERE a.is_active = TRUE 
        AND a.start_date <= ? 
        AND (a.end_date IS NULL OR a.end_date >= ?)
        ORDER BY 
            CASE a.priority 
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
            END,
            a.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $current_date, $current_date);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | Ifa Bula Kebele</title>
    
    <!-- Fonts & icons -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    
    <!-- Tailwind -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
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
        .app-frame {
            max-width: 1200px;
            margin: 28px auto;
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(246,249,246,0.98));
            box-shadow: 0 10px 30px rgba(2,6,23,0.12);
            overflow: hidden;
        }
        .announcement-card {
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        .announcement-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .category-general { border-left-color: #3b82f6; }
        .category-important { border-left-color: #f59e0b; }
        .category-maintenance { border-left-color: #8b5cf6; }
        .category-event { border-left-color: #10b981; }
        .priority-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .priority-urgent { background: #fecaca; color: #dc2626; }
        .priority-high { background: #fed7aa; color: #ea580c; }
        .priority-medium { background: #fef3c7; color: #d97706; }
        .priority-low { background: #d1fae5; color: #065f46; }
        .btn-primary {
            background: linear-gradient(135deg, var(--green-start), var(--green-end));
            color: white;
            padding: 14px 24px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);
        }
    </style>
</head>
<body data-theme="light">
    <div class="app-frame">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, var(--green-start), var(--green-end)); padding: 24px 32px; color: white;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                        <span class="material-icons-outlined" style="font-size: 24px;">campaign</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Kebele Announcements</h1>
                        <p class="text-green-100 text-sm">Stay updated with the latest news and updates</p>
                    </div>
                </div>
                <button id="themeBtn" class="icon-btn" style="background: rgba(255,255,255,0.2); color: white; border: none; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;" onclick="toggleTheme()">
                    <span class="material-icons-outlined">brightness_6</span>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl p-4 shadow-lg border border-gray-200 text-center">
                    <div class="text-2xl font-bold text-blue-600"><?= count($announcements) ?></div>
                    <div class="text-sm text-gray-600">Total Announcements</div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg border border-gray-200 text-center">
                    <div class="text-2xl font-bold text-orange-600">
                        <?= count(array_filter($announcements, fn($a) => $a['priority'] === 'urgent' || $a['priority'] === 'high')) ?>
                    </div>
                    <div class="text-sm text-gray-600">Important</div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg border border-gray-200 text-center">
                    <div class="text-2xl font-bold text-purple-600">
                        <?= count(array_filter($announcements, fn($a) => $a['category'] === 'event')) ?>
                    </div>
                    <div class="text-sm text-gray-600">Events</div>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg border border-gray-200 text-center">
                    <div class="text-2xl font-bold text-green-600">
                        <?= count(array_filter($announcements, fn($a) => $a['category'] === 'maintenance')) ?>
                    </div>
                    <div class="text-sm text-gray-600">Maintenance</div>
                </div>
            </div>

            <!-- Announcements List -->
            <div class="space-y-4">
                <?php if (empty($announcements)): ?>
                    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 text-center">
                        <div class="text-gray-400 mb-4">
                            <span class="material-icons-outlined text-6xl">campaign</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No Active Announcements</h3>
                        <p class="text-gray-500">There are no announcements at the moment. Please check back later.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($announcements as $announcement): ?>
                    <div class="announcement-card category-<?= $announcement['category'] ?> bg-white rounded-xl shadow-lg border border-gray-200 p-6">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="flex-1">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="text-xl font-bold text-gray-800 mb-2">
                                        <?= htmlspecialchars($announcement['title']) ?>
                                    </h3>
                                    <span class="priority-badge priority-<?= $announcement['priority'] ?>">
                                        <span class="material-icons-outlined" style="font-size: 14px;">
                                            <?= $announcement['priority'] === 'urgent' ? 'warning' : 'info' ?>
                                        </span>
                                        <?= ucfirst($announcement['priority']) ?>
                                    </span>
                                </div>
                                
                                <p class="text-gray-600 mb-4 leading-relaxed">
                                    <?= nl2br(htmlspecialchars($announcement['content'])) ?>
                                </p>
                                
                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                    <div class="flex items-center gap-1">
                                        <span class="material-icons-outlined text-sm">category</span>
                                        <span class="capitalize"><?= $announcement['category'] ?></span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="material-icons-outlined text-sm">schedule</span>
                                        <span>Posted: <?= date('M j, Y g:i A', strtotime($announcement['created_at'])) ?></span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="material-icons-outlined text-sm">person</span>
                                        <span>By: <?= htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']) ?></span>
                                    </div>
                                    <?php if ($announcement['end_date']): ?>
                                    <div class="flex items-center gap-1">
                                        <span class="material-icons-outlined text-sm">event_available</span>
                                        <span>Valid until: <?= date('M j, Y g:i A', strtotime($announcement['end_date'])) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Back Button -->
            <div class="mt-6 flex justify-center">
                <a href="../dashboard/kebale.php" class="btn-primary">
                    <span class="material-icons-outlined">arrow_back</span>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle
        function toggleTheme() {
            const body = document.body;
            const current = body.getAttribute('data-theme') || 'light';
            body.setAttribute('data-theme', current === 'dark' ? 'light' : 'dark');
        }

        // Auto-refresh announcements every 5 minutes
        setInterval(() => {
            window.location.reload();
        }, 300000); // 5 minutes
    </script>
</body>
</html>