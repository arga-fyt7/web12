# My Website - PHP Web Application

A modern, secure, and feature-rich PHP web application with user authentication, admin panel, and responsive design.

## 🚀 Features

### 🔐 Authentication & Security
- **User Registration & Login**: Complete user management system
- **Password Security**: Strong password requirements with strength indicator
- **Session Management**: Secure session handling with auto-logout
- **Role-Based Access**: User and admin roles with different permissions
- **Brute Force Protection**: Login attempt limiting and account lockout

### 👨‍💼 Admin Panel
- **Dashboard Overview**: Statistics and system status
- **User Management**: View, edit, and manage user accounts
- **Content Management**: Create and manage site content
- **Analytics**: View site statistics and user activity
- **System Settings**: Configure application settings

### 🎨 Design & UX
- **Responsive Design**: Works perfectly on all devices
- **Modern UI**: Clean, professional interface with Bootstrap 5
- **Dark Mode Support**: Automatic theme switching
- **Smooth Animations**: Enhanced user experience with CSS animations
- **Interactive Elements**: Tooltips, modals, and dynamic content

### 🛠️ Technical Features
- **Database Integration**: MySQL with PDO for secure queries
- **File Upload System**: Support for images and documents
- **Activity Logging**: Track user actions and system events
- **Error Handling**: Comprehensive error management
- **Caching System**: Performance optimization
- **API Ready**: RESTful API structure for future enhancements

## 📋 Requirements

- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Web Server**: Apache/Nginx with URL rewriting
- **Extensions**: PDO, cURL, GD, mbstring

## ⚙️ Installation

### 1. Clone or Download
```bash
# If using git
git clone [your-repository-url]
cd mywebsite

# Or download and extract the files
```

### 2. Database Setup
1. Create a new MySQL database
2. Import the database schema (if provided) or let the app create tables automatically
3. Update database credentials in `config/main.php`

### 3. Configuration
Edit `config/main.php`:
```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');

// Application Configuration
define('APP_NAME', 'My Website');
define('APP_URL', 'http://yourdomain.com');
```

### 4. File Permissions
```bash
# Set proper permissions
chmod 755 -R .
chmod 644 config/*.php
chmod 755 uploads/ (if exists)
chmod 755 logs/ (if exists)
```

### 5. Web Server Configuration
For Apache, ensure `.htaccess` is enabled and add:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```

## 📁 Project Structure

```
mywebsite/
├── index.php              # Main entry point
├── login.php              # Login page
├── register.php           # Registration page
├── README.md              # This file
├── admin/                 # Admin panel
│   └── dashboard.php      # Admin dashboard
├── assets/                # Static assets
│   ├── css/
│   │   └── style.css      # Custom styles
│   └── js/
│       └── main.js        # JavaScript functionality
└── config/                # Configuration files
    ├── main.php           # Main configuration
    └── auth.php           # Authentication functions
```

## 🔧 Configuration Options

### Security Settings
- `PASSWORD_MIN_LENGTH`: Minimum password length (default: 8)
- `MAX_LOGIN_ATTEMPTS`: Maximum failed login attempts (default: 5)
- `LOGIN_LOCKOUT_TIME`: Account lockout duration in seconds (default: 900)
- `ENABLE_REGISTRATION`: Allow new user registration (default: true)
- `ENABLE_EMAIL_VERIFICATION`: Require email verification (default: false)

### File Upload Settings
- `UPLOAD_PATH`: Directory for file uploads
- `MAX_FILE_SIZE`: Maximum file size in bytes (default: 5MB)
- `ALLOWED_EXTENSIONS`: Array of allowed file extensions

## 👤 User Guide

### For Users
1. **Registration**: Click "Register" and fill out the form
2. **Login**: Use your credentials to access the dashboard
3. **Profile**: Update your information in the user dashboard
4. **Password**: Change your password in settings

### For Administrators
1. **Default Login**:
   - Username: `admin`
   - Password: `admin123`
2. **Dashboard**: View system statistics and recent activity
3. **User Management**: Manage user accounts and permissions
4. **Content**: Create and manage site content
5. **Settings**: Configure system preferences

## 🔒 Security Features

- **SQL Injection Protection**: PDO prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **CSRF Protection**: Token-based request validation
- **Password Hashing**: bcrypt with salt
- **Session Security**: Secure session handling
- **File Upload Security**: File type and size validation

## 🎨 Customization

### Styling
- Edit `assets/css/style.css` for custom styles
- Modify Bootstrap variables for theme changes
- Add custom CSS classes as needed

### Functionality
- Extend `config/auth.php` for additional authentication features
- Add new pages following the existing structure
- Create custom admin modules in the `admin/` directory

### JavaScript
- Add custom functionality to `assets/js/main.js`
- Use the provided utility functions
- Follow the existing code structure

## 🔍 API Endpoints

The application includes several API endpoints:

- `POST /api/login.php` - User authentication
- `POST /api/register.php` - User registration
- `GET /api/session-check.php` - Session validation
- `POST /api/clear-cache.php` - Cache management
- `GET /api/dashboard-stats.php` - Dashboard statistics

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Check database credentials in `config/main.php`
- Ensure MySQL service is running
- Verify database exists and user has permissions

**File Upload Not Working**
- Check file permissions on upload directory
- Verify `MAX_FILE_SIZE` setting
- Ensure PHP file upload settings are configured

**Session Issues**
- Check PHP session configuration
- Verify session save path permissions
- Clear browser cookies and cache

**Styling Problems**
- Clear browser cache
- Check file paths in HTML
- Verify CSS file permissions

### Debug Mode
Enable debug mode by setting in `config/main.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

## 📈 Performance

### Optimization Tips
- Enable PHP opcache
- Use CDN for static assets
- Implement caching for database queries
- Optimize images and compress files
- Use gzip compression

### Monitoring
- Check error logs in `logs/` directory
- Monitor database performance
- Track user activity and system usage

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🆘 Support

For support and questions:
- Create an issue on GitHub
- Check the documentation
- Review the troubleshooting section

## 🔄 Updates

### Version History
- **v1.0.0**: Initial release
  - Complete authentication system
  - Admin panel
  - Responsive design
  - Security features

### Future Plans
- [ ] Email verification system
- [ ] Two-factor authentication
- [ ] Advanced user roles
- [ ] Content management system
- [ ] API documentation
- [ ] Mobile app support

---

**Built with ❤️ using PHP, Bootstrap 5, and modern web technologies**

For more information, visit our [documentation](#) or [contact us](#).
