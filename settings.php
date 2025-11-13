<?php
require_once 'config/database.php';
requireLogin();

$conn = getDBConnection();
$user = getCurrentUser();
$message = '';
$messageType = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if ($newPassword !== $confirmPassword) {
            $message = 'New passwords do not match!';
            $messageType = 'danger';
        } else {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            
            if (password_verify($currentPassword, $result['password'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $updateStmt->bind_param("si", $hashedPassword, $user['id']);
                
                if ($updateStmt->execute()) {
                    $message = 'Password changed successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Error changing password!';
                    $messageType = 'danger';
                }
                $updateStmt->close();
            } else {
                $message = 'Current password is incorrect!';
                $messageType = 'danger';
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'update_profile') {
        $fullName = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $fullName, $email, $user['id']);
        
        if ($stmt->execute()) {
            $_SESSION['full_name'] = $fullName;
            $_SESSION['email'] = $email;
            $message = 'Profile updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error updating profile!';
            $messageType = 'danger';
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'add_department') {
        $deptName = sanitizeInput($_POST['dept_name']);
        $deptDesc = sanitizeInput($_POST['dept_description']);
        
        $stmt = $conn->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $deptName, $deptDesc);
        
        if ($stmt->execute()) {
            $message = 'Department added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Error adding department!';
            $messageType = 'danger';
        }
        $stmt->close();
    }
}

// Handle department deletion
if (isset($_GET['delete_dept'])) {
    $deptId = intval($_GET['delete_dept']);
    $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
    $stmt->bind_param("i", $deptId);
    
    if ($stmt->execute()) {
        $message = 'Department deleted successfully!';
        $messageType = 'success';
    } else {
        $message = 'Error deleting department! Check if it has employees.';
        $messageType = 'danger';
    }
    $stmt->close();
}

// Get all departments
$departments = $conn->query("SELECT * FROM departments ORDER BY name");

// Get user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$userInfo = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - PayrollPro</title>
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
            <h1>Settings</h1>
            <p>Manage your account and system settings</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Profile Settings -->
        <div class="content-card mb-4">
            <div class="card-header-custom">
                <h3 class="card-title"><i class="fas fa-user-circle"></i> Profile Information</h3>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" class="form-control-custom" 
                               value="<?php echo htmlspecialchars($userInfo['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" class="form-control-custom" 
                               value="<?php echo htmlspecialchars($userInfo['username']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control-custom" 
                               value="<?php echo htmlspecialchars($userInfo['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Role</label>
                        <input type="text" class="form-control-custom" 
                               value="<?php echo ucfirst($userInfo['role']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Member Since</label>
                        <input type="text" class="form-control-custom" 
                               value="<?php echo formatDate($userInfo['created_at']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Last Login</label>
                        <input type="text" class="form-control-custom" 
                               value="<?php echo $userInfo['last_login'] ? formatDate($userInfo['last_login']) : 'Never'; ?>" readonly>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary-custom">
                    <i class="fas fa-save"></i> Update Profile
                </button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="content-card mb-4">
            <div class="card-header-custom">
                <h3 class="card-title"><i class="fas fa-lock"></i> Change Password</h3>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" class="form-control-custom" required>
                    </div>
                    
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control-custom" 
                               minlength="6" required>
                        <small class="text-muted">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control-custom" 
                               minlength="6" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary-custom">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </form>
        </div>

        <!-- Department Management -->
        <div class="content-card mb-4">
            <div class="card-header-custom">
                <h3 class="card-title"><i class="fas fa-building"></i> Department Management</h3>
            </div>
            
            <!-- Add Department Form -->
            <form method="POST" action="" class="mb-4">
                <input type="hidden" name="action" value="add_department">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Department Name</label>
                        <input type="text" name="dept_name" class="form-control-custom" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="dept_description" class="form-control-custom" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-primary-custom">
                    <i class="fas fa-plus"></i> Add Department
                </button>
            </form>

            <!-- Departments List -->
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Department Name</th>
                            <th>Description</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($dept = $departments->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($dept['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($dept['description']); ?></td>
                            <td><?php echo formatDate($dept['created_at']); ?></td>
                            <td>
                                <a href="?delete_dept=<?php echo $dept['id']; ?>" 
                                   class="action-btn delete" 
                                   onclick="return confirm('Are you sure you want to delete this department?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- System Information -->
        <div class="content-card">
            <div class="card-header-custom">
                <h3 class="card-title"><i class="fas fa-info-circle"></i> System Information</h3>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>System Name</label>
                    <input type="text" class="form-control-custom" value="PayrollPro" readonly>
                </div>
                
                <div class="form-group">
                    <label>Version</label>
                    <input type="text" class="form-control-custom" value="1.0.0" readonly>
                </div>
                
                <div class="form-group">
                    <label>PHP Version</label>
                    <input type="text" class="form-control-custom" value="<?php echo phpversion(); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label>Database</label>
                    <input type="text" class="form-control-custom" value="MySQL" readonly>
                </div>
                
                <div class="form-group">
                    <label>Server Time</label>
                    <input type="text" class="form-control-custom" value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
                </div>
                
                <div class="form-group">
                    <label>Timezone</label>
                    <input type="text" class="form-control-custom" value="<?php echo date_default_timezone_get(); ?>" readonly>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
    <script>
        // Password match validation
        document.querySelector('input[name="confirm_password"]').addEventListener('input', function() {
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>