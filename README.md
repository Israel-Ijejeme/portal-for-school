# School Dashboard System

## Project Overview

The School Dashboard System is a comprehensive web application built with PHP and MySQL, designed specifically for the Nigerian education system. It provides a centralized platform for managing academic activities with distinct interfaces for students, teachers, and administrators. The system follows the Nigerian grading system with a maximum CGPA of 5.0 and includes features for course registration, grade management, user administration, and academic reporting.

## System Architecture

### Technology Stack

- **Backend**: PHP 7.4+ (Object-Oriented)
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **UI Framework**: Bootstrap 5
- **Icons**: Font Awesome 6
- **AJAX**: Used for dynamic content loading

### Design Pattern

The application follows a modified Model-View-Controller (MVC) pattern:

- **Models**: PHP classes in the `/classes` directory handle data operations
- **Views**: PHP templates with embedded HTML in role-specific directories
- **Controllers**: Logic embedded in the PHP files that handle requests

## Core Components

### 1. Authentication System

The authentication system uses PHP sessions for maintaining user state and implements role-based access control with three distinct user types:

- **Admin**: Full system access with user management capabilities
- **Teacher**: Access to assigned courses, student grading, and reporting
- **Student**: Access to course registration, grade viewing, and profile management

Security measures include:
- Password hashing using PHP's `password_hash()` function
- Input sanitization to prevent SQL injection
- Session validation to prevent unauthorized access
- Role-specific access controls

### 2. Database Structure

The database consists of several interconnected tables:

- **users**: Stores user information including credentials and role assignments
- **user_types**: Defines the three user roles (admin, teacher, student)
- **departments**: Academic departments within the institution
- **courses**: Course information including code, title, and credit units
- **semesters**: Academic periods with current semester flag
- **student_courses**: Many-to-many relationship between students and courses
- **grades**: Student performance records with scores and calculated grade points

### 3. Class Architecture

The system uses object-oriented PHP with specialized classes:

- **Database**: Handles database connections and query operations
- **User**: Manages user authentication, registration, and profile operations
- **Course**: Handles course management and student registration
- **Grade**: Manages grade calculations and academic reporting
- **Department**: Handles department-related operations
- **Semester**: Manages semester settings and operations
- **SessionManager**: Handles PHP session operations and user state
- **Utility**: Provides helper functions for common tasks

## Feature Breakdown

### Admin Features

1. **Dashboard**
   - Overview statistics (total students, teachers, courses, departments)
   - Quick access to management functions
   - System information display

2. **User Management**
   - Add, edit, and delete teachers and students
   - Assign teachers to departments
   - View user details and statistics

3. **Course Management**
   - Create and manage courses
   - Assign courses to teachers and departments
   - Set course credit units and semester assignment

4. **Department Management**
   - Create and manage academic departments
   - View department statistics

5. **System Settings**
   - Manage current semester settings
   - View system information

### Teacher Features

1. **Dashboard**
   - Overview of assigned courses and enrolled students
   - Quick access to teaching functions
   - Current semester information

2. **Course Management**
   - View assigned courses
   - Access course details and enrolled students
   - Manage course materials

3. **Student Grading**
   - Enter and update student grades
   - View grade distribution for courses
   - Grade according to Nigerian grading system

4. **Student Management**
   - View students enrolled in courses
   - Access student academic information
   - Track student performance

5. **Profile Management**
   - Update personal information
   - Change password
   - Upload profile picture

### Student Features

1. **Dashboard**
   - Overview of enrolled courses and academic performance
   - Quick access to student functions
   - CGPA and classification display

2. **Course Registration**
   - Register for available courses
   - Filter courses by department
   - View registered courses and credit units

3. **Grade Viewing**
   - View course grades and GPA
   - Filter grades by semester
   - Calculate CGPA and academic standing
   - Print grade reports

4. **Profile Management**
   - Update personal information
   - Change password
   - Upload profile picture

## Technical Implementation

### Session Management

The `SessionManager` class provides a centralized way to handle user sessions:

```php
class SessionManager {
    // Start a new session if not already started
    public static function startSession() { ... }
    
    // Check if user is logged in
    public static function isLoggedIn() { ... }
    
    // Get the logged-in user's ID
    public static function getUserId() { ... }
    
    // Check user roles
    public static function isAdmin() { ... }
    public static function isTeacher() { ... }
    public static function isStudent() { ... }
    
    // Flash messages for one-time notifications
    public static function setFlash($key, $message) { ... }
    public static function getFlash($key) { ... }
}
```

### Database Operations

The `Database` class provides a secure interface for database operations:

```php
class Database {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "school_dashboard";
    private $conn;
    
    // Constructor establishes connection
    public function __construct() { ... }
    
    // Prepared statements for secure queries
    public function prepare($sql) { ... }
    
    // Query execution
    public function query($sql) { ... }
    
    // String escaping for security
    public function escapeString($string) { ... }
}
```

### Grade Calculation

The Nigerian grading system is implemented in the `Grade` class:

```php
// Calculate grade and grade point based on score (Nigerian grading system)
if ($score >= 70) {
    $grade = 'A';
    $grade_point = 5.0;
} elseif ($score >= 60) {
    $grade = 'B';
    $grade_point = 4.0;
} elseif ($score >= 50) {
    $grade = 'C';
    $grade_point = 3.0;
} elseif ($score >= 45) {
    $grade = 'D';
    $grade_point = 2.0;
} elseif ($score >= 40) {
    $grade = 'E';
    $grade_point = 1.0;
} else {
    $grade = 'F';
    $grade_point = 0.0;
}
```

### CGPA Calculation

The system calculates CGPA using weighted averages of course credit units:

