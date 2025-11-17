# 🎓 SchoolSphere

<div align="center">
  <img src="resources/images/logo.svg" alt="SchoolSphere Logo" width="120" height="120"/>
  <p><strong>Modern Educational Management Platform</strong></p>
</div>

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Chart.js](https://img.shields.io/badge/Chart.js-4.x-FF6384?style=for-the-badge&logo=chartdotjs&logoColor=white)](https://chartjs.org)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

> A modern, comprehensive educational management system built with Laravel 11, featuring role-based access control, academic record management, interactive data visualizations, and real-time analytics.

![Dashboard Preview](https://via.placeholder.com/1200x600/0066FF/FFFFFF?text=SchoolSphere+Dashboard)

## ✨ Key Features

### 🔐 **Multi-Role Authentication System**
- **Admin Portal**: Complete system control with user management, analytics, and configuration
- **Teacher Dashboard**: Grade management, attendance tracking, and student performance monitoring
- **Student Interface**: Personal academic records, grades, attendance, and report cards
- **Parent Access**: Multi-child monitoring, academic progress tracking, and teacher communications

### 📚 **Academic Management**
- **Dynamic Class Management**: Create and manage multiple classes with sections
- **Subject Administration**: Comprehensive subject catalog with credit hours and teacher assignments
- **Advanced Grading System**: Multiple exam types, weighted grades, and automatic GPA calculation
- **Attendance Tracking**: Daily attendance with justification system and absence reports

### 📊 **Analytics & Reporting**
- **Performance Analytics**: Real-time charts and graphs showing academic trends
- **Automated Report Cards**: Semester-based report generation with GPA calculation
- **Attendance Reports**: Detailed absence patterns and predictive analytics
- **Custom Reports**: Export functionality for all data with multiple formats

### 🗓️ **Event Management**
- **School Calendar**: Centralized event management for exams, meetings, and activities
- **Role-Based Visibility**: Events targeted to specific user groups
- **Notification System**: Automatic alerts for upcoming events and deadlines

## 🚀 Features

### For Administrators
- Complete user management (CRUD operations with soft delete)
- Class and subject management
- Teacher-subject-class assignments
- Global dashboard with analytics
- Event management
- System-wide reporting

### For Teachers
- Grade recording and management
- Attendance tracking
- Student performance monitoring
- Class rosters and student profiles
- Batch grade entry
- Academic history viewing

### For Students
- View personal grades and progress
- Access report cards and bulletins
- Check attendance records
- View upcoming events and exams
- Download/print report cards

### For Parents
- Monitor children's academic performance
- View grades and attendance
- Access report cards
- Receive school event notifications
- View teacher remarks and comments

## 🏗️ Architecture Overview

The application follows a **Domain-Driven MVC Architecture** with:
- **Service Layer Pattern**: Business logic extracted to service classes
- **Repository Pattern**: Data access abstraction through Eloquent
- **Form Request Validation**: Centralized input validation
- **Component-Based UI**: Reusable Blade components for consistency
- **Role-Based Middleware**: Granular access control

For detailed architecture documentation, see [Architecture Overview](docs/architecture-overview.md)

## 🛠️ Technology Stack

### Backend
- **Framework**: Laravel 11.x (latest)
- **PHP Version**: 8.4
- **Database**: MySQL 8.0 with InnoDB
- **Authentication**: Session-based with Laravel's built-in auth
- **Caching**: File-based (Redis ready)

### Frontend
- **CSS Framework**: Tailwind CSS 3.x
- **JavaScript**: Alpine.js 3.x
- **Build Tool**: Vite 5.x
- **Icons**: Heroicons & Custom SVGs
- **Components**: Custom Blade components

### Development Tools
- **Testing**: PHPUnit 10.x
- **Code Style**: PSR-12
- **Version Control**: Git
- **Package Management**: Composer & NPM

## 📋 Requirements

### System Requirements
- PHP 8.2 or higher
- Composer 2.0 or higher
- MySQL 8.0 or higher
- Node.js 18.0 or higher
- NPM or Yarn

### PHP Extensions Required
- BCMath PHP Extension
- Ctype PHP Extension
- cURL PHP Extension
- DOM PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PCRE PHP Extension
- PDO PHP Extension
- PDO MySQL Extension
- Tokenizer PHP Extension
- XML PHP Extension

## 🛠️ Installation

### Step 1: Clone or Extract the Project
```bash
# If you have the files
cd SchoolRecordManager

# OR clone from repository (if available)
git clone <repository-url> SchoolRecordManager
cd SchoolRecordManager
```

### Step 2: Install PHP Dependencies
```bash
composer install
```

### Step 3: Install Node Dependencies
```bash
npm install
# OR
yarn install
```

### Step 4: Environment Configuration
```bash
# Copy the environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Configure Database
Edit the `.env` file and update the database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_record_manager
DB_USERNAME=your_database_username
DB_PASSWORD=your_database_password
```

### Step 6: Create Database
Create a MySQL database named `school_record_manager`:
```sql
CREATE DATABASE school_record_manager CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 7: Run Migrations
```bash
php artisan migrate
```

### Step 8: Seed Demo Data (Optional)
```bash
php artisan db:seed
```

### Step 9: Build Frontend Assets
```bash
# For development
npm run dev

# For production
npm run build
```

### Step 10: Create Storage Link
```bash
php artisan storage:link
```

### Step 11: Start the Development Server
```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## 🔐 Default Login Credentials (After Seeding)

| Role    | Email                  | Password | Access Level |
|---------|------------------------|----------|--------------|
| Admin   | admin@school.com       | password | Full system access |
| Teacher | teacher@school.com     | password | Classes, grades, attendance |
| Student | student@school.com     | password | Personal records only |
| Parent  | parent@school.com      | password | Children's records |

## 📁 Project Structure

```
SchoolRecordManager/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Application controllers
│   │   ├── Middleware/       # Custom middleware
│   │   └── Requests/         # Form request validators
│   ├── Models/               # Eloquent models
│   └── Policies/             # Authorization policies
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── resources/
│   ├── css/                  # CSS files
│   ├── js/                   # JavaScript files
│   └── views/                # Blade templates
├── routes/
│   └── web.php               # Web routes
├── tests/
│   ├── Feature/              # Feature tests
│   └── Unit/                 # Unit tests
├── docs/                     # Documentation
│   ├── database-schema.md   # Database documentation
│   ├── uml.md               # UML diagrams
│   └── project-report.md    # Project report
└── public/                   # Public assets
```

## 🧪 Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

## 🚀 Production Deployment

### Optimization Commands
```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Build production assets
npm run build
```

### Environment Configuration
Make sure to update `.env` for production:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

### Web Server Configuration

#### Apache
Ensure `mod_rewrite` is enabled and use this `.htaccess` in the public directory:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

#### Nginx
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}
```

## 🔧 Troubleshooting

### Common Issues and Solutions

#### 1. Permission Issues
```bash
# Storage permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 2. Database Connection Error
- Verify MySQL is running
- Check database credentials in `.env`
- Ensure database exists
- Check if user has proper permissions

#### 3. 500 Internal Server Error
```bash
# Check logs
tail -f storage/logs/laravel.log

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

#### 4. Composer Dependencies Issues
```bash
# Clear composer cache
composer clear-cache

# Update dependencies
composer update

# Reinstall dependencies
rm -rf vendor
composer install
```

#### 5. NPM/Asset Issues
```bash
# Clear npm cache
npm cache clean --force

# Reinstall node modules
rm -rf node_modules package-lock.json
npm install
```

## 📚 Documentation

Detailed documentation is available in the `docs/` directory:
- [Database Schema](docs/database-schema.md) - Complete database structure and SQL
- [UML Diagrams](docs/uml.md) - System architecture and design diagrams
- [Project Report](docs/project-report.md) - Comprehensive project documentation

## 🔄 API Endpoints (Future Development)

The system is designed to easily expose RESTful APIs for mobile applications:
```
/api/v1/auth/login
/api/v1/auth/logout
/api/v1/user/profile
/api/v1/students/{id}/grades
/api/v1/students/{id}/absences
/api/v1/students/{id}/report-cards
/api/v1/events
```

## 🤝 Contributing

This is a course project, but suggestions and improvements are welcome:
1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📝 License

This project is created for educational purposes as part of a computer science course.

## 👥 Authors

- Course Project Team
- Computer Science Department

## 🙏 Acknowledgments

- Laravel Framework
- Tailwind CSS
- Alpine.js
- Font Awesome Icons
- MySQL Community

## 📞 Support

For issues or questions:
- Check the [Troubleshooting](#-troubleshooting) section
- Review the documentation in the `docs/` directory
- Contact the development team

---

**Note:** This is a demonstration project for educational purposes. For production use, additional security measures, testing, and optimization should be implemented.
