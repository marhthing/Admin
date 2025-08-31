# SFGS to CBT Migration System

## Overview

This is a web-based database migration tool designed to securely transfer educational data from a legacy SFGS (School Fee Generation System) database to a modern CBT (Computer-Based Testing) platform. The system provides intelligent data synchronization with security features including password hashing, authentication controls, and real-time progress tracking. It handles migration of users (admins, teachers, students), classes, and academic sessions while preserving data integrity and minimizing disruption to existing operations.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **Technology**: Vanilla HTML/CSS/JavaScript with modern CSS custom properties
- **Design Pattern**: Single-page application with responsive design
- **UI Framework**: Custom CSS with utility-first approach using CSS variables
- **Authentication UI**: Session-based interface with auto-logout functionality
- **Progress Tracking**: Real-time migration status display with live logging

### Backend Architecture
- **Technology**: PHP-based server-side processing
- **Database Layer**: Direct MySQL database connections for both source and target systems
- **Migration Engine**: Smart synchronization logic that only updates missing or incorrect data
- **Security Layer**: Master password authentication with session timeout (5 minutes)
- **Password Handling**: Automatic conversion from plain text to secure hashes during migration

### Data Flow Architecture
- **Source Database**: `if0_39795047_sfgs` (read-only access)
  - Tables: users, teachers, students, classes, sessions
- **Target Database**: `if0_39795047_cbt` (write-only access)
  - Tables: users, class_levels, sessions, terms
- **Migration Strategy**: Preserve existing records, only update missing/incorrect data
- **Data Mapping**: Intelligent mapping between different table structures and naming conventions

### Security Architecture
- **Authentication**: Master password protection for system access
- **Session Management**: Auto-logout after 5 minutes of inactivity
- **Password Security**: Automatic hashing of plain text passwords during migration
- **Database Access**: Separate read/write permissions for source and target databases
- **Data Integrity**: Validation and error handling during migration process

## External Dependencies

### Database Systems
- **MySQL**: Primary database engine for both SFGS and CBT systems
- **Database Hosts**: Remote MySQL hosting (appears to be shared hosting environment)
- **Connection Management**: PHP MySQLi or PDO for database connectivity

### Infrastructure Dependencies
- **PHP Runtime**: Server-side processing engine
- **Web Server**: HTTP server for hosting the web interface
- **Composer**: PHP dependency management (minimal usage)

### Browser Requirements
- **Modern Web Browser**: Support for CSS custom properties and ES6+ JavaScript
- **Responsive Design**: Mobile and desktop browser compatibility
- **Session Storage**: Browser session management for authentication state