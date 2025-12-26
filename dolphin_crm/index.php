<?php
require_once 'config.php';

// Check if user is trying to login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and clean the email from the form
    $user_email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $user_password = $_POST['password'] ?? '';
    
    // Look up the user in the database
    $query = $conn->prepare("SELECT * FROM Users WHERE email = ?");
    $query->execute([$user_email]);
    $user = $query->fetch(PDO::FETCH_ASSOC);
    
    // Check if user exists and password is correct
    if ($user && password_verify($user_password, $user['password'])) {
        // Save user info in session so they stay logged in
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['lastname'] = $user['lastname'];
        
        // Send them to the dashboard
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dolphin CRM</title>
    <link rel="stylesheet" href="includes/stylesheets/login_style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <img src="includes/icons/dolphin.png" alt="Dolphin Logo" style="height:35px;" />
            <span style="font-weight:bold; font-size:20px;">Dolphin CRM</span>
        </div>
    </header>
    
    <main>
        <div class="login-container">
            <div class="login-box">
                <h2>Login</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <strong>⚠️ <?php echo htmlspecialchars($error); ?></strong>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="index.php">
                    <div class="form-group">
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Email Address" 
                            required 
                            autofocus
                        />
                    </div>
                    
                    <div class="form-group">
                        <div class="password-wrapper">
                            <input 
                                type="password" 
                                name="password" 
                                id="password"
                                placeholder="Password" 
                                required
                            />
                            <span class="toggle-password" id="togglePassword">
                                <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-login">
                            <span class="lock-icon">🔒</span>
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <footer>
        <p>Copyright &copy; <?php echo date('Y'); ?> Dolphin CRM</p>
    </footer>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = this.querySelector('.eye-icon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.style.opacity = '0.5';
            } else {
                passwordInput.type = 'password';
                eyeIcon.style.opacity = '1';
            }
        });
    </script>
</body>
</html>