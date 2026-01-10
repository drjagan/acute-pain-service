-- Sample users for testing all 4 roles
-- Password for all: admin123 (hashed with bcrypt cost 12)
-- Hash: $2y$12$uyIAZsL4jfqqBqGm2zdwre67APUvCaXybNMN/0xQrh4fDw1ESxL9y

-- Admin user
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, created_at) VALUES
('admin', 'admin@hospital.com', '$2y$12$uyIAZsL4jfqqBqGm2zdwre67APUvCaXybNMN/0xQrh4fDw1ESxL9y', 'System', 'Administrator', 'admin', 'active', NOW());

-- Attending Physician
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, created_at) VALUES
('dr.sharma', 'sharma@hospital.com', '$2y$12$uyIAZsL4jfqqBqGm2zdwre67APUvCaXybNMN/0xQrh4fDw1ESxL9y', 'Rajesh', 'Sharma', 'attending', 'active', NOW());

-- Senior Resident
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, created_at) VALUES
('dr.patel', 'patel@hospital.com', '$2y$12$uyIAZsL4jfqqBqGm2zdwre67APUvCaXybNMN/0xQrh4fDw1ESxL9y', 'Priya', 'Patel', 'resident', 'active', NOW());

-- Nurse
INSERT INTO users (username, email, password_hash, first_name, last_name, role, status, created_at) VALUES
('nurse.kumar', 'kumar@hospital.com', '$2y$12$uyIAZsL4jfqqBqGm2zdwre67APUvCaXybNMN/0xQrh4fDw1ESxL9y', 'Anjali', 'Kumar', 'nurse', 'active', NOW());
