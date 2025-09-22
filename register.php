<?php
// Start session
session_start();

// Include configuration
require_once 'config/main.php';
require_once 'config/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

// Check if registration is enabled
if (!ENABLE_REGISTRATION) {
    die("Registration is currently disabled.");
}

// Handle registration form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $termsAccepted = isset($_POST['terms']);

    // Validation
    $errors = [];

    if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        $errors[] = 'All fields are required';
    }

    if (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }

    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    if (!$termsAccepted) {
        $errors[] = 'You must accept the terms and conditions';
    }

    // Check password strength
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
    }

    if (empty($errors)) {
        $result = $auth->register($username, $email, $password, $fullName);

        if ($result['success']) {
            $message = $result['message'];
            $messageType = 'success';

            // Redirect to login after successful registration
            header('refresh:3;url=login.php');
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .register-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }

        .register-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .register-header h2 {
            margin: 0;
            font-size: 1.8rem;
        }

        .register-body {
            padding: 2rem;
        }

        .form-control {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25);
        }

        .btn-register {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            width: 100%;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .register-footer {
            background: #f8f9fa;
            padding: 1rem 2rem;
            text-align: center;
        }

        .register-footer a {
            color: #10b981;
            text-decoration: none;
        }

        .register-footer a:hover {
            color: #059669;
            text-decoration: underline;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-right: none;
        }

        .form-control.with-icon {
            border-left: none;
        }

        .password-strength {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .password-strength.weak { color: #dc3545; }
        .password-strength.medium { color: #ffc107; }
        .password-strength.strong { color: #28a745; }

        .terms-link {
            color: #10b981;
            text-decoration: none;
        }

        .terms-link:hover {
            color: #059669;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="register-container">
                    <div class="register-header">
                        <i class="fas fa-user-plus fa-3x mb-3"></i>
                        <h2>Create Account</h2>
                        <p class="mb-0">Join us today</p>
                    </div>

                    <div class="register-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" id="registerForm">
                            <div class="mb-3">
                                <label for="full_name" class="form-label">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="full_name" name="full_name"
                                           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                                           placeholder="Enter your full name">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-at"></i></span>
                                    <input type="text" class="form-control" id="username" name="username"
                                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                           required placeholder="Choose a username">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                           required placeholder="Enter your email">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                           required placeholder="Create a password">
                                    <span class="input-group-text password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye" id="passwordIcon"></i>
                                    </span>
                                </div>
                                <div class="password-strength" id="passwordStrength"></div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                           required placeholder="Confirm your password">
                                    <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye" id="confirmPasswordIcon"></i>
                                    </span>
                                </div>
                                <div class="password-match" id="passwordMatch"></div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="terms-link" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and Conditions</a>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-register text-white">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </form>
                    </div>

                    <div class="register-footer">
                        <p class="mb-0">
                            Already have an account?
                            <a href="login.php">Sign in here</a>
                        </p>
                        <p class="mb-0 mt-2">
                            <a href="index.php">← Back to Home</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6>1. Acceptance of Terms</h6>
                    <p>By registering for an account, you agree to these terms and conditions...</p>

                    <h6>2. User Responsibilities</h6>
                    <p>You are responsible for maintaining the confidentiality of your account...</p>

                    <h6>3. Privacy Policy</h6>
                    <p>Your privacy is important to us. Please review our privacy policy...</p>

                    <h6>4. Account Termination</h6>
                    <p>We reserve the right to terminate accounts that violate these terms...</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + 'Icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            let strength = 0;
            let feedback = '';

            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^A-Za-z0-9]/)) strength++;

            switch (strength) {
                case 0:
                case 1:
                    feedback = '<i class="fas fa-times"></i> Very Weak';
                    strengthDiv.className = 'password-strength weak';
                    break;
                case 2:
                    feedback = '<i class="fas fa-exclamation-triangle"></i> Weak';
                    strengthDiv.className = 'password-strength weak';
                    break;
                case 3:
                    feedback = '<i class="fas fa-exclamation-circle"></i> Medium';
                    strengthDiv.className = 'password-strength medium';
                    break;
                case 4:
                    feedback = '<i class="fas fa-check-circle"></i> Strong';
                    strengthDiv.className = 'password-strength strong';
                    break;
                case 5:
                    feedback = '<i class="fas fa-shield-alt"></i> Very Strong';
                    strengthDiv.className = 'password-strength strong';
                    break;
            }

            strengthDiv.innerHTML = feedback;
        });

        // Password confirmation checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const matchDiv = document.getElementById('passwordMatch');

            if (confirmPassword === '') {
                matchDiv.innerHTML = '';
                return;
            }

            if (password === confirmPassword) {
                matchDiv.innerHTML = '<i class="fas fa-check text-success"></i> Passwords match';
                matchDiv.className = 'text-success';
            } else {
                matchDiv.innerHTML = '<i class="fas fa-times text-danger"></i> Passwords do not match';
                matchDiv.className = 'text-danger';
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match');
                return false;
            }

            if (!terms) {
                e.preventDefault();
                alert('Please accept the terms and conditions');
                return false;
            }

            if (password.length < <?php echo PASSWORD_MIN_LENGTH; ?>) {
                e.preventDefault();
                alert('Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters long');
                return false;
            }
        });

        // Auto-focus on first field
        document.getElementById('full_name').focus();
    </script>
</body>
</html>
