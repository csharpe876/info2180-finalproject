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
    <link rel="stylesheet" href="includes/stylesheets/styling.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>🐬 Dolphin CRM</h1>
            <h2>Login</h2>
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Email address</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</body>
</html>