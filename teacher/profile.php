<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Department.php';
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

// Get departments for the dropdown
$department = new Department();
$departments = $department->getAllDepartments();

// Handle profile update
if (Utility::isPostRequest() && isset($_POST['update_profile'])) {
    $full_name = Utility::sanitizeInput($_POST['full_name']);
    $email = Utility::sanitizeInput($_POST['email']);
    $age = isset($_POST['age']) ? intval($_POST['age']) : 0;
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
    
    // Validate inputs
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = 'Full name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!Utility::validateEmail($email)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if ($age <= 0) {
        $errors[] = 'Please enter a valid age.';
    }
    
    if ($department_id <= 0) {
        $errors[] = 'Please select a department.';
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        $result = $user->updateProfile($user_id, $full_name, $email, $age, $department_id);
        
        if ($result) {
            SessionManager::setFlash('success', 'Profile updated successfully.');
            header('Location: profile.php');
            exit;
        } else {
            SessionManager::setFlash('error', 'Failed to update profile. Please try again.');
        }
    } else {
        // Set error messages
        foreach ($errors as $error) {
            SessionManager::setFlash('error', $error);
        }
    }
}

// Handle profile picture upload
if (Utility::isPostRequest() && isset($_POST['update_picture']) && isset($_FILES['profile_picture'])) {
    // Create uploads directory if it doesn't exist
    $uploads_dir = '../assets/uploads/profile_pictures';
    if (!file_exists($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
    }
    
    // Upload the file
    $upload_result = Utility::uploadFile($_FILES['profile_picture'], $uploads_dir, ['jpg', 'jpeg', 'png'], 2097152);
    
    if ($upload_result['success']) {
        // Update profile picture in database
        $profile_picture = $upload_result['filename'];
        $result = $user->updateProfilePicture($user_id, $profile_picture);
        
        if ($result) {
            SessionManager::setFlash('success', 'Profile picture updated successfully.');
            header('Location: profile.php');
            exit;
        } else {
            SessionManager::setFlash('error', 'Failed to update profile picture in database. Please try again.');
        }
    } else {
        SessionManager::setFlash('error', $upload_result['message']);
    }
}

// Refresh user data
$userData = $user->getUserById($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile</title>
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
                <a href="grades.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-graduation-cap me-2"></i> Grades
                </a>
                <a href="profile.php" class="list-group-item list-group-item-action active">
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
                <h1 class="mb-4">My Profile</h1>

                <?php if (isset($_SESSION['flash'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['flash']['message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Update Profile</h6>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="full_name">Full Name</label>
                                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                                       value="<?php echo $userData['full_name']; ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="email">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo $userData['email']; ?>" required>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="age">Age</label>
                                                <input type="number" class="form-control" id="age" name="age" 
                                                       value="<?php echo $userData['age']; ?>" required min="18">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="department_id">Department</label>
                                                <select class="form-control" id="department_id" name="department_id" required>
                                                    <option value="">Select Department</option>
                                                    <?php foreach ($departments as $dept): ?>
                                                        <option value="<?php echo $dept['id']; ?>" <?php echo $userData['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                                            <?php echo $dept['name']; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="update_profile" class="btn btn-success">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Account Information</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Account Type:</strong> Teacher</p>
                                <p><strong>Joined Date:</strong> <?php echo Utility::formatDate($userData['created_at']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>