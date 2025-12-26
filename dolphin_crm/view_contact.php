<?php
require_once 'config.php';

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get the contact ID from the URL
$contact_id = $_GET['id'] ?? 0;

// Handle AJAX actions from JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // Add a note to this contact
    if ($_POST['action'] === 'add_note') {
        $note_text = $_POST['comment'] ?? '';
        $query = $conn->prepare("INSERT INTO Notes (contact_id, comment, created_by) VALUES (?, ?, ?)");
        $query->execute([$contact_id, $note_text, $_SESSION['user_id']]);
        
        // Return the new note with user info
        $note_id = $conn->lastInsertId();
        $query = $conn->prepare("SELECT n.*, CONCAT(u.firstname, ' ', u.lastname) as created_by_name FROM Notes n JOIN Users u ON n.created_by = u.id WHERE n.id = ?");
        $query->execute([$note_id]);
        $note = $query->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode(['success' => true, 'note' => $note]);
        exit();
    }
    
    // Assign this contact to the current user
    if ($_POST['action'] === 'assign_to_me') {
        $query = $conn->prepare("UPDATE Contacts SET assigned_to = ? WHERE id = ?");
        $query->execute([$_SESSION['user_id'], $contact_id]);
        
        $assigned_name = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];
        echo json_encode(['success' => true, 'assigned_name' => $assigned_name]);
        exit();
    }
    
    // Switch between Sales Lead and Support
    if ($_POST['action'] === 'switch_type') {
        // Get the current type
        $query = $conn->prepare("SELECT type FROM Contacts WHERE id = ?");
        $query->execute([$contact_id]);
        $current_type = $query->fetchColumn();
        
        // Switch to the opposite type
        $new_type = ($current_type === 'Sales Lead') ? 'Support' : 'Sales Lead';
        
        // Update in database
        $query = $conn->prepare("UPDATE Contacts SET type = ? WHERE id = ?");
        $query->execute([$new_type, $contact_id]);
        echo json_encode(['success' => true, 'new_type' => $new_type]);
        exit();
    }
}

// Load this contact's information
$query = $conn->prepare("SELECT c.*, CONCAT(u.firstname, ' ', u.lastname) as assigned_name, CONCAT(creator.firstname, ' ', creator.lastname) as created_by_name FROM Contacts c LEFT JOIN Users u ON c.assigned_to = u.id LEFT JOIN Users creator ON c.created_by = creator.id WHERE c.id = ?");
$query->execute([$contact_id]);
$contact = $query->fetch(PDO::FETCH_ASSOC);

// If contact doesn't exist, go back to dashboard
if (!$contact) {
    header('Location: dashboard.php');
    exit();
}

// Load all notes for this contact
$query = $conn->prepare("SELECT n.*, CONCAT(u.firstname, ' ', u.lastname) as created_by_name FROM Notes n JOIN Users u ON n.created_by = u.id WHERE n.contact_id = ? ORDER BY n.created_at DESC");
$query->execute([$contact_id]);
$notes = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contact - Dolphin CRM</title>
    <link rel="stylesheet" href="includes/stylesheets/view_contact_style.css">
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
            <div class="contact-header">
                <h1><?= htmlspecialchars(($contact['title'] ? $contact['title'] . ' ' : '') . $contact['firstname'] . ' ' . $contact['lastname']) ?></h1>
                <span class="contact-type <?= strtolower(str_replace(' ', '-', $contact['type'])) ?>" id="type-badge">
                    <?= htmlspecialchars($contact['type']) ?>
                </span>
            </div>
            
            <div class="contact-body">
                <div class="contact-details">
                    <div class="detail-row">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><?= htmlspecialchars($contact['email'] ?? 'N/A') ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Telephone</div>
                        <div class="detail-value"><?= htmlspecialchars($contact['telephone'] ?? 'N/A') ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Company</div>
                        <div class="detail-value"><?= htmlspecialchars($contact['company'] ?? 'N/A') ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Assigned To</div>
                        <div class="detail-value" id="assigned-name">
                            <?= htmlspecialchars($contact['assigned_name'] ?? 'Unassigned') ?>
                        </div>
                    </div>
                    
                    <div class="assign-section">
                        <h3>Switch Contact Type</h3>
                        <button class="assign-btn" id="switch-type-btn" onclick="switchType(<?= $contact_id ?>)">
                            Switch to <?= $contact['type'] === 'Sales Lead' ? 'Support' : 'Sales Lead' ?>
                        </button>
                    </div>
                    
                    <div class="assign-section">
                        <h3>Assign to</h3>
                        <button class="assign-btn" onclick="assignToMe(<?= $contact_id ?>)">Assign to Me</button>
                    </div>
                </div>
                
                <div class="notes-section">
                    <h2>Notes</h2>
                    <button class="add-note-btn" onclick="showNoteForm()">+ Add Note</button>
                    
                    <div id="note-form" style="display:none; margin-bottom:30px;">
                        <form onsubmit="addNote(event, <?= $contact_id ?>)">
                            <textarea id="note-comment" placeholder="Enter your note here..." rows="4" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; font-family:inherit; margin-bottom:10px;"></textarea>
                            <div style="display:flex; gap:10px;">
                                <button type="submit" class="add-note-btn" style="margin:0;">Save Note</button>
                                <button type="button" class="assign-btn" onclick="hideNoteForm()" style="margin:0;">Cancel</button>
                            </div>
                        </form>
                    </div>
                    
                    <div id="notes-list">
                        <?php if (empty($notes)): ?>
                            <p style="color:#999; font-style:italic;">No notes yet.</p>
                        <?php else: ?>
                            <?php foreach ($notes as $note): ?>
                                <div class="note">
                                    <div class="note-author"><?= htmlspecialchars($note['created_by_name']) ?></div>
                                    <div class="note-text"><?= nl2br(htmlspecialchars($note['comment'])) ?></div>
                                    <div class="note-date"><?= date('F j, Y \a\t g:ia', strtotime($note['created_at'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function showNoteForm() {
            document.getElementById('note-form').style.display = 'block';
        }
        
        function hideNoteForm() {
            document.getElementById('note-form').style.display = 'none';
            document.getElementById('note-comment').value = '';
        }
        
        function addNote(event, contactId) {
            event.preventDefault();
            const comment = document.getElementById('note-comment').value;
            
            fetch('view_contact.php?id=' + contactId, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=add_note&comment=' + encodeURIComponent(comment)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
        
        function assignToMe(contactId) {
            fetch('view_contact.php?id=' + contactId, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=assign_to_me'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('assigned-name').textContent = data.assigned_name;
                    alert('Contact assigned to you successfully!');
                }
            });
        }
        
        function switchType(contactId) {
            fetch('view_contact.php?id=' + contactId, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=switch_type'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }
    </script>
</body>
</html>