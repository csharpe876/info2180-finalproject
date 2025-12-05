<?php
require_once 'config.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Check if current user is an admin (only admins can add users)
$is_admin = $_SESSION['role'] === 'Admin';

// Create new user when form is submitted (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    // Get form data
    $new_firstname = $_POST['firstname'];
    $new_lastname = $_POST['lastname'];
    $new_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $new_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $new_role = $_POST['role'];
    
    // Add the new user to the database
    $query = $conn->prepare("INSERT INTO Users (firstname, lastname, password, email, role) VALUES (?, ?, ?, ?, ?)");
    $query->execute([$new_firstname, $new_lastname, $new_password, $new_email, $new_role]);
    
    $success = "User added successfully!";
}

// Load all users to display in the table
$query = $conn->query("SELECT id, firstname, lastname, email, role, created_at FROM Users ORDER BY created_at DESC");
$all_users = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Dolphin CRM</title>
    <link rel="stylesheet" href="includes/stylesheets/styling.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🐬 Dolphin CRM</h1>
            <nav>
                <a href="dashboard.php">Home</a>
                <a href="new_contact.php">New Contact</a>
                <a href="users.php" class="active">Users</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="users-header">
            <h2>Users</h2>
            <?php if ($is_admin): ?>
                <button onclick="document.getElementById('add-user-form').style.display='block'" class="btn btn-primary">+ Add User</button>
            <?php endif; ?>
        </div>

        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php if ($is_admin): ?>
        <div id="add-user-form" style="display:none;" class="form-container">
            <h3>Add User</h3>
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstname" required>
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lastname" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" required>
                            <option value="Member">Member</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" onclick="document.getElementById('add-user-form').style.display='none'" class="btn btn-secondary">Cancel</button>
            </form>
        </div>
        <?php endif; ?>

        <table class="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><span class="badge badge-role"><?php echo htmlspecialchars($user['role']); ?></span></td>
                    <td><?php echo date('F j, Y', strtotime($user['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
</body>
</html>