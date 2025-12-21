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
    <title>New User</title>
    <link rel="stylesheet" href="includes/stylesheets/dashboard_style.css">
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
                Dashboard
                <a href="new_contact.php" id="addContactBtn">+ Add Contact</a>
            </h1>
            <div class="table-container">
                <p>                    
                    <img src="includes/icons/filter.png" alt="Filter Icon" class="filter-icon"> 
                    Filter by:
                    <button id="allBtn">All</button>
                    <button id="adminBtn">Sales Lead</button>
                    <button id="allBtn">Support</button>
                    <button id="adminBtn">Assigned to me</button>   
                </p>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Company</th>
                            <th>Type</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <tr>
                            <td>Jane Doe</td>
                            <td>something@email.com</td>
                            <td>Admin</td>  
                            <td>01/01/2024</td>
                            <td>View</td>
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