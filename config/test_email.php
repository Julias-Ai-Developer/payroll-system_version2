<?php
require_once __DIR__ . '/config/email.php';

if (sendEmail('your-other-email@gmail.com', 'Test Email', '<p>This is a test email from PayrollPro.</p>')) {
    echo "✅ Email sent successfully!";
} else {
    echo "❌ Failed to send email. Check error log.";
}
?>
