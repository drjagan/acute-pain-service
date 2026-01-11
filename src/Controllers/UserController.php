<?php
namespace Controllers;

use Models\User;
use Helpers\Flash;

/**
 * User Management Controller
 * Admin-only functionality for managing system users
 */
class UserController extends BaseController {
    
    protected $userModel;
    
    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }
    
    /**
     * List all users (Admin only)
     */
    public function index() {
        $this->requireAuth();
        $this->requireRole('admin');
        
        // Get search parameters
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 20;
        
        // Build query
        $sql = "SELECT * FROM users WHERE deleted_at IS NULL";
        $params = [];
        
        if ($search) {
            $sql .= " AND (username LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM users WHERE deleted_at IS NULL";
        if ($search || $role || $status) {
            $countStmt = $this->db->prepare(str_replace("SELECT * FROM users WHERE deleted_at IS NULL", "SELECT COUNT(*) as total FROM users WHERE deleted_at IS NULL", $sql));
            $countStmt->execute($params);
            $total = $countStmt->fetch()['total'];
        } else {
            $stmt = $this->db->query($countSql);
            $total = $stmt->fetch()['total'];
        }
        
        // Get paginated results
        $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = ($page - 1) * $perPage;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();
        
        $this->view('users/index', [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage),
            'search' => $search,
            'roleFilter' => $role,
            'statusFilter' => $status
        ]);
    }
    
    /**
     * Show create user form
     */
    public function create() {
        $this->requireAuth();
        $this->requireRole('admin');
        
        $this->view('users/create');
    }
    
    /**
     * Store new user
     */
    public function store() {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->validateCSRF();
        
        // Validate input
        $validation = $this->validateUserData($_POST);
        if (!$validation['valid']) {
            Flash::error($validation['message']);
            return $this->redirect('/users/create');
        }
        
        // Check if username exists
        if ($this->userModel->findByUsername($_POST['username'])) {
            Flash::error('Username already exists.');
            return $this->redirect('/users/create');
        }
        
        // Check if email exists
        if ($this->userModel->findByEmail($_POST['email'])) {
            Flash::error('Email already exists.');
            return $this->redirect('/users/create');
        }
        
        // Create user
        $data = [
            'username' => trim($_POST['username']),
            'email' => trim($_POST['email']),
            'password_hash' => $this->userModel->hashPassword($_POST['password']),
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => $_POST['role'],
            'status' => $_POST['status'] ?? 'active',
            'created_by' => $this->user()['id'],
            'updated_by' => $this->user()['id']
        ];
        
        $userId = $this->userModel->create($data);
        
        if ($userId) {
            Flash::success('User created successfully.');
            return $this->redirect('/users');
        } else {
            Flash::error('Failed to create user.');
            return $this->redirect('/users/create');
        }
    }
    
    /**
     * Show edit user form
     */
    public function edit($id) {
        $this->requireAuth();
        $this->requireRole('admin');
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            Flash::error('User not found.');
            return $this->redirect('/users');
        }
        
        $this->view('users/edit', ['user' => $user]);
    }
    
    /**
     * Update user
     */
    public function update($id) {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->validateCSRF();
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            Flash::error('User not found.');
            return $this->redirect('/users');
        }
        
        // Validate input (without password requirement for update)
        $validation = $this->validateUserData($_POST, $id, false);
        if (!$validation['valid']) {
            Flash::error($validation['message']);
            return $this->redirect('/users/edit/' . $id);
        }
        
        // Check if email exists for another user
        $existingUser = $this->userModel->findByEmail($_POST['email']);
        if ($existingUser && $existingUser['id'] != $id) {
            Flash::error('Email already exists for another user.');
            return $this->redirect('/users/edit/' . $id);
        }
        
        // Update user
        $data = [
            'email' => trim($_POST['email']),
            'first_name' => trim($_POST['first_name']),
            'last_name' => trim($_POST['last_name']),
            'phone' => trim($_POST['phone'] ?? ''),
            'role' => $_POST['role'],
            'status' => $_POST['status'] ?? 'active',
            'updated_by' => $this->user()['id']
        ];
        
        // Update password if provided
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                Flash::error('Password must be at least 6 characters.');
                return $this->redirect('/users/edit/' . $id);
            }
            $data['password_hash'] = $this->userModel->hashPassword($_POST['password']);
        }
        
        $updated = $this->userModel->update($id, $data);
        
        if ($updated) {
            Flash::success('User updated successfully.');
            return $this->redirect('/users');
        } else {
            Flash::error('Failed to update user.');
            return $this->redirect('/users/edit/' . $id);
        }
    }
    
    /**
     * Delete user (soft delete)
     */
    public function delete($id) {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->validateCSRF();
        
        // Prevent deleting self
        if ($id == $this->user()['id']) {
            Flash::error('You cannot delete your own account.');
            return $this->redirect('/users');
        }
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            Flash::error('User not found.');
            return $this->redirect('/users');
        }
        
        $deleted = $this->userModel->delete($id);
        
        if ($deleted) {
            Flash::success('User deleted successfully.');
        } else {
            Flash::error('Failed to delete user.');
        }
        
        return $this->redirect('/users');
    }
    
    /**
     * Toggle user status
     */
    public function toggleStatus($id) {
        $this->requireAuth();
        $this->requireRole('admin');
        $this->validateCSRF();
        
        // Prevent toggling own status
        if ($id == $this->user()['id']) {
            Flash::error('You cannot change your own status.');
            return $this->redirect('/users');
        }
        
        $user = $this->userModel->find($id);
        
        if (!$user) {
            Flash::error('User not found.');
            return $this->redirect('/users');
        }
        
        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
        
        $updated = $this->userModel->update($id, ['status' => $newStatus]);
        
        if ($updated) {
            Flash::success('User status updated to ' . $newStatus . '.');
        } else {
            Flash::error('Failed to update user status.');
        }
        
        return $this->redirect('/users');
    }
    
    /**
     * Validate user data
     */
    private function validateUserData($data, $userId = null, $requirePassword = true) {
        $errors = [];
        
        // Username (only for new users)
        if ($userId === null) {
            if (empty($data['username'])) {
                $errors[] = 'Username is required';
            } elseif (strlen($data['username']) < 3) {
                $errors[] = 'Username must be at least 3 characters';
            } elseif (strlen($data['username']) > 50) {
                $errors[] = 'Username must not exceed 50 characters';
            }
        }
        
        // Email
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Password (only required for new users)
        if ($requirePassword) {
            if (empty($data['password'])) {
                $errors[] = 'Password is required';
            } elseif (strlen($data['password']) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }
        }
        
        // First name
        if (empty($data['first_name'])) {
            $errors[] = 'First name is required';
        }
        
        // Last name
        if (empty($data['last_name'])) {
            $errors[] = 'Last name is required';
        }
        
        // Role
        if (empty($data['role'])) {
            $errors[] = 'Role is required';
        } elseif (!in_array($data['role'], ['attending', 'resident', 'nurse', 'admin'])) {
            $errors[] = 'Invalid role selected';
        }
        
        // Status
        if (isset($data['status']) && !in_array($data['status'], ['active', 'inactive', 'suspended'])) {
            $errors[] = 'Invalid status selected';
        }
        
        return [
            'valid' => empty($errors),
            'message' => implode('<br>', $errors)
        ];
    }
}
