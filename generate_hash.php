<?php
// Generate password hash for schema.sql
$password = 'password123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Password hash for 'password123':\n";
echo $hash;
?>
