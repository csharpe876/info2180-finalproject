<?php
require_once 'config.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle requests from JavaScript to load contacts
if (isset($_GET['action']) && $_GET['action'] === 'get_contacts') {
    header('Content-Type: application/json');
    
    // Get the filter type (all, Sales Lead, Support, or assigned to me)
    $filter_type = $_GET['filter'] ?? 'all';
    $current_user_id = $_SESSION['user_id'];
    
    // Start building the database query
    $sql = "SELECT c.*, CONCAT(u.firstname, ' ', u.lastname) as assigned_name
            FROM Contacts c
            LEFT JOIN Users u ON c.assigned_to = u.id";
    
    $params = [];
    
    // Add filter conditions based on what the user selected
    if ($filter_type === 'Sales Lead' || $filter_type === 'Support') {
        $sql .= " WHERE c.type = ?";
        $params[] = $filter_type;
    } elseif ($filter_type === 'assigned') {
        $sql .= " WHERE c.assigned_to = ?";
        $params[] = $current_user_id;
    }
    
    // Show newest contacts first
    $sql .= " ORDER BY c.created_at DESC";
    
    // Run the query and get results
    $query = $conn->prepare($sql);
    $query->execute($params);
    $contacts = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Send the contacts back as JSON
    echo json_encode($contacts);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Dolphin CRM</title>
    <link rel="stylesheet" href="includes/stylesheets/styling.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🐬 Dolphin CRM</h1>
            <nav>
                <a href="dashboard.php" class="active">Home</a>
                <a href="new_contact.php">New Contact</a>
                <a href="users.php">Users</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="dashboard-header">
            <h2>Dashboard</h2>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All</button>
                <button class="filter-btn" data-filter="Sales Lead">Sales Leads</button>
                <button class="filter-btn" data-filter="Support">Support</button>
                <button class="filter-btn" data-filter="assigned">Assigned to me</button>
            </div>
        </div>

        <table id="contacts-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Company</th>
                    <th>Type</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="contacts-body">
                <tr><td colspan="5">Loading...</td></tr>
            </tbody>
        </table>
    </main>

    <script src="includes/javascript/jscript.js"></script>
</body>
</html>