<?php
require_once 'config.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Load list of users for the "Assign to" dropdown
if (isset($_GET['action']) && $_GET['action'] === 'get_users') {
    header('Content-Type: application/json');
    $query = $conn->query("SELECT id, firstname, lastname FROM Users ORDER BY firstname, lastname");
    $all_users = $query->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($all_users);
    exit();
}

// Save the new contact when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get all the form data
    $contact_title = $_POST['title'] ?? '';
    $contact_firstname = $_POST['firstname'];
    $contact_lastname = $_POST['lastname'];
    $contact_email = $_POST['email'] ?? '';
    $contact_phone = $_POST['telephone'] ?? '';
    $contact_company = $_POST['company'] ?? '';
    $contact_type = $_POST['type'];
    $assigned_to_user = $_POST['assigned_to'];
    
    // Save the contact to the database
    $query = $conn->prepare("INSERT INTO Contacts (title, firstname, lastname, email, telephone, company, type, assigned_to, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $query->execute([$contact_title, $contact_firstname, $contact_lastname, $contact_email, $contact_phone, $contact_company, $contact_type, $assigned_to_user, $_SESSION['user_id']]);
    
    $success = "Contact added successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact - Dolphin CRM</title>
    <link rel="stylesheet" href="includes/stylesheets/styling.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🐬 Dolphin CRM</h1>
            <nav>
                <a href="dashboard.php">Home</a>
                <a href="new_contact.php" class="active">New Contact</a>
                <a href="users.php">Users</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="form-container">
            <h2>New Contact</h2>
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label>Title</label>
                        <select name="title">
                            <option value="">Select</option>
                            <option value="Mr">Mr</option>
                            <option value="Mrs">Mrs</option>
                            <option value="Ms">Ms</option>
                            <option value="Dr">Dr</option>
                        </select>
                    </div>
                </div>
                
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
                        <input type="email" name="email">
                    </div>
                    <div class="form-group">
                        <label>Telephone</label>
                        <input type="tel" name="telephone">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Company</label>
                        <input type="text" name="company">
                    </div>
                    <div class="form-group">
                        <label>Type</label>
                        <select name="type" required>
                            <option value="">Select</option>
                            <option value="Sales Lead">Sales Lead</option>
                            <option value="Support">Support</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Assigned To</label>
                        <select name="assigned_to" id="assigned_to" required>
                            <option value="">Select User</option>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">Save</button>
            </form>
        </div>
    </main>

    <script>
        // Load users for dropdown
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'new_contact.php?action=get_users', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const users = JSON.parse(xhr.responseText);
                const select = document.getElementById('assigned_to');
                users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.firstname + ' ' + user.lastname;
                    select.appendChild(option);
                });
            }
        };
        xhr.send();
    </script>
</body>
</html>