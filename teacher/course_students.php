<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';
require_once '../classes/Grade.php';
require_once '../classes/Utility.php';

SessionManager::startSession();

// Redirect if not logged in or not a teacher
if (!SessionManager::isLoggedIn() || !SessionManager::isTeacher()) {
    SessionManager::setFlash('error', 'You must be logged in as a teacher to access this page.');
    header('Location: login.php');
    exit;
}

// Get user data
$user_id = SessionManager::getUserId();
$userData = (new User())->getUserById($user_id);

// Check if course_id is provided
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    SessionManager::setFlash('error', 'Course ID is required.');
    header('Location: courses.php');
    exit;
}

$course_id = intval($_GET['course_id']);

// Initialize classes
$course = new Course();
$grade = new Grade();

// Get course details
$courseData = $course->getCourseById($course_id);

// Check if course exists and belongs to the teacher
if (!$courseData || $courseData['teacher_id'] != $user_id) {
    SessionManager::setFlash('error', 'You are not authorized to view this course or the course does not exist.');
    header('Location: courses.php');
    exit;
}

// Get students enrolled in the course
$students = $course->getCourseStudents($course_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Students - <?php echo $courseData['title']; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .layout-container {
            display: table;
            width: 100%;
            height: calc(100vh - 56px);
        }
        .sidebar {
            display: table-cell;
            width: 250px;
            background-color: #f8f9fa;
            vertical-align: top;
            border-right: 1px solid #dee2e6;
        }
        .content {
            display: table-cell;
            vertical-align: top;
            padding: 20px;
        }
        .list-group-item {
            border-radius: 0;
            border-left: none;
            border-right: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Teacher Dashboard</a>
            <div class="ms-auto">
                <div class="dropdown">
                    <a class="text-white dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> <?php echo $userData['full_name']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Layout Container -->
    <div class="layout-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="courses.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-book me-2"></i> My Courses
                </a>
                <a href="attendance.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-clipboard-check me-2"></i> Attendance
                </a>
                <a href="grades.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-graduation-cap me-2"></i> Grades
                </a>
                <a href="profile.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user me-2"></i> Profile
                </a>
                <a href="../logout.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Students in <?php echo $courseData['course_code']; ?>: <?php echo $courseData['title']; ?></h1>
                    <a href="courses.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Courses
                    </a>
                </div>
                
                <!-- Course Details Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Course Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>Course Code:</strong> <?php echo $courseData['course_code']; ?></p>
                                <p><strong>Title:</strong> <?php echo $courseData['title']; ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Department:</strong> <?php echo $courseData['department_name']; ?></p>
                                <p><strong>Semester:</strong> <?php echo $courseData['semester_name']; ?></p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Credit Units:</strong> <?php echo $courseData['credit_units']; ?></p>
                                <p><strong>Students Enrolled:</strong> <?php echo count($students); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Students Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Enrolled Students</h6>
                        <a href="course_grades.php?course_id=<?php echo $course_id; ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-chart-bar"></i> Manage Grades
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($students)): ?>
                            <div class="alert alert-info">
                                <p>No students are currently enrolled in this course.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                            <th>Grade Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): 
                                            // Check if student has a grade for this course
                                            $studentGrade = $grade->getGrade($student['id'], $course_id, $courseData['semester_id']);
                                            $hasGrade = $studentGrade !== false;
                                        ?>
                                            <tr>
                                                <td><?php echo $student['id']; ?></td>
                                                <td><?php echo $student['full_name']; ?></td>
                                                <td><?php echo $student['email']; ?></td>
                                                <td><?php echo $student['department_name']; ?></td>
                                                <td>
                                                    <?php if ($hasGrade): ?>
                                                        <span class="badge bg-success">Graded (<?php echo $studentGrade['grade']; ?> - <?php echo $studentGrade['score']; ?>%)</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark">Not Graded</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($hasGrade): ?>
                                                        <a href="grade_student.php?student_id=<?php echo $student['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i> Edit Grade
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="grade_student.php?student_id=<?php echo $student['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-sm btn-success">
                                                            <i class="fas fa-plus-circle"></i> Add Grade
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>