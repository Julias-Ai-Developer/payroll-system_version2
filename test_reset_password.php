<?php
/**
 * Test Password Reset Form
 * This page lets you test the reset form WITHOUT needing email
 * Just navigate to this page directly
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Reset Password Form - PayrollPro</title>
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
            max-width: 500px;
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

        .test-badge {
            background: #ffc107;
            color: #000;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-top: 10px;
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

        .input-group-custom i.fa-lock {
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

        .toggle-password {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            transition: color 0.3s;
            z-index: 10;
        }

        .toggle-password:hover {
            color: var(--primary);
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

        .match-status {
            display: block;
            margin-top: 5px;
            font-size: 13px;
            font-weight: 500;
        }

        .text-muted {
            color: #6c757d;
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

        .step-indicator {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .step-indicator h3 {
            color: #1565c0;
            font-size: 16px;
            margin: 0 0 10px 0;
        }

        .step-indicator ol {
            margin: 0;
            padding-left: 20px;
        }

        .step-indicator li {
            color: #1976d2;
            margin: 5px 0;
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
                <h1>Reset Your Password</h1>
                <p>Enter your new password below</p>
                <span class="test-badge">🧪 TEST MODE</span>
            </div>
            
            <div class="reset-body">
                <div class="step-indicator">
                    <h3><i class="fas fa-clipboard-list"></i> What to do:</h3>
                    <ol>
                        <li><strong>Enter your new password</strong> in the first field</li>
                        <li><strong>Confirm your password</strong> by entering it again</li>
                        <li>Click the <strong>"Reset Password"</strong> button</li>
                    </ol>
                </div>

                <form id="testResetForm" onsubmit="return handleSubmit(event)">
                    <div class="form-group">
                        <label>1. New Password *</label>
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
                        <label>2. Confirm New Password *</label>
                        <div class="input-group-custom">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="confirm_password" id="confirmPassword" 
                                   class="form-control-custom" placeholder="Re-enter your new password" 
                                   minlength="6" required autocomplete="new-password">
                            <i class="fas fa-eye toggle-password" onclick="togglePassword('confirmPassword', this)"></i>
                        </div>
                        <span class="match-status" id="matchStatus"></span>
                    </div>

                    <button type="submit" class="btn-reset">
                        <i class="fas fa-key"></i> 3. Reset Password
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
            </div>

            <div class="reset-footer">
                <p style="margin-bottom: 10px;"><strong>This is a TEST PAGE</strong></p>
                <p>In production, users access this form via email link</p>
                <hr style="margin: 15px 0; border-color: #dee2e6;">
                <a href="login.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
                <span style="margin: 0 10px;">|</span>
                <a href="test_email.php" class="back-link">
                    <i class="fas fa-envelope"></i> Test Email
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

        // Real-time password match indicator
        const confirmPasswordField = document.getElementById('confirmPassword');
        const newPasswordField = document.getElementById('newPassword');
        const matchStatus = document.getElementById('matchStatus');
        
        confirmPasswordField.addEventListener('input', function() {
            const newPassword = newPasswordField.value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0) {
                if (newPassword !== confirmPassword) {
                    this.style.borderColor = '#dc3545';
                    matchStatus.style.color = '#dc3545';
                    matchStatus.textContent = '✗ Passwords do not match';
                } else {
                    this.style.borderColor = '#28a745';
                    matchStatus.style.color = '#28a745';
                    matchStatus.textContent = '✓ Passwords match perfectly!';
                }
            } else {
                this.style.borderColor = '';
                matchStatus.textContent = '';
            }
        });
        
        newPasswordField.addEventListener('input', function() {
            const confirmPassword = confirmPasswordField.value;
            if (confirmPassword.length > 0) {
                confirmPasswordField.dispatchEvent(new Event('input'));
            }
        });

        // Form submit handler
        function handleSubmit(event) {
            event.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword !== confirmPassword) {
                alert('❌ ERROR: Passwords do not match!\n\nPlease make sure both password fields contain the exact same password.');
                document.getElementById('confirmPassword').focus();
                return false;
            }
            
            if (newPassword.length < 6) {
                alert('❌ ERROR: Password too short!\n\nPassword must be at least 6 characters long.');
                document.getElementById('newPassword').focus();
                return false;
            }
            
            // Success simulation
            alert('✅ SUCCESS!\n\n' + 
                  'Password validation passed!\n\n' +
                  'New Password: ' + newPassword + '\n' +
                  'Confirm Password: ' + confirmPassword + '\n\n' +
                  'Both fields match! ✓\n\n' +
                  'In production, this would:\n' +
                  '1. Hash the password\n' +
                  '2. Update the database\n' +
                  '3. Redirect to login page');
            
            return false; // Prevent actual submission in test mode
        }

        // Show form is ready
        console.log('✅ Password reset form loaded and ready!');
        console.log('📝 Form has TWO password fields:');
        console.log('   1. New Password');
        console.log('   2. Confirm Password');
        console.log('👁️ Eye icons toggle password visibility');
        console.log('✓ Real-time password matching validation');
    </script>
</body>
</html>