```php
public function calculateCGPA($student_id) {
    $sql = "SELECT g.grade_point, c.credit_units 
            FROM grades g 
            JOIN courses c ON g.course_id = c.id 
            WHERE g.student_id = ?";
    
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $total_points = 0;
    $total_units = 0;
    
    while ($row = $result->fetch_assoc()) {
        $total_points += ($row['grade_point'] * $row['credit_units']);
        $total_units += $row['credit_units'];
    }
    
    if ($total_units > 0) {
        return round($total_points / $total_units, 2);
    }
    
    return 0;
}
```

### User Interface

The user interface is built with Bootstrap 5 and features:

1. **Responsive Design**: Works on desktop and mobile devices
2. **Role-Based Navigation**: Different sidebar menus for each user type
3. **Dashboard Cards**: Visual representation of key statistics
4. **Data Tables**: Sortable and filterable tables for data display
5. **Form Validation**: Client and server-side validation for data integrity
6. **Flash Messages**: Temporary notifications for user actions
7. **Print Functionality**: Printable grade reports and transcripts

### Security Considerations

1. **Password Security**: All passwords are hashed using PHP's secure password hashing functions
2. **SQL Injection Prevention**: Prepared statements are used for all database queries
3. **XSS Prevention**: Input sanitization and output escaping
4. **CSRF Protection**: Form tokens for sensitive operations
5. **Session Security**: Secure session handling and validation
6. **Role-Based Access Control**: Users can only access authorized sections

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher

## Installation

1. Clone or download this repository to your web server's document root.
2. Create a MySQL database named `school_dashboard`.
3. Import the database schema and dummy data by running the SQL script:
   ```
   php school_dashboard/config/init_db.php
   ```
4. Configure your web server to point to the `school_dashboard` directory.
5. Access the system through your web browser.

## Default Login Credentials

### Admin
- Email: admin@school.edu
- Password: Admin125$#.

### Teachers
- Email: john.doe@school.edu
- Password: Teacher125$#.

- Email: jane.smith@school.edu
- Password: teacher123

- Email: mike.johnson@school.edu
- Password: teacher123

### Students
- Email: emma.wilson@school.edu
- Password: Student125$#.

- Email: david.brown@school.edu
- Password: student123

- Email: sophia.lee@school.edu
- Password: student123

- Email: james.taylor@school.edu
- Password: student123

## Directory Structure

```
school_dashboard/
├── admin/                  # Admin interface files
│   ├── courses.php         # Course management
│   ├── dashboard.php       # Admin dashboard
│   ├── departments.php     # Department management
│   ├── login.php           # Admin login
│   ├── students.php        # Student management
│   └── teachers.php        # Teacher management
├── assets/                 # Static assets
│   ├── css/                # Stylesheets
│   ├── js/                 # JavaScript files
│   └── img/                # Images and icons
├── classes/                # PHP class files
│   ├── Course.php          # Course management class
│   ├── Database.php        # Database connection class
│   ├── Department.php      # Department management class
│   ├── Grade.php           # Grade calculation class
│   ├── Semester.php        # Semester management class
│   ├── SessionManager.php  # Session handling class
│   ├── User.php            # User management class
│   └── Utility.php         # Helper functions
├── config/                 # Configuration files
│   ├── database.php        # Database configuration
│   ├── init_db.php         # Database initialization
│   └── setup.sql           # SQL schema and initial data
├── includes/               # Shared components
│   ├── footer.php          # Footer template
│   └── header.php          # Header template
├── student/                # Student interface files
│   ├── courses.php         # View enrolled courses
│   ├── dashboard.php       # Student dashboard
│   ├── grades.php          # View grades and CGPA
│   ├── login.php           # Student login
│   ├── profile.php         # Profile management
│   ├── register.php        # New student registration
│   └── register_courses.php # Course registration
├── teacher/                # Teacher interface files
│   ├── course_grades.php   # Manage course grades
│   ├── course_students.php # View course students
│   ├── courses.php         # View assigned courses
│   ├── dashboard.php       # Teacher dashboard
│   ├── grade_student.php   # Grade individual students
│   ├── login.php           # Teacher login
│   ├── profile.php         # Profile management
│   └── register.php        # New teacher registration
├── index.php              # Main entry point
├── logout.php             # Logout handler
└── README.md              # Project documentation
```

## Usage

1. Visit the homepage to select your user type (student, teacher, or admin).
2. Log in with your credentials.
3. Use the sidebar navigation to access different features based on your role.

## Nigerian Education System

- Maximum CGPA: 5.0
- Grade scale:
  - 70-100: A (5.0 points)
  - 60-69: B (4.0 points)
  - 50-59: C (3.0 points)
  - 45-49: D (2.0 points)
  - 40-44: E (1.0 points)
  - 0-39: F (0.0 points)
- CGPA classifications:
  - 4.5-5.0: First Class
  - 3.5-4.49: Second Class Upper
  - 2.5-3.49: Second Class Lower
  - 1.5-2.49: Third Class
  - 1.0-1.49: Pass
  - Below 1.0: Fail

## Future Enhancements

The School Dashboard System has been designed with extensibility in mind. Future versions could include the following enhancements:

1. **Email Notifications**: Automated emails for grade updates and course registrations
2. **Mobile Application**: Native mobile app for Android and iOS
3. **Advanced Analytics**: Statistical analysis of student performance
4. **Online Payments**: Integration with payment gateways for fee collection
5. **Learning Management**: Course material uploads and online assignments
6. **Attendance Tracking**: Digital attendance system with QR code scanning
7. **API Integration**: RESTful API for third-party integrations
8. **Parent Portal**: Interface for parents to monitor their children's progress
9. **Academic Calendar**: Integrated scheduling system for academic events
10. **Messaging System**: Internal communication between users

## License

This project is open-source and available for educational purposes. 