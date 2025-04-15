<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stroke System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-primary text-white" >
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="index.php">Stroke System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown text-white">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="servicesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Services
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="servicesDropdown">
                                <?php if($_SESSION['role'] == 'doctor'): ?>
                                    <li><a class="dropdown-item" href="dashboard_doctor.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="patient_list.php"><i class="fas fa-list"></i> Patient List</a></li>
                                    <li><a class="dropdown-item" href="add_patient.php"><i class="fas fa-user-plus"></i> Add Patient</a></li>
                                    <li><a class="dropdown-item" href="analyze_scan.php"><i class="fas fa-upload"></i> Analyze Scan</a></li>
                                    <li><a class="dropdown-item" href="shared_cases.php"><i class="fas fa-share-alt"></i> Shared Cases</a></li>
                                    <li><a class="dropdown-item" href="submit_complaint.php"><i class="fas fa-exclamation-triangle"></i> Submit Complaint</a></li>
                                    <li><a class="dropdown-item" href="library.php"><i class="fas fa-book"></i> Library</a></li>
                                    <li><a class="dropdown-item" href="edit_profile.php"><i class="fas fa-edit"></i> Edit profile</a></li>
                                <?php elseif($_SESSION['role'] == 'admin'): ?>
                                    <li><a class="dropdown-item" href="dashboard_admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                                    <li><a class="dropdown-item" href="add_article.php"><i class="fas fa-plus"></i> Add Article</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="register.php"><i class="fas fa-user-plus"></i> Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>