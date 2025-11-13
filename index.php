<?php
require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();
$user = getCurrentUser();

// Get statistics
$totalEmployees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")->fetch_assoc()['count'];
$pendingPayrolls = $conn->query("SELECT COUNT(*) as count FROM payroll WHERE status = 'pending'")->fetch_assoc()['count'];
$paidSalaries = $conn->query("SELECT SUM(net_salary) as total FROM payroll WHERE status = 'paid' AND MONTH(payment_date) = MONTH(CURRENT_DATE)")->fetch_assoc()['total'] ?? 0;
$totalPayroll = $conn->query("SELECT SUM(net_salary) as total FROM payroll WHERE MONTH(pay_period_start) = MONTH(CURRENT_DATE)")->fetch_assoc()['total'] ?? 0;

// Get recent employees
$recentEmployees = $conn->query("SELECT * FROM employees WHERE status = 'active' ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PayrollPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar.php'; ?>

    <!-- Include Topbar -->
    <?php include 'includes/topbar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1>Dashboard Overview</h1>
            <p>Welcome back, <?php echo htmlspecialchars($user['full_name']); ?>! Here's what's happening with your payroll today.</p>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $totalEmployees; ?></div>
                        <div class="stat-label">Total Employees</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +12% from last month
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $pendingPayrolls; ?></div>
                        <div class="stat-label">Pending Payrolls</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-down"></i> Due in 3 days
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo formatCurrency($paidSalaries); ?></div>
                        <div class="stat-label">Paid Salaries</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> +8% from last month
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo formatCurrency($totalPayroll); ?></div>
                        <div class="stat-label">Total Payroll</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
                <div class="stat-change positive">
                    <i class="fas fa-arrow-up"></i> This month
                </div>
            </div>
        </div>

        <!-- Recent Employees -->
        <div class="content-card">
            <div class="card-header-custom">
                <h3 class="card-title">Recent Employees</h3>
                <a href="employees.php" class="btn-primary-custom">
                    <i class="fas fa-plus"></i> Add Employee
                </a>
            </div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Salary</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($emp = $recentEmployees->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="employee-info">
                                    <div class="employee-avatar">
                                        <?php echo strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong><br>
                                        <small style="color: var(--text-gray);"><?php echo htmlspecialchars($emp['employee_code']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($emp['position']); ?></td>
                            <td><?php echo htmlspecialchars($emp['department']); ?></td>
                            <td><strong><?php echo formatCurrency($emp['base_salary']); ?></strong></td>
                            <td><span class="badge-custom badge-success">Active</span></td>
                            <td>
                                <div class="action-btns">
                                    <a href="employees.php?action=edit&id=<?php echo $emp['id']; ?>" class="action-btn edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="employees.php?action=delete&id=<?php echo $emp['id']; ?>" 
                                       class="action-btn delete" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Include Footer -->
    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>