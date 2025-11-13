# PayrollPro - Payroll Management System

A comprehensive PHP-based payroll management system with modern UI and complete functionality.

## Features

- ✅ User Authentication (Login/Logout)
- ✅ Employee Management (Add/Edit/Delete)
- ✅ Payroll Processing with automatic calculations
- ✅ Salary Slip Generation & Printing
- ✅ Comprehensive Reports & Analytics
- ✅ Department-wise tracking
- ✅ Responsive Design (Mobile-friendly)
- ✅ Modern UI with animations
- ✅ Export functionality (CSV)

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx Web Server
- Web Browser (Chrome, Firefox, Safari, Edge)

## Installation Steps

### 1. Database Setup

```sql
1. Open phpMyAdmin or MySQL command line
2. Create a new database: payroll_system
3. Import the database.sql file
4. The default admin credentials will be created automatically
```

### 2. Configure Database Connection

Edit `config/database.php` and update your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'payroll_system');
```

### 3. File Structure

```
payroll-system/
├── config/
│   └── database.php
├── includes/
│   ├── sidebar.php
│   ├── topbar.php
│   └── footer.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── login.php
├── logout.php
├── index.php
├── employees.php
├── payroll.php
├── salary_slip.php
├── reports.php
├── settings.php
└── database.sql
```

### 4. Permissions

Set proper permissions for the directories:

```bash
chmod 755 config/
chmod 644 config/database.php
chmod 755 assets/
chmod 755 includes/
```

### 5. Access the System

1. Open your web browser
2. Navigate to: `http://localhost/payroll-system/`
3. You'll be redirected to the login page

## Default Login Credentials

**Username:** admin  
**Password:** admin123

**⚠️ IMPORTANT:** Change the default password after first login!

## Usage Guide

### Dashboard
- View overall statistics
- Quick access to recent employees
- Overview of pending payrolls

### Employees Module
- Add new employees with all details
- Edit existing employee information
- Manage employee status (Active/Inactive/Terminated)
- Search and filter employees
- View employee details

### Payroll Module
- Process new payroll for employees
- Automatic calculation of:
  - Gross salary
  - Overtime pay
  - Bonuses
  - Deductions
  - Tax (20% automatic)
  - Net salary
- Mark payrolls as paid
- Filter by status

### Salary Slip
- Generate printable salary slips
- Professional format with company branding
- Detailed breakdown of earnings and deductions
- Print or download

### Reports
- Monthly payroll trends
- Department-wise salary reports
- Recent transactions
- Export reports to CSV
- Generate custom reports

## Database Schema

### Tables:
1. **users** - User authentication
2. **employees** - Employee information
3. **payroll** - Payroll records
4. **salary_slips** - Generated salary slips
5. **departments** - Department list

## Security Features

- Password hashing using bcrypt
- SQL injection prevention (Prepared statements)
- XSS protection
- Session management
- Role-based access control
- CSRF protection ready

## Customization

### Change Colors
Edit `assets/css/style.css` and modify the CSS variables:

```css
:root {
    --primary: #2d6a4f;
    --primary-light: #40916c;
    --primary-lighter: #95d5b2;
    --primary-lightest: #d8f3dc;
    --primary-dark: #1b4332;
}
```

### Add New Departments
Insert into the departments table:

```sql
INSERT INTO departments (name, description) VALUES ('Your Department', 'Description');
```

### Modify Tax Rate
Edit `payroll.php` and change the tax calculation:

```javascript
const tax = grossSalary * 0.20; // Change 0.20 to your rate
```

## Troubleshooting

### Login Issues
- Check database credentials in config/database.php
- Verify the users table has data
- Check PHP session is enabled

### Database Connection Failed
- Ensure MySQL service is running
- Verify database name exists
- Check username and password

### Missing Styles
- Verify assets folder exists
- Check file paths in HTML
- Clear browser cache

### Calculation Errors
- Check JavaScript console for errors
- Verify numeric inputs are valid
- Check database field types (DECIMAL)

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Opera 76+

## Contributing

This is a complete standalone system. Feel free to modify and extend as needed.

## License

Free to use for educational and commercial purposes.

## Support

For issues or questions, please review the code comments and database structure.

## Version History

- **v1.0.0** - Initial release
  - Complete authentication system
  - Employee management
  - Payroll processing
  - Salary slip generation
  - Reports and analytics
  - Responsive design

## Credits

- Font Awesome for icons
- Bootstrap 5 for base components
- Inter font family
- Custom CSS framework

---

**Note:** This system is production-ready but should be reviewed for your specific security requirements before deployment.