<?php
require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle Add/Edit/Delete operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $stmt = $conn->prepare("INSERT INTO employees (employee_code, first_name, last_name, email, phone, position, department, base_salary, hire_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssdss", 
                $_POST['employee_code'], 
                $_POST['first_name'], 
                $_POST['last_name'], 
                $_POST['email'], 
                $_POST['phone'], 
                $_POST['position'], 
                $_POST['department'], 
                $_POST['base_salary'], 
                $_POST['hire_date'], 
                $_POST['status']
            );
            if ($stmt->execute()) {
                $message = 'Employee added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error adding employee!';
                $messageType = 'danger';
            }
            $stmt->close();
        } elseif ($_POST['action'] === 'update') {
            $stmt = $conn->prepare("UPDATE employees SET first_name=?, last_name=?, email=?, phone=?, position=?, department=?, base_salary=?, hire_date=?, status=? WHERE id=?");
            $stmt->bind_param("ssssssdssi", 
                $_POST['first_name'], 
                $_POST['last_name'], 
                $_POST['email'], 
                $_POST['phone'], 
                $_POST['position'], 
                $_POST['department'], 
                $_POST['base_salary'], 
                $_POST['hire_date'], 
                $_POST['status'], 
                $_POST['employee_id']
            );
            if ($stmt->execute()) {
                $message = 'Employee updated successfully!';
                $messageType = 'success';
            } else {
                $message = 'Error updating employee!';
                $messageType = 'danger';
            }
            $stmt->close();
        }
    }
}

// Handle Delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $conn->prepare("UPDATE employees SET status='terminated' WHERE id=?");
    $stmt->bind_param("i", $_GET['id']);
    if ($stmt->execute()) {
        $message = 'Employee deleted successfully!';
        $messageType = 'success';
    }
    $stmt->close();
}

// Get employee for editing
$editEmployee = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM employees WHERE id=?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $editEmployee = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Get all employees
$employees = $conn->query("SELECT * FROM employees ORDER BY created_at DESC");

// Get departments
$departments = $conn->query("SELECT name FROM departments ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - PayrollPro</title>
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
            <h1>Employee Management</h1>
            <p>Manage all your employees and their information</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Add/Edit Employee Form -->
        <div class="content-card mb-4">
            <div class="card-header-custom">
                <h3 class="card-title"><?php echo $editEmployee ? 'Edit Employee' : 'Add New Employee'; ?></h3>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $editEmployee ? 'update' : 'add'; ?>">
                <?php if ($editEmployee): ?>
                <input type="hidden" name="employee_id" value="<?php echo $editEmployee['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <?php if (!$editEmployee): ?>
                    <div class="form-group">
                        <label>Employee Code</label>
                        <input type="text" name="employee_code" class="form-control-custom" required 
                               value="EMP-<?php echo rand(1000, 9999); ?>">
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control-custom" required
                               value="<?php echo $editEmployee['first_name'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control-custom" required
                               value="<?php echo $editEmployee['last_name'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control-custom" required
                               value="<?php echo $editEmployee['email'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="tel" name="phone" class="form-control-custom"
                               value="<?php echo $editEmployee['phone'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Position</label>
                        <input type="text" name="position" class="form-control-custom" required
                               value="<?php echo $editEmployee['position'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Department</label>
                        <select name="department" class="form-control-custom" required>
                            <option value="">Select Department</option>
                            <?php $departments->data_seek(0); while ($dept = $departments->fetch_assoc()): ?>
                            <option value="<?php echo $dept['name']; ?>" 
                                <?php echo (isset($editEmployee) && $editEmployee['department'] === $dept['name']) ? 'selected' : ''; ?>>
                                <?php echo $dept['name']; ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Base Salary</label>
                        <input type="number" name="base_salary" class="form-control-custom" step="0.01" required
                               value="<?php echo $editEmployee['base_salary'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Hire Date</label>
                        <input type="date" name="hire_date" class="form-control-custom" required
                               value="<?php echo $editEmployee['hire_date'] ?? ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control-custom" required>
                            <option value="active" <?php echo (isset($editEmployee) && $editEmployee['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo (isset($editEmployee) && $editEmployee['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            <option value="terminated" <?php echo (isset($editEmployee) && $editEmployee['status'] === 'terminated') ? 'selected' : ''; ?>>Terminated</option>
                        </select>
                    </div>
                </div>
                
                <div class="d-flex gap-2 mt-3">
                    <button type="submit" class="btn-primary-custom">
                        <i class="fas fa-save"></i> <?php echo $editEmployee ? 'Update' : 'Save'; ?> Employee
                    </button>
                    <?php if ($editEmployee): ?>
                    <a href="employees.php" class="btn-secondary-custom">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Employees List -->
        <div class="content-card">
            <div class="card-header-custom">
                <h3 class="card-title">All Employees</h3>
                <div class="d-flex gap-2">
                    <input type="text" id="searchInput" class="form-control-custom" placeholder="Search employees..." 
                           style="max-width: 300px;">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table-custom" id="employeesTable">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Salary</th>
                            <th>Hire Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($emp = $employees->fetch_assoc()): ?>
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
                            <td><?php echo formatDate($emp['hire_date']); ?></td>
                            <td>
                                <span class="badge-custom badge-<?php 
                                    echo $emp['status'] === 'active' ? 'success' : 
                                        ($emp['status'] === 'inactive' ? 'pending' : 'danger'); 
                                ?>">
                                    <?php echo ucfirst($emp['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <a href="?action=edit&id=<?php echo $emp['id']; ?>" class="action-btn edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?action=delete&id=<?php echo $emp['id']; ?>" 
                                       class="action-btn delete" title="Delete"
                                       onclick="return confirm('Are you sure you want to delete this employee?')">
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

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#employeesTable tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>