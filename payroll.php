<?php
require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle Process Payroll
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'process') {
        $employeeId = $_POST['employee_id'];
        $payPeriodStart = $_POST['pay_period_start'];
        $payPeriodEnd = $_POST['pay_period_end'];
        $baseSalary = $_POST['base_salary'];
        $overtimeHours = $_POST['overtime_hours'] ?? 0;
        $overtimePay = $_POST['overtime_pay'] ?? 0;
        $bonuses = $_POST['bonuses'] ?? 0;
        $deductions = $_POST['deductions'] ?? 0;
        $tax = $_POST['tax'] ?? 0;
        
        $netSalary = $baseSalary + $overtimePay + $bonuses - $deductions - $tax;
        
        $stmt = $conn->prepare("INSERT INTO payroll (employee_id, pay_period_start, pay_period_end, base_salary, overtime_hours, overtime_pay, bonuses, deductions, tax, net_salary, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("issddddddd", $employeeId, $payPeriodStart, $payPeriodEnd, $baseSalary, $overtimeHours, $overtimePay, $bonuses, $deductions, $tax, $netSalary);
        
        if ($stmt->execute()) {
            $message = 'Payroll processed successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error processing payroll!';
            $messageType = 'danger';
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'mark_paid') {
        $payrollId = $_POST['payroll_id'];
        $stmt = $conn->prepare("UPDATE payroll SET status='paid', payment_date=CURDATE() WHERE id=?");
        $stmt->bind_param("i", $payrollId);
        
        if ($stmt->execute()) {
            $message = 'Payroll marked as paid!';
            $messageType = 'success';
        }
        $stmt->close();
    }
}

// Get all payroll records with employee info
$payrolls = $conn->query("
    SELECT p.*, e.first_name, e.last_name, e.employee_code, e.position, e.department 
    FROM payroll p 
    JOIN employees e ON p.employee_id = e.id 
    ORDER BY p.created_at DESC
");

// Get active employees for dropdown
$employees = $conn->query("SELECT id, employee_code, first_name, last_name, base_salary FROM employees WHERE status='active' ORDER BY first_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll - PayrollPro</title>
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
            <h1>Payroll Management</h1>
            <p>Process and manage employee payrolls</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Process New Payroll -->
        <div class="content-card mb-4">
            <div class="card-header-custom">
                <h3 class="card-title">Process New Payroll</h3>
            </div>
            <form method="POST" action="" id="payrollForm">
                <input type="hidden" name="action" value="process">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Select Employee</label>
                        <select name="employee_id" id="employeeSelect" class="form-control-custom" required>
                            <option value="">Choose Employee</option>
                            <?php while ($emp = $employees->fetch_assoc()): ?>
                            <option value="<?php echo $emp['id']; ?>" data-salary="<?php echo $emp['base_salary']; ?>">
                                <?php echo $emp['employee_code'] . ' - ' . $emp['first_name'] . ' ' . $emp['last_name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Pay Period Start</label>
                        <input type="date" name="pay_period_start" class="form-control-custom" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Pay Period End</label>
                        <input type="date" name="pay_period_end" class="form-control-custom" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Base Salary</label>
                        <input type="number" name="base_salary" id="baseSalary" class="form-control-custom" step="0.01" required readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Overtime Hours</label>
                        <input type="number" name="overtime_hours" id="overtimeHours" class="form-control-custom" step="0.01" value="0" onchange="calculateNetSalary()">
                    </div>
                    
                    <div class="form-group">
                        <label>Overtime Pay</label>
                        <input type="number" name="overtime_pay" id="overtimePay" class="form-control-custom" step="0.01" value="0" onchange="calculateNetSalary()">
                    </div>
                    
                    <div class="form-group">
                        <label>Bonuses</label>
                        <input type="number" name="bonuses" id="bonuses" class="form-control-custom" step="0.01" value="0" onchange="calculateNetSalary()">
                    </div>
                    
                    <div class="form-group">
                        <label>Deductions</label>
                        <input type="number" name="deductions" id="deductions" class="form-control-custom" step="0.01" value="0" onchange="calculateNetSalary()">
                    </div>
                    
                    <div class="form-group">
                        <label>Tax (20%)</label>
                        <input type="number" name="tax" id="tax" class="form-control-custom" step="0.01" value="0" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Net Salary</label>
                        <input type="number" id="netSalary" class="form-control-custom" step="0.01" value="0" readonly style="font-weight: bold; color: var(--primary);">
                    </div>
                </div>
                
                <button type="submit" class="btn-primary-custom mt-3">
                    <i class="fas fa-check-circle"></i> Process Payroll
                </button>
            </form>
        </div>

        <!-- Payroll Records -->
        <div class="content-card">
            <div class="card-header-custom">
                <h3 class="card-title">Payroll Records</h3>
                <div class="d-flex gap-2">
                    <select id="statusFilter" class="form-control-custom" style="max-width: 200px;">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processed">Processed</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table-custom" id="payrollTable">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Period</th>
                            <th>Base Salary</th>
                            <th>Additions</th>
                            <th>Deductions</th>
                            <th>Net Salary</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($payroll = $payrolls->fetch_assoc()): ?>
                        <tr data-status="<?php echo $payroll['status']; ?>">
                            <td>
                                <div class="employee-info">
                                    <div class="employee-avatar">
                                        <?php echo strtoupper(substr($payroll['first_name'], 0, 1) . substr($payroll['last_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($payroll['first_name'] . ' ' . $payroll['last_name']); ?></strong><br>
                                        <small style="color: var(--text-gray);"><?php echo htmlspecialchars($payroll['employee_code']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php echo formatDate($payroll['pay_period_start']); ?> - 
                                <?php echo formatDate($payroll['pay_period_end']); ?>
                            </td>
                            <td><strong><?php echo formatCurrency($payroll['base_salary']); ?></strong></td>
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
                                <div class="action-btns">
                                    <?php if ($payroll['status'] !== 'paid'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="mark_paid">
                                        <input type="hidden" name="payroll_id" value="<?php echo $payroll['id']; ?>">
                                        <button type="submit" class="action-btn view" title="Mark as Paid" 
                                                onclick="return confirm('Mark this payroll as paid?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <a href="salary_slip.php?payroll_id=<?php echo $payroll['id']; ?>" 
                                       class="action-btn edit" title="View Slip">
                                        <i class="fas fa-file-invoice"></i>
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

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Auto-fill base salary when employee is selected
        document.getElementById('employeeSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const baseSalary = selectedOption.getAttribute('data-salary');
            document.getElementById('baseSalary').value = baseSalary;
            calculateNetSalary();
        });

        // Calculate net salary
        function calculateNetSalary() {
            const baseSalary = parseFloat(document.getElementById('baseSalary').value) || 0;
            const overtimePay = parseFloat(document.getElementById('overtimePay').value) || 0;
            const bonuses = parseFloat(document.getElementById('bonuses').value) || 0;
            const deductions = parseFloat(document.getElementById('deductions').value) || 0;
            
            const grossSalary = baseSalary + overtimePay + bonuses;
            const tax = grossSalary * 0.20; // 20% tax
            
            document.getElementById('tax').value = tax.toFixed(2);
            
            const netSalary = grossSalary - deductions - tax;
            document.getElementById('netSalary').value = netSalary.toFixed(2);
        }

        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#payrollTable tbody tr');
            
            tableRows.forEach(row => {
                const status = row.getAttribute('data-status');
                row.style.display = !filterValue || status === filterValue ? '' : 'none';
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>