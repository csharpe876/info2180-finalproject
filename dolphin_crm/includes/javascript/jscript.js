// Dolphin CRM - JavaScript Functions
// This file contains all the interactive functionality for the CRM application


// DASHBOARD FUNCTIONS

// Load contacts from the server based on filter
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

// Display contacts in the dashboard table
function displayContacts(contacts) {
    const tbody = document.getElementById('contacts-body');
    
    // Show message if no contacts found
    if (contacts.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="no-data">No contacts found</td></tr>';
        return;
    }
    
    // Build table rows for each contact
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

// Set up filter buttons on dashboard
function initDashboardFilters() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            // Load contacts with the selected filter
            loadContacts(this.dataset.filter);
        });
    });
}

// NEW CONTACT PAGE FUNCTIONS

// Load users for the "Assigned To" dropdown
function loadUsersForDropdown() {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'new_contact.php?action=get_users', true);
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            const users = JSON.parse(xhr.responseText);
            const select = document.getElementById('assigned_to');
            
            // Add each user as an option
            users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.firstname + ' ' + user.lastname;
                select.appendChild(option);
            });
        }
    };
    
    xhr.send();
}


// VIEW CONTACT PAGE FUNCTIONS


// Add a note to a contact
function setupNoteForm(contactId) {
    const noteForm = document.getElementById('note-form');
    if (!noteForm) return;
    
    noteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const comment = document.getElementById('comment').value;
        
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'view_contact.php?id=' + contactId, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                // Reload the page to show the new note
                location.reload();
            }
        };
        
        xhr.send('action=add_note&comment=' + encodeURIComponent(comment));
    });
}

// Assign contact to the current user
function assignToMe(contactId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'view_contact.php?id=' + contactId, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Reload to show updated assignment
            location.reload();
        }
    };
    
    xhr.send('action=assign_to_me');
}

// Switch contact type between Sales Lead and Support
function switchType(contactId) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'view_contact.php?id=' + contactId, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            // Reload to show updated type
            location.reload();
        }
    };
    
    xhr.send('action=switch_type');
}


// UTILITY FUNCTIONS


// Format date to readable format
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

// Show a temporary success or error message
function showMessage(message, type = 'success') {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message message-${type}`;
    messageDiv.textContent = message;
    
    document.body.appendChild(messageDiv);
    
    // Remove message after 3 seconds
    setTimeout(() => {
        messageDiv.remove();
    }, 3000);
}


// INITIALIZATION

// Wait for page to load before running any code
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard if on dashboard page
    if (document.getElementById('contacts-body')) {
        initDashboardFilters();
        loadContacts(); // Load all contacts by default
    }
    
    // Initialize new contact page if dropdown exists
    if (document.getElementById('assigned_to')) {
        loadUsersForDropdown();
    }
    
    // Initialize view contact page if note form exists
    const contactIdElement = document.querySelector('[data-contact-id]');
    if (contactIdElement) {
        const contactId = contactIdElement.dataset.contactId;
        setupNoteForm(contactId);
    }
});
