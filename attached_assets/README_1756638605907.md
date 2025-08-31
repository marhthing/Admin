
# SFGS to CBT Migration System

A secure, web-based database migration tool designed to intelligently synchronize data between the SFGS legacy system and a modern CBT (Computer-Based Testing) platform.

## 🎯 Project Overview

This system provides a secure interface for migrating educational data from an existing SFGS database to a new CBT system while ensuring data integrity, security, and minimal disruption to existing records.

### Key Features

- 🔐 **Secure Authentication**: Master password protection with auto-logout after 5 minutes
- 🛡️ **Password Security**: Automatically converts plain text passwords to secure hashes during migration
- 🔄 **Smart Synchronization**: Only updates missing or incorrect data, preserves existing records
- 📊 **Real-time Progress Tracking**: Live migration status with detailed logging
- 📱 **Responsive Design**: Works on desktop and mobile devices
- 🎨 **Modern UI**: Clean, professional interface with intuitive navigation

## 🗄️ Database Architecture

### Source Database (SFGS)
- **Database**: `if0_39795047_sfgs` (Read-only)
- **Tables**: users, teachers, students, classes, sessions

### Target Database (CBT)
- **Database**: `if0_39795047_cbt` (Write-only)
- **Tables**: users, class_levels, sessions, terms

## 🔧 Migration Process

The system performs intelligent data synchronization across these areas:

1. **Admin Users Migration** (`sfgs.users` → `cbt.users`)
   - Converts plain text passwords to secure hashes
   - Preserves existing user data

2. **Teachers Migration** (`sfgs.teachers` → `cbt.users`)
   - Assigns teacher role and permissions
   - Secure password hashing

3. **Students Migration** (`sfgs.students` → `cbt.users`)
   - Student account creation with proper role assignment
   - Password security implementation

4. **Classes Migration** (`sfgs.classes` → `cbt.class_levels`)
   - Academic structure synchronization

5. **Sessions Migration** (`sfgs.sessions` → `cbt.sessions`)
   - Academic session data transfer

6. **Terms Migration** (Standard terms → `cbt.terms`)
   - Academic term structure setup

## 🛠️ Technical Stack

- **Backend**: PHP 8.2
- **Database**: MySQL/MariaDB with PDO
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Security**: Session-based authentication, password hashing
- **Architecture**: MVC-inspired structure

## 📁 File Structure

```
├── auth.php              # Authentication and session management
├── db.php                # Database configuration and connections
├── index.php             # Entry point with authentication check
├── login.php             # Secure login interface
├── dashboard.php         # Main migration dashboard
├── migrate.php           # Core migration API endpoints
├── results.php           # CBT results management
├── logout.php            # Secure logout functionality
├── session_ping.php      # Session activity tracking
└── attached_assets/      # Database schema files
    ├── if0_39795047_sfgs_*.sql
    └── if0_39795047_cbt_*.sql
```

## 🚀 Installation & Setup

1. **Configure Database Credentials**
   ```php
   // Edit db.php
   $db_config = [
       'sfgs' => [
           'host' => 'your_host',
           'database' => 'if0_39795047_sfgs',
           'username' => 'your_username',
           'password' => 'your_actual_password'
       ],
       'cbt' => [
           'host' => 'your_host',
           'database' => 'if0_39795047_cbt',
           'username' => 'your_username',
           'password' => 'your_actual_password'
       ]
   ];
   ```

2. **Set Master Password**
   ```php
   // In auth.php
   define('MASTER_PASSWORD', 'SUREFOUNDATIONGROUPOFSCHOOL2025');
   ```

3. **Deploy and Run**
   - The system runs on PHP's built-in server
   - Access via web browser
   - Default port: 3000

## 🔒 Security Features

- **Authentication**: Master password protection
- **Session Management**: 5-minute auto-logout for security
- **Password Hashing**: Automatic conversion of plain text passwords
- **Input Validation**: SQL injection protection via prepared statements
- **Access Control**: Protected endpoints require authentication

## 📊 Usage Instructions

1. **Login**: Access the system with the master password
2. **Dashboard**: Review migration overview and security notices
3. **Start Migration**: Click "Start Smart Sync" to begin the process
4. **Monitor Progress**: Watch real-time status updates and detailed logs
5. **Review Results**: Check migration summary and success metrics

## 🎨 UI/UX Features

- **Modern Design**: Clean, professional interface
- **Responsive Layout**: Desktop and mobile optimized
- **Real-time Feedback**: Live progress bars and status indicators
- **Detailed Logging**: Console-style output for transparency
- **Security Indicators**: Visual cues for security operations

## 🛡️ Data Protection

- **Read-only Source**: SFGS database remains unchanged
- **Smart Updates**: Only missing/incorrect data is modified
- **Password Security**: All passwords are hashed before insertion
- **Transaction Safety**: Database operations use transactions
- **Error Handling**: Comprehensive error catching and reporting

## 🔍 Monitoring & Logs

The system provides detailed logging for:
- Database connection status
- Migration progress by category
- Password hashing operations
- Error conditions and resolutions
- Performance metrics and timing

## 🎯 Target Users

- **School Administrators**: Managing data migration
- **IT Staff**: Technical implementation and monitoring
- **System Managers**: Overseeing the transition process

## 📈 Benefits

- **Data Integrity**: Ensures accurate data transfer
- **Security Enhancement**: Upgrades password security
- **Minimal Disruption**: Preserves existing data
- **Transparency**: Clear progress tracking
- **Professional Interface**: User-friendly operation

## 🔧 Customization

The system is designed to be easily customizable for:
- Different database schemas
- Additional migration rules
- Custom authentication methods
- Extended logging requirements

---

**Note**: This is a specialized tool for educational institutions transitioning from legacy SFGS systems to modern CBT platforms. Always test migrations in a development environment before production use.
