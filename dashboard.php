<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle AJAX requests for contacts
if (isset($_GET['action']) && $_GET['action'] === 'get_contacts') {
    header('Content-Type: application/json');
    
    $filter = $_GET['filter'] ?? 'all';
    $user_id = $_SESSION['user_id'];
    
    $sql = "SELECT c.*, CONCAT(u.firstname, ' ', u.lastname) as assigned_name
            FROM Contacts c
            LEFT JOIN Users u ON c.assigned_to = u.id";
    
    $params = [];
    
    if ($filter === 'Sales Lead' || $filter === 'Support') {
        $sql .= " WHERE c.type = ?";
        $params[] = $filter;
    } elseif ($filter === 'assigned') {
        $sql .= " WHERE c.assigned_to = ?";
        $params[] = $user_id;
    }
    
    $sql .= " ORDER BY c.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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

    <script>
        function loadContacts(filter = 'all') {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'dashboard.php?action=get_contacts&filter=' + filter, true);
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const contacts = JSON.parse(xhr.responseText);
                    displayContacts(contacts);
                }
            };
            
            xhr.send();
        }

        function displayContacts(contacts) {
            const tbody = document.getElementById('contacts-body');
            
            if (contacts.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="no-data">No contacts found</td></tr>';
                return;
            }
            
            tbody.innerHTML = contacts.map(contact => `
                <tr>
                    <td class="contact-name">
                        <a href="view_contact.php?id=${contact.id}">
                            ${contact.title ? contact.title + ' ' : ''}${contact.firstname} ${contact.lastname}
                        </a>
                    </td>
                    <td>${contact.email || ''}</td>
                    <td>${contact.company || ''}</td>
                    <td><span class="badge badge-${contact.type.toLowerCase().replace(' ', '-')}">${contact.type}</span></td>
                    <td><a href="view_contact.php?id=${contact.id}" class="btn-view">View</a></td>
                </tr>
            `).join('');
        }

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                loadContacts(this.dataset.filter);
            });
        });

        // Load contacts on page load
        loadContacts();
    </script>
</body>
</html>