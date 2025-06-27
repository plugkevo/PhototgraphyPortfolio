<?php
// signup.php

// Include the session manager script FIRST.
// This handles session_start(), session lifetime, and inactivity checks.
include 'session_manager.php'; 

if (isset($_SESSION['user_uid']) && !empty($_SESSION['user_uid'])) {
    header('Location: index.php'); // Redirect to your home/dashboard page
    exit(); // IMPORTANT: Always exit after a header redirect
}
// Now include your signup processing logic
include 'signup_process.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - Hisia Pixels</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Playfair+Display:ital,wght@1,700&display=swap" rel="stylesheet">
    
    <style>
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
            padding: 20px 0;
        }

        .signup-container {
            background: rgba(0, 0, 0, 0.8);
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            max-width: 500px;
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

        .signup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .signup-header h2 {
            color: var(--white);
            font-size: 1.8em;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .signup-header .user-icon {
            font-size: 3em;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
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

        .password-strength {
            margin-top: 5px;
            font-size: 0.85em;
        }

        .strength-weak { color: #ff6b6b; }
        .strength-medium { color: #ffd93d; }
        .strength-strong { color: #51cf66; }

        .btn-signup {
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
            margin-top: 10px;
        }

        .btn-signup:hover {
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

        .form-check-label a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .form-check-label a:hover {
            text-decoration: underline;
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-link p {
            color: var(--light-gray);
            margin-bottom: 10px;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
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
            .signup-container {
                margin: 20px;
                padding: 30px 25px;
            }
            
            .brand-logo h1 {
                font-size: 2em;
            }
            
            .signup-header h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    
    <div class="signup-container">
        <div class="brand-logo">
            <h1>Hisia Pixels</h1>
            <p>Join Our Photography Community</p>
        </div>
        
        <div class="signup-header">
            <i class="fas fa-user-plus user-icon"></i>
            <h2>Create Account</h2>
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

        <form method="POST" action="" id="signupForm"> <div class="form-group">
                <label for="fullName" class="form-label">
                    <i class="fas fa-user"></i> Full Name
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" 
                           class="form-control" 
                           id="fullName" 
                           name="fullName" 
                           placeholder="Enter your full name"
                           value="<?php echo isset($_POST['fullName']) ? htmlspecialchars($_POST['fullName']) : ''; ?>"
                           required
                           autocomplete="name">
                </div>
            </div>

            <div class="form-group">
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
                           placeholder="Create a strong password"
                           required
                           autocomplete="new-password">
                </div>
                <div id="password-strength" class="password-strength"></div>
            </div>

            <div class="form-group">
                <label for="confirmPassword" class="form-label">
                    <i class="fas fa-lock"></i> Confirm Password
                </label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" 
                           class="form-control" 
                           id="confirmPassword" 
                           name="confirmPassword" 
                           placeholder="Confirm your password"
                           required
                           autocomplete="new-password">
                </div>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="agreeTerms" name="agreeTerms" <?php echo isset($_POST['agreeTerms']) ? 'checked' : ''; ?> required>
                <label class="form-check-label" for="agreeTerms">
                    I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                </label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter" <?php echo isset($_POST['newsletter']) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="newsletter">
                    Subscribe to our newsletter for photography tips and updates
                </label>
            </div>

            <button type="submit" class="btn btn-signup">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="login-link">
            <p>Already have an account?</p>
            <a href="login.php">Sign In Here</a>
        </div>

        <div class="back-link">
            <a href="index.html">
                <i class="fas fa-arrow-left"></i> Back to Website
            </a>
        </div>
    </div>

    <script>
        // Password strength checker (client-side remains for UX)
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('password-strength');
            const strength = calculatePasswordStrength(password);
            
            strengthDiv.innerHTML = '';
            
            if (password.length > 0) {
                const strengthText = document.createElement('span');
                
                if (strength < 3) {
                    strengthText.className = 'strength-weak';
                    strengthText.textContent = 'Weak password';
                } else if (strength < 5) {
                    strengthText.className = 'strength-medium';
                    strengthText.textContent = 'Medium strength';
                } else {
                    strengthText.className = 'strength-strong';
                    strengthText.textContent = 'Strong password';
                }
                
                strengthDiv.appendChild(strengthText);
            }
        });

        function calculatePasswordStrength(password) {
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            return strength;
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>