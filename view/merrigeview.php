<?php
// Include database connection
include '../db_connection.php';

// 1. First, check if the table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'marriage_certificate_requests'");

// Handle Accept/Reject actions
$action_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $statusValue = 'approved';
        $stmt = $conn->prepare("UPDATE marriage_certificate_requests SET status = ?, rejection_reason = NULL WHERE id = ?");
        $stmt->bind_param("si", $statusValue, $request_id);
    } elseif ($action === 'reject') {
        $statusValue = 'rejected';
        $rejection_reason = isset($_POST['rejection_reason']) ? $conn->real_escape_string($_POST['rejection_reason']) : '';
        $stmt = $conn->prepare("UPDATE marriage_certificate_requests SET status = ?, rejection_reason = ? WHERE id = ?");
        $stmt->bind_param("ssi", $statusValue, $rejection_reason, $request_id);
    }
    
    if (isset($stmt)) {
        if ($stmt->execute()) {
            $action_message = "<div class='message success'>Request #$request_id has been $statusValue.</div>";
        } else {
            $action_message = "<div class='message error'>Error processing request: " . $stmt->error . "</div>";
        }
        $stmt->close();
    }
}

// Fetch all requests for display
$requests = [];
$sql = "SELECT id, applicant_name, purpose, created_at, status 
        FROM marriage_certificate_requests 
        ORDER BY created_at DESC";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    }
} else {
    // Fallback if status column doesn't exist
    $sql = "SELECT id, applicant_name, purpose, created_at, 'pending' as status 
            FROM marriage_certificate_requests 
            ORDER BY created_at DESC";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $requests[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marriage Certificate Requests | Ifa Bula Kebele</title>
    
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
            --purple-start: #7c3aed;
            --purple-end: #a855f7;
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
            background: linear-gradient(135deg, var(--purple-start), var(--purple-end));
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
            border-color: var(--purple-start);
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
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
    </style>
</head>
<body data-theme="light">
    <div class="app-frame">
        <!-- Header -->
        <div style="background: linear-gradient(135deg, var(--purple-start), var(--purple-end)); padding: 24px 32px; color: white;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;">
                        <span class="material-icons-outlined" style="font-size: 24px;">favorite</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Marriage Certificate Requests</h1>
                        <p class="text-purple-100 text-sm">Manage and process marriage certificate applications</p>
                    </div>
                </div>
                <button id="themeBtn" class="icon-btn" style="background: rgba(255,255,255,0.2); color: white; border: none; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer;" onclick="toggleTheme()">
                    <span class="material-icons-outlined">brightness_6</span>
                </button>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6">
            <?php echo $action_message; ?>

            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800">All Marriage Certificate Requests</h2>
                        <div class="flex items-center gap-2 text-sm text-gray-600">
                            <span class="material-icons-outlined text-sm">info</span>
                            <span>Total: <?php echo count($requests); ?> requests</span>
                        </div>
                    </div>

                    <table id="requestsTable" class="w-full" style="width:100%">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Request ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Applicant Name</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Purpose</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Request Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($requests as $request): ?>
                            <tr class='hover:bg-gray-50 transition-colors'>
                                <td class='px-4 py-3 text-sm font-medium text-gray-900'>#<?php echo htmlspecialchars($request['id']); ?></td>
                                <td class='px-4 py-3 text-sm text-gray-800'><?php echo htmlspecialchars($request['applicant_name']); ?></td>
                                <td class='px-4 py-3 text-sm text-gray-600'><?php echo htmlspecialchars($request['purpose']); ?></td>
                                <td class='px-4 py-3 text-sm text-gray-600'><?php echo date('M j, Y H:i', strtotime($request['created_at'])); ?></td>
                                <td class='px-4 py-3 text-sm'>
                                    <span class='status-<?php echo $request['status']; ?>'>
                                        <span class='material-icons-outlined' style='font-size: 14px;'>
                                            <?php echo $request['status'] === 'pending' ? 'schedule' : ($request['status'] === 'approved' ? 'check_circle' : 'cancel'); ?>
                                        </span>
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
                                </td>
                                <td class='px-4 py-3 text-sm'>
                                    <div class='flex gap-2'>
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <button class='action-btn btn-approve' 
                                                    data-bs-toggle='modal' 
                                                    data-bs-target='#approveModal' 
                                                    data-id='<?php echo $request['id']; ?>'>
                                                <span class='material-icons-outlined' style='font-size: 14px;'>check</span>
                                                Approve
                                            </button>
                                            <button class='action-btn btn-reject' 
                                                    data-bs-toggle='modal' 
                                                    data-bs-target='#rejectModal' 
                                                    data-id='<?php echo $request['id']; ?>'>
                                                <span class='material-icons-outlined' style='font-size: 14px;'>close</span>
                                                Reject
                                            </button>
                                        <?php else: ?>
                                            <button class='action-btn btn-processed' disabled>
                                                <span class='material-icons-outlined' style='font-size: 14px;'>done_all</span>
                                                Processed
                                            </button>
                                        <?php endif; ?>
                                        <button class='action-btn btn-details' 
                                                data-bs-toggle='modal' 
                                                data-bs-target='#detailsModal'
                                                data-id='<?php echo $request['id']; ?>'
                                                onclick="loadRequestDetails(<?php echo $request['id']; ?>)">
                                            <span class='material-icons-outlined' style='font-size: 14px;'>visibility</span>
                                            Details
                                        </button>
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
                        <h5 class="modal-title font-bold">Approve Request</h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <span class="material-icons-outlined text-green-600">check_circle</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Confirm Approval</p>
                                <p class="text-sm text-gray-600">Are you sure you want to approve this marriage certificate request?</p>
                            </div>
                        </div>
                        <input type="hidden" name="request_id" id="approveRequestId" value="">
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
                        <h5 class="modal-title font-bold">Reject Request</h5>
                        <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="material-icons-outlined text-red-600">warning</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Reject Request</p>
                                <p class="text-sm text-gray-600">Please provide a reason for rejecting this marriage certificate request:</p>
                            </div>
                        </div>
                        <input type="hidden" name="request_id" id="rejectRequestId" value="">
                        <input type="hidden" name="action" value="reject">
                        <textarea class="form-input" name="rejection_reason" rows="3" placeholder="Enter rejection reason..." required></textarea>
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
                    <h5 class="modal-title font-bold">Request Details</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-6" id="requestDetails">
                    <div class="text-center py-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                            <span class="material-icons-outlined text-blue-600 text-2xl">hourglass_empty</span>
                        </div>
                        <p class="text-gray-600">Loading request details...</p>
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
        });
        
        function loadRequestDetails(requestId) {
            $.ajax({
                url: 'get_marriage_request_details.php',
                type: 'GET',
                data: { id: requestId },
                success: function(response) {
                    $('#requestDetails').html(response);
                },
                error: function() {
                    $('#requestDetails').html(`
                        <div class="alert alert-danger flex items-center gap-2">
                            <span class="material-icons-outlined">error</span>
                            Error loading request details
                        </div>
                    `);
                }
            });
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>