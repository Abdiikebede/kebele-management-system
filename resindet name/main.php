<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Management | Ifa Bula Kebele</title>
    
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
            --teal-start: #0d9488;
            --teal-end: #14b8a6;
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
        .highlight {
            background-color: #fef3c7;
            font-weight: bold;
            color: #d97706;
            padding: 2px 4px;
            border-radius: 4px;
        }
        .status-approved {
            background: #d1fae5;
            color: #065f46;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .action-btn:hover {
            transform: translateY(-1px);
        }
        .btn-edit {
            background: #f59e0b;
            color: white;
        }
        .btn-edit:hover {
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        .btn-delete:hover {
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        .btn-print {
            background: #3b82f6;
            color: white;
        }
        .btn-print:hover {
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
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
        .search-box {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
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
            border-color: var(--teal-start);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }
        .stats-card {
            background: linear-gradient(135deg, var(--teal-start), var(--teal-end));
            color: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .gender-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .gender-male {
            background: #dbeafe;
            color: #1e40af;
        }
        .gender-female {
            background: #fce7f3;
            color: #be185d;
        }
        .gender-other {
            background: #f3e8ff;
            color: #7e22ce;
        }
    </style>
</head>
<body data-theme="light">
    <div class="app-frame">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, var(--teal-start), var(--teal-end)); padding: 24px 32px; color: white;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                        <span class="material-icons-outlined" style="font-size: 24px;">people</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Resident Management</h1>
                        <p class="text-teal-100 text-sm">Manage approved residents and their information</p>
                    </div>
                </div>
                <button id="themeBtn" class="icon-btn" style="background: rgba(255,255,255,0.2); color: white; border: none; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;" onclick="toggleTheme()">
                    <span class="material-icons-outlined">brightness_6</span>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <!-- Search Section -->
            <div class="search-box mb-6">
                <form method="GET" class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="form-label block text-sm font-semibold text-gray-700 mb-2">Search Residents</label>
                        <input type="text" name="search" class="form-input" 
                               placeholder="Search by name, ID, phone, email..." 
                               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    </div>
                    <div>
                        <button type="submit" class="btn-primary">
                            <span class="material-icons-outlined">search</span>
                            Search
                        </button>
                    </div>
                </form>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center mb-6">
                <a href="resident_form.php" class="btn-primary">
                    <span class="material-icons-outlined">person_add</span>
                    Add New Resident
                </a>
                <a href="../dashboard/user_management.php" class="btn-secondary">
                    <span class="material-icons-outlined">arrow_back</span>
                    Back to Dashboard
                </a>
            </div>

            <!-- Stats Overview -->
            <?php
            require_once '../db_connection.php';
            
            // Get total residents count
            $countSql = "
                SELECT COUNT(*) as total_count
                FROM users u
                INNER JOIN new_id_requests r ON u.email = r.email
                WHERE r.status = 'approved'
            ";
            
            if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
                $search = mysqli_real_escape_string($conn, trim($_GET['search']));
                $countSql .= " AND (
                    u.first_name LIKE '%$search%' OR
                    u.last_name LIKE '%$search%' OR
                    u.phone_number LIKE '%$search%' OR
                    u.email LIKE '%$search%' OR
                    r.id LIKE '%$search%'
                )";
            }
            
            $countResult = mysqli_query($conn, $countSql);
            $totalResidents = $countResult ? mysqli_fetch_assoc($countResult)['total_count'] : 0;
            ?>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="stats-card">
                    <div class="flex items-center justify-center gap-2 mb-2">
                        <span class="material-icons-outlined">groups</span>
                        <h3 class="text-lg font-bold">Total Residents</h3>
                    </div>
                    <p class="text-3xl font-bold"><?= $totalResidents ?></p>
                    <p class="text-teal-100 text-sm mt-1">Approved residents</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg border border-gray-200">
                    <div class="flex items-center gap-2 text-gray-600 mb-2">
                        <span class="material-icons-outlined">search</span>
                        <h3 class="text-sm font-semibold">Search Results</h3>
                    </div>
                    <p class="text-2xl font-bold text-gray-800"><?= $totalResidents ?></p>
                    <p class="text-gray-500 text-sm">Matching records</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg border border-gray-200">
                    <div class="flex items-center gap-2 text-gray-600 mb-2">
                        <span class="material-icons-outlined">verified</span>
                        <h3 class="text-sm font-semibold">Status</h3>
                    </div>
                    <p class="text-2xl font-bold text-green-600">Approved</p>
                    <p class="text-gray-500 text-sm">All residents</p>
                </div>
                <div class="bg-white rounded-xl p-4 shadow-lg border border-gray-200">
                    <div class="flex items-center gap-2 text-gray-600 mb-2">
                        <span class="material-icons-outlined">manage_accounts</span>
                        <h3 class="text-sm font-semibold">Management</h3>
                    </div>
                    <p class="text-2xl font-bold text-blue-600">Active</p>
                    <p class="text-gray-500 text-sm">System ready</p>
                </div>
            </div>

            <!-- Resident Table -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">Approved Residents</h2>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <span class="material-icons-outlined text-sm">list</span>
                            <span>Showing <?= $totalResidents ?> residents</span>
                        </div>
                    </div>

                    <table id="residentsTable" class="w-full" style="width:100%">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Full Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Gender</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            function highlight($text, $search) {
                                if (!$search) return htmlspecialchars($text);
                                return preg_replace(
                                    "/(" . preg_quote($search, '/') . ")/i",
                                    "<span class='highlight'>$1</span>",
                                    htmlspecialchars($text)
                                );
                            }

                            $sql = "
                                SELECT 
                                    u.id,
                                    CONCAT(u.first_name, ' ', u.last_name) AS full_name,
                                    r.gender,
                                    u.phone_number,
                                    u.email,
                                    r.status
                                FROM users u
                                INNER JOIN new_id_requests r ON u.email = r.email
                                WHERE r.status = 'approved'
                            ";

                            $search = '';
                            if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
                                $search = mysqli_real_escape_string($conn, trim($_GET['search']));
                                $sql .= " AND (
                                    u.first_name LIKE '%$search%' OR
                                    u.last_name LIKE '%$search%' OR
                                    u.phone_number LIKE '%$search%' OR
                                    u.email LIKE '%$search%' OR
                                    r.id LIKE '%$search%'
                                )";
                            }

                            $sql .= " ORDER BY u.first_name ASC";

                            $result = mysqli_query($conn, $sql);

                            if (!$result) {
                                echo '<tr><td colspan="7" class="px-4 py-8 text-center text-red-500">Query Error: ' . mysqli_error($conn) . '</td></tr>';
                            } elseif (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $genderClass = 'gender-other';
                                    if ($row['gender'] === 'male') $genderClass = 'gender-male';
                                    if ($row['gender'] === 'female') $genderClass = 'gender-female';
                                    ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            <?= highlight($row['id'], $search) ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-800">
                                            <?= highlight($row['full_name'], $search) ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="gender-badge <?= $genderClass ?>">
                                                <?= htmlspecialchars($row['gender']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <?= highlight($row['phone_number'], $search) ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <?= highlight($row['email'], $search) ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="status-approved">
                                                <span class="material-icons-outlined" style="font-size: 14px;">verified</span>
                                                Approved
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <div class="flex gap-2">
                                                <a href="edit_resident.php?id=<?= $row['id'] ?>" class="action-btn btn-edit">
                                                    <span class="material-icons-outlined" style="font-size: 14px;">edit</span>
                                                    Edit
                                                </a>
                                                <a href="delete_resident.php?id=<?= $row['id'] ?>" 
                                                   class="action-btn btn-delete" 
                                                   onclick="return confirm('Are you sure you want to delete this resident?')">
                                                    <span class="material-icons-outlined" style="font-size: 14px;">delete</span>
                                                    Delete
                                                </a>
                                                <a href="print_resident.php?id=<?= $row['id'] ?>" 
                                                   target="_blank" class="action-btn btn-print">
                                                    <span class="material-icons-outlined" style="font-size: 14px;">print</span>
                                                    Print
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No approved residents found.</td></tr>';
                            }

                            mysqli_close($conn);
                            ?>
                        </tbody>
                    </table>
                </div>
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
            $('#residentsTable').DataTable({
                responsive: true,
                order: [[1, 'asc']], // Sort by name by default
                language: {
                    search: "Search within results:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ residents",
                    paginate: {
                        previous: "‹",
                        next: "›"
                    }
                },
                dom: '<"flex justify-between items-center mb-4"lf>rt<"flex justify-between items-center mt-4"ip>'
            });
        });
    </script>
</body>
</html>