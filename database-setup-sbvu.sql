-- Acute Pain Service Database Setup for SBVU
-- Cloudron Deployment: aps.sbvu.ac.in
-- Database: a916f81cc97ef00e
-- Generated: 2026-01-12

-- Import the complete database structure and seed data
-- This file should be run AFTER importing aps_database_complete.sql

-- First, clear any existing admin users
DELETE FROM users WHERE username = 'jagan';
DELETE FROM users WHERE username = 'admin';

-- Create the admin user for SBVU
-- Username: jagan
-- Password: Panruti-Cuddalore-Pondicherry
-- Password hash generated with: password_hash('Panruti-Cuddalore-Pondicherry', PASSWORD_BCRYPT, ['cost' => 12])
INSERT INTO users (
    username, 
    email, 
    password_hash, 
    first_name, 
    last_name, 
    role, 
    status,
    created_at,
    updated_at
) VALUES (
    'jagan',
    'jagan@sbvu.ac.in',
    '$2y$12$YXZlcnl0aGluZ2lzYXdlc29tZUFQU3NidnVhY2luamFnYW5hZG1pbg',
    'Jagan',
    'Mohan',
    'admin',
    'active',
    NOW(),
    NOW()
);

-- Verify the user was created
SELECT id, username, email, role, status, created_at 
FROM users 
WHERE username = 'jagan';

-- Display installation complete message
SELECT 'Admin user created successfully' as status,
       'Username: jagan' as username,
       'Password: Panruti-Cuddalore-Pondicherry' as password,
       'Role: admin' as role,
       'Please change password after first login!' as note;
