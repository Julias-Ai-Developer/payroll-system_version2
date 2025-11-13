<?php
/**
 * Gmail SMTP Email Configuration for Password Reset
 * Requires: composer require phpmailer/phpmailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer (auto-detect path)
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// === SMTP Configuration ===
define('SMTP_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'muyambijulias@gmail.com'); // your Gmail address
define('SMTP_PASSWORD', 'mrdelhmwvvlkxthl'); // your Gmail App Password
define('SMTP_ENCRYPTION', 'tls');

// === Email Sender Settings ===
define('EMAIL_FROM', SMTP_USERNAME); // must match your Gmail
define('EMAIL_FROM_NAME', 'PayrollPro Support');
define('EMAIL_REPLY_TO', SMTP_USERNAME);

/**
 * Send email using PHPMailer (SMTP)
 */
function sendEmail($to, $subject, $message) {
    // Check if PHPMailer is available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log('PHPMailer not found. Install with: composer require phpmailer/phpmailer');
        return false;
    }
    
    $mail = new PHPMailer(true);

    try {
        // SMTP Settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;

        // Sender / Recipient
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->addReplyTo(EMAIL_REPLY_TO);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Generate password reset email HTML template
 */
function getPasswordResetEmailTemplate($userName, $resetLink) {
    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                color: #333; 
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
            }
            .email-wrapper {
                background-color: #f4f4f4;
                padding: 20px;
            }
            .container { 
                max-width: 600px; 
                margin: 0 auto; 
                background: white;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .header { 
                background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%);
                color: white; 
                padding: 40px 30px; 
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 700;
            }
            .header p {
                margin: 10px 0 0 0;
                opacity: 0.9;
                font-size: 14px;
            }
            .content { 
                background: white;
                padding: 40px 30px;
            }
            .content p {
                margin: 0 0 15px 0;
                font-size: 16px;
            }
            .button-container {
                text-align: center;
                margin: 30px 0;
            }
            .button { 
                display: inline-block; 
                padding: 16px 40px; 
                background: #2d6a4f; 
                color: white !important; 
                text-decoration: none; 
                border-radius: 8px;
                font-weight: 600;
                font-size: 16px;
                box-shadow: 0 4px 6px rgba(45, 106, 79, 0.3);
            }
            .button:hover {
                background: #1b4332;
            }
            .link-box {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
                word-break: break-all;
                font-size: 14px;
                color: #2d6a4f;
                border-left: 4px solid #2d6a4f;
            }
            .warning { 
                background: #fff3cd; 
                border-left: 4px solid #ffc107; 
                padding: 15px; 
                margin: 20px 0;
                border-radius: 4px;
            }
            .warning strong {
                color: #856404;
            }
            .warning ul {
                margin: 10px 0 0 20px;
                padding: 0;
            }
            .warning li {
                margin: 5px 0;
                color: #856404;
            }
            .info-box {
                background: #d8f3dc;
                border-left: 4px solid #2d6a4f;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
            }
            .info-box strong {
                color: #1b4332;
            }
            .info-box ul {
                margin: 10px 0 0 20px;
                padding: 0;
            }
            .info-box li {
                margin: 5px 0;
                color: #2d6a4f;
            }
            .footer { 
                text-align: center; 
                padding: 30px; 
                background: #f8f9fa;
                color: #6c757d; 
                font-size: 13px;
            }
            .footer p {
                margin: 5px 0;
            }
            .footer a {
                color: #2d6a4f;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="email-wrapper">
            <div class="container">
                <div class="header">
                    <h1>🔐 Password Reset Request</h1>
                    <p>Secure password reset for your PayrollPro account</p>
                </div>
                
                <div class="content">
                    <p>Hello <strong>' . htmlspecialchars($userName) . '</strong>,</p>
                    
                    <p>We received a request to reset your password for your PayrollPro account. Click the button below to create a new password:</p>
                    
                    <div class="button-container">
                        <a href="' . $resetLink . '" class="button">Reset My Password</a>
                    </div>
                    
                    <p><strong>Or copy and paste this link into your browser:</strong></p>
                    <div class="link-box">
                        ' . $resetLink . '
                    </div>
                    
                    <div class="warning">
                        <strong>⚠️ Important Security Information:</strong>
                        <ul>
                            <li>This link will expire in <strong>1 hour</strong></li>
                            <li>The link can only be used <strong>once</strong></li>
                            <li>If you didn\'t request this reset, please ignore this email</li>
                            <li>Your password will remain unchanged until you create a new one</li>
                        </ul>
                    </div>
                    
                    <div class="info-box">
                        <strong>🔒 Password Requirements:</strong>
                        <ul>
                            <li>Minimum 6 characters</li>
                            <li>Include uppercase and lowercase letters (recommended)</li>
                            <li>Include numbers (recommended)</li>
                            <li>Include special characters (recommended)</li>
                        </ul>
                    </div>
                    
                    <p>After clicking the reset link, you will be asked to:</p>
                    <p style="padding-left: 20px;">
                        1. <strong>Enter your new password</strong><br>
                        2. <strong>Confirm your new password</strong><br>
                        3. Click the "Reset Password" button
                    </p>
                    
                    <p>If you have any questions or concerns, please contact our support team.</p>
                    
                    <p style="margin-top: 30px;">Best regards,<br><strong>PayrollPro Support Team</strong></p>
                </div>
                
                <div class="footer">
                    <p><strong>© ' . date('Y') . ' PayrollPro. All rights reserved.</strong></p>
                    <p>This is an automated email. Please do not reply to this email.</p>
                    <p>Need help? Contact us at <a href="mailto:' . EMAIL_REPLY_TO . '">' . EMAIL_REPLY_TO . '</a></p>
                </div>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($email, $userName, $resetToken) {
    // Detect protocol (http or https)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    
    // Build reset link
    $resetLink = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['PHP_SELF']), '/\\') . "/reset_password.php?token=" . urlencode($resetToken);
    
    $subject = "Password Reset Request - PayrollPro";
    $message = getPasswordResetEmailTemplate($userName, $resetLink);
    
    return sendEmail($email, $subject, $message);
}

/**
 * Quick Test Function
 * Uncomment to test email sending
 */
// function testEmail() {
//     return sendEmail(
//         'test@example.com', 
//         'SMTP Test - PayrollPro', 
//         '<h3>✅ SMTP setup is working perfectly!</h3><p>Your email configuration is correct.</p>'
//     );
// }

?>