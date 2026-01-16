<?php
namespace Controllers;

use Helpers\Flash;
use Helpers\Sanitizer;

/**
 * Master Data Controller
 * Generic controller for managing all lookup/master data tables
 * 
 * @version 1.2.0
 */
class MasterDataController extends BaseController {
    
    private $config;
    private $currentType;
    private $currentConfig;
    private $model;
    
    public function __construct() {
        parent::__construct();
        
        // Admin only access
        $this->requireRole('admin');
        
        // Load master data configuration
        $this->config = require ROOT_PATH . '/config/masterdata.php';
    }
    
    /**
     * Master Data Dashboard
     * Shows all master data types
     */
    public function index() {
        $this->view('masterdata.index', [
            'title' => 'Master Data Management',
            'masterDataTypes' => $this->config
        ]);
    }
    
    /**
     * List all records for a master data type
     * 
     * @param string $type - Master data type key
     */
    public function list($type) {
        $this->loadType($type);
        
        // Get filter parameters
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $perPage = $_GET['per_page'] ?? 25;
        
        // Get data from model
        $result = $this->model->paginate(
            $page,
            $perPage,
            $search,
            $this->currentConfig['searchable'] ?? ['name']
        );
        
        // Add specialty names for surgeries
        if ($type === 'surgeries') {
            $specialtyModel = new \Models\LookupSpecialty();
            $specialties = $specialtyModel->getDropdownOptions(false);
            
            foreach ($result['data'] as &$item) {
                $item['specialty'] = $specialties[$item['specialty_id']] ?? 'N/A';
            }
        }
        
        // Add surgery count for specialties
        if ($type === 'specialties') {
            $result['data'] = $this->model->getAllWithSurgeryCount();
        }
        
        $this->view('masterdata.list', [
            'title' => $this->currentConfig['label'],
            'type' => $type,
            'config' => $this->currentConfig,
            'items' => $result['data'],
            'pagination' => $result,
            'search' => $search
        ]);
    }
    
    /**
     * Show create form
     * 
     * @param string $type - Master data type key
     */
    public function create($type) {
        $this->loadType($type);
        
        // Get foreign key options if needed
        $foreignOptions = $this->getForeignOptions();
        
        $this->view('masterdata.form', [
            'title' => 'Add New ' . rtrim($this->currentConfig['label'], 's'),
            'type' => $type,
            'config' => $this->currentConfig,
            'item' => null,
            'foreignOptions' => $foreignOptions,
            'action' => 'create'
        ]);
    }
    
