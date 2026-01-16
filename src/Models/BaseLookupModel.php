<?php
namespace Models;

use PDO;

/**
 * Base Lookup Model
 * Generic model for all lookup/master data tables
 * Provides common CRUD operations for master data management
 * 
 * @version 1.2.0
 */
abstract class BaseLookupModel extends BaseModel {
    
    /**
     * Get all active records ordered by sort_order and name
     * 
     * @param bool $activeOnly - Filter only active records
     * @return array
     */
    public function getActive($activeOnly = true) {
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
        
        if ($activeOnly) {
            $sql .= " AND active = 1";
        }
        
        // Order by sort_order first (if exists), then by name
        if ($this->hasColumn('sort_order')) {
            $sql .= " ORDER BY sort_order ASC, name ASC";
        } else {
            $sql .= " ORDER BY name ASC";
        }
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all records with pagination
     * 
     * @param int $page - Page number
     * @param int $perPage - Records per page
     * @param string $search - Search term
     * @param array $searchColumns - Columns to search in
     * @return array
     */
    public function paginate($page = 1, $perPage = 25, $search = '', $searchColumns = ['name']) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];
        
        // Add search conditions
        if (!empty($search)) {
            $searchConditions = [];
            foreach ($searchColumns as $column) {
                $searchConditions[] = "{$column} LIKE ?";
                $params[] = "%{$search}%";
            }
            $sql .= " AND (" . implode(' OR ', $searchConditions) . ")";
        }
        
        // Add ordering
        if ($this->hasColumn('sort_order')) {
            $sql .= " ORDER BY sort_order ASC, name ASC";
        } else {
            $sql .= " ORDER BY name ASC";
        }
        
