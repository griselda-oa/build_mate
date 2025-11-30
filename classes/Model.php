<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Base Model class
 */
abstract class Model
{
    protected PDO $db;
    protected string $table;
    
    public function __construct()
    {
        $this->db = DB::getInstance();
    }
    
    /**
     * Find record by ID
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Find all records
     */
    public function findAll(string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy}";
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Create record
     */
    public function create(array $data): int
    {
        // Safeguard: Truncate image_url if it exists and is too long
        // Check actual column size dynamically
        if (isset($data['image_url']) && is_string($data['image_url']) && $this->table === 'products') {
            $maxLength = 450; // Default safe for VARCHAR(500)
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM {$this->table} WHERE Field = 'image_url'");
                $column = $stmt->fetch();
                if ($column) {
                    $type = strtolower($column['Type'] ?? '');
                    if (strpos($type, 'varchar') !== false) {
                        preg_match('/varchar\((\d+)\)/', $type, $matches);
                        if (!empty($matches[1])) {
                            $maxLength = (int)$matches[1] - 50; // Leave 50 char buffer
                        }
                    } elseif (strpos($type, 'text') !== false) {
                        $maxLength = 2000; // TEXT can handle much longer
                    }
                }
            } catch (\Exception $e) {
                // Use default if check fails
            }
            
            if (strlen($data['image_url']) > $maxLength) {
                // Try to preserve file extension
                $extension = '';
                if (preg_match('/\.(jpg|jpeg|png|gif|webp)(\?|$)/i', $data['image_url'], $extMatches)) {
                    $extension = $extMatches[0];
                }
                $truncated = substr($data['image_url'], 0, $maxLength - strlen($extension)) . $extension;
                $data['image_url'] = $truncated;
                error_log("Warning: image_url truncated to {$maxLength} characters");
            }
        }
        
        $fields = array_keys($data);
        $placeholders = array_map(fn($f) => ":$f", $fields);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        
        return (int)$this->db->lastInsertId();
    }
    
    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        // Safeguard: Truncate image_url if it exists and is too long
        // Check actual column size dynamically
        if (isset($data['image_url']) && is_string($data['image_url']) && $this->table === 'products') {
            $maxLength = 450; // Default safe for VARCHAR(500)
            try {
                $stmt = $this->db->query("SHOW COLUMNS FROM {$this->table} WHERE Field = 'image_url'");
                $column = $stmt->fetch();
                if ($column) {
                    $type = strtolower($column['Type'] ?? '');
                    if (strpos($type, 'varchar') !== false) {
                        preg_match('/varchar\((\d+)\)/', $type, $matches);
                        if (!empty($matches[1])) {
                            $maxLength = (int)$matches[1] - 50; // Leave 50 char buffer
                        }
                    } elseif (strpos($type, 'text') !== false) {
                        $maxLength = 2000; // TEXT can handle much longer
                    }
                }
            } catch (\Exception $e) {
                // Use default if check fails
            }
            
            if (strlen($data['image_url']) > $maxLength) {
                // Try to preserve file extension
                $extension = '';
                if (preg_match('/\.(jpg|jpeg|png|gif|webp)(\?|$)/i', $data['image_url'], $extMatches)) {
                    $extension = $extMatches[0];
                }
                $truncated = substr($data['image_url'], 0, $maxLength - strlen($extension)) . $extension;
                $data['image_url'] = $truncated;
                error_log("Warning: image_url truncated to {$maxLength} characters");
            }
        }
        
        $fields = array_keys($data);
        $set = array_map(fn($f) => "$f = :$f", $fields);
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE id = :id";
        
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        
        try {
            $result = $stmt->execute($data);
            if (!$result) {
                $errorInfo = $stmt->errorInfo();
                error_log("Model::update() failed for table {$this->table}, id {$id}: " . print_r($errorInfo, true));
                error_log("SQL: {$sql}");
                error_log("Data: " . print_r($data, true));
            }
            return $result;
        } catch (\PDOException $e) {
            error_log("PDO Exception in Model::update() for table {$this->table}, id {$id}: " . $e->getMessage());
            error_log("SQL: {$sql}");
            error_log("Data: " . print_r($data, true));
            throw $e;
        }
    }
    
    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

