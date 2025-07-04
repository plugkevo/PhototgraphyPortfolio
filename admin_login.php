<?php
session_start();

// Simple admin credentials (in production, use database with hashed passwords)
$admin_username = "admin";
$admin_password = "hisia2024"; // Change this to a secure password

$error_message = "";

if ($_POST) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $error_message = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - Hisia Pixels</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #FF4500; /* Orangered */
            --dark-color: #1a1a1a;
            --light-gray: #cccccc;
            --white: #ffffff;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--dark-color) 0%, #2d2d2d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
        }

        .login-container {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 450px;
            width: 100%;
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo h1 {
            color: var(--primary-color);
            font-family: 'Playfair Display', serif;
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .brand-logo p {
            color: var(--light-gray);
            font-size: 1.1em;
            margin: 0;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: var(--white);
            font-size: 1.8em;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .login-header .admin-icon {
            font-size: 3em;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            color: var(--light-gray);
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: var(--white);
            padding: 12px 15px;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(255, 69, 0, 0.25);
            color: var(--white);
        }

        .form-control::placeholder {
            color: rgba(204, 204, 204, 0.7);
        }

        .input-group {
            position: relative;
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--light-gray);
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
        }

        .btn-login {
            background: linear-gradient(45deg, var(--primary-color), #ff6b35);
            border: none;
            border-radius: 8px;
            color: var(--white);
            font-weight: 600;
            font-size: 1.1em;
            padding: 12px;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-login:hover {
            background: linear-gradient(45deg, #e63e00, var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 69, 0, 0.3);
            color: var(--white);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }

        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: var(--light-gray);
            text-decoration: none;
            font-size: 0.9em;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: var(--primary-color);
        }

        .security-note {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            text-align: center;
        }

        .security-note i {
            color: #ffc107;
            margin-right: 8px;
        }

        .security-note small {
            color: var(--light-gray);
            font-size: 0.85em;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .login-container {
                margin: 20px;
                padding: 30px 25px;
            }
            
            .brand-logo h1 {
                font-size: 2em;
            }
            
            .login-header h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand-logo">
            <h1>Hisia Pixels</h1>
            <p>Photography Admin Portal</p>
        </div>
        
        <div class="login-header">
            <i class="fas fa-user-shield admin-icon"></i>
            <h2>Admin Login</h2>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="fas fa-user"></i> Username
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" 
                           class="form-control" 
                           id="username" 
                           name="username" 
                           placeholder="Enter your username"
                           required
                           autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Enter your password"
                           required
                           autocomplete="current-password">
                </div>
            </div>

            <button type="submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Login to Dashboard
            </button>
        </form>

        <div class="security-note">
            <i class="fas fa-shield-alt"></i>
            <small>This is a secure admin area. All login attempts are monitored.</small>
        </div>

        <div class="back-link">
            <a href="index.html">
                <i class="fas fa-arrow-left"></i> Back to Website
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
