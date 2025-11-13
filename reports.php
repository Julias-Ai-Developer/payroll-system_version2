<?php
require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();

// Get monthly payroll summary
$monthlyData = $conn->query("
    SELECT 
        DATE_FORMAT(pay_period_start, '%Y-%m') as month,
        COUNT(*) as total_payrolls,
        SUM(net_salary) as total_paid,
        AVG(net_salary) as avg_salary
    FROM payroll
    WHERE status = 'paid'
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
");

// Get department-wise salary
$deptData = $conn->query("
    SELECT 
        e.department,
        COUNT(DISTINCT e.id) as employee_count,
        SUM(p.net_salary) as total_salary,
        AVG(p.net_salary) as avg_salary
    FROM employees e
    LEFT JOIN payroll p ON e.id = p.employee_id AND p.status = 'paid' AND MONTH(p.pay_period_start) = MONTH(CURRENT_DATE)
    WHERE e.status = 'active'
    GROUP BY e.department
    ORDER BY total_salary DESC
");

// Get recent transactions
$recentTransactions = $conn->query("
    SELECT p.*, e.first_name, e.last_name, e.employee_code
    FROM payroll p
    JOIN employees e ON p.employee_id = e.id
    WHERE p.status = 'paid'
    ORDER BY p.payment_date DESC
    LIMIT 10
");

// Get statistics
$totalEmployees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status = 'active'")->fetch_assoc()['count'];
$totalPaidThisMonth = $conn->query("SELECT SUM(net_salary) as total FROM payroll WHERE status = 'paid' AND MONTH(payment_date) = MONTH(CURRENT_DATE)")->fetch_assoc()['total'] ?? 0;
$avgSalary = $conn->query("SELECT AVG(base_salary) as avg FROM employees WHERE status = 'active'")->fetch_assoc()['avg'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - PayrollPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/topbar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>Reports & Analytics</h1>
            <p>View comprehensive payroll reports and analytics</p>
        </div>

        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo $totalEmployees; ?></div>
                        <div class="stat-label">Active Employees</div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo formatCurrency($totalPaidThisMonth); ?></div>
                        <div class="stat-label">Paid This Month</div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo formatCurrency($avgSalary); ?></div>
                        <div class="stat-label">Average Salary</div>
                    </div>
                    <div class="stat-icon info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <div>
                        <div class="stat-value"><?php echo date('M Y'); ?></div>
                        <div class="stat-label">Current Period</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fas fa-calendar"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Payroll Trend -->
        <div class="content-card">
            <div class="card-header-custom">
                <h3 class="card-title">Monthly Payroll Trend</h3>
                <button class="btn-secondary-custom" onclick="exportTableToCSV('monthlyTable', 'monthly-report.csv')">
                    <i class="fas fa-download"></i> Export CSV
                </button>
            </div>
            <div class="table-responsive">
                <table class="table-custom" id="monthlyTable">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total Payrolls</th>
                            <th>Total Amount Paid</th>
                            <th>Average Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $monthlyData->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo date('F Y', strtotime($row['month'] . '-01')); ?></strong></td>
                            <td><?php echo $row['total_payrolls']; ?></td>
                            <td><strong style="color: var(--primary);"><?php echo formatCurrency($row['total_paid']); ?></strong></td>
                            <td><?php echo formatCurrency($row['avg_salary']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Department-wise Report -->
        <div class="content-card">
            <div class="card-header-custom">
                <h3 class="card-title">Department-wise Salary Report</h3>
                <button class="btn-secondary-custom" onclick="exportTableToCSV('deptTable', 'department-report.csv')">
                    <i class="fas fa-download"></i> Export CSV
                </button>
            </div>
            <div class="table-responsive">
                <table class="table-custom" id="deptTable">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Employees</th>
                            <th>Total Salary (This Month)</th>
                            <th>Average Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $deptData->data_seek(0); while ($row = $deptData->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['department']); ?></strong></td>
                            <td><?php echo $row['employee_count']; ?></td>
                            <td><strong style="color: var(--primary);"><?php echo formatCurrency($row['total_salary'] ?? 0); ?></strong></td>
                            <td><?php echo formatCurrency($row['avg_salary'] ?? 0); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="content-card">
            <div class="card-header-custom">
                <h3 class="card-title">Recent Transactions</h3>
            </div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Payment Date</th>
                            <th>Pay Period</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($trans = $recentTransactions->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="employee-info">
                                    <div class="employee-avatar">
                                        <?php echo strtoupper(substr($trans['first_name'], 0, 1) . substr($trans['last_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($trans['first_name'] . ' ' . $trans['last_name']); ?></strong><br>
                                        <small style="color: var(--text-gray);"><?php echo htmlspecialchars($trans['employee_code']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo formatDate($trans['payment_date']); ?></td>
                            <td>
                                <?php echo formatDate($trans['pay_period_start']); ?> - 
                                <?php echo formatDate($trans['pay_period_end']); ?>
                            </td>
                            <td><strong style="color: var(--primary);"><?php echo formatCurrency($trans['net_salary']); ?></strong></td>
                            <td><span class="badge-custom badge-success">Paid</span></td>
                            <td>
                                <a href="salary_slip.php?payroll_id=<?php echo $trans['id']; ?>" 
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

        <!-- Export Options -->
        <div class="content-card">
            <div class="card-header-custom">
                <h3 class="card-title">Generate Reports</h3>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Report Type</label>
                    <select class="form-control-custom">
                        <option>Monthly Payroll Report</option>
                        <option>Department-wise Report</option>
                        <option>Employee Salary Report</option>
                        <option>Tax Report</option>
                        <option>Yearly Summary</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>From Date</label>
                    <input type="date" class="form-control-custom">
                </div>
                <div class="form-group">
                    <label>To Date</label>
                    <input type="date" class="form-control-custom">
                </div>
                <div class="form-group">
                    <label>Format</label>
                    <select class="form-control-custom">
                        <option>PDF</option>
                        <option>Excel</option>
                        <option>CSV</option>
                    </select>
                </div>
            </div>
            <button class="btn-primary-custom mt-3">
                <i class="fas fa-file-export"></i> Generate Report
            </button>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>