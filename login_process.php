<?php
// login_process.php

// session_start() is now handled by session_manager.php, which is included in login.php

require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\UserNotFound;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\InvalidEmail;

$error_message = '';
$success_message = '';

// Path to your service account key file
$serviceAccountPath = __DIR__ . '/firebase_credentials.json';

try {
    $factory = (new Factory)->withServiceAccount($serviceAccountPath);
    $auth = $factory->createAuth();
} catch (\Exception $e) {
    error_log('Firebase initialization error: ' . $e->getMessage());
    $error_message = 'Failed to initialize Firebase. Please try again later.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login_submit'])) { // Handle login form submission
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error_message = 'Both email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Invalid email address format.';
        } else {
            try {
                $signInResult = $auth->signInWithEmailAndPassword($email, $password);
                
                $idToken = $signInResult->idToken();
                $uid = $signInResult->firebaseUserId();

                // Store user session data
                $_SESSION['user_uid'] = $uid;
                $_SESSION['user_email'] = $email;
                $_SESSION['id_token'] = $idToken;
                $_SESSION['last_activity'] = time(); // Set last activity time on successful login

                // Redirect to index.html after successful login
                header('Location: index.php');
                exit();

            } catch (UserNotFound $e) {
                $error_message = 'No account found with this email.';
            } catch (InvalidPassword $e) {
                $error_message = 'Incorrect password.';
            } catch (InvalidEmail $e) {
                $error_message = 'Invalid email format or user not found.';
            } catch (\Exception $e) {
                error_log('Login error: ' . $e->getMessage());
                $error_message = 'An unexpected error occurred during login. Please try again.';
            }
        }
    } elseif (isset($_POST['reset_password'])) { // Handle password reset form submission
        $reset_email = filter_input(INPUT_POST, 'reset_email', FILTER_SANITIZE_EMAIL);

        if (empty($reset_email) || !filter_var($reset_email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Please enter a valid email address for password reset.';
        } else {
            try {
                $auth->sendPasswordResetLink($reset_email);
                $success_message = 'Password reset link sent to ' . htmlspecialchars($reset_email) . '. Please check your inbox.';
            } catch (UserNotFound $e) {
                $error_message = 'No account found with this email address for password reset.';
            } catch (\Exception $e) {
                error_log('Password reset error: ' . $e->getMessage());
                $error_message = 'Failed to send password reset link. Please try again.';
            }
        }
    }
}
// Note: The pre-login redirect logic (if user is already logged in)
// is now handled by session_manager.php and its check at the very top.
?>