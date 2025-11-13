<?php
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';
$validToken = false;
$userId = null;

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    $message = 'Invalid reset link. Please request a new password reset.';
    $messageType = 'danger';
} else {
    $token = sanitizeInput($_GET['token']);
    
    // Verify token
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, username, full_name, reset_token_expiry FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];
        
        // Check if token has expired
        if (strtotime($user['reset_token_expiry']) < time()) {
            $message = 'This reset link has expired. Please request a new one.';
            $messageType = 'danger';
        } else {
            $validToken = true;
        }
    } else {
        $message = 'Invalid reset link. Please request a new password reset.';
        $messageType = 'danger';
    }
    
    $stmt->close();
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if (empty($newPassword) || empty($confirmPassword)) {
        $message = 'Please fill in all fields';
        $messageType = 'danger';
    } elseif (strlen($newPassword) < 6) {
        $message = 'Password must be at least 6 characters long';
        $messageType = 'danger';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Passwords do not match';
        $messageType = 'danger';
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
        $updateStmt->bind_param("si", $hashedPassword, $userId);
        
        if ($updateStmt->execute()) {
            $message = 'Password reset successful! You can now login with your new password.';
            $messageType = 'success';
            $validToken = false; // Prevent form from showing again
        } else {
            $message = 'Error resetting password. Please try again.';
            $messageType = 'danger';
        }
        
        $updateStmt->close();
    }
}

if (isset($conn)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - PayrollPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2d6a4f;
            --primary-light: #40916c;
            --primary-lighter: #95d5b2;
            --primary-lightest: #d8f3dc;
            --primary-dark: #1b4332;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            width: 100%;
            max-width: 450px;
        }

        .reset-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .reset-header {
            background: var(--primary);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .reset-icon {
            width: 70px;
            height: 70px;
            background: white;
            border-radius: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 32px;
            color: var(--primary);
        }

        .reset-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .reset-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .reset-body {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary-dark);
            font-size: 14px;
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .form-control-custom {
            width: 100%;
            padding: 14px 16px 14px 45px;
            border: 2px solid var(--primary-lightest);
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(45, 106, 79, 0.1);
        }

        .btn-reset {
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-reset:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 106, 79, 0.3);
        }

        .alert-custom {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-danger {
            background: #f8d7da;
            color: #dc3545;
            border: 1px solid #f5c2c7;
        }

        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .reset-footer {
            text-align: center;
            padding: 20px 30px;
            background: #f8f9fa;
            font-size: 14px;
            color: #6c757d;
        }

        .back-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: var(--primary-dark);
        }

        .password-requirements {
            background: var(--primary-lightest);
            padding: 16px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 13px;
        }

        .password-requirements ul {
            margin: 8px 0 0 20px;
            padding: 0;
        }

        .password-requirements li {
            margin: 4px 0;
            color: var(--primary-dark);
        }

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        .success-icon {
            font-size: 60px;
            color: #28a745;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <div class="reset-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h1>Reset Password</h1>
                <p>Enter your new password below</p>
            </div>
            
            <div class="reset-body">
                <?php if (!empty($message)): ?>
                    <div class="alert-custom alert-<?php echo $messageType; ?>">
                        <?php if ($messageType === 'success'): ?>
                            <div class="text-center">
                                <div class="success-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <p><?php echo $message; ?></p>
                            </div>
                        <?php else: ?>
                            <i class="fas fa-exclamation-circle"></i> <?php echo $message; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($validToken): ?>
                <form method="POST" action="" id="resetForm">
                    <div class="form-group">
                        <label>New Password *</label>
                        <div class="input-group-custom">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="new_password" id="newPassword" 
                                   class="form-control-custom" placeholder="Enter your new password" 
                                   minlength="6" required autocomplete="new-password">
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('newPassword', this)"></i>
                        </div>
                        <small class="text-muted">Minimum 6 characters required</small>
                    </div>

                    <div class="form-group">
                        <label>Confirm New Password *</label>
                        <div class="input-group-custom">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="confirm_password" id="confirmPassword" 
                                   class="form-control-custom" placeholder="Re-enter your new password" 
                                   minlength="6" required autocomplete="new-password">
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmPassword', this)"></i>
                        </div>
                        <small class="text-muted" id="matchStatus"></small>
                    </div>

                    <button type="submit" class="btn-reset">
                        <i class="fas fa-key"></i> Reset Password
                    </button>

                    <div class="password-requirements">
                        <strong><i class="fas fa-info-circle"></i> Password Requirements:</strong>
                        <ul>
                            <li>At least 6 characters long</li>
                            <li>Include uppercase and lowercase letters (recommended)</li>
                            <li>Include numbers (recommended)</li>
                            <li>Include special characters (recommended)</li>
                        </ul>
                    </div>
                </form>
                <?php endif; ?>

                <?php if ($messageType === 'success' || (!$validToken && !empty($message))): ?>
                    <div class="text-center mt-3">
                        <a href="login.php" class="btn-reset" style="text-decoration: none; display: block;">
                            <i class="fas fa-sign-in-alt"></i> Go to Login
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="reset-footer">
                <a href="login.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId, icon) {
            const field = document.getElementById(fieldId);
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password match validation
        document.getElementById('resetForm')?.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });

        // Real-time password match indicator
        document.getElementById('confirmPassword')?.addEventListener('input', function() {
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && newPassword !== confirmPassword) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#95d5b2';
            }
        });
    </script>
</body>
</html>