        // Add pagination
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return [
            'data' => $stmt->fetchAll(),
            'total' => $this->count($search, $searchColumns),
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($this->count($search, $searchColumns) / $perPage)
        ];
    }
    
    /**
     * Count records with optional search
     * 
     * @param string $search - Search term
     * @param array $searchColumns - Columns to search in
     * @return int
     */
    public function count($search = '', $searchColumns = ['name']) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];
        
        if (!empty($search)) {
            $searchConditions = [];
            foreach ($searchColumns as $column) {
                $searchConditions[] = "{$column} LIKE ?";
                $params[] = "%{$search}%";
            }
            $sql .= " AND (" . implode(' OR ', $searchConditions) . ")";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch()['count'];
    }
    
    /**
     * Toggle active status
     * 
     * @param int $id - Record ID
     * @return bool
     */
    public function toggleActive($id) {
        $record = $this->find($id);
        if (!$record) {
            return false;
        }
        
        $newStatus = !$record['active'];
        return $this->update($id, ['active' => $newStatus]);
    }
    
    /**
     * Update sort order
     * 
     * @param array $order - Array of [id => sort_order]
     * @return bool
     */
    public function updateSortOrder($order) {
        if (!$this->hasColumn('sort_order')) {
            error_log("Table {$this->table} does not have sort_order column");
            return false;
        }
        
        if (empty($order) || !is_array($order)) {
            error_log("Invalid order data received: " . print_r($order, true));
            return false;
        }
        
        try {
            $this->db->beginTransaction();
            
            foreach ($order as $id => $sortOrder) {
                $stmt = $this->db->prepare(
                    "UPDATE {$this->table} SET sort_order = ?, updated_at = NOW() WHERE id = ?"
                );
                $result = $stmt->execute([$sortOrder, $id]);
                if (!$result) {
                    error_log("Failed to update sort_order for id: $id in table: {$this->table}");
                }
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error updating sort order for {$this->table}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get records grouped by a column
     * 
     * @param string $groupColumn - Column to group by
     * @return array
     */
    public function getGrouped($groupColumn) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL AND active = 1";
        
        if ($this->hasColumn('sort_order')) {
            $sql .= " ORDER BY {$groupColumn}, sort_order ASC, name ASC";
        } else {
            $sql .= " ORDER BY {$groupColumn}, name ASC";
        }
        
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll();
        
        // Group results
        $grouped = [];
        foreach ($results as $row) {
            $key = $row[$groupColumn] ?? 'Uncategorized';
            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $row;
        }
        
        return $grouped;
    }
    
    /**
     * Restore soft deleted record
     * 
     * @param int $id - Record ID
     * @return bool
     */
    public function restore($id) {
        $stmt = $this->db->prepare(
            "UPDATE {$this->table} SET deleted_at = NULL, updated_at = NOW() 
             WHERE {$this->primaryKey} = ?"
        );
        return $stmt->execute([$id]);
    }
    
    /**
     * Export to CSV
     * 
     * @param array $columns - Columns to export
     * @return string - CSV content
     */
    public function exportToCsv($columns = ['name', 'active', 'created_at']) {
        $records = $this->getActive(false); // Get all records, including inactive
        
        $csv = [];
        
        // Header row
        $csv[] = array_map('ucfirst', $columns);
        
        // Data rows
        foreach ($records as $record) {
            $row = [];
            foreach ($columns as $column) {
                $value = $record[$column] ?? '';
                
                // Format boolean values
                if (is_bool($value) || $value === '0' || $value === '1') {
                    $value = $value ? 'Yes' : 'No';
                }
                
                // Format dates
                if (in_array($column, ['created_at', 'updated_at']) && $value) {
                    $value = date('Y-m-d H:i:s', strtotime($value));
                }
                
                $row[] = $value;
            }
            $csv[] = $row;
        }
        
        // Convert to CSV string
        $output = fopen('php://temp', 'r+');
        foreach ($csv as $row) {
            fputcsv($output, $row);
        }
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }
    
    /**
     * Check if a column exists in the table
     * 
     * @param string $column - Column name
     * @return bool
     */
    protected function hasColumn($column) {
        static $cache = [];
        
        if (isset($cache[$this->table][$column])) {
            return $cache[$this->table][$column];
        }
        
        // MariaDB/MySQL doesn't support placeholders in SHOW COLUMNS
        // Use direct query with escaped column name
        $escapedColumn = $this->db->quote($column);
        $stmt = $this->db->query("SHOW COLUMNS FROM {$this->table} LIKE {$escapedColumn}");
        $exists = $stmt->fetch() !== false;
        
        $cache[$this->table][$column] = $exists;
        return $exists;
    }
    
    /**
     * Find by code (for tables with code column)
     * 
     * @param string $code - Code value
     * @return array|false
     */
    public function findByCode($code) {
        if (!$this->hasColumn('code')) {
            return false;
        }
        
        return $this->findBy('code', $code);
    }
    
    /**
     * Get dropdown options (id => name pairs)
     * 
     * @param bool $activeOnly - Filter only active records
     * @return array
     */
    public function getDropdownOptions($activeOnly = true) {
        $records = $this->getActive($activeOnly);
        $options = [];
        
        foreach ($records as $record) {
            $options[$record['id']] = $record['name'];
        }
        
        return $options;
    }
    
    /**
     * Validate unique constraint
     * 
     * @param string $column - Column name
     * @param mixed $value - Value to check
     * @param int|null $excludeId - ID to exclude from check (for updates)
     * @return bool - True if unique, false if duplicate
     */
    public function isUnique($column, $value, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE {$column} = ? AND deleted_at IS NULL";
        $params = [$value];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch()['count'] == 0;
    }
    
    /**
     * Get records by column value
     * 
     * @param string $column - Column name
     * @param mixed $value - Value to match
     * @param bool $activeOnly - Filter only active records
     * @return array
     */
    public function getBy($column, $value, $activeOnly = true) {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = ? AND deleted_at IS NULL";
        $params = [$value];
        
        if ($activeOnly) {
            $sql .= " AND active = 1";
        }
        
        // Order by sort_order first (if exists), then by name
        if ($this->hasColumn('sort_order')) {
            $sql .= " ORDER BY sort_order ASC, name ASC";
        } else {
            $sql .= " ORDER BY name ASC";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
