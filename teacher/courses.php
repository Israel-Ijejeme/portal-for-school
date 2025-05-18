<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';
require_once '../classes/Semester.php';
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
$user = new User();
$userData = $user->getUserById($user_id);

// Get teacher's courses
$course = new Course();
$teacherCourses = $course->getCoursesByTeacherId($user_id);

// Get semester info
$semester = new Semester();
$semesters = $semester->getAllSemesters();
$currentSemester = $semester->getCurrentSemester();

// Filter by semester if requested
$selected_semester_id = isset($_GET['semester_id']) ? intval($_GET['semester_id']) : ($currentSemester ? $currentSemester['id'] : 0);

// Filter courses by semester if selected
if ($selected_semester_id > 0) {
    $filteredCourses = array_filter($teacherCourses, function($course) use ($selected_semester_id) {
        return $course['semester_id'] == $selected_semester_id;
    });
} else {
    $filteredCourses = $teacherCourses;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses</title>
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
                <h1 class="mt-4 mb-4">My Courses</h1>
                
                <!-- Semester Filter -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Filter Courses</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="courses.php" class="form-inline">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label for="semester_id" class="me-2">Semester:</label>
                                        <select name="semester_id" id="semester_id" class="form-control" onchange="this.form.submit()">
                                            <option value="0">All Semesters</option>
                                            <?php foreach ($semesters as $sem): ?>
                                                <option value="<?php echo $sem['id']; ?>" <?php echo $selected_semester_id == $sem['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $sem['name']; ?> <?php echo $sem['is_current'] ? '(Current)' : ''; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Courses Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">My Courses</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($filteredCourses)): ?>
                            <div class="alert alert-info">
                                <?php if ($selected_semester_id > 0): ?>
                                    <p>You are not assigned to any courses for the selected semester.</p>
                                <?php else: ?>
                                    <p>You are not assigned to any courses.</p>
                                <?php endif; ?>
                                <p>Please contact the administrator to be assigned to courses.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Course Code</th>
                                            <th>Title</th>
                                            <th>Department</th>
                                            <th>Semester</th>
                                            <th>Credit Units</th>
                                            <th>Students</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($filteredCourses as $courseData): 
                                            // Get student count for this course
                                            $students = $course->getCourseStudents($courseData['id']);
                                            $studentCount = count($students);
                                        ?>
                                            <tr>
                                                <td><?php echo $courseData['course_code']; ?></td>
                                                <td><?php echo $courseData['title']; ?></td>
                                                <td><?php echo $courseData['department_name']; ?></td>
                                                <td><?php echo $courseData['semester_name']; ?></td>
                                                <td><?php echo $courseData['credit_units']; ?></td>
                                                <td><?php echo $studentCount; ?></td>
                                                <td>
                                                    <a href="course_students.php?course_id=<?php echo $courseData['id']; ?>" class="btn btn-sm btn-primary mb-1">
                                                        <i class="fas fa-users"></i> View Students
                                                    </a>
                                                    <a href="course_grades.php?course_id=<?php echo $courseData['id']; ?>" class="btn btn-sm btn-success mb-1">
                                                        <i class="fas fa-chart-bar"></i> Manage Grades
                                                    </a>
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