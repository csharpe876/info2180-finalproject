<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get users for assignment dropdown
if (isset($_GET['action']) && $_GET['action'] === 'get_users') {
    header('Content-Type: application/json');
    $stmt = $conn->query("SELECT id, firstname, lastname FROM Users ORDER BY firstname, lastname");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $company = $_POST['company'] ?? '';
    $type = $_POST['type'];
    $assigned_to = $_POST['assigned_to'];
    
    $stmt = $conn->prepare("INSERT INTO Contacts (title, firstname, lastname, email, telephone, company, type, assigned_to, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $firstname, $lastname, $email, $telephone, $company, $type, $assigned_to, $_SESSION['user_id']]);
    
    $success = "Contact added successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact - Dolphin CRM</title>
    <link rel="stylesheet" href="styles.css">
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