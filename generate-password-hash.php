<?php
/**
 * Generate Password Hash for Admin User
 * Password: Panruti-Cuddalore-Pondicherry
 */

$password = 'Panruti-Cuddalore-Pondicherry';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

echo "Password: $password\n";
echo "Hash: $hash\n\n";

echo "Use this hash in the SQL INSERT statement.\n";
