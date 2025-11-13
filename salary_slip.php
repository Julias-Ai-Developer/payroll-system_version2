<?php
require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();

// Get payroll ID from URL
$payrollId = isset($_GET['payroll_id']) ? intval($_GET['payroll_id']) : 0;

// Get payroll details with employee info
$stmt = $conn->prepare("
    SELECT p.*, e.employee_code, e.first_name, e.last_name, e.email, e.phone, e.position, e.department, e.hire_date
    FROM payroll p
    JOIN employees e ON p.employee_id = e.id
    WHERE p.id = ?
");
$stmt->bind_param("i", $payrollId);
$stmt->execute();
$payroll = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$payroll) {
    header('Location: payroll.php');
    exit();
}

// Calculate totals
$grossSalary = $payroll['base_salary'] + $payroll['overtime_pay'] + $payroll['bonuses'];
$totalDeductions = $payroll['deductions'] + $payroll['tax'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip - <?php echo $payroll['employee_code']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .salary-slip {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }
        .slip-header {
            text-align: center;
            border-bottom: 3px solid var(--primary);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .slip-header h1 {
            color: var(--primary);
            margin-bottom: 5px;
        }
        .slip-header p {
            color: var(--text-gray);
            margin: 0;
        }
        .slip-info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-group label {
            font-weight: 600;
            color: var(--primary-dark);
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .info-group p {
            margin: 0;
            color: var(--text-dark);
        }
        .slip-table {
            width: 100%;
            margin: 20px 0;
        }
        .slip-table th {
            background: var(--bg-light);
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: var(--primary-dark);
            border-bottom: 2px solid var(--primary-lighter);
        }
        .slip-table td {
            padding: 12px;
            border-bottom: 1px solid var(--primary-lightest);
        }
        .slip-table tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .slip-summary {
            background: var(--primary-lightest);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .summary-row.total {
            border-top: 2px solid var(--primary);
            padding-top: 15px;
            margin-top: 15px;
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
        }
        .slip-footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid var(--primary-lightest);
            text-align: center;
            color: var(--text-gray);
            font-size: 14px;
        }
        @media print {
            body {
                background: white;
            }
            .sidebar, .topbar, .footer, .no-print {
                display: none !important;
            }
            .main-content {
                margin: 0;
                padding: 0;
            }
            .salary-slip {
                box-shadow: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>
    <?php include 'includes/topbar.php'; ?>

    <main class="main-content">
        <div class="no-print" style="margin-bottom: 20px;">
            <a href="payroll.php" class="btn-secondary-custom">
                <i class="fas fa-arrow-left"></i> Back to Payroll
            </a>
            <button onclick="window.print()" class="btn-primary-custom">
                <i class="fas fa-print"></i> Print Slip
            </button>
        </div>

        <div class="salary-slip">
            <div class="slip-header">
                <h1><i class="fas fa-money-check-alt"></i> SALARY SLIP</h1>
                <p>Pay Period: <?php echo formatDate($payroll['pay_period_start']); ?> - <?php echo formatDate($payroll['pay_period_end']); ?></p>
            </div>

            <div class="slip-info-row">
                <div class="info-group">
                    <label>Employee Name</label>
                    <p><?php echo htmlspecialchars($payroll['first_name'] . ' ' . $payroll['last_name']); ?></p>
                </div>
                <div class="info-group">
                    <label>Employee Code</label>
                    <p><?php echo htmlspecialchars($payroll['employee_code']); ?></p>
                </div>
                <div class="info-group">
                    <label>Position</label>
                    <p><?php echo htmlspecialchars($payroll['position']); ?></p>
                </div>
                <div class="info-group">
                    <label>Department</label>
                    <p><?php echo htmlspecialchars($payroll['department']); ?></p>
                </div>
                <div class="info-group">
                    <label>Email</label>
                    <p><?php echo htmlspecialchars($payroll['email']); ?></p>
                </div>
                <div class="info-group">
                    <label>Hire Date</label>
                    <p><?php echo formatDate($payroll['hire_date']); ?></p>
                </div>
            </div>

            <h3 style="color: var(--primary-dark); margin-top: 30px;">EARNINGS</h3>
            <table class="slip-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Base Salary</td>
                        <td class="text-right"><strong><?php echo formatCurrency($payroll['base_salary']); ?></strong></td>
                    </tr>
                    <?php if ($payroll['overtime_hours'] > 0): ?>
                    <tr>
                        <td>Overtime Pay (<?php echo $payroll['overtime_hours']; ?> hours)</td>
                        <td class="text-right"><strong><?php echo formatCurrency($payroll['overtime_pay']); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($payroll['bonuses'] > 0): ?>
                    <tr>
                        <td>Bonuses</td>
                        <td class="text-right"><strong><?php echo formatCurrency($payroll['bonuses']); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr style="background: var(--bg-light); font-weight: 600;">
                        <td>Gross Salary</td>
                        <td class="text-right"><?php echo formatCurrency($grossSalary); ?></td>
                    </tr>
                </tbody>
            </table>

            <h3 style="color: var(--primary-dark); margin-top: 30px;">DEDUCTIONS</h3>
            <table class="slip-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($payroll['deductions'] > 0): ?>
                    <tr>
                        <td>Other Deductions</td>
                        <td class="text-right"><strong><?php echo formatCurrency($payroll['deductions']); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td>Income Tax (20%)</td>
                        <td class="text-right"><strong><?php echo formatCurrency($payroll['tax']); ?></strong></td>
                    </tr>
                    <tr style="background: var(--bg-light); font-weight: 600;">
                        <td>Total Deductions</td>
                        <td class="text-right"><?php echo formatCurrency($totalDeductions); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="slip-summary">
                <div class="summary-row">
                    <span>Gross Salary:</span>
                    <strong><?php echo formatCurrency($grossSalary); ?></strong>
                </div>
                <div class="summary-row">
                    <span>Total Deductions:</span>
                    <strong>- <?php echo formatCurrency($totalDeductions); ?></strong>
                </div>
                <div class="summary-row total">
                    <span>NET SALARY:</span>
                    <span><?php echo formatCurrency($payroll['net_salary']); ?></span>
                </div>
            </div>

            <div class="slip-footer">
                <p><strong>This is a computer-generated salary slip and does not require a signature.</strong></p>
                <p>Generated on: <?php echo date('F d, Y'); ?> | Status: <?php echo ucfirst($payroll['status']); ?></p>
                <?php if ($payroll['payment_date']): ?>
                <p>Payment Date: <?php echo formatDate($payroll['payment_date']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>