<?php
session_start();
require_once '../db_connection.php';

// Check if user is admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    $category = $conn->real_escape_string($_POST['category']);
    $priority = $conn->real_escape_string($_POST['priority']);
    $start_date = $conn->real_escape_string($_POST['start_date']);
    $end_date = !empty($_POST['end_date']) ? $conn->real_escape_string($_POST['end_date']) : NULL;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $created_by = $_SESSION['user_id'];

    $sql = "INSERT INTO announcements (title, content, category, priority, start_date, end_date, is_active, created_by) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssii", $title, $content, $category, $priority, $start_date, $end_date, $is_active, $created_by);
    
    if ($stmt->execute()) {
        $message = "Announcement published successfully!";
        $message_type = "success";
    } else {
        $message = "Error publishing announcement: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $message = "Announcement deleted successfully!";
        $message_type = "success";
    } else {
        $message = "Error deleting announcement: " . $conn->error;
        $message_type = "error";
    }
    $stmt->close();
}

// Fetch all announcements
$announcements = [];
$sql = "SELECT a.*, u.first_name, u.last_name 
        FROM announcements a 
        LEFT JOIN users u ON a.created_by = u.id 
        ORDER BY a.created_at DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Announcements | Ifa Bula Kebele</title>
    
    <!-- Fonts & icons -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    
    <!-- Tailwind -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root{
            --green-start: #0f766e;
            --green-mid: #059669;
            --green-end: #10b981;
            --orange-start: #ea580c;
            --orange-end: #f97316;
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
            max-width: 1400px;
            margin: 28px auto;
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(246,249,246,0.98));
            box-shadow: 0 10px 30px rgba(2,6,23,0.12);
            overflow: hidden;
        }
        .category-general { background: #dbeafe; color: #1e40af; }
        .category-important { background: #fef3c7; color: #d97706; }
        .category-maintenance { background: #f3e8ff; color: #7e22ce; }
        .category-event { background: #d1fae5; color: #065f46; }
        .priority-low { background: #d1fae5; color: #065f46; }
        .priority-medium { background: #fef3c7; color: #d97706; }
        .priority-high { background: #fed7aa; color: #ea580c; }
        .priority-urgent { background: #fecaca; color: #dc2626; }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
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
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(5, 150, 105, 0.3);
        }
        .btn-secondary {
            background: #f8fafc;
            color: #475569;
            padding: 14px 24px;
            border-radius: 10px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            text-align: center;
        }
        .btn-secondary:hover {
            background: #f1f5f9;
            transform: translateY(-1px);
        }
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.2s;
            background: #ffffff;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--green-mid);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }
        .message {
            margin: 16px 0;
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 600;
        }
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
    </style>
</head>
<body data-theme="light">
    <div class="app-frame">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, var(--orange-start), var(--orange-end)); padding: 24px 32px; color: white;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                        <span class="material-icons-outlined" style="font-size: 24px;">campaign</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Announcement Management</h1>
                        <p class="text-orange-100 text-sm">Create and manage kebele announcements</p>
                    </div>
                </div>
                <button id="themeBtn" class="icon-btn" style="background: rgba(255,255,255,0.2); color: white; border: none; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;" onclick="toggleTheme()">
                    <span class="material-icons-outlined">brightness_6</span>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <?php if ($message): ?>
                <div class="message <?= $message_type === 'success' ? 'success' : 'error' ?>">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined">
                            <?= $message_type === 'success' ? 'check_circle' : 'error' ?>
                        </span>
                        <?= htmlspecialchars($message) ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Create Announcement Form -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Create New Announcement</h2>
                <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="form-label block text-sm font-semibold text-gray-700 mb-2">Title *</label>
                        <input type="text" name="title" class="form-input" placeholder="Enter announcement title" required>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="form-label block text-sm font-semibold text-gray-700 mb-2">Content *</label>
                        <textarea name="content" rows="4" class="form-input" placeholder="Enter announcement content" required></textarea>
                    </div>
                    
                    <div>
                        <label class="form-label block text-sm font-semibold text-gray-700 mb-2">Category</label>
                        <select name="category" class="form-input" required>
                            <option value="general">General</option>
                            <option value="important">Important</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="event">Event</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="form-label block text-sm font-semibold text-gray-700 mb-2">Priority</label>
                        <select name="priority" class="form-input" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="form-label block text-sm font-semibold text-gray-700 mb-2">Start Date *</label>
                        <input type="datetime-local" name="start_date" class="form-input" required>
                    </div>
                    
                    <div>
                        <label class="form-label block text-sm font-semibold text-gray-700 mb-2">End Date (Optional)</label>
                        <input type="datetime-local" name="end_date" class="form-input">
                    </div>
                    
                    <div class="md:col-span-2 flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" checked class="rounded text-green-600 focus:ring-green-500">
                        <label for="is_active" class="text-sm text-gray-700">Publish immediately</label>
                    </div>
                    
                    <div class="md:col-span-2">
                        <button type="submit" class="btn-primary">
                            <span class="material-icons-outlined">campaign</span>
                            Publish Announcement
                        </button>
                    </div>
                </form>
            </div>

            <!-- Announcements List -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">All Announcements</h2>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <span class="material-icons-outlined text-sm">list</span>
                            <span>Total: <?= count($announcements) ?> announcements</span>
                        </div>
                    </div>

                    <table id="announcementsTable" class="w-full" style="width:100%">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Title</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Priority</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Start Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created By</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($announcements as $announcement): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                    <?= htmlspecialchars($announcement['title']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="status-badge category-<?= $announcement['category'] ?>">
                                        <?= ucfirst($announcement['category']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="status-badge priority-<?= $announcement['priority'] ?>">
                                        <?= ucfirst($announcement['priority']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="status-badge <?= $announcement['is_active'] ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= $announcement['is_active'] ? 'Active' : 'Inactive' ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?= date('M j, Y H:i', strtotime($announcement['start_date'])) ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    <?= htmlspecialchars($announcement['first_name'] . ' ' . $announcement['last_name']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex gap-2">
                                        <a href="edit_announcement.php?id=<?= $announcement['id'] ?>" class="btn-secondary text-sm">
                                            <span class="material-icons-outlined" style="font-size: 14px;">edit</span>
                                            Edit
                                        </a>
                                        <a href="?delete_id=<?= $announcement['id'] ?>" 
                                           class="btn-secondary text-sm bg-red-50 text-red-600 border-red-200"
                                           onclick="return confirm('Are you sure you want to delete this announcement?')">
                                            <span class="material-icons-outlined" style="font-size: 14px;">delete</span>
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6 flex justify-center">
                <a href="../dashboard/user_management.php" class="btn-secondary">
                    <span class="material-icons-outlined">arrow_back</span>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Theme toggle
        function toggleTheme() {
            const body = document.body;
            const current = body.getAttribute('data-theme') || 'light';
            body.setAttribute('data-theme', current === 'dark' ? 'light' : 'dark');
        }

        $(document).ready(function() {
            // Initialize DataTable
            $('#announcementsTable').DataTable({
                responsive: true,
                order: [[4, 'desc']], // Sort by start date
                language: {
                    search: "Search announcements:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ announcements",
                    paginate: {
                        previous: "‹",
                        next: "›"
                    }
                }
            });

            // Set default start date to current datetime
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.querySelector('input[name="start_date"]').value = now.toISOString().slice(0, 16);
        });
    </script>
</body>
</html>