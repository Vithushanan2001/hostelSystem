<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Hostel Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg,rgb(227, 237, 239) 0%, #000DFF 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #4e54c8;
        }
        .btn-custom {
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            margin: 0 10px;
        }
        .btn-login {
            background: white;
            color: #4e54c8;
            border: 2px solid white;
        }
        .btn-signup {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        .btn-login:hover {
            background: #f8f9fa;
        }
        .btn-signup:hover {
            background: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">HostelPro</a>
            <div class="ms-auto d-flex">
                <a href="login.php" class="btn btn-login btn-custom me-2">Login</a>
                <a href="register.php" class="btn btn-signup btn-custom">Sign Up</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-4">Welcome to University Hostel Management System</h1>
            <p class="lead mb-5">Streamlining hostel operations for better student living experience</p>
            <div class="d-flex justify-content-center">
                <a href="login.php" class="btn btn-light btn-lg px-4 me-3">Get Started</a>
                <a href="#features" class="btn btn-outline-light btn-lg px-4">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container py-5">
            <h2 class="text-center mb-5">Key Features</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="text-center">
                            <div class="feature-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <h3>Student Management</h3>
                            <p>Easily manage student records, room allocations, and personal details in one centralized system.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="text-center">
                            <div class="feature-icon">
                                <i class="bi bi-building"></i>
                            </div>
                            <h3>Room Allocation</h3>
                            <p>Automated and manual room allocation with real-time availability tracking and conflict resolution.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="text-center">
                            <div class="feature-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <h3>Maintenance Requests</h3>
                            <p>Streamlined process for submitting and tracking maintenance requests with priority levels.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="text-center">
                            <div class="feature-icon">
                                <i class="bi bi-credit-card"></i>
                            </div>
                            <h3>Fee Management</h3>
                            <p>Automated fee calculations, payment tracking, and receipt generation for hostel fees.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="text-center">
                            <div class="feature-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h3>Security</h3>
                            <p>Secure access control with role-based permissions and activity logging for enhanced security.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="text-center">
                            <div class="feature-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <h3>Reports & Analytics</h3>
                            <p>Comprehensive reports and analytics for better decision making and resource management.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-light py-5">
        <div class="container text-center py-4">
            <h2 class="mb-4">Ready to get started?</h2>
            <p class="lead mb-4">Join hundreds of students and administrators using our platform</p>
            <a href="register.php" class="btn btn-primary btn-lg px-5">Sign Up Now</a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> University Hostel Management System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</body>
</html>