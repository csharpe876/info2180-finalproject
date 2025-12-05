<?php
require_once 'config.php';

try {
    // Read and execute schema.sql without the INSERT statement
    $schema = file_get_contents('schema.sql');
    
    // Remove the existing INSERT statement from schema
    $schema = preg_replace('/-- Insert admin user.*?VALUES.*?;/s', '', $schema);
    
    // Execute schema
    $conn->exec($schema);
    
    // Now insert admin user with properly hashed password
    $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO Users (firstname, lastname, password, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['Admin', 'User', $hashedPassword, 'admin@project2.com', 'Admin']);
    
    echo "Database setup completed successfully!<br>";
    echo "You can now login with:<br>";
    echo "Email: admin@project2.com<br>";
    echo "Password: password123<br>";
    echo "<br><a href='index.php'>Go to Login Page</a>";
    
} catch(PDOException $e) {
    echo "Setup failed: " . $e->getMessage();
}
?>
