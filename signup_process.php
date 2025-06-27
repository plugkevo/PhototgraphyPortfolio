<?php
// signup_process.php

// session_start() and the check for already logged-in users are now handled by session_manager.php
// which is included at the very top of signup.php.

require __DIR__ . '/vendor/autoload.php';

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\EmailExists;
use Kreait\Firebase\Exception\Auth\WeakPassword;
use Kreait\Firebase\Exception\Auth\InvalidEmail;

$error_message = '';
$success_message = '';

// Initialize Firebase Admin SDK
try {
    $factory = (new Factory)->withServiceAccount(__DIR__ . '/firebase_credentials.json');
    $auth = $factory->createAuth();
} catch (\Exception $e) {
    error_log('Firebase initialization error in signup_process.php: ' . $e->getMessage());
    $error_message = 'Failed to initialize system. Please try again later.';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $agreeTerms = isset($_POST['agreeTerms']);
    $newsletter = isset($_POST['newsletter']);

    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $error_message = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Invalid email address format.';
    } elseif ($password !== $confirmPassword) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif (!$agreeTerms) {
        $error_message = 'You must agree to the Terms of Service and Privacy Policy.';
    } else {
        try {
            if (!isset($auth)) {
                throw new \Exception("Firebase Auth not initialized.");
            }

            $userProperties = [
                'email' => $email,
                'emailVerified' => false,
                'password' => $password,
                'displayName' => $fullName,
                'disabled' => false,
            ];

            $userRecord = $auth->createUser($userProperties);
            
            $success_message = 'Account created successfully for ' . htmlspecialchars($userRecord->email) . '!';
            
            // OPTIONAL: Auto-login user after successful signup
            // If you uncomment this, the user will be logged in immediately after signing up
            // and the session_manager.php will handle setting last_activity time.
            /*
            $_SESSION['user_uid'] = $userRecord->uid;
            $_SESSION['user_email'] = $userRecord->email;
            $_SESSION['last_activity'] = time(); // Set last activity time for auto-login
            header('Location: index.html');
            exit();
            */

        } catch (EmailExists $e) {
            $error_message = 'The email address is already registered. Please try logging in or use a different email.';
        } catch (WeakPassword $e) {
            $error_message = 'The password is too weak. ' . $e->getMessage();
        } catch (InvalidEmail $e) {
            $error_message = 'The email address is not valid.';
        } catch (\Exception $e) {
            error_log('Firebase Auth Error in signup_process.php: ' . $e->getMessage());
            $error_message = 'An unexpected error occurred. Please try again later. (' . $e->getMessage() . ')';
        }
    }
}
?>