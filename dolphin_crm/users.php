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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New User</title>
    <link rel="stylesheet" href="includes/stylesheets/users_style.css">
    <script src="script.js"></script>
</head>
<body>
    <header>
        <p>Dolphin CRM</p>
        <img src="includes/icons/dolphin.png" alt="Dolphin Logo" />
    </header>
    <div class="container">        
        <div class="main">
            <h1>
                Users
                <a href="front/New User/new_user.html" id="newUserBtn">+ Add User</a>
            </h1>
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
                    <tbody id="userTableBody">
                        <tr>
                            <td>Jane Doe</td>
                            <td>something@email.com</td>
                            <td>Admin</td>  
                            <td>01/01/2024</td>
                        </tr>   
                    </tbody>  
                </table>
            </div>
        </div>
        <div class="aside">
            <nav>
                <ul>
                    <li><a href="dashboard.php"><img src="includes/icons/home.jpg" alt="Home" class="nav-icon">Home</a></li>
                    <li><a href="new_contact.php"><img src="includes/icons/user.jpg" alt="New Contact" class="nav-icon">New Contact</a></li>
                    <li><a href="users.php"><img src="includes/icons/users.jpg" alt="Users" class="nav-icon">Users</a></li>
                </ul>
            </nav>        
            <div class="logout">
                <a href="logout.php"><img src="includes/icons/logout.jpg" alt="Logout" class="nav-icon">Logout</a>
            </div>  