-- Users table for authentication and RBAC
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
    -- Authentication
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    
    -- Personal Details
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NULL,
    
    -- Role & Status
    role ENUM('attending', 'resident', 'nurse', 'admin') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    
    -- Remember Me
    remember_token VARCHAR(255) NULL,
    remember_token_expires DATETIME NULL,
    
    -- Password Reset
    password_reset_token VARCHAR(255) NULL,
    password_reset_expires DATETIME NULL,
    
    -- Session Management
    last_login_at DATETIME NULL,
    last_login_ip VARCHAR(45) NULL,
    session_timeout INT DEFAULT 3600 COMMENT 'seconds',
    
    -- Soft Delete
    deleted_at DATETIME NULL,
    
    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,
    updated_by INT UNSIGNED NULL,
    
    -- Indexes
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_deleted (deleted_at),
    INDEX idx_remember_token (remember_token),
    INDEX idx_reset_token (password_reset_token),
    
    -- Foreign Keys
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
