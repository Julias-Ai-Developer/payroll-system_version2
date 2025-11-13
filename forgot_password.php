<?php
require_once './config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    
    if (empty($email)) {
        $message = 'Please enter your email address';
        $messageType = 'danger';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, username, full_name, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Generate reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in database
            $updateStmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $updateStmt->bind_param("ssi", $token, $expiry, $user['id']);
            $updateStmt->execute();
            
            // Create reset link
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
            
            // Try to send email
            require_once 'config/email.php';
            $emailSent = sendPasswordResetEmail($user['email'], $user['full_name'], $token);
            
            if ($emailSent) {
                $message = "Password reset link has been sent to your email address!<br><br>
                           <small>Please check your inbox (and spam folder). The link will expire in 1 hour.</small>";
                $messageType = 'success';
            } else {
                // Fallback: Show link directly (Demo Mode)
                $message = "Password reset link has been generated!<br><br>
                           <strong>Demo Mode:</strong> Email sending is not configured.<br>
                           <a href='{$resetLink}' class='btn-link'>Click here to reset password</a><br><br>
                           <small>Link expires in 1 hour</small><br><br>
                           <small class='text-muted'>Configure SMTP in config/email.php to enable email delivery.</small>";
                $messageType = 'success';
            }
            
            $updateStmt->close();
        } else {
            // Don't reveal if email exists for security
            $message = "If that email exists in our system, a password reset link has been sent.";
            $messageType = 'info';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - PayrollPro</title>
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

        .forgot-container {
            width: 100%;
            max-width: 450px;
        }

        .forgot-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .forgot-header {
            background: var(--primary);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .forgot-icon {
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

        .forgot-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .forgot-header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .forgot-body {
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

        .btn-forgot {
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

        .btn-forgot:hover {
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

        .alert-info {
            background: #cff4fc;
            color: #055160;
            border: 1px solid #b6effb;
        }

        .forgot-footer {
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

        .info-box {
            background: var(--primary-lightest);
            padding: 16px;
            border-radius: 12px;
            margin-top: 20px;
            font-size: 13px;
            color: var(--primary-dark);
        }

        .info-box i {
            margin-right: 8px;
        }

        .btn-link {
            display: inline-block;
            padding: 10px 20px;
            background: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .btn-link:hover {
            background: var(--primary-dark);
            color: white;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-card">
            <div class="forgot-header">
                <div class="forgot-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h1>Forgot Password?</h1>
                <p>No worries, we'll send you reset instructions</p>
            </div>
            
            <div class="forgot-body">
                <?php if (!empty($message)): ?>
                    <div class="alert-custom alert-<?php echo $messageType; ?>">
                        <i class="fas fa-<?php echo $messageType === 'danger' ? 'exclamation-circle' : ($messageType === 'success' ? 'check-circle' : 'info-circle'); ?>"></i> 
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($message) || $messageType === 'danger'): ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-group-custom">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" class="form-control-custom" 
                                   placeholder="Enter your registered email" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-forgot">
                        <i class="fas fa-paper-plane"></i> Send Reset Link
                    </button>
                </form>

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> The reset link will expire in 1 hour. 
                    If you don't receive an email, check your spam folder.
                </div>
                <?php endif; ?>
            </div>

            <div class="forgot-footer">
                <a href="login.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>