<?php
require_once 'config.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Check if current user is an admin (only admins can add users)
$is_admin = $_SESSION['role'] === 'Admin';

$showForm = isset($_GET['addUser']);

// Create new user when form is submitted (admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_admin) {
    // Get form data
    $new_firstname = $_POST['firstname'] ?? '';
    $new_lastname = $_POST['lastname'] ?? '';
    $new_email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $new_password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $new_role = $_POST['role'] ?? 'Member';
    
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
    <title>Dolphin CRM - Users</title>

    <link rel="stylesheet" href="includes/stylesheets/users_style.css">
    <link rel="stylesheet" href="includes/stylesheets/new_user_style.css">
</head>
<body>

<header>
    <a href="dashboard.php" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:10px;">
        <img src="includes/icons/dolphin.png" alt="Dolphin Logo" />
        <span style="font-weight:bold; font-size:16px;">Dolphin CRM</span>
    </a>
</header>

<div class="container">

    <div class="main">
        <h1>
            Users
            <?php if ($is_admin): ?>
                <button id="showAddUserForm" id="newUserBtn">+ Add User</button>
            <?php endif; ?>
        </h1>

        <?php if (isset($success)): ?>
            <p class="success-message">
                <?= htmlspecialchars($success) ?>
            </p>
        <?php endif; ?>

        <?php if ($is_admin): ?>
        <!-- Hidden Add User form (styled by new_user_style.css) -->
        <div id="add-user-form" class="form-container" style="display:none;">
            <h2>New User</h2>
            <form method="POST">
                <div class="form-field" id="firstName">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" required>
                </div>

                <div class="form-field" id="lastName">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>

                <div class="form-field" id="email">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-field" id="password">
                    <label for="password">Password</label>
                    
                    <div class="password-wrapper">
                        <input type="password" id="newUserPassword" name="password" required>
                        <i id="toggleNewUserPassword" class="eye">👁</i>
                    </div>
                </div>

                <div class="form-field" id="role">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="Member">Member</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit">Save</button>
                    <button type="button" id="cancelAddUser">Cancel</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- USERS TABLE -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <?= date('Y/m/d H:i', strtotime($user['created_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

    <?php include 'includes/sidebar.php'; ?>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addForm     = document.getElementById('add-user-form');
    const showBtn     = document.getElementById('showAddUserForm');
    const cancelBtn   = document.getElementById('cancelAddUser');
    const pwdField    = document.getElementById('newUserPassword');
    const togglePwd   = document.getElementById('toggleNewUserPassword');

    if (showBtn && addForm) {
        showBtn.addEventListener('click', () => {
            addForm.style.display = 'block';
        });
    }

    if (cancelBtn && addForm) {
        cancelBtn.addEventListener('click', () => {
            addForm.style.display = 'none';
        });
    }

    if (pwdField && togglePwd) {
        togglePwd.addEventListener('click', () => {
            const type = pwdField.getAttribute('type') === 'password' ? 'text' : 'password';
            pwdField.setAttribute('type', type);
        });
    }
});
</script>

</body>
</html>
