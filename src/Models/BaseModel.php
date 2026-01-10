<?php
namespace Models;

use PDO;

/**
 * Base Model
 * All models extend this class
 */
abstract class BaseModel {
    
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = \Database::getInstance();
    }
    
    /**
     * Find record by ID
     */
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all records
     */
    public function all() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} WHERE deleted_at IS NULL ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Create new record
     */
    public function create($data) {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update record
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $fields = [];
        foreach (array_keys($data) as $key) {
            $fields[] = "{$key} = ?";
        }
        $fieldString = implode(', ', $fields);
        
        $sql = "UPDATE {$this->table} SET {$fieldString} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        
        $values = array_values($data);
        $values[] = $id;
        
        return $stmt->execute($values);
    }
    
    /**
     * Soft delete record
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET deleted_at = NOW() WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Hard delete record
     */
    public function forceDelete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Find by column value
     */
    public function findBy($column, $value) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ? AND deleted_at IS NULL");
        $stmt->execute([$value]);
        return $stmt->fetch();
    }
    
    /**
     * Find all by column value
     */
    public function findAllBy($column, $value) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$column} = ? AND deleted_at IS NULL ORDER BY created_at DESC");
        $stmt->execute([$value]);
        return $stmt->fetchAll();
    }
    
    /**
     * Count records
     */
    public function count() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL");
        return $stmt->fetch()['count'];
    }
    
    /**
     * Execute raw query
     */
    protected function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