    /**
     * Store new record
     * 
     * @param string $type - Master data type key
     */
    public function store($type) {
        $this->loadType($type);
        $this->validateCSRF();
        
        // Validate and sanitize input
        $data = $this->validateInput($_POST);
        
        if (empty($data['errors'])) {
            // Create record
            try {
                $id = $this->model->create($data['values']);
                
                if ($id) {
                    Flash::success($this->currentConfig['label'] . ' created successfully');
                    $this->redirect("/masterdata/list/{$type}");
                } else {
                    Flash::error('Failed to create record');
                    $this->redirect("/masterdata/create/{$type}");
                }
            } catch (\PDOException $e) {
                // Handle duplicate entry or other database errors
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    Flash::error('This name already exists. Please use a different name.');
                } else {
                    Flash::error('Database error: ' . $e->getMessage());
                }
                $this->redirect("/masterdata/create/{$type}");
            }
        } else {
            // Validation errors
            foreach ($data['errors'] as $error) {
                Flash::error($error);
            }
            $this->redirect("/masterdata/create/{$type}");
        }
    }
    
    /**
     * Show edit form
     * 
     * @param string $type - Master data type key
     * @param int $id - Record ID
     */
    public function edit($type, $id) {
        $this->loadType($type);
        
        $item = $this->model->find($id);
        
        if (!$item) {
            Flash::error('Record not found');
            $this->redirect("/masterdata/list/{$type}");
        }
        
        // Get foreign key options if needed
        $foreignOptions = $this->getForeignOptions();
        
        $this->view('masterdata.form', [
            'title' => 'Edit ' . rtrim($this->currentConfig['label'], 's'),
            'type' => $type,
            'config' => $this->currentConfig,
            'item' => $item,
            'foreignOptions' => $foreignOptions,
            'action' => 'edit'
        ]);
    }
    
    /**
     * Update existing record
     * 
     * @param string $type - Master data type key
     * @param int $id - Record ID
     */
    public function update($type, $id) {
        $this->loadType($type);
        $this->validateCSRF();
        
        // Check record exists
        $existing = $this->model->find($id);
        if (!$existing) {
            Flash::error('Record not found');
            $this->redirect("/masterdata/list/{$type}");
        }
        
        // Validate and sanitize input
        $data = $this->validateInput($_POST, $id);
        
        if (empty($data['errors'])) {
            // Update record
            try {
                $success = $this->model->update($id, $data['values']);
                
                if ($success) {
                    Flash::success($this->currentConfig['label'] . ' updated successfully');
                    $this->redirect("/masterdata/list/{$type}");
                } else {
                    Flash::error('Failed to update record');
                    $this->redirect("/masterdata/edit/{$type}/{$id}");
                }
            } catch (\PDOException $e) {
                // Handle duplicate entry or other database errors
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    Flash::error('This name already exists. Please use a different name.');
                } else {
                    Flash::error('Database error: ' . $e->getMessage());
                }
                $this->redirect("/masterdata/edit/{$type}/{$id}");
            }
        } else {
            // Validation errors
            foreach ($data['errors'] as $error) {
                Flash::error($error);
            }
            $this->redirect("/masterdata/edit/{$type}/{$id}");
        }
    }
    
    /**
     * Delete record (soft delete)
     * 
     * @param string $type - Master data type key
     * @param int $id - Record ID
     */
    public function delete($type, $id) {
        $this->loadType($type);
        $this->validateCSRF();
        
        $success = $this->model->delete($id);
        
        if ($success) {
            Flash::success($this->currentConfig['label'] . ' deleted successfully');
        } else {
            Flash::error('Failed to delete record. It may have related data.');
        }
        
        $this->redirect("/masterdata/list/{$type}");
    }
    
    /**
     * Toggle active status (AJAX)
     * 
     * @param string $type - Master data type key
     * @param int $id - Record ID
     */
    public function toggleActive($type, $id) {
        $this->loadType($type);
        
        $success = $this->model->toggleActive($id);
        
        $this->json([
            'success' => $success,
            'message' => $success ? 'Status updated' : 'Failed to update status'
        ]);
    }
    
    /**
     * Update sort order (AJAX)
     * 
     * @param string $type - Master data type key
     */
    public function reorder($type) {
        $this->loadType($type);
        
        if (!$this->currentConfig['sortable']) {
            $this->json(['success' => false, 'message' => 'Sorting not supported'], 400);
        }
        
        // Get JSON input
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $order = $data['order'] ?? $_POST['order'] ?? [];
        
        if (empty($order)) {
            $this->json(['success' => false, 'message' => 'No order data received'], 400);
        }
        
        $success = $this->model->updateSortOrder($order);
        
        $this->json([
            'success' => $success,
            'message' => $success ? 'Order updated' : 'Failed to update order'
        ]);
    }
    
    /**
     * Export to CSV
     * 
     * @param string $type - Master data type key
     */
    public function export($type) {
        $this->loadType($type);
        
        if (!$this->currentConfig['export']) {
            Flash::error('Export not supported for this data type');
            $this->redirect("/masterdata/list/{$type}");
        }
        
        $columns = $this->currentConfig['list_columns'] ?? ['name', 'active', 'created_at'];
        $csv = $this->model->exportToCsv($columns);
        
        // Set headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $type . '_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $csv;
        exit;
    }
    
    /**
     * Get surgeries by specialty (AJAX)
     * For dynamic dropdown filtering
     * 
     * @param int $specialtyId - Specialty ID
     */
    public function getSurgeriesBySpecialty($specialtyId) {
        $surgeryModel = new \Models\LookupSurgery();
        $surgeries = $surgeryModel->getBySpecialty($specialtyId);
        
        $this->json([
            'success' => true,
            'data' => $surgeries
        ]);
    }
    
    /**
     * Quick add modal (AJAX)
     * 
     * @param string $type - Master data type key
     */
    public function quickAdd($type) {
        $this->loadType($type);
        $this->validateCSRF();
        
        // Only accept name field for quick add
        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            $this->json(['success' => false, 'message' => 'Name is required'], 400);
        }
        
        // Check if already exists
        if (!$this->model->isUnique('name', $name)) {
            $this->json(['success' => false, 'message' => 'This name already exists'], 400);
        }
        
        // Create with default values
        $data = [
            'name' => Sanitizer::string($name),
            'active' => 1,
            'sort_order' => 0
        ];
        
        // Add specialty_id for surgeries
        if ($type === 'surgeries' && !empty($_POST['specialty_id'])) {
            $data['specialty_id'] = (int)$_POST['specialty_id'];
        }
        
        $id = $this->model->create($data);
        
        if ($id) {
            $item = $this->model->find($id);
            $this->json([
                'success' => true,
                'message' => 'Created successfully',
                'data' => $item
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create'], 500);
        }
    }
    
    // ========================================================================
    // PRIVATE HELPER METHODS
    // ========================================================================
    
    /**
     * Load and validate master data type
     * 
     * @param string $type - Master data type key
     */
    private function loadType($type) {
        if (!isset($this->config[$type])) {
            Flash::error('Invalid master data type');
            $this->redirect('/masterdata/index');
        }
        
        $this->currentType = $type;
        $this->currentConfig = $this->config[$type];
        
        // Load model
        $modelClass = '\\Models\\' . $this->currentConfig['model'];
        if (!class_exists($modelClass)) {
            Flash::error('Model not found: ' . $modelClass);
            $this->redirect('/masterdata/index');
        }
        
        $this->model = new $modelClass();
    }
    
    /**
     * Validate and sanitize form input
     * 
     * @param array $input - POST data
     * @param int|null $id - Record ID (for updates)
     * @return array - ['values' => [], 'errors' => []]
     */
    private function validateInput($input, $id = null) {
        $values = [];
        $errors = [];
        
        foreach ($this->currentConfig['fields'] as $field => $config) {
            $value = $input[$field] ?? null;
            
            // Handle checkbox fields
            if ($config['type'] === 'checkbox') {
                $value = isset($input[$field]) ? 1 : 0;
            }
            
            // Required field validation
            if (!empty($config['required']) && empty($value) && $value !== '0') {
                $errors[] = ($config['label'] ?? ucfirst($field)) . ' is required';
                continue;
            }
            
            // Skip if not required and empty
            if (empty($value) && $value !== '0' && empty($config['required'])) {
                // Use default value if specified
                if (isset($config['default'])) {
                    $values[$field] = $config['default'];
                }
                continue;
            }
            
            // Type-specific validation and sanitization
            switch ($config['type']) {
                case 'text':
                    $value = Sanitizer::string($value);
                    
                    // Check unique constraint
                    if (!empty($config['unique'])) {
                        if (!$this->model->isUnique($field, $value, $id)) {
                            $errors[] = ($config['label'] ?? ucfirst($field)) . ' already exists';
                        }
                    }
                    
                    // Validate pattern
                    if (!empty($config['pattern'])) {
                        if (!preg_match('/' . $config['pattern'] . '/', $value)) {
                            $errors[] = ($config['label'] ?? ucfirst($field)) . ' format is invalid';
                        }
                    }
                    
                    $values[$field] = $value;
                    break;
                    
                case 'textarea':
                    $values[$field] = Sanitizer::string($value);
                    break;
                    
                case 'number':
                    $values[$field] = is_numeric($value) ? $value : 0;
                    break;
                    
                case 'select':
                    // Validate against allowed options
                    if (isset($config['options']) && !isset($config['options'][$value])) {
                        if (empty($config['foreign'])) {
                            $errors[] = 'Invalid value for ' . ($config['label'] ?? ucfirst($field));
                        }
                    }
                    $values[$field] = Sanitizer::string($value);
                    break;
                    
                case 'checkbox':
                    $values[$field] = (int)$value;
                    break;
                    
                default:
                    $values[$field] = Sanitizer::string($value);
            }
        }
        
        return [
            'values' => $values,
            'errors' => $errors
        ];
    }
    
    /**
     * Get foreign key dropdown options
     * 
     * @return array
     */
    private function getForeignOptions() {
        $options = [];
        
        foreach ($this->currentConfig['fields'] as $field => $config) {
            if ($config['type'] === 'select' && !empty($config['foreign'])) {
                $foreignTable = $config['foreign'];
                $foreignModel = $this->getForeignModel($foreignTable);
                
                if ($foreignModel) {
                    $options[$field] = $foreignModel->getDropdownOptions(false);
                }
            }
        }
        
        return $options;
    }
    
    /**
     * Get foreign model instance
     * 
     * @param string $table - Foreign table name
     * @return object|null
     */
    private function getForeignModel($table) {
        // Map table names to model classes
        $modelMap = [
            'lookup_specialties' => '\\Models\\LookupSpecialty',
            'lookup_surgeries' => '\\Models\\LookupSurgery',
            'lookup_comorbidities' => '\\Models\\LookupComorbidity',
            'lookup_drugs' => '\\Models\\LookupDrug',
            'lookup_adjuvants' => '\\Models\\LookupAdjuvant',
            'lookup_red_flags' => '\\Models\\LookupRedFlag',
            'lookup_catheter_indications' => '\\Models\\LookupCatheterIndication',
            'lookup_removal_indications' => '\\Models\\LookupRemovalIndication',
            'lookup_sentinel_events' => '\\Models\\LookupSentinelEvent'
        ];
        
        if (isset($modelMap[$table])) {
            $modelClass = $modelMap[$table];
            return new $modelClass();
        }
        
        return null;
    }
    
    // ========================================================================
    // CHILD MANAGEMENT METHODS (for parent-child relationships like specialties-surgeries)
    // ========================================================================
    
    /**
     * Manage children records (e.g., surgeries under a specialty)
     * 
     * @param string $parentType - Parent master data type key
     * @param int $parentId - Parent record ID
     */
    public function manageChildren($parentType, $parentId) {
        $this->loadType($parentType);
        
        // Verify parent has children defined
        if (!isset($this->currentConfig['has_children'])) {
            Flash::error('This data type does not have child records');
            $this->redirect("/masterdata/list/{$parentType}");
        }
        
        // Get parent record
        $parent = $this->model->find($parentId);
        if (!$parent) {
            Flash::error('Parent record not found');
            $this->redirect("/masterdata/list/{$parentType}");
        }
        
        // Get child configuration
        $childTable = $this->currentConfig['has_children']['table'];
        $childType = str_replace('lookup_', '', $childTable);
        $childType = str_replace('_', '', $childType); // Remove underscores for config key
        
        // Load child configuration
        if (!isset($this->config[$childType])) {
            Flash::error('Child configuration not found');
            $this->redirect("/masterdata/list/{$parentType}");
        }
        
        $childConfig = $this->config[$childType];
        
        // Get child model
        $childModel = $this->getForeignModel($childTable);
        if (!$childModel) {
            Flash::error('Child model not found');
            $this->redirect("/masterdata/list/{$parentType}");
        }
        
        // Get all children for this parent
        $foreignKey = $this->currentConfig['has_children']['foreign_key'];
        $children = $childModel->getBy($foreignKey, $parentId);
        
        $this->view('masterdata.manage-children', [
            'parent' => $parent,
            'parentType' => $parentType,
            'parentConfig' => $this->currentConfig,
            'children' => $children,
            'childType' => $childType,
            'childConfig' => $childConfig,
            'foreignKey' => $foreignKey
        ]);
    }
    
    /**
     * Store new child record
     * 
     * @param string $parentType - Parent master data type key
     * @param int $parentId - Parent record ID
     */
    public function storeChild($parentType, $parentId) {
        $this->loadType($parentType);
        $this->validateCSRF();
        
        // Get child configuration
        $childTable = $this->currentConfig['has_children']['table'];
        $childType = str_replace('lookup_', '', $childTable);
        $childType = str_replace('_', '', $childType);
        $childConfig = $this->config[$childType];
        
        // Get child model
        $childModel = $this->getForeignModel($childTable);
        
        // Prepare data
        $data = $_POST;
        $data['active'] = 1;
        $data['sort_order'] = 0;
        
        try {
            $id = $childModel->create($data);
            
            if ($id) {
                Flash::success($childConfig['label'] . ' added successfully');
            } else {
                Flash::error('Failed to add ' . $childConfig['label']);
            }
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                Flash::error('This name already exists. Please use a different name.');
            } else {
                Flash::error('Database error: ' . $e->getMessage());
            }
        }
        
        $this->redirect("/masterdata/manageChildren/{$parentType}/{$parentId}");
    }
    
    /**
     * Update child record
     * 
     * @param string $childType - Child master data type key
     * @param int $childId - Child record ID
     */
    public function updateChild($childType, $childId) {
        $this->validateCSRF();
        
        // Get child model
        $childTable = 'lookup_' . $childType;
        $childModel = $this->getForeignModel($childTable);
        
        if (!$childModel) {
            Flash::error('Child model not found');
            $this->redirect('/masterdata/index');
        }
        
        // Get current record to find parent
        $child = $childModel->find($childId);
        if (!$child) {
            Flash::error('Record not found');
            $this->redirect('/masterdata/index');
        }
        
        // Update data
        $data = [
            'name' => Sanitizer::string($_POST['name'] ?? ''),
            'description' => Sanitizer::string($_POST['description'] ?? '')
        ];
        
        try {
            $success = $childModel->update($childId, $data);
            
            if ($success) {
                Flash::success('Updated successfully');
            } else {
                Flash::error('Failed to update');
            }
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                Flash::error('This name already exists. Please use a different name.');
            } else {
                Flash::error('Database error: ' . $e->getMessage());
            }
        }
        
        // Redirect back to manage children page
        // Find parent type and ID from child record
        $parentType = $this->findParentType($childType);
        $parentIdField = $this->findParentIdField($childType);
        $parentId = $child[$parentIdField] ?? null;
        
        if ($parentType && $parentId) {
            $this->redirect("/masterdata/manageChildren/{$parentType}/{$parentId}");
        } else {
            $this->redirect("/masterdata/index");
        }
    }
    
    /**
     * Delete child record
     * 
     * @param string $childType - Child master data type key
     * @param int $childId - Child record ID
     */
    public function deleteChild($childType, $childId) {
        $this->validateCSRF();
        
        // Get child model
        $childTable = 'lookup_' . $childType;
        $childModel = $this->getForeignModel($childTable);
        
        if (!$childModel) {
            Flash::error('Child model not found');
            $this->redirect('/masterdata/index');
        }
        
        // Get current record to find parent
        $child = $childModel->find($childId);
        if (!$child) {
            Flash::error('Record not found');
            $this->redirect('/masterdata/index');
        }
        
        // Delete
        $success = $childModel->delete($childId);
        
        if ($success) {
            Flash::success('Deleted successfully');
        } else {
            Flash::error('Failed to delete');
        }
        
        // Redirect back to manage children page
        $parentType = $this->findParentType($childType);
        $parentIdField = $this->findParentIdField($childType);
        $parentId = $child[$parentIdField] ?? null;
        
        if ($parentType && $parentId) {
            $this->redirect("/masterdata/manageChildren/{$parentType}/{$parentId}");
        } else {
            $this->redirect("/masterdata/index");
        }
    }
    
    /**
     * Toggle child active status (AJAX)
     * 
     * @param string $childType - Child master data type key
     * @param int $childId - Child record ID
     */
    public function toggleChildActive($childType, $childId) {
        // Get child model
        $childTable = 'lookup_' . $childType;
        $childModel = $this->getForeignModel($childTable);
        
        if (!$childModel) {
            $this->json(['success' => false, 'message' => 'Model not found'], 404);
        }
        
        $success = $childModel->toggleActive($childId);
        
        $this->json([
            'success' => $success,
            'message' => $success ? 'Status updated' : 'Failed to update status'
        ]);
    }
    
    /**
     * Reorder children (AJAX)
     * 
     * @param string $childType - Child master data type key
     */
    public function reorderChildren($childType) {
        // Get child model
        $childTable = 'lookup_' . $childType;
        $childModel = $this->getForeignModel($childTable);
        
        if (!$childModel) {
            error_log("reorderChildren: Model not found for table: {$childTable}");
            $this->json(['success' => false, 'message' => 'Model not found'], 404);
            return;
        }
        
        // Get JSON input
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        $order = $data['order'] ?? [];
        
        error_log("reorderChildren: Received order data: " . print_r($order, true));
        
        if (empty($order)) {
            error_log("reorderChildren: Empty order data received");
            $this->json(['success' => false, 'message' => 'No order data received'], 400);
            return;
        }
        
        $success = $childModel->updateSortOrder($order);
        
        error_log("reorderChildren: updateSortOrder result: " . ($success ? 'true' : 'false'));
        
        $this->json([
            'success' => $success,
            'message' => $success ? 'Order updated' : 'Failed to update order'
        ]);
    }
    
    /**
     * Find parent type from child type
     * 
     * @param string $childType
     * @return string|null
     */
    private function findParentType($childType) {
        foreach ($this->config as $type => $config) {
            if (isset($config['has_children']) && 
                strpos($config['has_children']['table'], $childType) !== false) {
                return $type;
            }
        }
        return null;
    }
    
    /**
     * Find parent ID field from child type
     * 
     * @param string $childType
     * @return string|null
     */
    private function findParentIdField($childType) {
        $parentType = $this->findParentType($childType);
        if ($parentType && isset($this->config[$parentType]['has_children'])) {
            return $this->config[$parentType]['has_children']['foreign_key'];
        }
        return null;
    }
}
