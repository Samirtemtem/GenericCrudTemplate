# Global CRUD MVC System

## Overview
A dynamic, flexible PHP-based database management web application with automatic CRUD generation.

## Prerequisites
- PHP 7.4+
- MySQL/MariaDB
- Composer (optional, for autoloading)
- Web Server (Apache/Nginx) or PHP Built-in Server

## Installation Steps

### 1. Database Configuration
1. Create a MySQL database
2. Update database credentials in `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

### 2. Generate Models and Controllers
Run the following scripts in order:
```bash
php install.php        # Generate model classes
php install_controllers.php  # Generate controller classes
```

### 3. Run the Application

#### Option 1: PHP Built-in Server
```bash
php -S localhost:8000
```
Access: `http://localhost:8000`

#### Option 2: XAMPP/WAMP
1. Place project in `htdocs` directory
2. Start Apache and MySQL
3. Access: `http://localhost/globalcrud`

## Features
- Automatic model generation
- Dynamic CRUD operations
- Intelligent relationship detection
- Responsive Bootstrap UI
- Minimal configuration required

## Troubleshooting
- Ensure PHP extensions are enabled (PDO, MySQL)
- Check file permissions
- Verify database connection
- PHP error logs for detailed debugging

## License
MIT License

## Contact
Developed by Samir Temtem
