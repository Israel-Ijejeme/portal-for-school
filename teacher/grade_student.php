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
    header('Location: ../login.php');
    exit;
}

// Get user data
$user_id = SessionManager::getUserId();
$user = new User();
$userData = $user->getUserById($user_id);

// Get student ID from URL
$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
if (!$student_id) {
    SessionManager::setFlash('error', 'Invalid student ID.');
    header('Location: grades.php');
    exit;
}

// Get student data
$studentData = $user->getUserById($student_id);
if (!$studentData || $studentData['user_type'] != 3) { // 3 = student
    SessionManager::setFlash('error', 'Student not found.');
    header('Location: grades.php');
    exit;
}

// Initialize classes
$course = new Course();
$grade = new Grade();

// Get existing grade if any
$existingGrade = $grade->getGrade($student_id, null, null); // Adjusted for general grading

// Process grade submission
if (Utility::isPostRequest()) {
    // Validate score
    $score = isset($_POST['score']) ? floatval($_POST['score']) : 0;
    
    if ($score < 0 || $score > 100) {
        SessionManager::setFlash('error', 'Score must be between 0 and 100.');
    } else {
        // Save grade
        $result = $grade->setGrade($student_id, null, null, $score, $user_id); // Adjusted for general grading
        
        if ($result) {
            SessionManager::setFlash('success', 'Grade has been saved successfully.');
            header('Location: grades.php');
            exit;
        } else {
            SessionManager::setFlash('error', 'Failed to save grade. Please try again.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Student - <?php echo $studentData['full_name']; ?></title>
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
                <a href="courses.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-book me-2"></i> My Courses
                </a>
                <a href="attendance.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-clipboard-check me-2"></i> Attendance
                </a>
                <a href="grades.php" class="list-group-item list-group-item-action active">
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
                    <h1>Grade Student: <?php echo $studentData['full_name']; ?></h1>
                    <a href="grades.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Grades
                    </a>
                </div>

                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['flash']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <!-- Grade Form -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><?php echo $existingGrade ? 'Edit' : 'Add'; ?> Grade</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <p><strong>Nigerian Grading System:</strong></p>
                            <ul class="mb-0">
                                <li>70-100: A (5.0 points)</li>
                                <li>60-69: B (4.0 points)</li>
                                <li>50-59: C (3.0 points)</li>
                                <li>45-49: D (2.0 points)</li>
                                <li>40-44: E (1.0 points)</li>
                                <li>0-39: F (0.0 points)</li>
                            </ul>
                        </div>
                        
                        <form method="POST" action="">
                            <div class="form-group mb-3">
                                <label for="score">Score (0-100):</label>
                                <input type="number" class="form-control" id="score" name="score" 
                                       min="0" max="100" step="0.01" required
                                       value="<?php echo $existingGrade ? $existingGrade['score'] : ''; ?>">
                            </div>
                            
                            <?php if ($existingGrade): ?>
                            <div class="form-group mb-3">
                                <label>Current Grade:</label>
                                <div class="form-control" readonly><?php echo $existingGrade['grade']; ?></div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>Current Grade Point:</label>
                                <div class="form-control" readonly><?php echo $existingGrade['grade_point']; ?></div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>Last Graded By:</label>
                                <div class="form-control" readonly><?php echo $existingGrade['grader_name']; ?></div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label>Last Graded At:</label>
                                <div class="form-control" readonly><?php echo Utility::formatDate($existingGrade['graded_at'], 'd M, Y H:i'); ?></div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> <?php echo $existingGrade ? 'Update' : 'Save'; ?> Grade
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>