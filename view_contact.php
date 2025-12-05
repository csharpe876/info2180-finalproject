<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$contact_id = $_GET['id'] ?? 0;

// Handle AJAX actions
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'add_note') {
        $comment = $_POST['comment'];
        $stmt = $conn->prepare("INSERT INTO Notes (contact_id, comment, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$contact_id, $comment, $_SESSION['user_id']]);
        echo json_encode(['success' => true]);
        exit();
    }
    
    if ($_POST['action'] === 'assign_to_me') {
        $stmt = $conn->prepare("UPDATE Contacts SET assigned_to = ? WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $contact_id]);
        echo json_encode(['success' => true]);
        exit();
    }
    
    if ($_POST['action'] === 'switch_type') {
        $stmt = $conn->prepare("SELECT type FROM Contacts WHERE id = ?");
        $stmt->execute([$contact_id]);
        $current = $stmt->fetchColumn();
        $new_type = ($current === 'Sales Lead') ? 'Support' : 'Sales Lead';
        
        $stmt = $conn->prepare("UPDATE Contacts SET type = ? WHERE id = ?");
        $stmt->execute([$new_type, $contact_id]);
        echo json_encode(['success' => true, 'new_type' => $new_type]);
        exit();
    }
}

// Get contact details
$stmt = $conn->prepare("SELECT c.*, CONCAT(u.firstname, ' ', u.lastname) as assigned_name, CONCAT(creator.firstname, ' ', creator.lastname) as created_by_name FROM Contacts c LEFT JOIN Users u ON c.assigned_to = u.id LEFT JOIN Users creator ON c.created_by = creator.id WHERE c.id = ?");
$stmt->execute([$contact_id]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    header('Location: dashboard.php');
    exit();
}

// Get notes
$stmt = $conn->prepare("SELECT n.*, CONCAT(u.firstname, ' ', u.lastname) as created_by_name FROM Notes n JOIN Users u ON n.created_by = u.id WHERE n.contact_id = ? ORDER BY n.created_at DESC");
$stmt->execute([$contact_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($contact['firstname'] . ' ' . $contact['lastname']); ?> - Dolphin CRM</title>
    <link rel="stylesheet" href="includes/stylesheets/styling.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>🐬 Dolphin CRM</h1>
            <nav>
                <a href="dashboard.php">Home</a>
                <a href="new_contact.php">New Contact</a>
                <a href="users.php">Users</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="contact-details">
            <div class="details-header">
                <div>
                    <h2 id="contact-name"><?php echo htmlspecialchars(($contact['title'] ? $contact['title'] . ' ' : '') . $contact['firstname'] . ' ' . $contact['lastname']); ?></h2>
                    <p><?php echo htmlspecialchars($contact['email'] ?? ''); ?></p>
                </div>
                <div class="action-buttons">
                    <button onclick="assignToMe()" class="btn btn-secondary">Assign to me</button>
                    <button onclick="switchType()" class="btn btn-secondary" id="switch-btn">Switch to <span id="switch-type"><?php echo $contact['type'] === 'Sales Lead' ? 'Support' : 'Sales Lead'; ?></span></button>
                </div>
            </div>

            <div class="details-grid">
                <div class="info-section">
                    <h3>Contact Information</h3>
                    <table class="info-table">
                        <tr>
                            <td>Email</td>
                            <td><?php echo htmlspecialchars($contact['email'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Telephone</td>
                            <td><?php echo htmlspecialchars($contact['telephone'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Company</td>
                            <td><?php echo htmlspecialchars($contact['company'] ?? 'N/A'); ?></td>
                        </tr>
                        <tr>
                            <td>Type</td>
                            <td><span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $contact['type'])); ?>" id="type-badge"><?php echo htmlspecialchars($contact['type']); ?></span></td>
                        </tr>
                        <tr>
                            <td>Assigned To</td>
                            <td id="assigned-name"><?php echo htmlspecialchars($contact['assigned_name'] ?? 'Unassigned'); ?></td>
                        </tr>
                        <tr>
                            <td>Created by</td>
                            <td><?php echo htmlspecialchars($contact['created_by_name']); ?></td>
                        </tr>
                        <tr>
                            <td>Created</td>
                            <td><?php echo date('F j, Y \a\t g:ia', strtotime($contact['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <td>Updated</td>
                            <td><?php echo date('F j, Y \a\t g:ia', strtotime($contact['updated_at'])); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="notes-section">
                    <h3>Notes</h3>
                    <div id="notes-list">
                        <?php if (empty($notes)): ?>
                            <p class="no-notes">No notes yet.</p>
                        <?php else: ?>
                            <?php foreach ($notes as $note): ?>
                                <div class="note">
                                    <div class="note-header">
                                        <strong><?php echo htmlspecialchars($note['created_by_name']); ?></strong>
                                        <span><?php echo date('F j, Y \a\t g:ia', strtotime($note['created_at'])); ?></span>
                                    </div>
                                    <p><?php echo nl2br(htmlspecialchars($note['comment'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <form id="note-form" class="note-form">
                        <label>Add a note about <?php echo htmlspecialchars($contact['firstname']); ?></label>
                        <textarea name="comment" id="comment" rows="4" required></textarea>
                        <button type="submit" class="btn btn-primary">Add Note</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script>
        const contactId = <?php echo $contact_id; ?>;

        document.getElementById('note-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const comment = document.getElementById('comment').value;
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'view_contact.php?id=' + contactId, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    location.reload();
                }
            };
            
            xhr.send('action=add_note&comment=' + encodeURIComponent(comment));
        });

        function assignToMe() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'view_contact.php?id=' + contactId, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    location.reload();
                }
            };
            
            xhr.send('action=assign_to_me');
        }

        function switchType() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'view_contact.php?id=' + contactId, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    location.reload();
                }
            };
            
            xhr.send('action=switch_type');
        }
    </script>
</body>
</html>