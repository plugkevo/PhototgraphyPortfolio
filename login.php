<?php
    // login.php

    // Include the session manager script FIRST.
    // This handles session_start(), session lifetime, and inactivity checks.
    include 'session_manager.php'; 

    if (isset($_SESSION['user_uid']) && !empty($_SESSION['user_uid'])) {
    header('Location: index.php'); // Redirect to your home/dashboard page
    exit(); // IMPORTANT: Always exit after a header redirect
}

    // Now include your login processing logic
    include 'login_process.php'; 

    // Check for and clear session expired message
    $expired_message = '';
    if (isset($_SESSION['session_expired_message'])) {
        $expired_message = $_SESSION['session_expired_message'];
        unset($_SESSION['session_expired_message']); // Clear the message after displaying it
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Hisia Pixels</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">
    
    <style>
        /* Your CSS styles here */
        :root {
            --primary-color: #FF4500;
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
            padding: 20px 0; /* Add padding to prevent content from touching edges on small screens */
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

        .login-header .user-icon {
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

        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            color: #51cf66;
            border: 1px solid rgba(40, 167, 69, 0.3);
        }

        .form-check {
            margin: 20px 0;
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--light-gray);
            font-size: 0.9em;
        }

        .forgot-password {
            text-align: right;
            margin-top: 10px;
        }

        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9em;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #ff6b35;
            text-decoration: underline;
        }

        .signup-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .signup-link p {
            color: var(--light-gray);
            margin-bottom: 10px;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #ff6b35;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
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
            <p>Welcome Back</p>
        </div>
        
        <div class="login-header">
            <i class="fas fa-sign-in-alt user-icon"></i>
            <h2>Sign In</h2>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action=""> <div class="form-group">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="Enter your email address"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required
                           autocomplete="email">
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
                <div class="forgot-password">
                    <a href="#" onclick="showPasswordReset()">Forgot your password?</a>
                </div>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe" <?php echo isset($_POST['rememberMe']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="rememberMe">
                    Remember me on this device
                </label>
            </div>

            <button type="submit" name="login_submit" class="btn btn-login">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <div class="signup-link">
            <p>Don't have an account?</p>
            <a href="signup.php">Create Account Here</a>
        </div>

        <div class="back-link">
            <a href="index.html">
                <i class="fas fa-arrow-left"></i> Back to Website
            </a>
        </div>
    </div>

    <div class="modal fade" id="passwordResetModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background: rgba(0, 0, 0, 0.9); color: white;">
                <div class="modal-header">
                    <h5 class="modal-title" style="color: var(--primary-color);">Reset Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action=""> <div class="modal-body">
                        <div class="form-group">
                            <label for="reset_email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="reset_email" name="reset_email" required placeholder="Enter your email for reset">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="reset_password" class="btn btn-primary" style="background: linear-gradient(45deg, var(--primary-color), #ff6b35); border: none;">Send Reset Link</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Function to show the password reset modal
        function showPasswordReset() {
            const modal = new bootstrap.Modal(document.getElementById('passwordResetModal'));
            modal.show();
        }

        // Check if there's an error/success message from PHP, then display the modal if needed
        // This is for scenarios where the password reset form was submitted via PHP
        document.addEventListener('DOMContentLoaded', function() {
            const errorMessage = "<?php echo addslashes($error_message); ?>";
            const successMessage = "<?php echo addslashes($success_message); ?>";

            if (errorMessage && errorMessage.includes('password reset')) {
                // If the error message is related to password reset, show the modal
                showPasswordReset();
            } else if (successMessage && successMessage.includes('Password reset link sent')) {
                // If the success message is for password reset, show the modal
                showPasswordReset();
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>