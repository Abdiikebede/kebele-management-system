<?php
session_start();

// Check if user is an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'kebele_management';

// Create database connection
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error log file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $requestId = intval($_POST['requestId']);
    $action = $_POST['action'];
    
    if (in_array($action, ['approve', 'reject'])) {
        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        // Update status in new_id_requests
        $sql = "UPDATE new_id_requests SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            error_log("Prepare failed for UPDATE new_id_requests: " . $conn->error);
            die("Error preparing UPDATE query: " . $conn->error);
        }
        $stmt->bind_param("si", $status, $requestId);
        
        if ($stmt->execute()) {
            $message = "Request #$requestId has been $status.";
            
            // If approved, transfer to resident table
            if ($status === 'approved') {
                // Fetch request data
                $sql = "SELECT nir.email, nir.birth_date, nir.gender, nir.address, nir.kebele_origin, 
                               nir.previous_address, nir.father_name, nir.mother_name, nir.spouse_name, 
                               nir.occupation, nir.education_level, nir.has_disability, nir.disability_desc, 
                               nir.well_go_letter 
                        FROM new_id_requests nir 
                        WHERE nir.id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    error_log("Prepare failed for SELECT new_id_requests: " . $conn->error);
                    $message .= " Error preparing SELECT query: " . $conn->error;
                } else {
                    $stmt->bind_param("i", $requestId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $row = $result->fetch_assoc();
                    
                    if ($row) {
                        // Validate required fields
                        if (empty($row['email']) || empty($row['birth_date']) || empty($row['gender']) || 
                            empty($row['address']) || empty($row['kebele_origin'])) {
                            $message .= " Error: Required fields (email, birth_date, gender, address, kebele_origin) cannot be null.";
                        } else {
                            // Generate unique kebele_id
                            $kebele_id = 'KB' . substr(uniqid(), -6);
                            
                            // Check if resident already exists
                            $sql = "SELECT resident_id FROM resident WHERE email = ?";
                            $checkStmt = $conn->prepare($sql);
                            if ($checkStmt === false) {
                                error_log("Prepare failed for SELECT resident: " . $conn->error);
                                $message .= " Error preparing resident check: " . $conn->error;
                            } else {
                                $checkStmt->bind_param("s", $row['email']);
                                $checkStmt->execute();
                                $checkResult = $checkStmt->get_result();
                                
                                if ($checkResult->num_rows > 0) {
                                    $message .= " Warning: Resident with email {$row['email']} already exists.";
                                } else {
                                    // Insert into resident table
                                    $sql = "INSERT INTO resident (
                                                kebele_id, email, birth_date, gender, address, kebele_origin, 
                                                previous_address, father_name, mother_name, spouse_name, 
                                                occupation, education_level, has_disability, disability_desc, 
                                                well_go_letter
                                            ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                                    $insertStmt = $conn->prepare($sql);
                                    if ($insertStmt === false) {
                                        error_log("Prepare failed for INSERT resident: " . $conn->error);
                                        $message .= " Error preparing INSERT query: " . $conn->error;
                                    } else {
                                        $insertStmt->bind_param(
                                            'sssssssssssssis',
                                            $kebele_id,
                                            $row['email'],
                                            $row['birth_date'],
                                            $row['gender'],
                                            $row['address'],
                                            $row['kebele_origin'],
                                            $row['previous_address'],
                                            $row['father_name'],
                                            $row['mother_name'],
                                            $row['spouse_name'],
                                            $row['occupation'],
                                            $row['education_level'],
                                            $row['has_disability'],
                                            $row['disability_desc'],
                                            $row['well_go_letter']
                                        );
                                        
                                        if (!$insertStmt->execute()) {
                                            error_log("Execute failed for INSERT resident: " . $conn->error);
                                            $message .= " Error adding to resident table: " . $conn->error;
                                        }
                                        $insertStmt->close();
                                    }
                                }
                                $checkStmt->close();
                            }
                        }
                    } else {
                        $message .= " Error: Could not fetch request data.";
                    }
                }
            }
        } else {
            error_log("Execute failed for UPDATE new_id_requests: " . $conn->error);
            $message = "Error updating request: " . $conn->error;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Requests | Ifa Bula Kebele</title>
    
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
            --blue-start: #1e40af;
            --blue-end: #3b82f6;
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
        .status-pending { 
            background: #fef3c7; 
            color: #d97706; 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-weight: 600;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
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
        .status-rejected { 
            background: #fee2e2; 
            color: #dc2626; 
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
        .btn-approve {
            background: linear-gradient(135deg, var(--green-start), var(--green-end));
            color: white;
        }
        .btn-approve:hover {
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }
        .btn-reject {
            background: #ef4444;
            color: white;
        }
        .btn-reject:hover {
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        .btn-details {
            background: #3b82f6;
            color: white;
        }
        .btn-details:hover {
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        .btn-processed {
            background: #6b7280;
            color: white;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .print-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            margin-left: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .print-link:hover {
            text-decoration: underline;
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
        .modal-header {
            background: linear-gradient(135deg, var(--blue-start), var(--blue-end));
            color: white;
            border-radius: 12px 12px 0 0;
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
            border-color: var(--blue-start);
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
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
        .detail-section {
            background: #f8fafc;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .detail-section h6 {
            color: #374151;
            font-weight: 600;
            margin-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 8px;
        }
    </style>
</head>
<body data-theme="light">
    <div class="app-frame">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, var(--blue-start), var(--blue-end)); padding: 24px 32px; color: white;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                        <span class="material-icons-outlined" style="font-size: 24px;">badge</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">ID Requests Management</h1>
                        <p class="text-blue-100 text-sm">Review and process digital ID applications</p>
                    </div>
                </div>
                <button id="themeBtn" class="icon-btn" style="background: rgba(255,255,255,0.2); color: white; border: none; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;" onclick="toggleTheme()">
                    <span class="material-icons-outlined">brightness_6</span>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <?php if (isset($message)): ?>
                <div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
                    <div class="flex items-center gap-2">
                        <span class="material-icons-outlined">
                            <?= strpos($message, 'Error') !== false ? 'error' : 'check_circle' ?>
                        </span>
                        <?= htmlspecialchars($message) ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">All ID Requests</h2>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <span class="material-icons-outlined text-sm">info</span>
                            <span>Total: <?php echo $result->num_rows ?? 0; ?> requests</span>
                        </div>
                    </div>

                    <table id="requestsTable" class="w-full" style="width:100%">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Request ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Applicant Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Address</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Request Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                            $sql = "SELECT nir.id, nir.email, nir.address, nir.created_at, nir.status, nir.kebele_origin, nir.birth_date, nir.gender, 
                                           u.first_name, u.last_name, u.phone_number 
                                    FROM new_id_requests nir 
                                    LEFT JOIN users u ON nir.email = u.email 
                                    ORDER BY nir.created_at DESC";
                            $result = $conn->query($sql);
                            
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $applicantName = htmlspecialchars(($row['first_name'] ?? 'N/A') . ' ' . ($row['last_name'] ?? 'N/A'));
                                    $phone = htmlspecialchars($row['phone_number'] ?? 'N/A');
                                    $requestDate = date('M j, Y H:i', strtotime($row['created_at']));
                                    $statusClass = 'status-' . ($row['status'] ?? 'pending');
                                    
                                    echo "<tr class='hover:bg-gray-50 transition-colors'>";
                                    echo "<td class='px-4 py-3 text-sm font-medium text-gray-900'>#{$row['id']}</td>";
                                    echo "<td class='px-4 py-3 text-sm text-gray-800'>$applicantName</td>";
                                    echo "<td class='px-4 py-3 text-sm text-gray-600'>" . htmlspecialchars($row['address']) . "</td>";
                                    echo "<td class='px-4 py-3 text-sm text-gray-600'>$phone</td>";
                                    echo "<td class='px-4 py-3 text-sm text-gray-600'>$requestDate</td>";
                                    
                                    // Status column
                                    echo "<td class='px-4 py-3 text-sm'>";
                                    echo "<span class='$statusClass'>";
                                    echo "<span class='material-icons-outlined' style='font-size: 14px;'>";
                                    echo ($row['status'] ?? 'pending') === 'pending' ? 'schedule' : (($row['status'] ?? '') === 'approved' ? 'check_circle' : 'cancel');
                                    echo "</span>";
                                    echo ucfirst($row['status'] ?? 'pending');
                                    echo "</span>";
                                    if (($row['status'] ?? '') === 'approved') {
                                        echo "<span class='print-link' onclick=\"window.open('print_id.php?id={$row['id']}', '_blank')\">";
                                        echo "<span class='material-icons-outlined' style='font-size: 14px;'>print</span>";
                                        echo "Print ID";
                                        echo "</span>";
                                    }
                                    echo "</td>";
                                    
                                    // Actions column
                                    echo "<td class='px-4 py-3 text-sm'>";
                                    echo "<div class='flex gap-2'>";
                                    if (($row['status'] ?? 'pending') === 'pending') {
                                        echo "<button class='action-btn btn-approve' data-bs-toggle='modal' data-bs-target='#approveModal' data-id='{$row['id']}'>";
                                        echo "<span class='material-icons-outlined' style='font-size: 14px;'>check</span>";
                                        echo "Approve";
                                        echo "</button>";
                                        echo "<button class='action-btn btn-reject' data-bs-toggle='modal' data-bs-target='#rejectModal' data-id='{$row['id']}'>";
                                        echo "<span class='material-icons-outlined' style='font-size: 14px;'>close</span>";
                                        echo "Reject";
                                        echo "</button>";
                                    } else {
                                        echo "<button class='action-btn btn-processed' disabled>";
                                        echo "<span class='material-icons-outlined' style='font-size: 14px;'>done_all</span>";
                                        echo "Processed";
                                        echo "</button>";
                                    }
                                    
                                    echo "<button class='action-btn btn-details' data-bs-toggle='modal' data-bs-target='#detailsModal' 
                                          data-id='{$row['id']}'
                                          data-applicant='$applicantName'
                                          data-address='" . htmlspecialchars($row['address']) . "'
                                          data-phone='$phone'
                                          data-request-date='$requestDate'
                                          data-status='" . ($row['status'] ?? 'pending') . "'
                                          data-kebele-origin='{$row['kebele_origin']}'
                                          data-birth-date='{$row['birth_date']}'
                                          data-gender='{$row['gender']}'
                                          data-email='{$row['email']}'>";
                                    echo "<span class='material-icons-outlined' style='font-size: 14px;'>visibility</span>";
                                    echo "Details";
                                    echo "</button>";
                                    echo "</div>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='7' class='px-4 py-8 text-center text-gray-500'>No ID requests found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6 flex justify-center">
                <a href="../dashboard/user_management.php" class="btn-secondary inline-flex items-center gap-2">
                    <span class="material-icons-outlined">arrow_back</span>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title font-bold">Approve ID Request</h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <span class="material-icons-outlined text-green-600">check_circle</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Confirm Approval</p>
                                <p class="text-sm text-gray-600">Are you sure you want to approve this ID request? This will create a resident record.</p>
                            </div>
                        </div>
                        <input type="hidden" name="requestId" id="approveRequestId" value="">
                        <input type="hidden" name="action" value="approve">
                    </div>
                    <div class="modal-footer p-4 bg-gray-50 flex gap-3">
                        <button type="button" class="btn-secondary flex-1 justify-center" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-primary flex-1 justify-center">
                            <span class="material-icons-outlined">check</span>
                            Confirm Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <form method="POST" action="">
                    <div class="modal-header" style="background: #ef4444;">
                        <h5 class="modal-title font-bold">Reject ID Request</h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="material-icons-outlined text-red-600">warning</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Confirm Rejection</p>
                                <p class="text-sm text-gray-600">Are you sure you want to reject this ID request?</p>
                            </div>
                        </div>
                        <input type="hidden" name="requestId" id="rejectRequestId" value="">
                        <input type="hidden" name="action" value="reject">
                    </div>
                    <div class="modal-footer p-4 bg-gray-50 flex gap-3">
                        <button type="button" class="btn-secondary flex-1 justify-center" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="action-btn btn-reject flex-1 justify-center">
                            <span class="material-icons-outlined">close</span>
                            Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #3b82f6, #60a5fa);">
                    <h5 class="modal-title font-bold">ID Request Details</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="detail-section">
                            <h6>Applicant Information</h6>
                            <div class="space-y-3">
                                <p><strong class="text-gray-700">Name:</strong> <span id="detailApplicant" class="text-gray-900"></span></p>
                                <p><strong class="text-gray-700">Email:</strong> <span id="detailEmail" class="text-gray-900"></span></p>
                                <p><strong class="text-gray-700">Phone:</strong> <span id="detailPhone" class="text-gray-900"></span></p>
                                <p><strong class="text-gray-700">Birth Date:</strong> <span id="detailBirthDate" class="text-gray-900"></span></p>
                                <p><strong class="text-gray-700">Gender:</strong> <span id="detailGender" class="text-gray-900"></span></p>
                            </div>
                        </div>
                        <div class="detail-section">
                            <h6>Address Information</h6>
                            <div class="space-y-3">
                                <p><strong class="text-gray-700">Address:</strong> <span id="detailAddress" class="text-gray-900"></span></p>
                                <p><strong class="text-gray-700">Kebele Origin:</strong> <span id="detailKebeleOrigin" class="text-gray-900"></span></p>
                            </div>
                        </div>
                    </div>
                    <div class="detail-section">
                        <h6>Request Information</h6>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <p><strong class="text-gray-700">Request ID:</strong> <span id="detailId" class="text-gray-900"></span></p>
                            <p><strong class="text-gray-700">Request Date:</strong> <span id="detailRequestDate" class="text-gray-900"></span></p>
                            <p><strong class="text-gray-700">Status:</strong> <span id="detailStatus" class="text-gray-900"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-4 bg-gray-50">
                    <button type="button" class="btn-secondary justify-center" data-bs-dismiss="modal">Close</button>
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
            $('#requestsTable').DataTable({
                responsive: true,
                order: [[0, 'desc']],
                language: {
                    search: "Search requests:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ requests",
                    paginate: {
                        previous: "‹",
                        next: "›"
                    }
                }
            });
            
            // Approve modal handler
            $('#approveModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var requestId = button.data('id');
                $('#approveRequestId').val(requestId);
            });
            
            // Reject modal handler
            $('#rejectModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var requestId = button.data('id');
                $('#rejectRequestId').val(requestId);
            });
            
            // Details modal handler
            $('#detailsModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                $('#detailId').text(button.data('id'));
                $('#detailApplicant').text(button.data('applicant'));
                $('#detailEmail').text(button.data('email'));
                $('#detailAddress').text(button.data('address'));
                $('#detailPhone').text(button.data('phone'));
                $('#detailRequestDate').text(button.data('request-date'));
                $('#detailStatus').text(button.data('status'));
                $('#detailKebeleOrigin').text(button.data('kebele-origin') === 'same' ? 'This Kebele' : 'Other Kebele');
                $('#detailBirthDate').text(button.data('birth-date'));
                $('#detailGender').text(button.data('gender'));
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>