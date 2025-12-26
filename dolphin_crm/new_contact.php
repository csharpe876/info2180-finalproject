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
    $contact_firstname = $_POST['firstname'] ?? '';
    $contact_lastname = $_POST['lastname'] ?? '';
    $contact_email = $_POST['email'] ?? '';
    $contact_phone = $_POST['telephone'] ?? '';
    $contact_company = $_POST['company'] ?? '';
    $contact_type = $_POST['type'] ?? '';
    $assigned_to_user = $_POST['assigned_to'] ?? null;
    
    // Save the contact to the database
    $query = $conn->prepare("INSERT INTO Contacts (title, firstname, lastname, email, telephone, company, type, assigned_to, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $query->execute([$contact_title, $contact_firstname, $contact_lastname, $contact_email, $contact_phone, $contact_company, $contact_type, $assigned_to_user, $_SESSION['user_id']]);
    
    // Redirect to dashboard after successful save
    header('Location: dashboard.php');
    exit();
}

// Load all users for the dropdown
$query = $conn->query("SELECT id, firstname, lastname FROM Users ORDER BY firstname, lastname");
$all_users = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact - Dolphin CRM</title>
    <link rel="stylesheet" href="includes/stylesheets/new_contact_style.css">
</head>
<body>
    <header>
        <a href="dashboard.php" style="text-decoration:none; color:inherit; display:flex; align-items:center; gap:10px;">
            <img src="includes/icons/dolphin.png" alt="Dolphin Logo" style="height:30px;" />
            <span style="font-weight:bold; font-size:18px;">Dolphin CRM</span>
        </a>
    </header>
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="main">
            <h1>New Contact</h1>
            <div class="form-container">
                <form id="newContactForm" method="POST" action="new_contact.php">
                    <div class="form-field full-width">
                        <label for="title">Title</label>
                        <select id="title" name="title" required>
                            <option value="">Select Title</option>
                            <option value="Mr.">Mr.</option>
                            <option value="Mrs.">Mrs.</option>
                            <option value="Ms.">Ms.</option>
                            <option value="Dr.">Dr.</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="firstname">First Name</label>
                        <input type="text" placeholder="Jane" id="firstname" name="firstname" required />
                    </div>

                    <div class="form-field">
                        <label for="lastname">Last Name</label>
                        <input type="text" placeholder="Doe" id="lastname" name="lastname" required />
                    </div>

                    <div class="form-field">
                        <label for="email">Email</label>
                        <input type="email" placeholder="something@example.com" id="email" name="email" required />
                    </div>

                    <div class="form-field">
                        <label for="telephone">Telephone</label>
                        <input type="tel" placeholder="(876)555-1234" id="telephone" name="telephone" pattern="^\(\d{3}\)\d{3}-\d{4}$" required />
                        <small style="color:#888;">Format: (xxx)xxx-xxxx</small>
                    </div>

                    <div class="form-field">
                        <label for="company">Company</label>
                        <input type="text" placeholder="Company Name" id="company" name="company" required />
                    </div>

                    <div class="form-field">
                        <label for="type">Type</label>
                        <select id="type" name="type" required>
                            <option value="">Select Type</option>
                            <option value="Sales Lead">Sales Lead</option>
                            <option value="Support">Support</option>
                        </select>
                    </div>

                    <div class="form-field full-width">
                        <label for="assigned_to">Assigned To</label>
                        <select id="assigned_to" name="assigned_to" required>
                            <option value="">Select User</option>
                            <?php foreach ($all_users as $user): ?>
                                <option value="<?= $user['id'] ?>">
                                    <?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-save">Save</button>
                        <button type="button" class="btn-cancel" onclick="window.location.href='dashboard.php'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>