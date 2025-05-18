<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .layout-container {
            display: table;
            width: 100%;
            height: calc(100vh - 56px); /* Subtract navbar height */
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
    <?php
    require_once '../classes/SessionManager.php';
    require_once '../classes/User.php';
    require_once '../classes/Department.php';
    require_once '../classes/Course.php';
    require_once '../classes/Utility.php';

    SessionManager::startSession();

    // Redirect if not logged in or not an admin
    if (!SessionManager::isLoggedIn() || !SessionManager::isAdmin()) {
        SessionManager::setFlash('error', 'You must be logged in as an admin to access this page.');
        header('Location: login.php');
        exit;
    }

    // Initialize classes
    $user = new User();
    $department = new Department();
    $course = new Course();

    // Get user data for the navbar
    $userData = $user->getUserById(SessionManager::getUserId());

    // Get all departments
    $departments = $department->getAllDepartments();

    // Get all teachers for select dropdown
    $teachers = $user->getUsersByType(2); // 2 = teacher type

    // Get all courses with department and teacher info
    $courses = $course->getAllCoursesWithDetails();

    // Handle delete request
    if (Utility::isPostRequest() && isset($_POST['delete_course']) && isset($_POST['course_id'])) {
        $course_id = intval($_POST['course_id']);
        
        // Delete the course
        $result = $course->deleteCourse($course_id);
        
        if ($result) {
            SessionManager::setFlash('success', 'Course deleted successfully.');
        } else {
            SessionManager::setFlash('error', 'Failed to delete course. The course may have enrolled students or grades.');
        }
        
        // Refresh the page
        header('Location: courses.php');
        exit;
    }

    // Handle add/edit course
    if (Utility::isPostRequest() && isset($_POST['save_course'])) {
        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $title = Utility::sanitizeInput($_POST['course_name']);
        $course_code = Utility::sanitizeInput($_POST['course_code']);
        $description = Utility::sanitizeInput($_POST['description']);
        $credit_units = intval($_POST['credit_hours']);
        $department_id = intval($_POST['department_id']);
        $teacher_id = intval($_POST['teacher_id']);
        
        // Validate inputs
        $errors = [];
        
        if (empty($title)) {
            $errors[] = 'Course name is required.';
        }
        
        if (empty($course_code)) {
            $errors[] = 'Course code is required.';
        }
        
        if ($credit_units <= 0) {
            $errors[] = 'Credit hours must be greater than zero.';
        }
        
        if ($department_id <= 0) {
            $errors[] = 'Please select a department.';
        }
        
        if ($teacher_id <= 0) {
            $errors[] = 'Please select a teacher.';
        }
        
        // If no errors, add/update course
        if (empty($errors)) {
            if ($course_id > 0) {
                // Update existing course
                $result = $course->updateCourse($course_id, $title, $course_code, $description, $credit_units, $department_id, $teacher_id);
                $message = 'Course updated successfully.';
            } else {
                // Add new course
                $result = $course->addCourse($title, $course_code, $description, $credit_units, $department_id, $teacher_id);
                $message = 'Course added successfully.';
            }
            
            if ($result) {
                SessionManager::setFlash('success', $message);
                header('Location: courses.php');
                exit;
            } else {
                SessionManager::setFlash('error', 'Failed to save course. Course code may already be in use.');
            }
        } else {
            // Set error messages
            foreach ($errors as $error) {
                SessionManager::setFlash('error', $error);
            }
        }
    }

    ?>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Admin Dashboard</a>
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
                <a href="departments.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-building me-2"></i> Departments
                </a>
                <a href="courses.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-book me-2"></i> Courses
                </a>
                <a href="teachers.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-chalkboard-teacher me-2"></i> Teachers
                </a>
                <a href="students.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-graduate me-2"></i> Students
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
                    <h1>Manage Courses</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">
                        <i class="fas fa-plus-circle"></i> Add New Course
                    </button>
                </div>
                
                <!-- Courses Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Courses (<?php echo count($courses); ?>)</h6>
                    </div>
                    <div class="card-body">
                        <?php if (empty($courses)): ?>
                            <div class="alert alert-info">
                                <p>No courses found.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Credit Hours</th>
                                            <th>Teacher</th>
                                            <th>Students</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($courses as $c): 
                                            // Get enrolled students count
                                            $studentsCount = $course->getEnrolledStudentsCount($c['id']);
                                        ?>
                                            <tr>
                                                <td><?php echo $c['id']; ?></td>
                                                <td><?php echo $c['course_code']; ?></td>
                                                <td><?php echo $c['title']; ?></td>
                                                <td><?php echo $c['department_name']; ?></td>
                                                <td><?php echo $c['credit_units']; ?></td>
                                                <td><?php echo $c['teacher_name']; ?></td>
                                                <td><?php echo $studentsCount; ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary mb-1 edit-course" 
                                                            data-id="<?php echo $c['id']; ?>"
                                                            data-name="<?php echo $c['title']; ?>"
                                                            data-code="<?php echo $c['course_code']; ?>"
                                                            data-description="<?php echo isset($c['description']) ? $c['description'] : ''; ?>"
                                                            data-credit-hours="<?php echo $c['credit_units']; ?>"
                                                            data-department="<?php echo $c['department_id']; ?>"
                                                            data-teacher="<?php echo $c['teacher_id']; ?>"
                                                            data-bs-toggle="modal" data-bs-target="#editCourseModal">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <form method="POST" action="" style="display:inline;">
                                                        <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                                                        <button type="submit" name="delete_course" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.');">
                                                            <i class="fas fa-trash-alt"></i> Delete
                                                        </button>
                                                    </form>
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

            <!-- Add Course Modal -->
            <div class="modal fade" id="addCourseModal" tabindex="-1" aria-labelledby="addCourseModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addCourseModalLabel">Add New Course</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="course_name" class="form-label">Course Name</label>
                                    <input type="text" class="form-control" id="course_name" name="course_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="course_code" class="form-label">Course Code</label>
                                    <input type="text" class="form-control" id="course_code" name="course_code" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="credit_hours" class="form-label">Credit Hours</label>
                                    <input type="number" class="form-control" id="credit_hours" name="credit_hours" min="1" max="6" required>
                                </div>
                                <div class="mb-3">
                                    <label for="department_id" class="form-label">Department</label>
                                    <select class="form-control" id="department_id" name="department_id" required>
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="teacher_id" class="form-label">Teacher</label>
                                    <select class="form-control" id="teacher_id" name="teacher_id" required>
                                        <option value="">Select Teacher</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['full_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="save_course" class="btn btn-primary">Save Course</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Course Modal -->
            <div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editCourseModalLabel">Edit Course</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" id="edit_course_id" name="course_id">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="edit_course_name" class="form-label">Course Name</label>
                                    <input type="text" class="form-control" id="edit_course_name" name="course_name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_course_code" class="form-label">Course Code</label>
                                    <input type="text" class="form-control" id="edit_course_code" name="course_code" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_credit_hours" class="form-label">Credit Hours</label>
                                    <input type="number" class="form-control" id="edit_credit_hours" name="credit_hours" min="1" max="6" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_department_id" class="form-label">Department</label>
                                    <select class="form-control" id="edit_department_id" name="department_id" required>
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_teacher_id" class="form-label">Teacher</label>
                                    <select class="form-control" id="edit_teacher_id" name="teacher_id" required>
                                        <option value="">Select Teacher</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['full_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="save_course" class="btn btn-primary">Update Course</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script>
            // Set edit form values when edit button is clicked
            document.addEventListener('DOMContentLoaded', function() {
                const editButtons = document.querySelectorAll('.edit-course');
                
                editButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const name = this.getAttribute('data-name');
                        const code = this.getAttribute('data-code');
                        const description = this.getAttribute('data-description');
                        const creditHours = this.getAttribute('data-credit-hours');
                        const department = this.getAttribute('data-department');
                        const teacher = this.getAttribute('data-teacher');
                        
                        document.getElementById('edit_course_id').value = id;
                        document.getElementById('edit_course_name').value = name;
                        document.getElementById('edit_course_code').value = code;
                        document.getElementById('edit_description').value = description;
                        document.getElementById('edit_credit_hours').value = creditHours;
                        document.getElementById('edit_department_id').value = department;
                        document.getElementById('edit_teacher_id').value = teacher;
                    });
                });
            });
            </script>

            <?php
            // Include footer
            include '../includes/footer.php';
            ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>