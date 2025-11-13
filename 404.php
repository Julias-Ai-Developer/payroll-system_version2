<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | PayrollPro</title>
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
            --primary-dark: #1b4332;
            --primary-lighter: #95d5b2;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 20px;
        }

        .error-container {
            text-align: center;
            max-width: 600px;
        }

        .error-code {
            font-size: 150px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 20px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }

        .error-icon {
            font-size: 80px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .error-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .error-message {
            font-size: 18px;
            margin-bottom: 40px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .btn-home {
            display: inline-block;
            padding: 14px 32px;
            background: white;
            color: var(--primary);
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 16px;
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.3);
            color: var(--primary);
        }

        .btn-home i {
            margin-right: 8px;
        }

        .error-links {
            margin-top: 30px;
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .error-link {
            color: white;
            text-decoration: none;
            opacity: 0.8;
            transition: opacity 0.3s;
        }

        .error-link:hover {
            opacity: 1;
            color: white;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-message">
            Oops! The page you're looking for doesn't exist. It might have been moved or deleted.
            Let's get you back on track.
        </p>
        <a href="index.php" class="btn-home">
            <i class="fas fa-home"></i> Back to Dashboard
        </a>
        
        <div class="error-links">
            <a href="employees.php" class="error-link">
                <i class="fas fa-users"></i> Employees
            </a>
            <a href="payroll.php" class="error-link">
                <i class="fas fa-wallet"></i> Payroll
            </a>
            <a href="reports.php" class="error-link">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </div>
    </div>
</body>
</html>