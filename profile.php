<?php
require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();
$user = getCurrentUser();

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$userInfo = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get employee record if exists
$employeeInfo = null;
$stmt = $conn->prepare("SELECT * FROM employees WHERE user_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $employeeInfo = $result->fetch_assoc();
}
$stmt->close();

// Get payroll history if employee
$payrollHistory = [];
if ($employeeInfo) {
    $stmt = $conn->prepare("SELECT * FROM payroll WHERE employee_id = ? ORDER BY pay_period_start DESC LIMIT 10");
    $stmt->bind_param("i", $employeeInfo['id']);
    $stmt->execute();
    $payrollHistory = $stmt->get_result();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PayrollPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: white;
            padding: 40px;
            border-radius: 16px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
        }
        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: 700;
            color: var(--primary);
        }
        .profile-info h2 {
            margin: 0 0 10px 0;
            font-size: 32px;
        }
        .profile-info p {
            margin: 5px 0;
            opacity: 0.9;
        }
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid var(--primary);
        }
        .info-card h4 {
            color: var(--primary-dark);
            margin-bottom: 15px;
            font-size: 18px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--primary-lightest);
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: var(--text-gray);
        }
        .info-value {
            color: var(--text-dark);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/topbar.php'; ?>

    <main class="main-content">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-avatar-large">
                <?php 
                $nameParts = explode(' ', $userInfo['full_name']);
                echo strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                ?>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($userInfo['full_name']); ?></h2>
                <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($userInfo['email']); ?></p>
                <p><i class="fas fa-user-shield"></i> <?php echo ucfirst($userInfo['role']); ?></p>
                <p><i class="fas fa-calendar-alt"></i> Member since <?php echo formatDate($userInfo['created_at']); ?></p>
            </div>
        </div>

        <div class="row">
            <!-- Account Information -->
            <div class="col-md-6 mb-4">
                <div class="content-card">
                    <div class="info-card">
                        <h4><i class="fas fa-user-circle"></i> Account Information</h4>
                        <div class="info-row">
                            <span class="info-label">Username:</span>
                            <span class="info-value"><?php echo htmlspecialchars($userInfo['username']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($userInfo['email']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Role:</span>
                            <span class="info-value">
                                <span class="badge-custom badge-primary"><?php echo ucfirst($userInfo['role']); ?></span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Last Login:</span>
                            <span class="info-value">
                                <?php echo $userInfo['last_login'] ? formatDate($userInfo['last_login']) : 'Never'; ?>
                            </span>
                        </div>
                        <div class="mt-3">
                            <a href="settings.php" class="btn-primary-custom">
                                <i class="fas fa-edit"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Information -->
            <div class="col-md-6 mb-4">
                <div class="content-card">
                    <div class="info-card">
                        <h4><i class="fas fa-id-card"></i> Employee Information</h4>
                        <?php if ($employeeInfo): ?>
                        <div class="info-row">
                            <span class="info-label">Employee Code:</span>
                            <span class="info-value"><?php echo htmlspecialchars($employeeInfo['employee_code']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Position:</span>
                            <span class="info-value"><?php echo htmlspecialchars($employeeInfo['position']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Department:</span>
                            <span class="info-value"><?php echo htmlspecialchars($employeeInfo['department']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Hire Date:</span>
                            <span class="info-value"><?php echo formatDate($employeeInfo['hire_date']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Base Salary:</span>
                            <span class="info-value"><strong><?php echo formatCurrency($employeeInfo['base_salary']); ?></strong></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status:</span>
                            <span class="info-value">
                                <span class="badge-custom badge-success"><?php echo ucfirst($employeeInfo['status']); ?></span>
                            </span>
                        </div>
                        <?php else: ?>
                        <p class="text-center text-muted py-3">
                            <i class="fas fa-info-circle"></i> No employee record linked to this account.
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll History -->
        <?php if ($employeeInfo && $payrollHistory->num_rows > 0): ?>
        <div class="content-card">
            <div class="card-header-custom">
                <h3 class="card-title"><i class="fas fa-history"></i> My Payroll History</h3>
            </div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Pay Period</th>
                            <th>Base Salary</th>
                            <th>Additions</th>
                            <th>Deductions</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($payroll = $payrollHistory->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php echo formatDate($payroll['pay_period_start']); ?> - 
                                <?php echo formatDate($payroll['pay_period_end']); ?>
                            </td>
                            <td><?php echo formatCurrency($payroll['base_salary']); ?></td>
                            <td><?php echo formatCurrency($payroll['overtime_pay'] + $payroll['bonuses']); ?></td>
                            <td><?php echo formatCurrency($payroll['deductions'] + $payroll['tax']); ?></td>
                            <td><strong style="color: var(--primary);"><?php echo formatCurrency($payroll['net_salary']); ?></strong></td>
                            <td>
                                <span class="badge-custom badge-<?php 
                                    echo $payroll['status'] === 'paid' ? 'success' : 
                                        ($payroll['status'] === 'processed' ? 'primary' : 'pending'); 
                                ?>">
                                    <?php echo ucfirst($payroll['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="salary_slip.php?payroll_id=<?php echo $payroll['id']; ?>" 
                                   class="action-btn view" title="View Slip">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>